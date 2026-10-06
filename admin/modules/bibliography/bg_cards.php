<?php
/**
 * Каталожни картички за картотеката (офлайн каталозите) по образеца на НБКМ:
 * азбучен, систематичен и топографски каталог + допълнителни картички,
 * и празни картички за описване и класифициране на ръка. Печат на A4 или по една картичка.
 */

use SLiMS\Bg\Db;
use SLiMS\Bg\LabelData;
use SLiMS\Bg\PrintSheet;
use SLiMS\Bg\Ui;
use SLiMS\Catalog\BgCatalogCard;

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
if ((int)$_SESSION['uid'] !== 1 && !utility::haveAccess('bibliography.bg-cards') && !utility::haveAccess('bibliography.catalog-printing')) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

$self = MWB . 'bibliography/bg_cards.php';
$title = __('Catalogue Cards (card catalogue)');

$types = [
    'main' => __('Main card – alphabetical catalogue (heading word), with inventory numbers'),
    'systematic' => __('Systematic catalogue card (filed by UDC)'),
    'shelf' => __('Shelf-list card (by call number, all copies and departments)'),
    'title' => __('Added entry: title (when the heading is an author)'),
    'author' => __('Added entries: co-authors'),
    'subject' => __('Added entries: subject headings'),
];
$layouts = [
    'card_packed' => __('Packed on A4 for cutting – 5 cards (some rotated 90°)'),
    'card_a4_3' => __('A4 portrait – 3 cards'),
    'card_a4_4' => __('A4 landscape – 4 cards'),
    'card_single' => __('One card per page (12.5 × 7.5 cm)'),
];
$sigSources = ['auto' => __('Automatic'), 'call_number' => __('Call Number'), 'inventory' => __('Inventory number')];
$defaults = ['types' => ['main', 'systematic'], 'layout' => 'card_packed', 'signature_source' => 'auto', 'font_size' => 10, 'border' => 1, 'margin' => 5, 'offset_x' => 0, 'offset_y' => 0];
$saved = @unserialize((string)Db::value("SELECT setting_value FROM setting WHERE setting_name = 'bgcard_print_settings'"));
$cfg = is_array($saved) ? array_merge($defaults, $saved) : $defaults;

/* ------------------------------------------------------------------ печат (нов раздел) */
if (($_GET['do'] ?? '') === 'preview') {
    $c = $cfg;
    $c['types'] = array_values(array_intersect(array_keys($types), (array)($_GET['types'] ?? [])));
    if (isset($layouts[$_GET['layout'] ?? ''])) $c['layout'] = $_GET['layout'];
    if (isset($sigSources[$_GET['signature_source'] ?? ''])) $c['signature_source'] = $_GET['signature_source'];
    $c['font_size'] = max(7, min(12, (int)($_GET['font_size'] ?? $c['font_size'])));
    $c['border'] = !empty($_GET['border']) ? 1 : 0;
    foreach (['offset_x', 'offset_y'] as $k) $c[$k] = max(-20, min(20, (float)str_replace(',', '.', (string)($_GET[$k] ?? 0))));
    $c['margin'] = max(0, min(20, (float)str_replace(',', '.', (string)($_GET['margin'] ?? $c['margin']))));
    $blank = max(0, min(60, (int)($_GET['blank'] ?? 0)));
    if (!empty($_GET['save_defaults']) && utility::havePrivilege('bibliography', 'w')) {
        Db::q('INSERT INTO setting (setting_name, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)', ['bgcard_print_settings', serialize($c)]);
    }
    $ids = ($_GET['source'] ?? '') === 'queue'
        ? array_map('intval', array_values($_SESSION['cards']['item'] ?? []))
        : LabelData::parseIds($_GET['ids'] ?? '');
    $cells = [];
    if ($c['types']) {
        foreach ($ids as $id) {
            $b = BgCatalogCard::load($id);
            if ($b) $cells = array_merge($cells, BgCatalogCard::cards($b, ['card_types' => $c['types'], 'signature_source' => $c['signature_source']]));
        }
    }
    for ($i = 0; $i < $blank; $i++) $cells[] = BgCatalogCard::blank();
    $g = PrintSheet::geometry($c['layout'], [], $c['offset_x'], $c['offset_y'], ['margin' => $c['margin'], 'rotate' => true]);
    // плътно (контур до контур): една обща линия за рязане вместо рамка на всяка картичка
    $packed = $c['layout'] === 'card_packed';
    echo PrintSheet::render($cells, $g, (int)($_GET['start'] ?? 1), $title,
        BgCatalogCard::css($c['font_size'], $c['border'] && !$packed), ['padding' => 0, 'border' => $c['border'] && $packed]);
    exit();
}

/* ------------------------------------------------------------------ формата */
$ids = LabelData::parseIds($_GET['ids'] ?? '');
$queue = count($_SESSION['cards']['item'] ?? []);
$source = $ids ? 'ids' : ($queue ? 'queue' : 'none');
$titles = [];
if ($ids) {
    $in = implode(',', array_fill(0, count($ids), '?'));
    $titles = array_column(Db::all("SELECT title FROM biblio WHERE biblio_id IN ($in) ORDER BY title", $ids), 'title');
}

echo Ui::header($title);
$f = '<form method="get" action="' . Ui::h($self) . '" target="_blank" class="p-3 notAJAX" id="bgCardsForm">';
$f .= '<input type="hidden" name="do" value="preview" />';
if ($source === 'ids') {
    $f .= '<input type="hidden" name="ids" value="' . Ui::h(implode(',', $ids)) . '" />';
    $list = implode('; ', array_map([Ui::class, 'h'], array_slice($titles, 0, 8))) . (count($titles) > 8 ? ' …' : '');
    $f .= Ui::row(__('Titles'), '<b>' . sprintf(__('Selected titles: %d'), count($ids)) . '</b><br><small>' . $list . '</small>');
} elseif ($source === 'queue') {
    $f .= '<input type="hidden" name="source" value="queue" />';
    $f .= Ui::row(__('Titles'), '<b>' . sprintf(__('Print queue (%d)'), $queue) . '</b>');
} else {
    $f .= Ui::row(__('Titles'), '<div class="alert alert-info mb-0">' . __('Select titles in the bibliographic list and choose "Catalogue cards" – or print only blank cards below.') . '</div>');
}
$chk = '';
foreach ($types as $k => $label) {
    $chk .= '<div class="form-check"><input class="form-check-input" type="checkbox" name="types[]" value="' . $k . '" id="ct_' . $k . '"' . (in_array($k, $cfg['types'], true) ? ' checked' : '') . '><label class="form-check-label" for="ct_' . $k . '">' . Ui::h($label) . '</label></div>';
}
$f .= Ui::row(__('Cards for each title'), $chk, __('The alphabetical catalogue is arranged by the heading word, the systematic one by UDC, the shelf list by call number.'));
$f .= Ui::row(__('Blank cards'), '<input type="number" name="blank" min="0" max="60" value="0" class="form-control col-2" />', __('Cards with ruled fields for describing and classifying books by hand; the data is entered in the program later.'));
$f .= Ui::row(__('Page layout'), Ui::select('layout', $layouts, $cfg['layout'], 'class="form-control col-6"'));
$f .= Ui::row(__('Signature Source'), Ui::select('signature_source', $sigSources, $cfg['signature_source'], 'class="form-control col-4"'));
$f .= Ui::row(__('Printer margin (mm)'), '<input type="number" step="0.5" min="0" max="20" name="margin" value="' . Ui::h($cfg['margin']) . '" class="form-control col-2" />', __('For the packed layout: the strip at the paper edge that the printer cannot print; the cards are cut edge to edge.'));
$f .= Ui::row(__('Start at position'), '<input type="number" name="start" min="1" max="5" value="1" class="form-control col-2" />');
$f .= Ui::row(__('Font size (pt)'), '<input type="number" name="font_size" min="7" max="12" value="' . (int)$cfg['font_size'] . '" class="form-control col-2" />');
$f .= Ui::row(__('Correction X/Y (mm)'), '<div class="form-inline"><input type="number" step="0.1" name="offset_x" value="' . Ui::h($cfg['offset_x']) . '" class="form-control" style="width:6.5em" />&nbsp;/&nbsp;<input type="number" step="0.1" name="offset_y" value="' . Ui::h($cfg['offset_y']) . '" class="form-control" style="width:6.5em" /></div>');
$f .= Ui::row(__('Options'), '<label class="mr-3"><input type="checkbox" name="border" value="1"' . ($cfg['border'] ? ' checked' : '') . '> ' . __('Print a frame') . '</label>'
    . '<label><input type="checkbox" name="save_defaults" value="1"> ' . __('Save these settings as default') . '</label>', __('Packed layout: the frame is printed as cutting lines shared by neighbouring cards.'));
$f .= '<div class="text-right"><button type="submit" class="btn btn-primary">' . __('Preview and print') . '</button></div></form>';
echo $f;
echo '<div class="p-3"><small class="text-muted">' . __('Card size 12.5 × 7.5 cm. Print at 100% / "Actual size". The card shows the heading word in bold, the call number top left and the full UDC index bottom right (NLB handbook).') . '</small></div>';
