<?php
/**
 * КДБФ част 1 – регистриране на постъпилите документи (Наредба № 3, чл. 14, Приложение № 1).
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

$title = __('KDBF part 1 – Acquisitions');
if (!bg_report_is_view()) {
    bg_report_filter_page($title, __('Year') . '&nbsp;<input type="number" name="year" min="1900" max="2100" value="' . date('Y') . '" class="form-control col-2" />');
    exit();
}
$year = (int)($_GET['year'] ?? date('Y'));
$methods = Kdbf::acqMethods();
$kinds = Kdbf::kindColumns();
$contents = Kdbf::contentColumns();
$langs = Kdbf::langColumns();

$header = array_merge(
    [__('No and date'), __('From where and how'), __('Document'), __('Total'), __('Inventoried'), __('Value') . ' (лв.)', __('Value') . ' (€)', __('Inventory No. from–to')],
    array_values($kinds), array_values($contents), array_values($langs), [__('Not inventoried')]
);
$rows = [];
$tot = ['total' => 0, 'count' => 0, 'bgn' => 0.0, 'eur' => 0.0, 'kind' => [], 'content' => [], 'lang' => [], 'noninv' => 0];
foreach (Db::all('SELECT e.*, s.supplier_name FROM kdbf_entry e LEFT JOIN mst_supplier s ON s.supplier_id = e.supplier_id WHERE e.entry_year = ? ORDER BY e.entry_no', [$year]) as $e) {
    $sum = Kdbf::entrySummary((int)$e['entry_id']);
    $d = $sum['dist'];
    $row = [
        $e['entry_no'] . ' / ' . Ui::date($e['entry_date']),
        ($methods[$e['acq_method']] ?? $e['acq_method']) . ' – ' . ($e['source_name'] ?: $e['supplier_name']),
        trim($e['doc_type'] . ' ' . ($e['doc_no'] ? '№ ' . $e['doc_no'] : '') . ' ' . Ui::date($e['doc_date'])),
        bg_int($e['total_count']),
        bg_int($d['count']),
        bg_num($d['bgn']),
        bg_num($d['eur']),
        $sum['ranges'],
    ];
    foreach ($kinds as $k => $_) { $row[] = bg_int($d['kind'][$k]); $tot['kind'][$k] = ($tot['kind'][$k] ?? 0) + $d['kind'][$k]; }
    foreach ($contents as $k => $_) { $row[] = bg_int($d['content'][$k]); $tot['content'][$k] = ($tot['content'][$k] ?? 0) + $d['content'][$k]; }
    foreach ($langs as $k => $_) { $row[] = bg_int($d['lang'][$k]); $tot['lang'][$k] = ($tot['lang'][$k] ?? 0) + $d['lang'][$k]; }
    $row[] = $e['noninv_count'] ? (int)$e['noninv_count'] . ' / ' . Inventory::fmt($e['noninv_value']) . ' ' . Inventory::currencyLabel($e['currency']) : '';
    $rows[] = $row;
    $tot['total'] += (int)$e['total_count'];
    $tot['count'] += $d['count'];
    $tot['bgn'] += $d['bgn'];
    $tot['eur'] += $d['eur'];
    $tot['noninv'] += (int)$e['noninv_count'];
}
if ($rows) {
    $row = [['html' => '<b>' . __('Total') . '</b>', 'csv' => __('Total')], '', '', bg_int($tot['total']), bg_int($tot['count']), bg_num($tot['bgn']), bg_num($tot['eur']), ''];
    foreach ($kinds as $k => $_) $row[] = bg_int($tot['kind'][$k] ?? 0);
    foreach ($contents as $k => $_) $row[] = bg_int($tot['content'][$k] ?? 0);
    foreach ($langs as $k => $_) $row[] = bg_int($tot['lang'][$k] ?? 0);
    $row[] = $tot['noninv'] ?: '';
    $rows[] = $row;
}
$footer = '<p style="font-size:8pt">' . __('By content: UDC main classes; "Fiction" includes UDC 82 and the old 83–89 (e.g. 886.7); "Children" – collection type "Children\'s literature".') . '</p>';
bg_report_output($title, sprintf(__('Year %d'), $year), $header, $rows, $footer, true, 'kdbf_part1_' . $year);
