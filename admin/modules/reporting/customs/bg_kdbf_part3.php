<?php
/**
 * КДБФ част 3 – резултати от движението на библиотечния фонд (Наредба № 3, Приложение № 3).
 */

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

$title = __('KDBF part 3 – Results of the fund movement');
if (!bg_report_is_view()) {
    bg_report_filter_page($title, __('Year') . '&nbsp;<input type="number" name="year" min="1900" max="2100" value="' . date('Y') . '" class="form-control col-2" />');
    exit();
}
$year = (int)($_GET['year'] ?? date('Y'));
$first = Kdbf::firstYear() ?? $year;
$header = [__('Year'), __('At the beginning – copies'), __('At the beginning – €'), __('Received – copies'), __('Received – €'),
    __('Written off – copies'), __('Written off – €'), __('At the end – copies'), __('At the end – €'), __('At the end – лв.'), __('Source of the opening balance')];
$src = ['manual' => __('entered manually'), 'closing' => __('closed year'), 'computed' => __('computed'), 'none' => __('no data')];
$rows = [];
for ($y = max($first, $year - 9); $y <= $year; $y++) {
    $r = Kdbf::yearResults($y);
    $rows[] = [
        (string)$y,
        bg_int($r['opening']['count']), bg_num($r['opening']['eur']),
        bg_int($r['in']['count']), bg_num($r['in']['eur']),
        bg_int($r['out']['count']), bg_num($r['out']['eur']),
        bg_int($r['closing']['count']), bg_num($r['closing']['eur']), bg_num($r['closing']['bgn']),
        $src[$r['opening']['source']] ?? '',
    ];
}
$footer = '<p style="font-size:8pt">' . __('At the end = at the beginning + received − written off. 1 EUR = 1.95583 BGN.') . '</p>';
bg_report_output($title, sprintf(__('Up to %d'), $year), $header, $rows, $footer, true, 'kdbf_part3_' . $year);
