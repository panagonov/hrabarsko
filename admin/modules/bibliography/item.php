<?php

/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

use SLiMS\Form\FormAjaxWithCustomField;
use SLiMS\SearchEngine\DefaultEngine;
use SLiMS\SearchEngine\Engine;

/* Item Management section */

// key to authenticate
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
if (!defined('SB')) {
    require '../../../sysconfig.inc.php';
}

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start the session
require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO . 'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

# CHECK ACCESS
if ($_SESSION['uid'] != 1) {
    if (!utility::haveAccess('bibliography.item-list')) {
        die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
    }
}

$in_pop_up = false;
// check if we are inside pop-up window
if (isset($_GET['inPopUp'])) {
    $in_pop_up = true;
}

/* RECORD OPERATION */
if (isset($_POST['saveData']) and $can_read and $can_write) {
    // проверка на полетата (lib/Bg/Validator.php) – при грешка нищо не се записва
    $bgv = new \SLiMS\Bg\Validator();
    $bg_update_id = isset($_POST['updateRecordID']) ? (int)$_POST['updateRecordID'] : 0;
    $bg_old = $bg_update_id ? \SLiMS\Bg\Db::row('SELECT * FROM item WHERE item_id = ?', [$bg_update_id]) : null;
    if ($bg_update_id && !$bg_old) {
        die("Invalid updateRecordID");
    }
    $itemCode = trim(strip_tags($_POST['itemCode'] ?? ''));
    if (empty($itemCode)) {
        utility::jsToastr('Item', __('Item Code can\'t be empty!'), 'error');
        exit();
    } else {
        // отчислен екземпляр – заключен, променя се само забележката
        if ($bg_old && $bg_old['item_status_id'] === 'WO') {
            $bg_notes = trim(strip_tags($_POST['itemNotes'] ?? ''));
            \SLiMS\Bg\Db::q('UPDATE item SET item_notes = ?, last_update = NOW() WHERE item_id = ?', [$bg_notes, $bg_update_id]);
            utility::jsToastr('Item', __('The item is written off – only the notes were saved'), 'warning');
            echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url);</script>';
            exit();
        }
        // инвентарен номер = баркод: цяло число, уникален, не се сменя след деня на въвеждане
        if (!$bg_old || $itemCode !== (string)$bg_old['item_code']) {
            $bgv->inventoryNo('itemCode', $itemCode);
            if ($bg_old && !\SLiMS\Bg\Inventory::canChangeCode($bg_old['input_date'])) {
                $bgv->error('itemCode', __('The inventory number cannot be changed after the day of entry – describe the correction in "Notes"'));
            }
            if ((int)\SLiMS\Bg\Db::value('SELECT COUNT(*) FROM item WHERE item_code = ? AND item_id <> ?', [$itemCode, $bg_update_id]) > 0) {
                $bgv->error('itemCode', sprintf(__('Inventory number %s is already used'), $itemCode));
            }
            $bg_next = \SLiMS\Bg\Inventory::nextNo();
            if (ctype_digit($itemCode) && (int)$itemCode > $bg_next + 100 && empty($_POST['confirmBigJump'])) {
                $bgv->error('itemCode', sprintf(__('Inventory number %d is far above the next free number %d – please confirm'), (int)$itemCode, $bg_next));
            }
        }
        // biblio title
        $title = trim($_POST['biblioTitle']);
        $data['biblio_id'] = intval($_POST['biblioID']);
        if ($data['biblio_id'] < 1) {
            die("Invalid biblioID");
        }
        // стойностите се записват през simbio_dbop (подготвени заявки) – без escape_string
        $data['item_code'] = $itemCode;
        $data['inventory_code'] = $itemCode;
        $data['call_number'] = trim(strip_tags($_POST['callNumber']));
        $data['volume'] = trim(strip_tags($_POST['itemVolume'] ?? ''));
        $data['location_id'] = trim(strip_tags($_POST['locationID'] ?? ''));
        $data['site'] = trim(strip_tags($_POST['itemSite']));
        $data['coll_type_id'] = intval($_POST['collTypeID']);
        $data['item_status_id'] = trim(strip_tags($_POST['itemStatusID']));
        $data['source'] = intval($_POST['source']);
        if ($data['source'] < 0) {
            die("Invalid source");
        }
        $data['order_no'] = trim(strip_tags($_POST['orderNo']));
        $data['supplier_id'] = intval($_POST['supplierID']);
        if ($data['supplier_id'] < 0) {
            die("Invalid supplier_id");
        }
        $data['invoice'] = trim(strip_tags($_POST['invoice']));
        $bg_today = date('Y-m-d');
        $data['received_date'] = $bgv->date('recvDate', $_POST['recvDate'] ?? '', __('Entry date'), true, $bg_today) ?? 'literal{NULL}';
        $data['order_date'] = $bgv->date('ordDate', $_POST['ordDate'] ?? '', __('Order Date'), false, $bg_today) ?? 'literal{NULL}';
        $data['invoice_date'] = $bgv->date('invcDate', $_POST['invcDate'] ?? '', __('Invoice Date'), false, $bg_today) ?? 'literal{NULL}';
        $data['price'] = $bgv->price('price', $_POST['price'] ?? '', true) ?? 'literal{NULL}';
        $data['price_currency'] = $bgv->currency('priceCurrency', $_POST['priceCurrency'] ?? '', $data['received_date'] === 'literal{NULL}' ? null : $data['received_date']);
        $data['item_notes'] = trim(strip_tags($_POST['itemNotes'] ?? ''));
        $data['kdbf_legacy_ref'] = trim(strip_tags($_POST['kdbfLegacyRef'] ?? ''));
        $bg_entry_id = (int)($_POST['kdbfEntryID'] ?? 0);
        $data['kdbf_entry_id'] = $bg_entry_id ?: 'literal{NULL}';

        $bgv->required('locationID', $data['location_id'], __('Location'));
        if ($data['coll_type_id'] < 1) $bgv->error('collTypeID', sprintf(__('%s is required'), __('Collection Type')));
        if ($data['item_status_id'] === 'WO') $bgv->error('itemStatusID', __('Status "Written off" is set only by an approved write-off act'));
        $bgv->maxLen('callNumber', $data['call_number'], 50, __('Call Number'));
        $bgv->maxLen('itemVolume', $data['volume'], 30, __('Volume'));
        $bgv->maxLen('itemSite', $data['site'], 50, __('Shelf Location'));
        $bgv->maxLen('orderNo', $data['order_no'], 20, __('Order Number'));
        $bgv->maxLen('invoice', $data['invoice'], 20, __('Invoice'));
        $bgv->maxLen('kdbfLegacyRef', $data['kdbf_legacy_ref'], 40, __('Paper KDBF reference'));
        // връзка с КДБФ (Наредба № 3, чл. 16, т. 8)
        if ($bg_entry_id) {
            $bg_entry = \SLiMS\Bg\Kdbf::entry($bg_entry_id);
            if (!$bg_entry) {
                $bgv->error('kdbfEntryID', __('The selected KDBF entry does not exist'));
            } elseif ($data['received_date'] !== 'literal{NULL}' && $data['received_date'] < $bg_entry['entry_date']) {
                $bgv->warn('recvDate', __('Entry date is before the date of the KDBF entry'));
            }
        } elseif ($data['kdbf_legacy_ref'] === '' && ctype_digit($itemCode)) {
            $bg_was_linked = $bg_old && (!empty($bg_old['kdbf_entry_id']) || !empty($bg_old['kdbf_legacy_ref']));
            if ((int)$itemCode > \SLiMS\Bg\Inventory::floor() && (!$bg_old || $bg_was_linked)) {
                $bgv->error('kdbfEntryID', __('Choose the KDBF entry (acquisition) for this copy'));
            } else {
                $bgv->warn('kdbfEntryID', __('No KDBF entry – fill in the reference from the paper KDBF if you have it'));
            }
        }
        if ($bgv->hasErrors()) {
            $bgv->failAndExit(__('Item'));
        }
        // поправките по стар запис се описват в „Забележка“ (Наредба № 3, чл. 17)
        if ($bg_old) {
            $bg_note = \SLiMS\Bg\Inventory::protectedChangeNote($bg_old, $data, (string)$_SESSION['realname']);
            if ($bg_note) $data['item_notes'] = trim($data['item_notes'] . "\n" . $bg_note);
        }
        if ($data['volume'] === '') $data['volume'] = 'literal{NULL}';
        if ($data['kdbf_legacy_ref'] === '') $data['kdbf_legacy_ref'] = 'literal{NULL}';
        $data['input_date'] = date('Y-m-d H:i:s');
        $data['last_update'] = date('Y-m-d H:i:s');
        $data['uid'] = $_SESSION['uid'];

        // create sql op object
        $sql_op = new simbio_dbop($dbs);
        if (isset($_POST['updateRecordID'])) {
            /* UPDATE RECORD MODE */
            // remove input date
            unset($data['input_date']);
            unset($data['uid']);
            // filter update record ID
            $updateRecordID = (int)$_POST['updateRecordID'];
            if ($updateRecordID < 1) {
                die("Invalid updateRecordID");
            }
            // update the data
            $update = $sql_op->update('item', $data, "item_id=" . $updateRecordID);
            if ($update) {
                // save custom data
                if (FormAjaxWithCustomField::cf_available('item')) {
                    FormAjaxWithCustomField::saveCustomData('item_custom', 'item',  'item_id', $updateRecordID);
                }
                // write log
                writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'] . ' update item data (' . $data['item_code'] . ') with title (' . $title . ')', 'Item', 'Update');
                if ($sysconf['bibliography_item_update_notification']) {
                    utility::jsToastr('Item', __('Item Data Successfully Updated'), 'success');
                }
                $bgv->flushWarnings(__('Item'));
                if ($in_pop_up) {
                    echo '<script type="text/javascript">top.setIframeContent(\'itemIframe\', \'' . MWB . 'bibliography/iframe_item_list.php?biblioID=' . $data['biblio_id'] . '\');</script>';
                    echo '<script type="text/javascript">top.jQuery.colorbox.close();</script>';
                } else {
                    echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url);</script>';
                }
            } else {
                utility::jsToastr('Item', __('Item Data FAILED to Save. Please Contact System Administrator') . "\nDEBUG : " . $sql_op->error, 'error');
            }
            exit();
        } else {
            /* INSERT RECORD MODE */
            // insert the data
            $insert = $sql_op->insert('item', $data);
            if ($insert) {
                // save custom data
                if (FormAjaxWithCustomField::cf_available('item')) {
                    FormAjaxWithCustomField::saveCustomData('item_custom', 'item', 'item_id', $dbs->insert_id);
                }
                // write log
                writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'] . ' insert item data (' . $data['item_code'] . ') with title (' . $title . ')', 'Item', 'Add');
                utility::jsToastr('Item', __('New Item Data Successfully Saved'), 'success');
                $bgv->flushWarnings(__('Item'));
                if (!empty($data['kdbf_entry_id']) && $data['kdbf_entry_id'] !== 'literal{NULL}') $_SESSION['bg_last_kdbf_entry'] = (int)$data['kdbf_entry_id'];
                if ($in_pop_up) {
                    echo '<script type="text/javascript">top.setIframeContent(\'itemIframe\', \'' . MWB . 'bibliography/iframe_item_list.php?biblioID=' . $data['biblio_id'] . '\');</script>';
                    echo '<script type="text/javascript">top.jQuery.colorbox.close();</script>';
                } else {
                    echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\'' . $_SERVER['PHP_SELF'] . '\');</script>';
                }
            } else {
                utility::jsToastr('Item', __('Item Data FAILED to Save. Please Contact System Administrator') . "\nDEBUG : " . $sql_op->error, 'error');
            }
            exit();
        }
    }
    exit();
} else if (isset($_POST['itemID']) and !empty($_POST['itemID']) and isset($_POST['itemAction'])) {
    if (!($can_read and $can_write)) {
        die();
    }
    /* DATA DELETION PROCESS */
    // create sql op object
    $sql_op = new simbio_dbop($dbs);
    $failed_array = array();
    $error_num = 0;
    $still_on_loan = array();
    $bg_refused = array();
    if (!is_array($_POST['itemID'])) {
        // make an array
        $_POST['itemID'] = array((int)$_POST['itemID']);
    }
    // loop array
    foreach ($_POST['itemID'] as $itemID) {
        $itemID = (int)$itemID;
        // инвентарните номера не се изтриват – само запис, въведен по грешка днес
        $bg_reason = \SLiMS\Bg\Inventory::canDelete($itemID);
        if ($bg_reason !== null) {
            $bg_refused[] = $bg_reason;
            $error_num++;
            continue;
        }
        // check if the item still on loan
        $loan_q = $dbs->query('SELECT i.item_code, b.title, COUNT(l.loan_id) FROM item AS i
            LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
            LEFT JOIN loan AS l ON (i.item_code=l.item_code AND l.is_lent=1 AND l.is_return=0)
            WHERE i.item_id=' . $itemID . ' GROUP BY i.item_code');
        $loan_d = $loan_q->fetch_row();
        // if there is no loan
        if ($loan_d[2] < 1) {
            if (!$sql_op->delete('item', 'item_id=' . $itemID)) {
                $error_num++;
            } else {
                // delete custom field data
                $sql_op->delete('item_custom', 'item_id=' . $itemID);
                // write log
                writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'] . ' DELETE item data (' . $loan_d[0] . ') with title (' . $loan_d[1] . ')', 'Item', 'Delete');
            }
        } else {
            $still_on_loan[] = $loan_d[0] . ' - ' . $loan_d[1];
            $error_num++;
        }
    }

    if ($bg_refused) {
        utility::jsToastr(__('Item'), implode("\n", array_unique($bg_refused)), 'error');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\'' . $_SERVER['PHP_SELF'] . '?' . $dbs->escape_string($_POST['lastQueryStr'] ?? '') . '\');</script>';
        exit();
    }
    if ($still_on_loan) {
        $items = '';
        foreach ($still_on_loan as $item) {
            $items .= $item . "\n";
        }
        utility::jsToastr('Item on Hold', __('Item data can not be deleted because still on hold by members') . " : \n" . $items, 'error');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\'' . $_SERVER['PHP_SELF'] . '?' . $dbs->escape_string($_POST['lastQueryStr']) . '\');</script>';
        exit();
    }
    // error alerting
    if ($error_num == 0) {
        utility::jsToastr('Item', __('Item succesfully removed!'), 'success');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\'' . $_SERVER['PHP_SELF'] . '?' . $dbs->escape_string($_POST['lastQueryStr']) . '\');</script>';
    } else {
        utility::jsToastr('Item', __('Item FAILED to removed!'), 'error');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\'' . $_SERVER['PHP_SELF'] . '?' . $dbs->escape_string($_POST['lastQueryStr']) . '\');</script>';
    }
    exit();
}
/* RECORD OPERATION END */

if (!$in_pop_up) {
    /* search form */
?>
    <div class="menuBox">
        <div class="menuBoxInner itemIcon">
            <div class="per_title">
                <h2><?php echo __('Items'); ?></h2>
            </div>
            <div class="sub_section">
                <form name="search" action="<?php echo MWB; ?>bibliography/item.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?>
                    <input type="text" name="keywords" id="keywords" size="30" class="form-control col-md-3" />
                    <select name="searchby" class="form-control col-md-2">
                        <option value="item">Item</option>
                        <option value="others"><?php echo __('Others'); ?> </option>
                    </select>
                    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default" />
                </form>
            </div>
        </div>
    </div>
<?php
    /* search form end */
}
/* main content */
if (isset($_POST['detail']) or (isset($_GET['action']) and $_GET['action'] == 'detail')) {
    if (!($can_read and $can_write)) {
        die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
    }
    /* RECORD FORM */
    // try query
    $itemID = (int)isset($_POST['itemID']) ? $_POST['itemID'] : 0;
    // if ($itemID < 1) { die("Invalid itemID"); }
    $rec_q = $dbs->query('SELECT item.*, b.biblio_id, b.title, s.supplier_name
        FROM item
        LEFT JOIN biblio AS b ON item.biblio_id=b.biblio_id
        LEFT JOIN mst_supplier AS s ON item.supplier_id=s.supplier_id
        WHERE item_id=' . $itemID);
    $rec_d = $rec_q->fetch_assoc();

    // create new instance
    $form = new FormAjaxWithCustomField('itemForm', $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'post');
    $form->submit_button_attr = 'name="saveData" value="' . __('Save') . '" class="s-btn btn btn-default"';
    // form table attributes
    $form->table_attr = 'id="dataList" class="s-table table"';
    $form->table_header_attr = 'class="alterCell font-weight-bold"';
    $form->table_content_attr = 'class="alterCell2"';

    if ($in_pop_up) {
        $form->back_button = false;
    }

    // edit mode flag set
    if ($rec_q->num_rows > 0) {
        $form->edit_mode = true;
        // record ID for delete process
        if (!$in_pop_up) {
            $form->record_id = $itemID;
        } else {
            $form->addHidden('updateRecordID', $itemID);
        }
        // form record title
        $form->record_title = $rec_d['title'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="' . __('Update') . '" class="btn btn-default"';
        // default biblio title and biblio ID
        $b_title = $rec_d['title'];
        $b_id = $rec_d['biblio_id'];
        if (trim($rec_d['call_number'] ?? '') == '') {
            $biblio_q = $dbs->query('SELECT call_number FROM biblio WHERE biblio_id=' . $rec_d['biblio_id']);
            $biblio_d = $biblio_q->fetch_assoc();
            $rec_d['call_number'] = $biblio_d['call_number'];
        }
    } else {
        // get biblio title and biblio ID from database if we are not on edit mode
        $biblioID = 0;
        if (isset($_GET['biblioID'])) {
            $biblioID = (int)$_GET['biblioID'];
        }
        $biblio_q = $dbs->query('SELECT biblio_id, title, call_number FROM biblio WHERE biblio_id=' . $biblioID);
        $biblio_d = $biblio_q->fetch_assoc();
        $b_title = $biblio_d['title'];
        $b_id = $biblio_d['biblio_id'];
        $def_call_number = $biblio_d['call_number'];
    }

    /* Form Element(s) */
    // title
    if (!$in_pop_up) {
        $str_input = $b_title;
        $str_input .= '<div class="makeHidden"><a class="s-btn btn btn-default notAJAX openPopUp" href="' . MWB . 'bibliography/pop_biblio.php?inPopUp=true&action=detail&itemID=' . ($rec_d['biblio_id'] ?? '') . '&itemCollID=' . ($rec_d['item_id'] ?? '') . '" width="750" height="500" title="' . __('Edit Biblographic data') . '">' . __('Edit Biblographic data') . '</a></div>';
    } else {
        $str_input = $b_title;
    }
    $form->addAnything(__('Title'), $str_input);
    $form->addHidden('biblioTitle', $b_title);
    $form->addHidden('biblioID', $b_id);
    // инвентарен номер = баркод (пореден, само цифри, не се използва повторно)
    $bg_next_inv = \SLiMS\Bg\Inventory::nextNo();
    $bg_is_wo = ($rec_d['item_status_id'] ?? '') === 'WO';
    $bg_code_locked = $form->edit_mode && !\SLiMS\Bg\Inventory::canChangeCode($rec_d['input_date'] ?? '');
    $bg_h = fn($v) => htmlspecialchars((string)$v);
    $str_input  = '<div class="container-fluid">';
    $str_input .= '<div class="row">';
    $str_input .= '<input type="text" name="itemCode" id="itemCode" value="' . $bg_h($rec_d['item_code'] ?? $bg_next_inv) . '"'
        . ' onblur="ajaxCheckID(\'' . SWB . 'admin/AJAX_check_id.php\', \'item\', \'item_code\', \'msgBox\', \'itemCode\')" style="width: 50%;" class="form-control col-5"'
        . ' inputmode="numeric" maxlength="20" data-bg-label="' . $bg_h(__('Item Code')) . '"'
        . ($form->edit_mode ? '' : ' data-bgval="invno" data-bg-next="' . $bg_next_inv . '"')
        . ($bg_code_locked || $bg_is_wo ? ' readonly' : '') . ' />';
    $str_input .= '<span id="msgBox" class="col p-2"></span>';
    $str_input .= '</div>';
    $str_input .= '<small class="form-text text-muted">' . ($form->edit_mode ? __('The inventory number cannot be changed after the day of entry') : sprintf(__('The inventory number is also the barcode. Next free number: %d'), $bg_next_inv)) . '</small>';
    $str_input .= '</div>';
    $form->addAnything(__('Item Code'), $str_input);
    // call number: префиксът на отдела се добавя автоматично (локална таблица, както в COBISS)
    $bg_base_cn = $rec_d['call_number'] ?? $def_call_number;
    $bg_biblio_cn = (string)\SLiMS\Bg\Db::value('SELECT call_number FROM biblio WHERE biblio_id = ?', [(int)$b_id]);
    $form->addTextField('text', 'callNumber', __('Call Number'), $bg_h($bg_base_cn), 'style="width: 50%;" class="form-control" maxlength="50" data-bg-base="' . $bg_h($bg_biblio_cn) . '"');
    // том / част
    $form->addTextField('text', 'itemVolume', __('Volume'), $bg_h($rec_d['volume'] ?? ''), 'style="width: 20%;" class="form-control" maxlength="30" placeholder="2"', __('Volume or part (inventory book column "Volume")'));
    // item location
    // get location data related to this record from database
    $location_q = $dbs->query("SELECT location_id, location_name FROM mst_location");
    $location_options = array(array('', '-- ' . __('Location') . ' --'));
    while ($location_d = $location_q->fetch_row()) {
        $location_options[] = array($location_d[0], $location_d[1]);
    }
    $form->addSelectList('locationID', __('Location') . ' *', $location_options, $rec_d['location_id'] ?? '', 'style="width: 50%" class="form-control"');
    // item site
    $form->addTextField('text', 'itemSite', __('Shelf Location'), $bg_h($rec_d['site'] ?? ''), 'style="width: 50%;" class="form-control" maxlength="50"');
    // collection type
    // get collection type data related to this record from database
    $coll_type_q = $dbs->query("SELECT coll_type_id, coll_type_name FROM mst_coll_type");
    $coll_type_options = array(array('', '-- ' . __('Collection Type') . ' --'));
    while ($coll_type_d = $coll_type_q->fetch_row()) {
        $coll_type_options[] = array($coll_type_d[0], $coll_type_d[1]);
    }
    $form->addSelectList('collTypeID', __('Collection Type') . ' *', $coll_type_options, $rec_d['coll_type_id'] ?? '', 'style="width: 40%" class="form-control"');
    // item status – „Отчислен“ се поставя само от утвърден акт за отчисляване
    $item_status_q = $dbs->query("SELECT item_status_id, item_status_name FROM mst_item_status");
    $item_status_options[] = array('0', __('Available'));
    while ($item_status_d = $item_status_q->fetch_row()) {
        if ($item_status_d[0] === 'WO' && !$bg_is_wo) continue;
        $item_status_options[] = array($item_status_d[0], $item_status_d[1]);
    }
    $form->addSelectList('itemStatusID', __('Item Status'), $item_status_options, $rec_d['item_status_id'] ?? '', 'style="width:40%" class="form-control"' . ($bg_is_wo ? ' disabled' : ''));
    if (!empty($rec_d['writeoff_id'])) {
        $bg_act = \SLiMS\Bg\Kdbf::act((int)$rec_d['writeoff_id']);
        if ($bg_act) {
            $bg_status = \SLiMS\Bg\Kdbf::statusLabels()[$bg_act['status']] ?? $bg_act['status'];
            $form->addAnything(__('Write-off act'), '<b>' . $bg_h(\SLiMS\Bg\Kdbf::actLabel($bg_act)) . '</b> (' . $bg_h($bg_status) . ')');
        }
    }
    // order number
    $form->addTextField('text', 'orderNo', __('Order Number'), $bg_h($rec_d['order_no'] ?? ''), 'style="width: 40%;" class="form-control" maxlength="20"');
    // order date
    $form->addDateField('ordDate', __('Order Date'), $rec_d['order_date'] ?? '', 'class="form-control" max="' . date('Y-m-d') . '" data-bgval="date-past" data-bg-label="' . $bg_h(__('Order Date')) . '"');
    // дата на вписване (постъпване) – колона 1 на инвентарната книга
    $form->addDateField('recvDate', __('Entry date') . ' *', $rec_d['received_date'] ?? date('Y-m-d'), 'class="form-control" max="' . date('Y-m-d') . '" data-bgval="date-past" data-bg-required="1" data-bg-label="' . $bg_h(__('Entry date')) . '"');
    // запис в КДБФ (Наредба № 3, чл. 16, т. 8)
    $bg_entry_sel = (int)($rec_d['kdbf_entry_id'] ?? ($form->edit_mode ? 0 : ($_SESSION['bg_last_kdbf_entry'] ?? 0)));
    $bg_kdbf_options = \SLiMS\Bg\BatchItems::kdbfOptionsHtml($bg_entry_sel);
    if ($bg_kdbf_options !== '') {
        $form->addAnything(__('KDBF entry'), '<select name="kdbfEntryID" id="kdbfEntryID" class="form-control" style="width: 70%">' . $bg_kdbf_options . '</select>');
    }
    $form->addTextField('text', 'kdbfLegacyRef', __('Paper KDBF reference'), $bg_h($rec_d['kdbf_legacy_ref'] ?? ''), 'style="width: 40%;" class="form-control" maxlength="40" placeholder="12/1987"', __('For books entered before the system: number/year of the entry in the paper KDBF'));
    // item supplier
    // get item status data from database
    $supplier_q = $dbs->query("SELECT supplier_id, supplier_name FROM mst_supplier");
    $supplier_options[] = array('0', __('Not Applicable'));
    while ($supplier_d = $supplier_q->fetch_row()) {
        $supplier_options[] = array($supplier_d[0], $supplier_d[1]);
    }
    $form->addSelectList('supplierID', __('Supplier'), $supplier_options, $rec_d['supplier_id'] ?? '', 'class="form-control"');
    // item source
    $source_options[] = array('1', __('Buy'));
    $source_options[] = array('2', __('Prize/Grant'));
    $form->addRadio('source', __('Source'), $source_options, !empty($rec_d['source']) ? $rec_d['source'] : '1');
    // item invoice
    $form->addTextField('text', 'invoice', __('Invoice'), $bg_h($rec_d['invoice'] ?? ''), 'style="width: 100%;" class="form-control" maxlength="20"');
    // invoice date
    $form->addDateField('invcDate', __('Invoice Date'), $rec_d['invoice_date'] ?? '', 'class="form-control" max="' . date('Y-m-d') . '" data-bgval="date-past" data-bg-label="' . $bg_h(__('Invoice Date')) . '"');
    // price: цена + валута (EUR / BGN), с до 2 знака след десетичната точка
    $bg_currencies = array_map(fn($c) => is_array($c) ? $c : array($c, $c), $sysconf['currencies']);
    $str_input  = '<div class="container-fluid">';
    $str_input .= '<div class="row">';
    $str_input .= '<input type="text" name="price" id="price" value="' . $bg_h(isset($rec_d['price']) && $rec_d['price'] !== null ? number_format((float)$rec_d['price'], 2, '.', '') : '') . '" style="width: 40%;" class="form-control col-4" inputmode="decimal" maxlength="11" placeholder="12.50" data-bgval="price" data-bg-required="1" data-bg-label="' . $bg_h(__('Price')) . '" />';
    $str_input .= simbio_form_element::selectList('priceCurrency', $bg_currencies, $rec_d['price_currency'] ?? \SLiMS\Bg\Inventory::defaultCurrency(), 'style="width: 10%;" class="form-control col-2"');
    $str_input .= '</div>';
    $str_input .= '<small class="form-text text-muted">' . __('For donations enter an estimated value. Reports also show the amount in euro (1 EUR = 1.95583 BGN).') . '</small>';
    $str_input .= '</div>';
    $form->addAnything(__('Price') . ' *', $str_input);
    // забележка (колона „Забележка“ на инвентарната книга; поправките се описват тук)
    $form->addTextField('textarea', 'itemNotes', __('Item Notes'), $bg_h($rec_d['item_notes'] ?? ''), 'rows="2" class="form-control"', __('Corrections of the record are described here with date (Ordinance No 3, art. 17)'));

    // load custom field
    $form->loadCustomField('item', 'item_id', $itemID);

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="s-alert infoBox">' . __('You are going to edit Item data') . ': <b>' . $rec_d['title'] . '</b> ' //mfc
            . '<br />' . __('Last Updated') . '&nbsp;' . date('d F Y h:i:s', strtotime($rec_d['last_update']));
        echo '</div>' . "\n";
    }
    // print out the form object
    echo $form->printOut();
    echo \SLiMS\Bg\Validator::jsI18nScript();
    // сигнатурата на екземпляра = префикс на отдела + сигнатурата на заглавието
    $bg_prefixes = json_encode(\SLiMS\Bg\Inventory::locationPrefixes(), JSON_UNESCAPED_UNICODE);
    echo <<<HTML
<script type="text/javascript">
(function ($) {
  var prefixes = {$bg_prefixes};
  var cn = $('#callNumber'), base = String(cn.data('bg-base') || '');
  function compose(loc) { var p = prefixes[String(loc || '').toUpperCase()] || ''; return (p && base) ? (p + ' ' + base) : base; }
  var auto = compose($('#locationID').val());
  $('#locationID').on('change', function () {
    if (cn.val() === '' || cn.val() === base || cn.val() === auto) { auto = compose($(this).val()); cn.val(auto); }
  });
})(jQuery);
</script>
HTML;
} else {
    require SIMBIO . 'simbio_UTILS/simbio_tokenizecql.inc.php';
    require LIB . 'biblio_list_model.inc.php';

    if (in_array(Engine::active(), [DefaultEngine::class, \SLiMS\SearchEngine\BgCatalogEngine::class], true) || (isset($_GET['searchby']) && $_GET['searchby'] == 'item')) {
        require LIB . 'biblio_list.inc.php';
        $title_field_idx = 1;
        // callback function to show title and authors in datagrid
        function showTitleAuthors($obj_db, $array_data)
        {
            global $title_field_idx;
            // biblio author detail
            $_biblio_q = $obj_db->query('SELECT b.title, a.author_name FROM biblio AS b
                LEFT JOIN biblio_author AS ba ON b.biblio_id=ba.biblio_id
                LEFT JOIN mst_author AS a ON ba.author_id=a.author_id
                WHERE b.biblio_id=' . $array_data[$title_field_idx]);
            echo $obj_db->error;
            $_authors = '';
            while ($_biblio_d = $_biblio_q->fetch_row()) {
                $_title = $_biblio_d[0];
                $_authors .= $_biblio_d[1] . ' - ';
            }
            $_authors = substr_replace($_authors, '', -3);
            $_output = '<div style="float: left;"><span class="title">' . $_title . '</span><div class="authors">' . $_authors . '</div></div>';
            return $_output;
        }

        /* ITEM LIST */
        // table spec
        $table_spec = 'item
            LEFT JOIN biblio ON item.biblio_id=biblio.biblio_id
            LEFT JOIN mst_location AS loc ON item.location_id=loc.location_id
            LEFT JOIN mst_coll_type AS ct ON item.coll_type_id=ct.coll_type_id';

        // create datagrid
        $datagrid = new simbio_datagrid();
        if ($can_write) {
            $datagrid->setSQLColumn(
                'item.item_id',
                'item.item_code AS \'' . __('Item Code') . '\'',
                'item.biblio_id AS \'' . __('Title') . '\'',
                'ct.coll_type_name AS \'' . __('Collection Type') . '\'',
                'loc.location_name AS \'' . __('Location') . '\'',
                'biblio.classification AS \'' . __('Classification') . '\'',
                'item.last_update AS \'' . __('Last Updated') . '\''
            );
            $datagrid->modifyColumnContent(2, 'callback{showTitleAuthors}');
            $title_field_idx = 2;
        } else {
            $datagrid->setSQLColumn(
                'item.item_code AS \'' . __('Item Code') . '\'',
                'item.biblio_id AS \'' . __('Title') . '\'',
                'ct.coll_type_name AS \'' . __('Collection Type') . '\'',
                'loc.location_name AS \'' . __('Location') . '\'',
                'biblio.classification AS \'' . __('Classification') . '\'',
                'item.last_update AS \'' . __('Last Updated') . '\''
            );
            $datagrid->modifyColumnContent(1, 'callback{showTitleAuthors}');
        }
        $datagrid->setSQLorder('item.last_update DESC');
    } else {
        require LIB . 'biblio_list_index.inc.php';

        // callback function to show title and authors in datagrid
        function showTitleAuthors($obj_db, $array_data)
        {
            global $title_field_idx;
            $_output = '<div style="float: left;"><span class="title">' . $array_data[$title_field_idx] . '</span><div class="authors">' . $array_data[$title_field_idx + 1] . '</div></div>';
            return $_output;
        }

        /* ITEM LIST */
        // table spec
        $table_spec = '(item
            LEFT JOIN mst_location AS loc ON item.location_id=loc.location_id
            LEFT JOIN mst_coll_type AS ct ON item.coll_type_id=ct.coll_type_id)
            LEFT JOIN search_biblio AS `index` ON item.biblio_id=index.biblio_id';

        // create datagrid
        $datagrid = new simbio_datagrid();
        if ($can_write) {
            $datagrid->setSQLColumn(
                'item.item_id',
                'item.item_code AS \'' . __('Item Code') . '\'',
                'index.title AS \'' . __('Title') . '\'',
                'index.author AS \'' . __('Author') . '\'',
                'ct.coll_type_name AS \'' . __('Collection Type') . '\'',
                'loc.location_name AS \'' . __('Location') . '\'',
                #'index.classification AS \''.__('Classification').'\'',
                'item.call_number AS \'' . __('Call Number') . '\'',
                'item.last_update AS \'' . __('Last Updated') . '\''
            );
            $datagrid->invisible_fields = array(2);
            $title_field_idx = 2;
            $datagrid->modifyColumnContent(2, 'callback{showTitleAuthors}');
        } else {
            $datagrid->setSQLColumn(
                'item.item_code AS \'' . __('Item Code') . '\'',
                'index.title AS \'' . __('Title') . '\'',
                'index.author AS \'' . __('Author') . '\'',
                'ct.coll_type_name AS \'' . __('Collection Type') . '\'',
                'loc.location_name AS \'' . __('Location') . '\'',
                #'index.classification AS \''.__('Classification').'\'',
                'item.call_number AS \'' . __('Call Number') . '\'',
                'item.last_update AS \'' . __('Last Updated') . '\''
            );
            $datagrid->invisible_fields = array(2);
            $title_field_idx = 1;
            $datagrid->modifyColumnContent(1, 'callback{showTitleAuthors}');
        }
        $datagrid->setSQLorder('item.last_update DESC');
    }


    // is there any search
    if (isset($_GET['keywords']) && $_GET['keywords']) {
        $keywords = utility::filterData('keywords', 'get', true, true, true);
        $searchable_fields = array('title', 'author', 'subject', 'itemcode');
        $search_str = '';
        // if no qualifier in fields
        if (!preg_match('@[a-z]+\s*=\s*@i', $keywords)) {
            foreach ($searchable_fields as $search_field) {
                $search_str .= $search_field . '=' . $keywords . ' OR ';
            }
        } else {
            $search_str = $keywords;
        }
        $biblio_list = new biblio_list($dbs, 20);
        $criteria = $biblio_list->setSQLcriteria($search_str);
    }
    if (isset($criteria)) {
        $datagrid->setSQLcriteria('(' . $criteria['sql_criteria'] . ')');
    }

    // set table and table header attributes
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];

    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, ($can_read and $can_write));
    if (isset($_GET['keywords']) and $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
        echo '<div class="infoBox">' . $msg . ' : ' . htmlspecialchars($_GET['keywords']) . '<div>' . __('Query took') . ' <b>' . $datagrid->query_time . '</b> ' . __('second(s) to complete') . '</div></div>'; //mfc
    }

    echo $datagrid_result;
}
/* main content end */
