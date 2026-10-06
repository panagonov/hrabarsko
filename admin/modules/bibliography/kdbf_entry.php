<?php
/**
 * КДБФ част 1 – постъпления (Наредба № 3 от 18.11.2014 г., чл. 13–15).
 * Всяко постъпление (покупка, дарение…) е един запис; екземплярите се свързват с него.
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

Ui::requireAccess('bibliography.kdbf-entries');
$can_write = utility::havePrivilege('bibliography', 'w');
$self = MWB . 'bibliography/kdbf_entry.php';
$title = __('Acquisitions (KDBF part 1)');

/* ------------------------------------------------------------------ запис */
if (isset($_POST['saveEntry'])) {
    if (!$can_write) die();
    if (!Ui::tokenValid()) {
        Ui::toast($title, __('Invalid form submission token!'), 'error');
        exit();
    }
    $id = (int)($_POST['entryID'] ?? 0);
    $v = new Validator();
    $today = date('Y-m-d');
    $date = $v->date('entryDate', $_POST['entryDate'] ?? '', __('Entry date'), true, $today);
    $year = $date ? (int)substr($date, 0, 4) : (int)date('Y');
    $no = $v->positiveInt('entryNo', $_POST['entryNo'] ?? '', __('Entry number'));
    $methods = Kdbf::acqMethods();
    $method = (string)($_POST['acqMethod'] ?? '');
    $v->oneOf('acqMethod', $method, array_keys($methods), __('How acquired'));
    $source = trim(strip_tags($_POST['sourceName'] ?? ''));
    $supplier = (int)($_POST['supplierID'] ?? 0);
    if ($source === '' && $supplier < 1) $v->error('sourceName', __('Enter from whom the books were received (or choose a supplier)'));
    $v->maxLen('sourceName', $source, 200, __('From whom'));
    $docType = trim(strip_tags($_POST['docType'] ?? ''));
    $docNo = trim(strip_tags($_POST['docNo'] ?? ''));
    $v->maxLen('docType', $docType, 50, __('Document type'));
    $v->maxLen('docNo', $docNo, 30, __('Document number'));
    $docDate = $v->date('docDate', $_POST['docDate'] ?? '', __('Document date'), false, $date ?: $today);
    $total = $v->positiveInt('totalCount', $_POST['totalCount'] ?? '', __('Total number of copies'));
    $value = $v->price('totalValue', $_POST['totalValue'] ?? '', true);
    $currency = $v->currency('currency', $_POST['currency'] ?? '', $date);
    $noninv = $v->nonNegativeInt('noninvCount', $_POST['noninvCount'] ?? '', __('Not inventoried (count)'));
    $noninvValue = $v->price('noninvValue', $_POST['noninvValue'] ?? '', false);
    if ($total !== null && $noninv > $total) $v->error('noninvCount', __('Not inventoried copies cannot be more than the total'));
    $notes = trim(strip_tags($_POST['notes'] ?? ''));

    if ($no !== null && $date) {
        $dup = Db::value('SELECT entry_id FROM kdbf_entry WHERE entry_year = ? AND entry_no = ? AND entry_id <> ?', [$year, $no, $id]);
        if ($dup) $v->error('entryNo', sprintf(__('Entry number %d/%d is already used'), $no, $year));
        $suggested = Kdbf::nextEntryNo($year);
        if (!$id && $no > $suggested) $v->warn('entryNo', sprintf(__('Numbers %d–%d of %d are skipped'), $suggested, $no - 1, $year));
    }
    if ($v->hasErrors()) $v->failAndExit($title);

    $params = [$year, $no, $date, $method, $source ?: null, $supplier ?: null, $docType ?: null, $docNo ?: null, $docDate,
        $total, $value, $currency, $noninv, $noninvValue, $notes ?: null];
    try {
        if ($id) {
            Db::q('UPDATE kdbf_entry SET entry_year=?, entry_no=?, entry_date=?, acq_method=?, source_name=?, supplier_id=?, doc_type=?, doc_no=?, doc_date=?,
                          total_count=?, total_value=?, currency=?, noninv_count=?, noninv_value=?, notes=?, last_update=NOW(), uid=? WHERE entry_id=?',
                array_merge($params, [(int)$_SESSION['uid'], $id]));
        } else {
            Db::q('INSERT INTO kdbf_entry (entry_year, entry_no, entry_date, acq_method, source_name, supplier_id, doc_type, doc_no, doc_date,
                          total_count, total_value, currency, noninv_count, noninv_value, notes, input_date, last_update, uid)
                   VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW(),?)', array_merge($params, [(int)$_SESSION['uid']]));
            $id = (int)Db::lastInsertId();
        }
    } catch (Throwable $e) {
        Ui::toast($title, __('Data FAILED to save') . ': ' . $e->getMessage(), 'error');
        exit();
    }
    writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'] . ' save KDBF entry ' . $no . '/' . $year);
    Ui::toast($title, sprintf(__('KDBF entry %d/%d saved'), $no, $year), 'success');
    $v->flushWarnings($title);
    $_SESSION['bg_last_kdbf_entry'] = $id;
    Ui::reload($self . '?action=detail&id=' . $id);
    exit();
}

/* ------------------------------------------------------------------ изтриване */
if (isset($_POST['deleteEntry'])) {
    if (!$can_write) die();
    if (!Ui::tokenValid()) {
        Ui::toast($title, __('Invalid form submission token!'), 'error');
        exit();
    }
    $id = (int)$_POST['deleteEntry'];
    $e = Kdbf::entry($id);
    $items = (int)Db::value('SELECT COUNT(*) FROM item WHERE kdbf_entry_id = ?', [$id]);
    $last = $e ? (int)Db::value('SELECT MAX(entry_no) FROM kdbf_entry WHERE entry_year = ?', [$e['entry_year']]) : 0;
    if (!$e) exit();
    if ($items > 0) {
        Ui::toast($title, __('The entry has items linked to it and cannot be deleted'), 'error');
    } elseif ((int)$e['entry_no'] !== $last) {
        Ui::toast($title, __('Only the last entry of the year can be deleted (the numbering must stay continuous)'), 'error');
    } else {
        Db::q('DELETE FROM kdbf_entry WHERE entry_id = ?', [$id]);
        writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'] . ' delete KDBF entry ' . $e['entry_no'] . '/' . $e['entry_year']);
        Ui::toast($title, __('Entry deleted'), 'success');
        Ui::reload($self . '?year=' . $e['entry_year']);
    }
    exit();
}

/* ------------------------------------------------------------------ екран */
$year = (int)($_GET['year'] ?? date('Y'));
echo Ui::header($title, [__('List') => $self, __('New entry') => $self . '?action=detail'],
    '<form action="' . Ui::h($self) . '" method="get" class="form-inline mt-2">' . __('Year') . '&nbsp;<input type="number" name="year" min="1900" max="2100" value="' . $year . '" class="form-control col-2" />&nbsp;<input type="submit" class="btn btn-default" value="' . __('Show') . '" /></form>');
echo Ui::submitFrame();

$methods = Kdbf::acqMethods();

if (($_GET['action'] ?? '') === 'detail') {
    $id = (int)($_GET['id'] ?? 0);
    $e = $id ? Kdbf::entry($id) : null;
    $date = $e['entry_date'] ?? date('Y-m-d');
    $suppliers = ['0' => '— ' . __('Not Applicable') . ' —'];
    foreach (Db::all('SELECT supplier_id, supplier_name FROM mst_supplier ORDER BY supplier_name') as $r) $suppliers[$r['supplier_id']] = $r['supplier_name'];
    $currencies = [];
    foreach ($sysconf['currencies'] as $c) $currencies[is_array($c) ? $c[0] : $c] = is_array($c) ? $c[1] : $c;

    $f = '<form method="post" action="' . Ui::h($self) . '" target="submitExec" id="kdbfEntryForm" class="p-3">';
    $f .= Ui::token('kdbfEntryForm');
    $f .= '<input type="hidden" name="saveEntry" value="1" /><input type="hidden" name="entryID" value="' . $id . '" />';
    $f .= Ui::row(__('Entry date') . ' *', '<input type="date" name="entryDate" id="entryDate" value="' . Ui::h($date) . '" max="' . date('Y-m-d') . '" class="form-control col-4" data-bgval="date-past" data-bg-required="1" data-bg-label="' . Ui::h(__('Entry date')) . '" />');
    $f .= Ui::row(__('Entry number') . ' *', '<input type="number" name="entryNo" id="entryNo" min="1" step="1" value="' . Ui::h($e['entry_no'] ?? Kdbf::nextEntryNo((int)substr($date, 0, 4))) . '" class="form-control col-2" data-bgval="posint" data-bg-required="1" data-bg-label="' . Ui::h(__('Entry number')) . '" />',
        __('Numbering starts from 1 every calendar year'));
    $f .= Ui::row(__('How acquired') . ' *', Ui::select('acqMethod', $methods, $e['acq_method'] ?? 'BUY', 'class="form-control col-6"'));
    $f .= Ui::row(__('From whom'), '<input type="text" name="sourceName" id="sourceName" maxlength="200" value="' . Ui::h($e['source_name'] ?? '') . '" class="form-control" placeholder="' . Ui::h(__('e.g. donation from Ivan Petrov, Ministry of Culture programme')) . '" />');
    $f .= Ui::row(__('Supplier'), Ui::select('supplierID', $suppliers, $e['supplier_id'] ?? '0', 'class="form-control col-6"'));
    $f .= Ui::row(__('Accounting document'), '<div class="form-inline"><input type="text" name="docType" id="docType" maxlength="50" value="' . Ui::h($e['doc_type'] ?? '') . '" class="form-control col-4 mr-2" placeholder="' . Ui::h(__('Invoice / donation deed / protocol')) . '" />'
        . '<input type="text" name="docNo" id="docNo" maxlength="30" value="' . Ui::h($e['doc_no'] ?? '') . '" class="form-control col-3 mr-2" placeholder="№" />'
        . '<input type="date" name="docDate" id="docDate" value="' . Ui::h($e['doc_date'] ?? '') . '" max="' . date('Y-m-d') . '" class="form-control col-4" data-bgval="date-past" data-bg-label="' . Ui::h(__('Document date')) . '" /></div>',
        __('Type, number and date of the primary accounting document'));
    $f .= Ui::row(__('Total number of copies') . ' *', '<input type="number" name="totalCount" id="totalCount" min="1" step="1" value="' . Ui::h($e['total_count'] ?? '') . '" class="form-control col-2" data-bgval="posint" data-bg-required="1" data-bg-label="' . Ui::h(__('Total number of copies')) . '" />');
    $f .= Ui::row(__('Total value') . ' *', '<div class="form-inline"><input type="text" name="totalValue" id="totalValue" inputmode="decimal" maxlength="11" value="' . Ui::h($e['total_value'] ?? '') . '" class="form-control col-3 mr-2" data-bgval="price" data-bg-required="1" data-bg-label="' . Ui::h(__('Total value')) . '" placeholder="125.50" />'
        . Ui::select('currency', $currencies, $e['currency'] ?? Inventory::defaultCurrency($date), 'class="form-control col-3"') . '</div>');
    $f .= Ui::row(__('Not inventoried'), '<div class="form-inline"><input type="number" name="noninvCount" id="noninvCount" min="0" step="1" value="' . Ui::h($e['noninv_count'] ?? '0') . '" class="form-control col-2 mr-2" /> ' . __('copies, value') . '&nbsp;<input type="text" name="noninvValue" id="noninvValue" inputmode="decimal" maxlength="11" value="' . Ui::h($e['noninv_value'] ?? '') . '" class="form-control col-3" data-bgval="price" data-bg-label="' . Ui::h(__('Not inventoried (value)')) . '" /></div>',
        __('Brochures, periodicals and other documents that are registered only in total'));
    $f .= Ui::row(__('Notes'), '<textarea name="notes" id="notes" rows="2" class="form-control">' . Ui::h($e['notes'] ?? '') . '</textarea>');
    if ($can_write) $f .= '<div class="text-right"><input type="submit" class="btn btn-primary" value="' . __('Save') . '" /></div>';
    $f .= '</form>';
    echo '<div class="s-alert infoBox">' . ($e ? sprintf(__('KDBF entry %s'), Ui::h(Kdbf::entryLabel($e))) : __('New KDBF entry')) . '</div>';
    echo $f;
    echo \SLiMS\Bg\Validator::jsI18nScript();
    // предложение за номер при смяна на годината
    echo '<script type="text/javascript">(function($){var y0=' . json_encode(substr($date, 0, 4)) . ';$("#entryDate").on("change",function(){var y=String(this.value).slice(0,4);if(' . ($id ? 'false' : 'true') . '&&y.length===4&&y!==y0){y0=y;$.getJSON("' . MWB . 'bibliography/kdbf_ajax.php",{q:"nextEntryNo",year:y},function(d){if(d&&d.value)$("#entryNo").val(d.value);});}});})(jQuery);</script>';

    if ($e) {
        $sum = Kdbf::entrySummary($id);
        echo '<div class="p-3"><h5>' . __('Inventoried copies') . '</h5>';
        echo '<p>' . sprintf(__('Inventoried %d of %d (remaining %d)'), $sum['dist']['count'], $sum['expected'], $sum['remaining'])
            . ' · ' . __('Value') . ': ' . Inventory::fmt($sum['dist']['bgn']) . ' лв. / ' . Inventory::fmt($sum['dist']['eur']) . ' €'
            . ($sum['ranges'] !== '' ? ' · ' . __('Inventory numbers') . ': <b>' . Ui::h($sum['ranges']) . '</b>' : '') . '</p>';
        echo '<p><a class="btn btn-success" href="' . MWB . 'bibliography/index.php?action=detail&kdbfEntryID=' . $id . '">' . __('Add a title to this entry') . '</a> ';
        echo '<a class="btn btn-default notAJAX" target="_blank" href="' . MWB . 'reporting/customs/bg_inventory_book.php?kdbf=' . $id . '&reportView=true&print=1">' . __('Print inventory book (this entry)') . '</a> ';
        echo '<a class="btn btn-default" href="' . MWB . 'bibliography/bg_labels.php?source=kdbf&kdbf=' . $id . '">' . __('Labels and barcodes') . '</a></p>';
        if ($sum['items']) {
            echo '<table class="table table-sm table-striped"><thead><tr><th>' . __('Inv. No.') . '</th><th>' . __('Title') . '</th><th>' . __('Volume') . '</th><th>' . __('Department') . '</th><th class="text-right">' . __('Price') . '</th></tr></thead><tbody>';
            foreach ($sum['items'] as $it) {
                echo '<tr><td>' . Ui::h($it['item_code']) . '</td><td>' . Ui::h($it['title']) . '</td><td>' . Ui::h($it['volume']) . '</td><td>' . Ui::h($it['location_name']) . '</td><td class="text-right">'
                    . Inventory::fmt($it['price']) . ' ' . Inventory::currencyLabel($it['price_currency']) . '</td></tr>';
            }
            echo '</tbody></table>';
        } elseif ($can_write) {
            $f = '<form method="post" action="' . Ui::h($self) . '" target="submitExec" onsubmit="return confirm(' . Ui::h(json_encode(__('Delete this entry?'))) . ')">'
                . Ui::token('kdbfEntryDelete') . '<input type="hidden" name="deleteEntry" value="' . $id . '" />'
                . '<input type="submit" class="btn btn-danger" value="' . __('Delete entry') . '" /></form>';
            echo $f;
        }
        echo '</div>';
    }
    exit();
}

/* ------------------------------------------------------------------ списък */
$rows = Db::all('SELECT e.*, s.supplier_name FROM kdbf_entry e LEFT JOIN mst_supplier s ON s.supplier_id = e.supplier_id WHERE e.entry_year = ? ORDER BY e.entry_no', [$year]);
echo '<div class="p-2">';
if (!$rows) {
    echo '<div class="alert alert-info">' . sprintf(__('No KDBF entries for %d yet.'), $year) . ' <a href="' . $self . '?action=detail">' . __('New entry') . '</a></div>';
} else {
    echo '<table class="table table-sm table-striped table-hover"><thead><tr><th>№</th><th>' . __('Date') . '</th><th>' . __('How acquired') . '</th><th>' . __('From whom') . '</th><th>' . __('Document') . '</th>'
        . '<th class="text-right">' . __('Total') . '</th><th class="text-right">' . __('Inventoried') . '</th><th class="text-right">' . __('Value') . '</th><th>' . __('Inventory numbers') . '</th></tr></thead><tbody>';
    foreach ($rows as $e) {
        $sum = Kdbf::entrySummary((int)$e['entry_id']);
        $done = $sum['remaining'] === 0;
        echo '<tr><td><a href="' . $self . '?action=detail&id=' . (int)$e['entry_id'] . '">' . (int)$e['entry_no'] . '/' . (int)$e['entry_year'] . '</a></td>'
            . '<td>' . Ui::date($e['entry_date']) . '</td><td>' . Ui::h($methods[$e['acq_method']] ?? $e['acq_method']) . '</td>'
            . '<td>' . Ui::h($e['source_name'] ?: $e['supplier_name']) . '</td>'
            . '<td>' . Ui::h(trim(($e['doc_type'] ?? '') . ' ' . ($e['doc_no'] ? '№ ' . $e['doc_no'] : '') . ' ' . Ui::date($e['doc_date']))) . '</td>'
            . '<td class="text-right">' . (int)$e['total_count'] . '</td>'
            . '<td class="text-right">' . ($done ? '✓ ' : '') . $sum['dist']['count'] . ($e['noninv_count'] ? ' + ' . (int)$e['noninv_count'] : '') . '</td>'
            . '<td class="text-right">' . Inventory::fmt($e['total_value']) . ' ' . Inventory::currencyLabel($e['currency']) . '</td>'
            . '<td>' . Ui::h($sum['ranges']) . '</td></tr>';
    }
    echo '</tbody></table>';
}
echo '</div>';
