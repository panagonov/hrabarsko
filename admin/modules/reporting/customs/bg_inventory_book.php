<?php
/**
 * Инвентарна книга (Наредба № 3 от 18.11.2014 г., чл. 16 и Приложение № 4)
 * в електронен вид – за преглед, печат, xlsx и CSV.
 */

use SLiMS\Bg\Db;
use SLiMS\Bg\Inventory;
use SLiMS\Bg\Kdbf;
use SLiMS\Bg\Ui;

define('INDEX_AUTH', '1');
require '../../../../sysconfig.inc.php';
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-reporting');
require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';
require __DIR__ . '/bg_report.inc.php';
bg_report_access();

$title = __('Inventory Book');
const BG_PRINT_LIMIT = 2000;

if (!bg_report_is_view()) {
    $locations = ['' => __('All departments')];
    foreach (Db::all('SELECT location_id, location_name FROM mst_location ORDER BY location_name') as $l) $locations[$l['location_id']] = $l['location_name'];
    $fields = __('Inventory No.') . '&nbsp;<input type="text" name="from" inputmode="numeric" class="form-control col-1" placeholder="' . __('from') . '" />'
        . '&nbsp;–&nbsp;<input type="text" name="to" inputmode="numeric" class="form-control col-1 mr-2" placeholder="' . __('to') . '" />'
        . __('Entry date') . '&nbsp;<input type="date" name="dfrom" class="form-control mr-1" />–<input type="date" name="dto" class="form-control mr-2" />'
        . Ui::select('location', $locations, '', 'class="form-control mr-2"')
        . Ui::select('status', ['' => __('All items'), 'active' => __('In the fund'), 'wo' => __('Written off')], '', 'class="form-control"');
    bg_report_filter_page($title, $fields, __('Ordinance No 3 of 18.11.2014, Annex 4'));
    exit();
}

/* ------------------------------------------------------------------ филтри */
$where = ['1=1'];
$params = [];
$sub = [];
$from = trim((string)($_GET['from'] ?? ''));
$to = trim((string)($_GET['to'] ?? ''));
if (preg_match('/^\d{1,9}$/', $from)) { $where[] = "i.item_code REGEXP '^[0-9]+$' AND CAST(i.item_code AS UNSIGNED) >= ?"; $params[] = (int)$from; $sub[] = __('from No') . ' ' . (int)$from; }
if (preg_match('/^\d{1,9}$/', $to)) { $where[] = "i.item_code REGEXP '^[0-9]+$' AND CAST(i.item_code AS UNSIGNED) <= ?"; $params[] = (int)$to; $sub[] = __('to No') . ' ' . (int)$to; }
$dfrom = (string)($_GET['dfrom'] ?? '');
$dto = (string)($_GET['dto'] ?? '');
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dfrom)) { $where[] = 'i.received_date >= ?'; $params[] = $dfrom; $sub[] = __('entered from') . ' ' . Ui::date($dfrom); }
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dto)) { $where[] = 'i.received_date <= ?'; $params[] = $dto; $sub[] = __('entered to') . ' ' . Ui::date($dto); }
if (($_GET['location'] ?? '') !== '') { $where[] = 'i.location_id = ?'; $params[] = (string)$_GET['location']; $sub[] = __('department') . ' ' . Ui::h($_GET['location']); }
if (($_GET['status'] ?? '') === 'active') { $where[] = "(i.item_status_id IS NULL OR i.item_status_id <> 'WO')"; $sub[] = __('in the fund'); }
if (($_GET['status'] ?? '') === 'wo') { $where[] = "i.item_status_id = 'WO'"; $sub[] = __('written off'); }
if ((int)($_GET['kdbf'] ?? 0) > 0) {
    $where[] = 'i.kdbf_entry_id = ?';
    $params[] = (int)$_GET['kdbf'];
    $sub[] = __('KDBF entry') . ' ' . Ui::h(Kdbf::entryLabel(Kdbf::entry((int)$_GET['kdbf'])));
}

$sql = "SELECT i.item_id, i.item_code, i.received_date, i.volume, i.price, i.price_currency, i.call_number, i.item_notes,
               i.kdbf_legacy_ref, i.item_status_id, b.title, b.edition, b.publish_year, b.classification, b.sor,
               pl.place_name, loc.location_name, e.entry_no, e.entry_year, e.entry_date,
               w.act_no, w.act_year, w.act_date, w.status AS act_status, au.authors, ck.checks
          FROM item i
          JOIN biblio b ON b.biblio_id = i.biblio_id
          LEFT JOIN mst_place pl ON pl.place_id = b.publish_place_id
          LEFT JOIN mst_location loc ON loc.location_id = i.location_id
          LEFT JOIN kdbf_entry e ON e.entry_id = i.kdbf_entry_id
          LEFT JOIN kdbf_writeoff w ON w.writeoff_id = i.writeoff_id
          LEFT JOIN (SELECT ba.biblio_id, GROUP_CONCAT(a.author_name ORDER BY ba.level, a.author_name SEPARATOR '; ') AS authors
                       FROM biblio_author ba JOIN mst_author a ON a.author_id = ba.author_id
                      WHERE ba.level <= 2 GROUP BY ba.biblio_id) au ON au.biblio_id = b.biblio_id
          LEFT JOIN (SELECT item_id, GROUP_CONCAT(CONCAT(YEAR(check_date), ' ', CASE result WHEN 'found' THEN '✓' WHEN 'on_loan' THEN '✓з' ELSE '✗' END)
                                      ORDER BY check_date SEPARATOR '; ') AS checks
                       FROM item_check_log GROUP BY item_id) ck ON ck.item_id = i.item_id
         WHERE " . implode(' AND ', $where) . "
         ORDER BY (i.item_code REGEXP '^[0-9]+$') DESC, CAST(i.item_code AS UNSIGNED), i.item_code";
$data = Db::all($sql, $params);

$isCsv = ($_GET['format'] ?? '') === 'csv';
$truncated = false;
if (!$isCsv && count($data) > BG_PRINT_LIMIT && !isset($_GET['all'])) {
    $truncated = count($data);
    $data = array_slice($data, 0, BG_PRINT_LIMIT);
}

$header = [__('Entry date'), __('Inv. No.'), __('Author'), __('Title'), __('Volume'), __('Edition'), __('Place'), __('Year'),
    __('Price'), '≈ €', __('KDBF No and date'), __('UDC'), __('Call Number'), __('Department'), __('Write-off act No and date'),
    __('Check marks'), __('Notes')];
$rows = [];
$numbers = [];
foreach ($data as $d) {
    if (preg_match('/^\d+$/', (string)$d['item_code'])) $numbers[] = (int)$d['item_code'];
    $cur = $d['price_currency'] ?: Inventory::defaultCurrency($d['received_date']);
    $kdbf = $d['entry_no'] ? $d['entry_no'] . '/' . $d['entry_year'] . ' ' . __('from') . ' ' . Ui::date($d['entry_date']) : (string)$d['kdbf_legacy_ref'];
    $act = ($d['act_no'] && $d['act_status'] === 'approved') ? $d['act_no'] . '/' . $d['act_year'] . ' ' . __('from') . ' ' . Ui::date($d['act_date']) : '';
    $priceTxt = $d['price'] !== null ? Inventory::fmt($d['price']) . ' ' . Inventory::currencyLabel($cur) . ($d['price_currency'] ? '' : ' ?') : '';
    $rows[] = [
        Ui::date($d['received_date']),
        ['html' => '<b>' . Ui::h($d['item_code']) . '</b>', 'csv' => $d['item_code']],
        (string)($d['authors'] ?: $d['sor']),
        $d['title'],
        (string)$d['volume'],
        (string)$d['edition'],
        (string)$d['place_name'],
        (string)$d['publish_year'],
        ['html' => Ui::h($priceTxt), 'class' => 'num', 'csv' => $priceTxt],
        $d['price'] !== null ? bg_num(Inventory::toEur($d['price'], $cur)) : '',
        $kdbf,
        (string)$d['classification'],
        (string)$d['call_number'],
        (string)$d['location_name'],
        $act,
        (string)$d['checks'],
        ['html' => nl2br(Ui::h($d['item_notes'])), 'csv' => (string)$d['item_notes']],
    ];
}

$footer = '';
if ($truncated) {
    $footer .= '<p class="text-danger">' . sprintf(__('Only the first %d of %d rows are shown – narrow the range of inventory numbers or use CSV/xlsx.'), BG_PRINT_LIMIT, $truncated) . '</p>';
}
if ($numbers) {
    $gFrom = preg_match('/^\d{1,9}$/', $from) ? (int)$from : min($numbers);
    $gTo = preg_match('/^\d{1,9}$/', $to) ? (int)$to : max($numbers);
    $otherFilters = $dfrom !== '' || $dto !== '' || ($_GET['location'] ?? '') !== '' || ($_GET['status'] ?? '') !== '' || (int)($_GET['kdbf'] ?? 0) > 0;
    if (!$otherFilters) {
        $gaps = Inventory::gaps($gFrom, $gTo);
        $footer .= '<p><b>' . __('Free numbers in the range') . ' ' . $gFrom . '–' . $gTo . ':</b> ' . ($gaps ? Ui::h(Inventory::formatRanges($gaps)) : __('none')) . '</p>';
    }
}
$footer .= '<p style="font-size:8pt">' . __('Check marks: ✓ found, ✓з on loan at the check, ✗ missing.') . ' ' . __('"?" after a price: the currency is not recorded and is assumed by the entry date.') . '</p>';

bg_report_output($title, implode(', ', $sub) ?: __('All items'), $header, $rows, $footer, true, 'inventory_book');
