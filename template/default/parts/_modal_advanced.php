<?php
/**
 * Разширено търсене по модела на COBISS+: до 3 условия, свързани с И / ИЛИ / И НЕ,
 * и ограничения (години от–до, език, вид материал).
 * Заявката се обработва от SLiMS\SearchEngine\BgQueryParser.
 */

use SLiMS\SearchEngine\BgQueryParser;

$bgFields = BgQueryParser::labels();
unset($bgFields['NT'], $bgFields['CN']);
$bgOps = BgQueryParser::opLabels();
$bgDefaults = [1 => 'KW', 2 => 'AU', 3 => 'TI'];
$bgH = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

// само езиците и видовете материал, които наистина се срещат в каталога
$bgLangs = [];
$bgGmds = [];
try {
    $db = \SLiMS\DB::getInstance();
    $q = $db->query("SELECT l.language_id, l.language_name FROM mst_language l WHERE EXISTS (SELECT 1 FROM biblio b WHERE b.language_id = l.language_id) ORDER BY (l.language_id = 'bg') DESC, l.language_name");
    if ($q) $bgLangs = $q->fetchAll(\PDO::FETCH_NUM);
    $q = $db->query("SELECT g.gmd_id, g.gmd_name FROM mst_gmd g WHERE EXISTS (SELECT 1 FROM biblio b WHERE b.gmd_id = g.gmd_id) ORDER BY g.gmd_name");
    if ($q) $bgGmds = $q->fetchAll(\PDO::FETCH_NUM);
} catch (\Throwable $e) {
}
$bgYears = isset($_GET['years']) && preg_match('/^(\d{3,4});(\d{3,4})$/', (string)$_GET['years'], $m) ? [$m[1], $m[2]] : ['', ''];
?>

<div class="modal fade" id="adv-modal" tabindex="-1" role="dialog" aria-labelledby="advSearchLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form class="modal-content" action="index.php" method="get" id="bg-adv-form" novalidate
              data-msg-year="<?= $bgH(__('The year must be a 4-digit number')); ?>"
              data-msg-range="<?= $bgH(__('The start year cannot be after the end year')); ?>"
              data-msg-isbn="<?= $bgH(__('Invalid ISBN – only digits, X, hyphens and spaces are allowed')); ?>"
              data-msg-empty="<?= $bgH(__('Enter at least one search word or limit')); ?>"
              data-ph-year="<?= $bgH(__('e.g. 2019 or 2010-2020')); ?>"
              data-ph-isbn="978-619-7043-60-0"
              data-ph-udc="886.7*"
              data-ph-text="<?= $bgH(__('word or "phrase in quotes"')); ?>">
            <div class="modal-header">
                <h5 class="modal-title" id="advSearchLabel"><?= __('Advanced Search'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= $bgH(__('Close')); ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php for ($i = 1; $i <= 3; $i++):
                    $f = strtoupper((string)($_GET['f' . $i] ?? $bgDefaults[$i]));
                    if (!isset($bgFields[$f])) $f = $bgDefaults[$i];
                    $op = (string)($_GET['op' . $i] ?? 'and');
                    ?>
                    <div class="form-row align-items-center mb-2 bg-adv-row">
                        <div class="col-sm-2 mb-1">
                            <?php if ($i > 1): ?>
                                <select name="op<?= $i; ?>" class="form-control" aria-label="<?= $bgH(__('Operator')); ?>">
                                    <?php foreach ($bgOps as $k => $label): ?>
                                        <option value="<?= $k; ?>"<?= $op === $k ? ' selected' : ''; ?>><?= $bgH($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                        <div class="col-sm-4 mb-1">
                            <select name="f<?= $i; ?>" class="form-control bg-adv-field" aria-label="<?= $bgH(__('Field')); ?>">
                                <?php foreach ($bgFields as $k => $label): ?>
                                    <option value="<?= $k; ?>"<?= $f === $k ? ' selected' : ''; ?>><?= $bgH($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-6 mb-1">
                            <input type="text" name="t<?= $i; ?>" class="form-control bg-adv-term" maxlength="200"
                                   value="<?= $bgH($_GET['t' . $i] ?? ''); ?>" aria-label="<?= $bgH(__('Search term')); ?>">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                <?php endfor; ?>

                <hr>
                <h6><?= __('Limits'); ?></h6>
                <div class="form-row">
                    <div class="col-sm-4 mb-2">
                        <label for="bg-year-from" class="small mb-0"><?= __('Year from / to'); ?></label>
                        <div class="d-flex">
                            <input type="text" id="bg-year-from" class="form-control mr-1" inputmode="numeric" maxlength="4" placeholder="1900" value="<?= $bgH($bgYears[0]); ?>">
                            <input type="text" id="bg-year-to" class="form-control" inputmode="numeric" maxlength="4" placeholder="<?= date('Y'); ?>" value="<?= $bgH($bgYears[1]); ?>">
                        </div>
                        <div class="invalid-feedback d-block" id="bg-year-msg"></div>
                        <input type="hidden" name="years" id="bg-years" value="">
                    </div>
                    <div class="col-sm-4 mb-2">
                        <label for="bg-lang" class="small mb-0"><?= __('Language'); ?></label>
                        <select name="lang" id="bg-lang" class="form-control">
                            <option value=""><?= __('All languages'); ?></option>
                            <?php foreach ($bgLangs as [$id, $name]): ?>
                                <option value="<?= $bgH($id); ?>"<?= ($_GET['lang'] ?? '') === $id ? ' selected' : ''; ?>><?= $bgH($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-4 mb-2">
                        <label for="bg-gmd" class="small mb-0"><?= __('Material type'); ?></label>
                        <select name="gmd" id="bg-gmd" class="form-control">
                            <option value=""><?= __('All types'); ?></option>
                            <?php foreach ($bgGmds as [$id, $name]): ?>
                                <option value="<?= (int)$id; ?>"<?= (string)($_GET['gmd'] ?? '') === (string)$id ? ' selected' : ''; ?>><?= $bgH($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <small class="form-text text-muted">
                    <?= __('* replaces the end of a word (истори* → история, истории); words in quotes are searched as a phrase ("Под игото"); capital and small letters do not matter.'); ?>
                </small>
                <div class="alert alert-danger mt-2 mb-0 d-none" id="bg-adv-error"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="bg-adv-clear"><?= __('Clear'); ?></button>
                <button type="submit" name="search" value="search" class="btn btn-primary"><?= __('Search'); ?></button>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo assets(v('js/bg-advanced-search.js')); ?>"></script>
