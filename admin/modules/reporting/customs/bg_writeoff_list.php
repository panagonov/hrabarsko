<?php
/**
 * Списък към акт за отчисляване (Наредба № 3, чл. 35): автор, заглавие, том, година,
 * инвентарен номер, стойност, отдел; общ брой и обща стойност; подписи на комисията.
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

$_GET['reportView'] = 'true';
$id = (int)($_GET['id'] ?? 0);
$act = Kdbf::act($id);
if (!$act) die('<div class="errorBox">' . __('Act not found') . '</div>');
$reasons = Kdbf::writeoffReasons();
$items = Kdbf::actItems($id);
$authors = [];
if ($items) {
    $ids = array_unique(array_column($items, 'biblio_id'));
    $in = implode(',', array_fill(0, count($ids), '?'));
    foreach (Db::all("SELECT ba.biblio_id, GROUP_CONCAT(a.author_name ORDER BY ba.level SEPARATOR '; ') AS authors FROM biblio_author ba JOIN mst_author a ON a.author_id = ba.author_id WHERE ba.level <= 2 AND ba.biblio_id IN ($in) GROUP BY ba.biblio_id", array_values($ids)) as $r) {
        $authors[$r['biblio_id']] = $r['authors'];
    }
}

$title = sprintf(__('List of documents written off with act No %s'), $act['act_no'] . '/' . $act['act_year'] . ' ' . __('from') . ' ' . Ui::date($act['act_date']));
$sub = '<b>' . __('Reason') . ':</b> ' . Ui::h($reasons[$act['reason']] ?? $act['reason']) . ($act['reason_details'] ? ' (' . Ui::h($act['reason_details']) . ')' : '')
    . ($act['status'] === 'approved' ? ' · <b>' . __('KDBF part 2') . ':</b> ' . (int)$act['kdbf_no'] . ' / ' . Ui::date($act['kdbf_date']) : '');

$header = ['№', __('Inv. No.'), __('Author'), __('Title'), __('Volume'), __('Year'), __('Value'), '≈ €', __('Department')];
$rows = [];
$n = 0;
$bgn = 0.0;
$eur = 0.0;
foreach ($items as $it) {
    $n++;
    $cur = $it['price_currency'] ?: Inventory::defaultCurrency($it['received_date']);
    $bgn += Inventory::toBgn($it['price'] ?? 0, $cur);
    $eur += Inventory::toEur($it['price'] ?? 0, $cur);
    $rows[] = [
        bg_int($n), (string)$it['item_code'], (string)($authors[$it['biblio_id']] ?? ''), $it['title'], (string)$it['volume'], (string)$it['publish_year'],
        ['html' => Inventory::fmt($it['price']) . ' ' . Inventory::currencyLabel($cur), 'class' => 'num', 'csv' => Inventory::fmt($it['price']) . ' ' . Inventory::currencyLabel($cur)],
        bg_num(Inventory::toEur($it['price'] ?? 0, $cur)),
        (string)$it['location_name'],
    ];
}
if ((int)$act['noninv_count'] > 0) {
    $bgn += (float)$act['noninv_value'];
    $eur += Inventory::toEur($act['noninv_value'] ?? 0, 'BGN');
    $rows[] = ['', '', '', __('Not inventoried documents'), '', '', ['html' => Inventory::fmt($act['noninv_value']) . ' лв.', 'class' => 'num', 'csv' => Inventory::fmt($act['noninv_value']) . ' лв.'], bg_num(Inventory::toEur($act['noninv_value'] ?? 0, 'BGN')), bg_int($act['noninv_count'])];
}
$count = $n + (int)$act['noninv_count'];

$footer = '<p class="mt-2"><b>' . sprintf(__('Total: %d copies, value %s лв. (%s €)'), $count, Inventory::fmt($bgn), Inventory::fmt($eur)) . '</b></p>';
$footer .= '<div class="bg-sign"><b>' . __('Commission') . ':</b>';
foreach (preg_split('/\r\n|\r|\n/', trim((string)$act['commission'])) as $member) {
    if (trim($member) === '') continue;
    $footer .= '<div>' . Ui::h(trim($member)) . ' …………………………</div>';
}
$footer .= '<div style="margin-top:10mm"><b>' . __('Approved by') . ':</b> ' . Ui::h($act['approved_by'] ?: '……………………………………') . ' &nbsp; ' . __('Date') . ': ' . ($act['approved_date'] ? Ui::date($act['approved_date']) : '…………………') . '</div></div>';
if ($act['status'] === 'draft') $footer .= '<div class="bg-draft">' . __('DRAFT') . '</div>';
if ($act['status'] === 'cancelled') $footer .= '<div class="bg-draft">' . __('CANCELLED') . '</div>';

bg_report_output($title, $sub, $header, $rows, $footer, false, 'writeoff_act_' . $act['act_no'] . '_' . $act['act_year']);
