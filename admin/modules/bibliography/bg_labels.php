<?php
/**
 * Печат на етикети върху A4 листове със стикери: баркод (инвентарен номер),
 * сигнатурен етикет и комбиниран. Започване от позиция N, брой копия, тестова страница.
 */

use SLiMS\Bg\Db;
use SLiMS\Bg\Kdbf;
use SLiMS\Bg\LabelData;
use SLiMS\Bg\LabelTemplates;
use SLiMS\Bg\PrintSheet;
use SLiMS\Bg\Ui;

define('INDEX_AUTH', '1');
define('DB_ACCESS', 'fa');

if (!defined('SB')) {
    require '../../../sysconfig.inc.php';
}
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';

if (!utility::havePrivilege('bibliography', 'r')) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}
if ((int)$_SESSION['uid'] !== 1 && !utility::haveAccess('bibliography.bg-labels') && !utility::haveAccess('bibliography.item-barcodes-printing') && !utility::haveAccess('bibliography.labels-printing')) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

$self = MWB . 'bibliography/bg_labels.php';
$title = __('Labels and Barcodes (A4)');

/* ------------------------------------------------------------------ настройки */
$defaults = [
    'template' => 'barcode',
    // по подразбиране: обикновена хартия, плътно (контур до контур) за рязане
    'preset' => 'packed',
    'header_text' => trim(($sysconf['library_name'] ?? '') . ' – с. Храбърско'),
    'show_title' => 1,
    'title_len' => 30,
    'show_callnumber' => 0,
    'padding' => 1.5,
    'border' => 1,
    'margin' => 5,
    'rotate' => 1,
    // размер на етикета при плътно нареждане (мм) – по шаблон
    'packed_size' => ['barcode' => [50, 25], 'signature' => [30, 20], 'combined' => [70, 30]],
    'offset_x' => 0,
    'offset_y' => 0,
    'custom' => ['cols' => 3, 'rows' => 8, 'w' => 70, 'h' => 37, 'left' => 0, 'top' => 0.5, 'gapX' => 0, 'gapY' => 0],
];
$saved = @unserialize((string)Db::value("SELECT setting_value FROM setting WHERE setting_name = 'bglabel_print_settings'"));
$cfg = is_array($saved) ? array_replace_recursive($defaults, $saved) : $defaults;

/** Настройките от формата (GET) – с граници, за да не се счупи печатът. */
function bg_labels_read(array $in, array $cfg): array
{
    $presets = PrintSheet::labelPresets();
    $c = $cfg;
    if (isset($in['template']) && isset(LabelTemplates::types()[$in['template']])) $c['template'] = $in['template'];
    if (isset($in['preset']) && (isset($presets[$in['preset']]) || $in['preset'] === 'custom')) $c['preset'] = $in['preset'];
    if (isset($in['header_text'])) $c['header_text'] = mb_substr(trim(strip_tags($in['header_text'])), 0, 60);
    $c['show_title'] = !empty($in['show_title']) ? 1 : 0;
    $c['show_callnumber'] = !empty($in['show_callnumber']) ? 1 : 0;
    $c['border'] = !empty($in['border']) ? 1 : 0;
    $c['rotate'] = !empty($in['rotate']) ? 1 : 0;
    if (isset($in['margin']) && is_numeric(str_replace(',', '.', $in['margin']))) $c['margin'] = max(0, min(20, (float)str_replace(',', '.', $in['margin'])));
    foreach (['p_w' => 0, 'p_h' => 1] as $k => $i) {
        if (isset($in[$k]) && is_numeric(str_replace(',', '.', $in[$k]))) $c['packed_size'][$c['template']][$i] = max(5, min(200, (float)str_replace(',', '.', $in[$k])));
    }
    if (isset($in['title_len'])) $c['title_len'] = max(10, min(80, (int)$in['title_len']));
    foreach (['padding' => [0, 5], 'offset_x' => [-20, 20], 'offset_y' => [-20, 20]] as $k => [$min, $max]) {
        if (isset($in[$k]) && is_numeric(str_replace(',', '.', $in[$k]))) $c[$k] = max($min, min($max, (float)str_replace(',', '.', $in[$k])));
    }
    foreach (['cols', 'rows', 'w', 'h', 'left', 'top', 'gapX', 'gapY'] as $k) {
        if (isset($in['c_' . $k]) && is_numeric(str_replace(',', '.', $in['c_' . $k]))) $c['custom'][$k] = (float)str_replace(',', '.', $in['c_' . $k]);
    }
    return $c;
}

/* ------------------------------------------------------------------ печат (нов раздел) */
$action = $_GET['do'] ?? '';
if ($action === 'preview' || $action === 'calibrate') {
    $c = bg_labels_read($_GET, $cfg);
    $bg_ps = $c['packed_size'][$c['template']] ?? [50, 25];
    $g = PrintSheet::geometry($c['preset'], $c['preset'] === 'packed' ? ['w' => $bg_ps[0], 'h' => $bg_ps[1]] : $c['custom'],
        (float)$c['offset_x'], (float)$c['offset_y'], ['margin' => $c['margin'], 'rotate' => (bool)$c['rotate']]);
    if (!empty($_GET['save_defaults']) && utility::havePrivilege('bibliography', 'w')) {
        Db::q('INSERT INTO setting (setting_name, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)', ['bglabel_print_settings', serialize($c)]);
    }
    if ($action === 'calibrate') {
        echo PrintSheet::calibration($g);
        exit();
    }
    $source = $_GET['source'] ?? 'range';
    $missing = [];
    $err = null;
    if ($source === 'kdbf') {
        $items = LabelData::fromKdbf((int)($_GET['kdbf'] ?? 0));
    } elseif ($source === 'biblio') {
        $items = LabelData::fromBiblioIds(LabelData::parseIds($_GET['ids'] ?? ''));
    } elseif ($source === 'queue') {
        $items = LabelData::fromQueue();
    } else {
        $res = LabelData::fromRanges((string)($_GET['ranges'] ?? ''), $err);
        $items = $res['items'];
        $missing = $res['missing'];
    }
    $copies = max(1, min(10, (int)($_GET['copies'] ?? 1)));
    $cells = [];
    foreach ($items as $r) {
        $html = LabelTemplates::render($c['template'], $r, $c);
        for ($i = 0; $i < $copies; $i++) $cells[] = $html;
    }
    // линии за рязане само на обикновена хартия (на готовите листове стикерите са изрязани)
    $page = PrintSheet::render($cells, $g, (int)($_GET['start'] ?? 1), LabelTemplates::types()[$c['template']], LabelTemplates::css($g, $c),
        array_merge($c, ['border' => $c['border'] && in_array($c['preset'], ['packed', 'custom'], true)]));
    if ($err || $missing) {
        $msg = $err ?: sprintf(__('Not found: %s'), \SLiMS\Bg\Inventory::formatRanges($missing));
        $page = str_replace('</small></div>', '</small><br><b style="color:#c00">' . htmlspecialchars($msg) . '</b></div>', $page);
    }
    echo $page;
    exit();
}

/* ------------------------------------------------------------------ формата */
$c = $cfg;
$presets = PrintSheet::labelPresets();
$presetOpts = [];
foreach ($presets as $k => $p) $presetOpts[$k] = $p['label'];
$presetOpts['custom'] = __('Custom size');
$kdbfOpts = ['0' => '—'];
if (Db::tableExists('kdbf_entry')) {
    foreach (Db::all('SELECT * FROM kdbf_entry ORDER BY entry_year DESC, entry_no DESC LIMIT 100') as $e) $kdbfOpts[$e['entry_id']] = Kdbf::entryLabel($e) . ' – ' . ($e['source_name'] ?? '');
}
$selKdbf = (int)($_GET['kdbf'] ?? 0);
// избраните заглавия от списъка с библиографски записи
$selIds = LabelData::parseIds($_GET['ids'] ?? '');
$selItems = $selIds ? count(LabelData::fromBiblioIds($selIds)) : 0;
if (isset($_GET['template']) && isset(LabelTemplates::types()[$_GET['template']])) $c['template'] = $_GET['template'];
$source = in_array($_GET['source'] ?? '', ['range', 'kdbf', 'queue', 'biblio'], true) ? $_GET['source'] : ($selKdbf ? 'kdbf' : (LabelData::queueCount() ? 'queue' : 'range'));
if ($source === 'biblio' && !$selIds) $source = 'range';
$num = fn($name, $val, $step = '0.1', $attr = '') => '<input type="number" step="' . $step . '" name="' . $name . '" value="' . Ui::h($val) . '" class="form-control" style="width:6.5em" ' . $attr . ' />';

echo Ui::header($title);
$f = '<form method="get" action="' . Ui::h($self) . '" target="_blank" class="p-3 notAJAX" id="bgLabelsForm">';
$f .= Ui::row(__('Template'), Ui::select('template', LabelTemplates::types(), $c['template'], 'class="form-control col-6"'));
$f .= Ui::row(__('Sticker sheet'), Ui::select('preset', $presetOpts, $c['preset'], 'class="form-control col-6"'),
    __('Plain paper: as many as fit, edge to edge, cut with straight cuts. Sticker sheets: the positions are fixed by the manufacturer.'));
$bg_ps = $c['packed_size'][$c['template']] ?? [50, 25];
$packedRow = '<div class="form-inline flex-wrap">'
    . __('Label width/height (mm)') . '&nbsp;' . $num('p_w', $bg_ps[0], '0.5', 'id="p_w" min="5" max="200"') . '×' . $num('p_h', $bg_ps[1], '0.5', 'id="p_h" min="5" max="200"') . '&nbsp;&nbsp;'
    . __('Printer margin (mm)') . '&nbsp;' . $num('margin', $c['margin'], '0.5', 'min="0" max="20"') . '&nbsp;&nbsp;'
    . '<label><input type="checkbox" name="rotate" value="1"' . ($c['rotate'] ? ' checked' : '') . '>&nbsp;' . __('Rotate 90° when more fit') . '</label></div>';
$f .= '<div id="bgPacked"' . ($c['preset'] === 'packed' ? '' : ' style="display:none"') . '>' . Ui::row(__('Plain paper'), $packedRow,
    __('The program finds the arrangement with the most labels (both page orientations, with or without rotation); all cuts go from edge to edge.')) . '</div>';
$cust = '<div class="form-inline flex-wrap">'
    . __('Columns') . '&nbsp;' . $num('c_cols', $c['custom']['cols'], '1', 'min="1" max="10"') . '&nbsp;'
    . __('Rows') . '&nbsp;' . $num('c_rows', $c['custom']['rows'], '1', 'min="1" max="30"') . '&nbsp;'
    . __('Label width/height (mm)') . '&nbsp;' . $num('c_w', $c['custom']['w']) . '×' . $num('c_h', $c['custom']['h']) . '&nbsp;'
    . __('Top/left margin (mm)') . '&nbsp;' . $num('c_top', $c['custom']['top']) . '/' . $num('c_left', $c['custom']['left']) . '&nbsp;'
    . __('Horizontal/vertical gap (mm)') . '&nbsp;' . $num('c_gapX', $c['custom']['gapX']) . '/' . $num('c_gapY', $c['custom']['gapY']) . '</div>';
$f .= '<div id="bgCustom"' . ($c['preset'] === 'custom' ? '' : ' style="display:none"') . '>' . Ui::row(__('Custom size'), $cust) . '</div>';
$f .= Ui::row(__('Correction X/Y (mm)'), '<div class="form-inline">' . $num('offset_x', $c['offset_x']) . '&nbsp;/&nbsp;' . $num('offset_y', $c['offset_y']) . '</div>',
    __('Shift of all labels after calibration: + right / + down'));
$f .= Ui::row(__('Start at position'), '<div class="form-inline">' . $num('start', 1, '1', 'min="1" max="300"') . '&nbsp;' . __('Copies per item') . '&nbsp;' . $num('copies', 1, '1', 'min="1" max="10"') . '</div>',
    __('To use a partly used sheet: positions are counted left to right, top to bottom'));
$src = '<div>'
    . ($selIds ? '<label class="d-block mb-2"><input type="radio" name="source" value="biblio"' . ($source === 'biblio' ? ' checked' : '') . '> <b>'
        . sprintf(__('Selected titles: %d (%d copies)'), count($selIds), $selItems) . '</b></label><input type="hidden" name="ids" value="' . Ui::h(implode(',', $selIds)) . '" />' : '')
    . '<label class="mr-3"><input type="radio" name="source" value="range"' . ($source === 'range' ? ' checked' : '') . '> ' . __('Inventory numbers') . '</label>'
    . '<input type="text" name="ranges" id="ranges" class="form-control mb-2" placeholder="' . Ui::h(__('e.g. 101-130, 135')) . '" value="' . Ui::h($_GET['ranges'] ?? '') . '" data-bgval="ranges" />'
    . '<label class="mr-3"><input type="radio" name="source" value="kdbf"' . ($source === 'kdbf' ? ' checked' : '') . '> ' . __('KDBF entry') . '</label>'
    . Ui::select('kdbf', $kdbfOpts, $selKdbf, 'class="form-control mb-2"')
    . '<label><input type="radio" name="source" value="queue"' . ($source === 'queue' ? ' checked' : '') . '> ' . sprintf(__('Print queue (%d)'), LabelData::queueCount()) . '</label>'
    . '</div>';
$f .= Ui::row(__('Items'), $src);
$f .= Ui::row(__('Text above the barcode'), '<input type="text" name="header_text" maxlength="60" value="' . Ui::h($c['header_text']) . '" class="form-control col-8" />');
$f .= Ui::row(__('Options'), '<label class="mr-3"><input type="checkbox" name="show_title" value="1"' . ($c['show_title'] ? ' checked' : '') . '> ' . __('Show title') . '</label>'
    . __('Title length') . '&nbsp;' . $num('title_len', $c['title_len'], '1', 'min="10" max="80"')
    . '<label class="ml-3 mr-3"><input type="checkbox" name="show_callnumber" value="1"' . ($c['show_callnumber'] ? ' checked' : '') . '> ' . __('Show call number') . '</label>'
    . '<label class="mr-3"><input type="checkbox" name="border" value="1"' . ($c['border'] ? ' checked' : '') . '> ' . __('Print cutting lines') . '</label>'
    . __('Inner margin (mm)') . '&nbsp;' . $num('padding', $c['padding']));
$f .= Ui::row('', '<label><input type="checkbox" name="save_defaults" value="1"> ' . __('Save these settings as default') . '</label>');
$f .= '<div class="text-right"><button type="submit" name="do" value="calibrate" class="btn btn-default mr-2">' . __('Calibration page') . '</button>'
    . '<button type="submit" name="do" value="preview" class="btn btn-primary">' . __('Preview and print') . '</button></div>';
$f .= '</form>';
echo $f;
echo \SLiMS\Bg\Validator::jsI18nScript();
$bg_sizes = json_encode($c['packed_size']);
echo '<script type="text/javascript">(function($){var sizes=' . $bg_sizes . ';$("#preset").on("change",function(){$("#bgCustom").toggle(this.value==="custom");$("#bgPacked").toggle(this.value==="packed");});'
    . '$("#template").on("change",function(){var s=sizes[this.value];if(s){$("#p_w").val(s[0]);$("#p_h").val(s[1]);}});'
    . '$("#ranges").on("focus",function(){$("input[name=source][value=range]").prop("checked",true);});'
    . '$("#kdbf").on("change",function(){$("input[name=source][value=kdbf]").prop("checked",true);});})(jQuery);</script>';
echo '<div class="p-3"><small class="text-muted">' . __('Tip: first print the calibration page on plain paper at 100% and compare it with a sticker sheet against the light. The red lines must be exactly 100 mm.') . '</small></div>';
