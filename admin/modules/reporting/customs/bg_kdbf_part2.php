<?php
/**
 * КДБФ част 2 – регистриране на отчислените документи (Наредба № 3, чл. 39, Приложение № 2).
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

$title = __('KDBF part 2 – Written off documents');
if (!bg_report_is_view()) {
    bg_report_filter_page($title, __('Year') . '&nbsp;<input type="number" name="year" min="1900" max="2100" value="' . date('Y') . '" class="form-control col-2" />');
    exit();
}
$year = (int)($_GET['year'] ?? date('Y'));
$reasons = Kdbf::writeoffReasons();
$kinds = Kdbf::kindColumns();
$contents = Kdbf::contentColumns();
$langs = Kdbf::langColumns();

$header = array_merge(
    [__('KDBF No and date'), __('Act No and date'), __('Reason'), __('Copies'), __('Value') . ' (лв.)', __('Value') . ' (€)'],
    array_values($kinds), array_values($contents), array_values($langs)
);
$rows = [];
$tot = ['count' => 0, 'bgn' => 0.0, 'eur' => 0.0, 'kind' => [], 'content' => [], 'lang' => []];
foreach (Db::all("SELECT * FROM kdbf_writeoff WHERE act_year = ? AND status IN ('approved','cancelled') ORDER BY act_no", [$year]) as $a) {
    if ($a['status'] === 'cancelled') {
        $rows[] = array_merge(['', $a['act_no'] . ' / ' . Ui::date($a['act_date']), ['html' => '<i>' . __('Cancelled') . '</i>', 'csv' => __('Cancelled')], '', '', ''],
            array_fill(0, count($kinds) + count($contents) + count($langs), ''));
        continue;
    }
    $d = Kdbf::distribution(Kdbf::actItems((int)$a['writeoff_id']));
    $count = (int)$a['total_count'] + (int)$a['noninv_count'];
    $row = [
        $a['kdbf_no'] . ' / ' . Ui::date($a['kdbf_date']),
        $a['act_no'] . ' / ' . Ui::date($a['act_date']),
        ($reasons[$a['reason']] ?? $a['reason']) . ($a['reason_details'] ? ' (' . $a['reason_details'] . ')' : ''),
        bg_int($count),
        bg_num((float)$a['total_value_bgn'] + (float)$a['noninv_value']),
        bg_num((float)$a['total_value_eur'] + Inventory::toEur($a['noninv_value'] ?? 0, 'BGN')),
    ];
    foreach ($kinds as $k => $_) { $row[] = bg_int($d['kind'][$k]); $tot['kind'][$k] = ($tot['kind'][$k] ?? 0) + $d['kind'][$k]; }
    foreach ($contents as $k => $_) { $row[] = bg_int($d['content'][$k]); $tot['content'][$k] = ($tot['content'][$k] ?? 0) + $d['content'][$k]; }
    foreach ($langs as $k => $_) { $row[] = bg_int($d['lang'][$k]); $tot['lang'][$k] = ($tot['lang'][$k] ?? 0) + $d['lang'][$k]; }
    $rows[] = $row;
    $tot['count'] += $count;
    $tot['bgn'] += (float)$a['total_value_bgn'] + (float)$a['noninv_value'];
    $tot['eur'] += (float)$a['total_value_eur'] + Inventory::toEur($a['noninv_value'] ?? 0, 'BGN');
}
if ($rows) {
    $row = [['html' => '<b>' . __('Total') . '</b>', 'csv' => __('Total')], '', '', bg_int($tot['count']), bg_num($tot['bgn']), bg_num($tot['eur'])];
    foreach ($kinds as $k => $_) $row[] = bg_int($tot['kind'][$k] ?? 0);
    foreach ($contents as $k => $_) $row[] = bg_int($tot['content'][$k] ?? 0);
    foreach ($langs as $k => $_) $row[] = bg_int($tot['lang'][$k] ?? 0);
    $rows[] = $row;
}
bg_report_output($title, sprintf(__('Year %d'), $year), $header, $rows, '', true, 'kdbf_part2_' . $year);
