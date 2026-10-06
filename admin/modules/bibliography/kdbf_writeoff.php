<?php
/**
 * Актове за отчисляване – КДБФ част 2 (Наредба № 3 от 18.11.2014 г., чл. 30–39).
 * Чернова → добавяне на инв. № → печат на списъка → утвърждаване (екземплярите стават „Отчислен“).
 */

use SLiMS\Bg\Db;
use SLiMS\Bg\Inventory;
use SLiMS\Bg\Kdbf;
use SLiMS\Bg\Ui;
use SLiMS\Bg\Validator;

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

Ui::requireAccess('bibliography.kdbf-writeoffs');
$can_write = utility::havePrivilege('bibliography', 'w');
$self = MWB . 'bibliography/kdbf_writeoff.php';
$title = __('Write-off Acts (KDBF part 2)');
$reasons = Kdbf::writeoffReasons();

function bg_wo_guard(string $title): void
{
    global $can_write;
    if (!$can_write) die();
    if (!Ui::tokenValid()) {
        Ui::toast($title, __('Invalid form submission token!'), 'error');
        exit();
    }
}

/* ------------------------------------------------------------------ запис на акта */
if (isset($_POST['saveAct'])) {
    bg_wo_guard($title);
    $id = (int)($_POST['actID'] ?? 0);
    $old = $id ? Kdbf::act($id) : null;
    if ($old && $old['status'] !== 'draft') {
        // утвърден/анулиран акт – променя се само забележката
        Db::q('UPDATE kdbf_writeoff SET notes = ?, last_update = NOW() WHERE writeoff_id = ?', [trim(strip_tags($_POST['notes'] ?? '')), $id]);
        Ui::toast($title, __('The act is approved – only the notes were saved'), 'warning');
        Ui::reload($self . '?action=detail&id=' . $id);
        exit();
    }
    $v = new Validator();
    $today = date('Y-m-d');
    $date = $v->date('actDate', $_POST['actDate'] ?? '', __('Act date'), true, $today);
    $year = $date ? (int)substr($date, 0, 4) : (int)date('Y');
    $no = $v->positiveInt('actNo', $_POST['actNo'] ?? '', __('Act number'));
    $reason = (string)($_POST['reason'] ?? '');
    $v->oneOf('reason', $reason, array_keys($reasons), __('Reason'));
    $details = trim(strip_tags($_POST['reasonDetails'] ?? ''));
    $v->maxLen('reasonDetails', $details, 255, __('Details'));
    if (in_array($reason, Kdbf::REASONS_NEED_DETAILS, true) && $details === '') {
        $v->error('reasonDetails', __('For theft or natural disaster enter the protocol number and date'));
    }
    $commission = trim(strip_tags($_POST['commission'] ?? ''));
    if ($commission === '') $v->error('commission', __('Enter the commission members (one per line)'));
    $noninv = $v->nonNegativeInt('noninvCount', $_POST['noninvCount'] ?? '', __('Not inventoried (count)'));
    $noninvValue = $v->price('noninvValue', $_POST['noninvValue'] ?? '', false);
    $notes = trim(strip_tags($_POST['notes'] ?? ''));
    if ($no !== null && $date) {
        if (Db::value('SELECT writeoff_id FROM kdbf_writeoff WHERE act_year = ? AND act_no = ? AND writeoff_id <> ?', [$year, $no, $id])) {
            $v->error('actNo', sprintf(__('Act number %d/%d is already used'), $no, $year));
        }
        $suggested = Kdbf::nextActNo($year);
        if (!$id && $no > $suggested) $v->warn('actNo', sprintf(__('Numbers %d–%d of %d are skipped'), $suggested, $no - 1, $year));
    }
    // датата на акта не може да е преди вписването на екземплярите
    if ($id && $date) {
        $late = Db::value('SELECT GROUP_CONCAT(item_code) FROM item WHERE writeoff_id = ? AND received_date > ?', [$id, $date]);
        if ($late) $v->error('actDate', sprintf(__('These items were entered after the act date: %s'), $late));
    }
    if ($v->hasErrors()) $v->failAndExit($title);
    $params = [$year, $no, $date, $reason, $details ?: null, $commission, $noninv, $noninvValue, $notes ?: null];
    try {
        if ($id) {
            Db::q('UPDATE kdbf_writeoff SET act_year=?, act_no=?, act_date=?, reason=?, reason_details=?, commission=?, noninv_count=?, noninv_value=?, notes=?, last_update=NOW(), uid=? WHERE writeoff_id=?',
                array_merge($params, [(int)$_SESSION['uid'], $id]));
        } else {
            Db::q("INSERT INTO kdbf_writeoff (act_year, act_no, act_date, reason, reason_details, commission, noninv_count, noninv_value, notes, status, input_date, last_update, uid)
                   VALUES (?,?,?,?,?,?,?,?,?,'draft',NOW(),NOW(),?)", array_merge($params, [(int)$_SESSION['uid']]));
            $id = (int)Db::lastInsertId();
        }
    } catch (Throwable $e) {
        Ui::toast($title, __('Data FAILED to save') . ': ' . $e->getMessage(), 'error');
        exit();
    }
    writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'] . ' save write-off act ' . $no . '/' . $year);
    Ui::toast($title, sprintf(__('Write-off act %d/%d saved'), $no, $year), 'success');
    $v->flushWarnings($title);
    Ui::reload($self . '?action=detail&id=' . $id);
    exit();
}

/* ------------------------------------------------------------------ добавяне / премахване на екземпляри */
if (isset($_POST['addItems'])) {
    bg_wo_guard($title);
    $id = (int)$_POST['actID'];
    $err = null;
    $numbers = Inventory::parseRanges((string)$_POST['addItems'], $err);
    if ($numbers === null) {
        // позволяваме и стари нечислови кодове, разделени с интервал/запетая
        $codes = array_values(array_filter(preg_split('/[\s,;]+/u', trim((string)$_POST['addItems']))));
        if (!$codes || count($codes) > Inventory::MAX_RANGE) {
            Ui::toast($title, (string)$err, 'error');
            exit();
        }
    } else {
        $codes = array_map('strval', $numbers);
    }
    $res = Kdbf::addItemsToAct($id, $codes);
    if ($res['added']) Ui::toast($title, sprintf(__('Added %d items'), $res['added']), 'success');
    if ($res['errors']) Ui::toast($title, implode("\n", $res['errors']), 'error');
    if ($res['warnings']) Ui::toast($title, implode("\n", $res['warnings']), 'warning');
    Ui::reload($self . '?action=detail&id=' . $id);
    exit();
}
if (isset($_POST['removeItem'])) {
    bg_wo_guard($title);
    $id = (int)$_POST['actID'];
    if (!Kdbf::removeItemFromAct($id, (int)$_POST['removeItem'])) Ui::toast($title, __('Items can be removed only from a draft act'), 'error');
    Ui::reload($self . '?action=detail&id=' . $id);
    exit();
}

/* ------------------------------------------------------------------ утвърждаване / анулиране / изтриване */
if (isset($_POST['approveAct'])) {
    bg_wo_guard($title);
    if (!Ui::canApprove()) {
        Ui::toast($title, __('You are not allowed to approve write-off acts'), 'error');
        exit();
    }
    $id = (int)$_POST['approveAct'];
    $v = new Validator();
    $act = Kdbf::act($id);
    $by = trim(strip_tags($_POST['approvedBy'] ?? ''));
    if ($by === '') $v->error('approvedBy', __('Enter who approves the act (chairperson)'));
    $v->maxLen('approvedBy', $by, 100, __('Approved by'));
    $date = $v->date('approvedDate', $_POST['approvedDate'] ?? '', __('Approval date'), true, date('Y-m-d'), $act['act_date'] ?? null);
    if ($v->hasErrors()) $v->failAndExit($title);
    try {
        Kdbf::approveAct($id, $by, $date);
    } catch (Throwable $e) {
        Ui::toast($title, $e->getMessage(), 'error');
        exit();
    }
    writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'] . ' approve write-off act ' . Kdbf::actLabel($act));
    Ui::toast($title, __('The act is approved and the items are written off'), 'success');
    Ui::reload($self . '?action=detail&id=' . $id);
    exit();
}
if (isset($_POST['cancelAct'])) {
    bg_wo_guard($title);
    $id = (int)$_POST['cancelAct'];
    $act = Kdbf::act($id);
    try {
        $last = (int)Db::value('SELECT MAX(act_no) FROM kdbf_writeoff WHERE act_year = ?', [$act['act_year'] ?? 0]);
        if ($act && $act['status'] === 'draft' && (int)$act['act_no'] === $last && !Db::value('SELECT COUNT(*) FROM item WHERE writeoff_id = ?', [$id])) {
            // последна празна чернова – изтрива се, за да не остава дупка в номерацията
            Db::q('DELETE FROM kdbf_writeoff WHERE writeoff_id = ?', [$id]);
            Ui::toast($title, __('The empty draft was deleted'), 'success');
            Ui::reload($self . '?year=' . $act['act_year']);
            exit();
        }
        Kdbf::cancelDraft($id);
    } catch (Throwable $e) {
        Ui::toast($title, $e->getMessage(), 'error');
        exit();
    }
    writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'] . ' cancel write-off act ' . Kdbf::actLabel($act));
    Ui::toast($title, __('The draft is cancelled; its number stays in the register as cancelled'), 'success');
    Ui::reload($self . '?action=detail&id=' . $id);
    exit();
}

/* ------------------------------------------------------------------ екран */
$year = (int)($_GET['year'] ?? date('Y'));
echo Ui::header($title, [__('List') => $self, __('New act') => $self . '?action=detail'],
    '<form action="' . Ui::h($self) . '" method="get" class="form-inline mt-2">' . __('Year') . '&nbsp;<input type="number" name="year" min="1900" max="2100" value="' . $year . '" class="form-control col-2" />&nbsp;<input type="submit" class="btn btn-default" value="' . __('Show') . '" /></form>');
echo Ui::submitFrame();
$statuses = Kdbf::statusLabels();

if (($_GET['action'] ?? '') === 'detail') {
    $id = (int)($_GET['id'] ?? 0);
    $a = $id ? Kdbf::act($id) : null;
    $draft = !$a || $a['status'] === 'draft';
    $date = $a['act_date'] ?? date('Y-m-d');
    $ro = $draft ? '' : ' readonly';

    echo '<div class="s-alert infoBox">' . ($a ? sprintf(__('Write-off act %s'), Ui::h(Kdbf::actLabel($a))) . ' – <b>' . Ui::h($statuses[$a['status']]) . '</b>'
            . ($a['status'] === 'approved' ? ' · ' . sprintf(__('KDBF part 2 entry No %s from %s'), (int)$a['kdbf_no'], Ui::date($a['kdbf_date'])) : '')
            : __('New write-off act')) . '</div>';

    $f = '<form method="post" action="' . Ui::h($self) . '" target="submitExec" id="kdbfActForm" class="p-3">';
    $f .= Ui::token('kdbfActForm') . '<input type="hidden" name="saveAct" value="1" /><input type="hidden" name="actID" value="' . $id . '" />';
    $f .= Ui::row(__('Act date') . ' *', '<input type="date" name="actDate" id="actDate" value="' . Ui::h($date) . '" max="' . date('Y-m-d') . '" class="form-control col-4" data-bgval="date-past" data-bg-required="1" data-bg-label="' . Ui::h(__('Act date')) . '"' . $ro . ' />');
    $f .= Ui::row(__('Act number') . ' *', '<input type="number" name="actNo" id="actNo" min="1" step="1" value="' . Ui::h($a['act_no'] ?? Kdbf::nextActNo((int)substr($date, 0, 4))) . '" class="form-control col-2" data-bgval="posint" data-bg-required="1" data-bg-label="' . Ui::h(__('Act number')) . '"' . $ro . ' />',
        __('Numbering starts from 1 every calendar year'));
    $f .= Ui::row(__('Reason') . ' *', Ui::select('reason', $reasons, $a['reason'] ?? 'WRN', 'class="form-control col-8"' . ($draft ? '' : ' disabled')),
        __('One act – one reason (Ordinance No 3, art. 30)'));
    $f .= Ui::row(__('Details'), '<input type="text" name="reasonDetails" id="reasonDetails" maxlength="255" value="' . Ui::h($a['reason_details'] ?? '') . '" class="form-control" placeholder="' . Ui::h(__('e.g. police protocol No … / date')) . '"' . $ro . ' />');
    $f .= Ui::row(__('Commission') . ' *', '<textarea name="commission" id="commission" rows="3" class="form-control" placeholder="' . Ui::h(__('Chairperson and members, one per line')) . '"' . $ro . '>' . Ui::h($a['commission'] ?? '') . '</textarea>',
        __('Appointed by order; includes a librarian and an accountant'));
    $f .= Ui::row(__('Not inventoried'), '<div class="form-inline"><input type="number" name="noninvCount" min="0" step="1" value="' . Ui::h($a['noninv_count'] ?? '0') . '" class="form-control col-2 mr-2"' . $ro . ' /> ' . __('copies, value (BGN)') . '&nbsp;<input type="text" name="noninvValue" id="noninvValue" inputmode="decimal" maxlength="11" value="' . Ui::h($a['noninv_value'] ?? '') . '" class="form-control col-3" data-bgval="price"' . $ro . ' /></div>');
    $f .= Ui::row(__('Notes'), '<textarea name="notes" rows="2" class="form-control">' . Ui::h($a['notes'] ?? '') . '</textarea>');
    if ($can_write) $f .= '<div class="text-right"><input type="submit" class="btn btn-primary" value="' . __('Save') . '" /></div>';
    $f .= '</form>';
    echo $f;
    echo Validator::jsI18nScript();
    echo '<script type="text/javascript">(function($){var y0=' . json_encode(substr($date, 0, 4)) . ';$("#actDate").on("change",function(){var y=String(this.value).slice(0,4);if(' . ($id ? 'false' : 'true') . '&&y.length===4&&y!==y0){y0=y;$.getJSON("' . MWB . 'bibliography/kdbf_ajax.php",{q:"nextActNo",year:y},function(d){if(d&&d.value)$("#actNo").val(d.value);});}});})(jQuery);</script>';

    if ($a) {
        $items = Kdbf::actItems($id);
        $dist = Kdbf::distribution($items);
        echo '<div class="p-3"><h5>' . __('Items in the act') . '</h5>';
        echo '<p>' . sprintf(__('%d items'), $dist['count']) . ' · ' . __('Value') . ': <b>' . Inventory::fmt($dist['bgn']) . ' лв.</b> / <b>' . Inventory::fmt($dist['eur']) . ' €</b>'
            . ' · <a class="notAJAX btn btn-default btn-sm" target="_blank" href="' . MWB . 'reporting/customs/bg_writeoff_list.php?id=' . $id . '">' . __('Print the list') . '</a></p>';
        if ($draft && $can_write) {
            echo '<form method="post" action="' . Ui::h($self) . '" target="submitExec" class="form-inline mb-3">' . Ui::token('kdbfActItems')
                . '<input type="hidden" name="actID" value="' . $id . '" />'
                . '<input type="text" name="addItems" id="addItems" class="form-control col-6 mr-2" placeholder="' . Ui::h(__('Inventory numbers, e.g. 120, 125-130 (barcode scanner works too)')) . '" data-bgval="ranges" autofocus />'
                . '<input type="submit" class="btn btn-default" value="' . __('Add') . '" /></form>';
        }
        if ($items) {
            echo '<table class="table table-sm table-striped"><thead><tr><th>#</th><th>' . __('Inv. No.') . '</th><th>' . __('Title') . '</th><th>' . __('Volume') . '</th><th>' . __('Year') . '</th><th>' . __('Department') . '</th><th>' . __('Status') . '</th><th class="text-right">' . __('Value') . '</th><th></th></tr></thead><tbody>';
            $n = 0;
            foreach ($items as $it) {
                $n++;
                $rm = '';
                if ($draft && $can_write) {
                    $rm = '<form method="post" action="' . Ui::h($self) . '" target="submitExec" class="d-inline">' . Ui::token('kdbfActRemove')
                        . '<input type="hidden" name="actID" value="' . $id . '" /><input type="hidden" name="removeItem" value="' . (int)$it['item_id'] . '" />'
                        . '<input type="submit" class="btn btn-sm btn-link text-danger" value="' . __('Remove') . '" /></form>';
                }
                echo '<tr><td>' . $n . '</td><td>' . Ui::h($it['item_code']) . '</td><td>' . Ui::h($it['title']) . '</td><td>' . Ui::h($it['volume']) . '</td><td>' . Ui::h($it['publish_year']) . '</td>'
                    . '<td>' . Ui::h($it['location_name']) . '</td><td>' . Ui::h($it['item_status_id']) . '</td><td class="text-right">' . Inventory::fmt($it['price']) . ' ' . Inventory::currencyLabel($it['price_currency']) . '</td><td>' . $rm . '</td></tr>';
            }
            echo '</tbody></table>';
        }
        if ($draft && $can_write) {
            if (Ui::canApprove()) {
                echo '<form method="post" action="' . Ui::h($self) . '" target="submitExec" class="border rounded p-2 mb-2" onsubmit="return confirm(' . Ui::h(json_encode(__('Approve the act? The items will be written off and the act will be locked.'))) . ')">'
                    . Ui::token('kdbfActApprove') . '<input type="hidden" name="approveAct" value="' . $id . '" />'
                    . '<div class="form-inline">' . __('Approved by') . '&nbsp;<input type="text" name="approvedBy" id="approvedBy" maxlength="100" class="form-control col-4 mr-2" placeholder="' . Ui::h(__('Chairperson of the community centre')) . '" />'
                    . __('on') . '&nbsp;<input type="date" name="approvedDate" id="approvedDate" value="' . date('Y-m-d') . '" max="' . date('Y-m-d') . '" class="form-control col-3 mr-2" />'
                    . '<input type="submit" class="btn btn-success" value="' . __('Approve the act') . '" /></div></form>';
            }
            echo '<form method="post" action="' . Ui::h($self) . '" target="submitExec" onsubmit="return confirm(' . Ui::h(json_encode(__('Cancel this draft act?'))) . ')">'
                . Ui::token('kdbfActCancel') . '<input type="hidden" name="cancelAct" value="' . $id . '" />'
                . '<input type="submit" class="btn btn-danger" value="' . __('Cancel the draft') . '" /></form>';
        }
        echo '</div>';
    }
    exit();
}

/* ------------------------------------------------------------------ списък */
$rows = Db::all('SELECT w.*, (SELECT COUNT(*) FROM item i WHERE i.writeoff_id = w.writeoff_id) AS items FROM kdbf_writeoff w WHERE w.act_year = ? ORDER BY w.act_no', [$year]);
echo '<div class="p-2">';
if (!$rows) {
    echo '<div class="alert alert-info">' . sprintf(__('No write-off acts for %d yet.'), $year) . ' <a href="' . $self . '?action=detail">' . __('New act') . '</a></div>';
} else {
    echo '<table class="table table-sm table-striped table-hover"><thead><tr><th>' . __('Act') . '</th><th>' . __('Date') . '</th><th>' . __('Reason') . '</th><th class="text-right">' . __('Items') . '</th>'
        . '<th class="text-right">' . __('Value') . ' (лв.)</th><th class="text-right">' . __('Value') . ' (€)</th><th>' . __('Status') . '</th><th>' . __('KDBF part 2') . '</th></tr></thead><tbody>';
    foreach ($rows as $a) {
        $approved = $a['status'] === 'approved';
        if (!$approved) {
            $d = Kdbf::distribution(Kdbf::actItems((int)$a['writeoff_id']));
            $bgn = $d['bgn']; $eur = $d['eur'];
        } else {
            $bgn = $a['total_value_bgn']; $eur = $a['total_value_eur'];
        }
        echo '<tr' . ($a['status'] === 'cancelled' ? ' class="text-muted"' : '') . '><td><a href="' . $self . '?action=detail&id=' . (int)$a['writeoff_id'] . '">' . (int)$a['act_no'] . '/' . (int)$a['act_year'] . '</a></td>'
            . '<td>' . Ui::date($a['act_date']) . '</td><td>' . Ui::h($reasons[$a['reason']] ?? $a['reason']) . '</td>'
            . '<td class="text-right">' . (int)$a['items'] . ($a['noninv_count'] ? ' + ' . (int)$a['noninv_count'] : '') . '</td>'
            . '<td class="text-right">' . Inventory::fmt($bgn) . '</td><td class="text-right">' . Inventory::fmt($eur) . '</td>'
            . '<td>' . Ui::h($statuses[$a['status']] ?? $a['status']) . '</td>'
            . '<td>' . ($approved ? (int)$a['kdbf_no'] . ' / ' . Ui::date($a['kdbf_date']) : '') . '</td></tr>';
    }
    echo '</tbody></table>';
}
echo '</div>';
