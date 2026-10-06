<?php
/**
 * Copyright (C) 2007,2008,2009,2010  Arie Nugraha (dicarve@yahoo.com)
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

/* Bibliography Management section */

// key to authenticate
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}

#use SLiMS\AdvancedLogging;
use SLiMS\AlLibrarian;
use SLiMS\Filesystems\Storage;
use SLiMS\Form\FormAjaxWithCustomField;
use SLiMS\Plugins;
use SLiMS\SearchEngine\DefaultEngine;
use SLiMS\SearchEngine\Engine;
use SLiMS\SearchEngine\SphinxSearchEngine;
use SLiMS\Ucs;

// key to get full database access
define('DB_ACCESS', 'fa');

if (!defined('SB')) {
    // main system configuration
    require '../../../sysconfig.inc.php';
    // start the session
    require SB . 'admin/default/session.inc.php';
}
// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');

require SB . 'admin/default/session_check.inc.php';
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO . 'simbio_DB/simbio_dbop.inc.php';
require SIMBIO . 'simbio_FILE/simbio_file_upload.inc.php';
require MDLBS . 'system/biblio_indexer.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
    die('<div class="errorBox">' . __('_You are not authorized to view this section') . '</div>');
}
# CHECK ACCESS
if ($_SESSION['uid'] != 1) {
    if (!utility::haveAccess('bibliography.bibliography-list')) {
        die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
    }
}

// execute registered hook
Plugins::getInstance()->execute(Plugins::BIBLIOGRAPHY_INIT);

// load settings
utility::loadSettings($dbs);

$in_pop_up = false;
// check if we are inside pop-up window
if (isset($_GET['inPopUp'])) {
    $in_pop_up = true;
}

if (!function_exists('getimagesizefromstring')) {
    function getimagesizefromstring($string_data)
    {
        $uri = 'data://application/octet-stream;base64,' . base64_encode($string_data);
        return getimagesize($uri);
    }
}

// RDA Content, Media and Carrier
$rda_cmc = array('content' => 'Content Type', 'media' => 'Media Type', 'carrier' => 'Carrier Type');

/* REMOVE IMAGE */
if (isset($_POST['removeImage']) && isset($_POST['bimg']) && isset($_POST['img'])) {
    // validate post image
    $biblio_id = utility::filterData('bimg', 'post', true, true, true);
    $image_name = utility::filterData('img', 'post', true, true, true);

    $query_image = $dbs->query("SELECT biblio_id FROM biblio WHERE biblio_id='{$biblio_id}' AND image='{$image_name}'");
    if ($query_image->num_rows > 0) {
        $_delete = $dbs->query(sprintf('UPDATE biblio SET image=NULL WHERE biblio_id=%d', $biblio_id));
        $_delete2 = $dbs->query(sprintf('UPDATE search_biblio SET image=NULL WHERE biblio_id=%d', $biblio_id));
        if ($_delete) {
            $postImage = stripslashes($_POST['img']);
            $postImage = str_replace('/', '', $postImage);
            @unlink(sprintf(IMGBS . 'docs/%s', $postImage));
            utility::jsToastr('Bibliography', str_replace('{imageFilename}', $_POST['img'], __('{imageFilename} successfully removed!')), 'success');
            // exit('<script type="text/javascript">$(\'#biblioImage, #imageFilename\').remove();</script>');
            exit('<img src="../lib/minigalnano/createthumb.php?filename=images/default/image.png&width=130" class="img-fluid rounded" alt="">');
        }
    }
    exit();
}
/* RECORD OPERATION */
if (isset($_POST['saveData']) AND $can_read AND $can_write) {
    # CHECK ACCESS
    if ($_SESSION['uid'] != 1) {
        if (!utility::haveAccess('bibliography.bibliography-add')) {
            utility::jsToastr('Bibliography', __('You are not authorized to view this section'), 'error');
            writeLog('staff', $_SESSION['uid'], 'system', $_SESSION['realname'] . ' try to access bibliography module without having access right from ' . $_SERVER['REMOTE_ADDR']);
            exit();
        }
    }
    if (!simbio_form_maker::isTokenValid()) {
        utility::jsToastr('Bibliography', __('Invalid form submission token!'), 'error');
        writeLog('staff', $_SESSION['uid'], 'system', 'Invalid form submission token, might be a CSRF attack from ' . $_SERVER['REMOTE_ADDR']);
        exit();
    }
    $title = trim(strip_tags($_POST['title']));
    // check form validity
    if (empty($title)) {
        utility::jsToastr('Bibliography', __('Title can not be empty'), 'error');
        exit();
    } else {
        // include custom fields file
        if (file_exists(MDLBS . 'bibliography/custom_fields.inc.php')) {
            include MDLBS . 'bibliography/custom_fields.inc.php';
        }

        // проверка на полетата преди каквото и да е записване (lib/Bg/Validator.php)
        $bgv = new \SLiMS\Bg\Validator();
        $bg_gmd_code = (string)\SLiMS\Bg\Db::value('SELECT gmd_code FROM mst_gmd WHERE gmd_id = ?', [(int)($_POST['gmdID'] ?? 0)]);
        $bg_isbn = $bgv->isbnIssn('isbn_issn', strip_tags($_POST['isbn_issn'] ?? ''));
        $bg_year = $bgv->publishYear('year', strip_tags($_POST['year'] ?? ''), ((int)($_POST['frequencyID'] ?? 0)) > 0);
        // празният избор в падащото меню идва като „NONE“
        if (strtoupper(trim((string)($_POST['class'] ?? ''))) === 'NONE') $_POST['class'] = '';
        $bgv->udc('class', trim(strip_tags(str_ireplace('NEW:', '', $_POST['class'] ?? ''))), __('Classification'));
        $bgv->authorMark('author_mark', $_POST['author_mark'] ?? '');
        $bgv->collation('collation', $_POST['collation'] ?? '', in_array($bg_gmd_code, ['', 'TE'], true));
        foreach ([
            ['sor', 200, __('Statement of Responsibility')], ['edition', 50, __('Edition')], ['collation', 100, __('Collation')],
            ['seriesTitle', 200, __('Series Title')], ['series_number', 20, __('Series Number')], ['callNumber', 50, __('Call Number')],
            ['author_mark', 10, __('Author Mark')], ['printing', 150, __('Printing House')], ['price_terms', 100, __('Price/Terms (ISBD area 8)')],
            ['class', 100, __('Classification')], ['placeID', 100, __('Publishing Place')], ['publisherID', 100, __('Publisher')],
        ] as [$bg_f, $bg_max, $bg_label]) {
            $bgv->maxLen($bg_f, trim(strip_tags(str_ireplace('NEW:', '', (string)($_POST[$bg_f] ?? '')))), $bg_max, $bg_label);
        }
        $bg_batch = \SLiMS\Bg\BatchItems::validate($bgv, $_POST, (int)$sysconf['max_insert_batch']);
        if ($bgv->hasErrors()) {
            $bgv->failAndExit(__('Bibliography'));
        }

        // create biblio_indexer class instance
        $indexer = new biblio_indexer($dbs);

        /**
         * Custom fields
         */
        if (isset($biblio_custom_fields)) {
            if (is_array($biblio_custom_fields) && $biblio_custom_fields) {
                foreach ($biblio_custom_fields as $fid => $cfield) {
                    // custom field data
                    $cf_dbfield = $cfield['dbfield'];
                    if (isset($_POST[$cf_dbfield])) {
                        if (is_array($_POST[$cf_dbfield])) {
                            foreach ($_POST[$cf_dbfield] as $value) {
                                $arr[$value] = $value;
                            }
                            $custom_data[$cf_dbfield] = serialize($arr);
                        } else {
                            $cf_val = $dbs->escape_string(strip_tags(trim($_POST[$cf_dbfield]), $sysconf['content']['allowable_tags']));
                            if ($cfield['type'] == 'numeric' && (!is_numeric($cf_val) && $cf_val != '')) {
                                utility::jsToastr(__('Bibliography'), sprintf(__('Field %s only number for allowed'), $cfield['label']), 'error');
                                exit();
                            } elseif ($cfield['type'] == 'date' && $cf_val == '') {
                                utility::jsToastr(__('Bibliography'), sprintf(__('Field %s is date format, empty not allowed'), $cfield['label']), 'error');
                                exit();
                            }
                            $custom_data[$cf_dbfield] = $cf_val;
                        }
                    } else {
                        $custom_data[$cf_dbfield] = serialize(array());
                    }
                }
            }
        }

        // Register advance custom field data
        Plugins::getInstance()->execute(Plugins::BIBLIOGRAPHY_CUSTOM_FIELD_DATA, ['custom_data' => &$custom_data]);

        // стойностите се записват през simbio_dbop (подготвени заявки) – без escape_string
        $data['title'] = $title;
        /* modified by hendro */
        $data['sor'] = trim(strip_tags($_POST['sor']));
        /* end of modification */
        $data['edition'] = trim(strip_tags($_POST['edition']));
        $data['gmd_id'] = $_POST['gmdID'];
        $data['isbn_issn'] = (string)$bg_isbn;
        $data['price_terms'] = trim(strip_tags($_POST['price_terms'] ?? ''));

        $class = str_ireplace('NEW:', '', trim(strip_tags($_POST['class'])));
        $data['classification'] = trim(strip_tags($class));
        $data['uid'] = $_SESSION['uid'];

        // check publisher
        // echo stripos($_POST['publisherID'], 'NEW:');
        if (stripos($_POST['publisherID'], 'NEW:') === 0) {
            $new_publisher = str_ireplace('NEW:', '', trim(strip_tags($_POST['publisherID'])));
            $new_id = utility::getID($dbs, 'mst_publisher', 'publisher_id', 'publisher_name', $new_publisher);
            $data['publisher_id'] = $new_id;
        } else if (intval($_POST['publisherID']) > 0) {
            $data['publisher_id'] = intval($_POST['publisherID']);
        }

        $data['publish_year'] = $bg_year;
        $data['collation'] = trim(strip_tags($_POST['collation']));
        $data['series_title'] = trim(strip_tags($_POST['seriesTitle']));
        $data['series_number'] = trim(strip_tags($_POST['series_number'] ?? ''));
        $data['call_number'] = trim(strip_tags($_POST['callNumber']));
        $data['author_mark'] = trim(strip_tags($_POST['author_mark'] ?? ''));
        $data['language_id'] = trim(strip_tags($_POST['languageID']));
        // check place
        if (stripos($_POST['placeID'], 'NEW:') === 0) {
            $new_place = str_ireplace('NEW:', '', trim(strip_tags($_POST['placeID'])));
            $new_id = utility::getID($dbs, 'mst_place', 'place_id', 'place_name', $new_place);
            $data['publish_place_id'] = $new_id;
        } else if (intval($_POST['placeID']) > 0) {
            $data['publish_place_id'] = intval($_POST['placeID']);
        }
        $data['printing'] = trim(strip_tags($_POST['printing'] ?? ''));

        $data['notes'] = trim(strip_tags($_POST['notes'], '<br><p><div><span><i><em><strong><b><code>s'));
        $data['opac_hide'] = ($_POST['opacHide'] == '0') ? 'literal{0}' : '1';
        $data['promoted'] = ($_POST['promote'] == '0') ? 'literal{0}' : '1';
        // labels
        $arr_label = array();
        if (!empty($_POST['labels'])) {
            foreach ($_POST['labels'] as $label) {
                if (trim($label) != '') {
                    $arr_label[] = array($label, isset($_POST['label_urls'][$label]) ? $_POST['label_urls'][$label] : null);
                }
            }
        }

        $data['labels'] = $arr_label ? serialize($arr_label) : 'literal{NULL}';
        $data['frequency_id'] = ($_POST['frequencyID'] == '0') ? 'literal{0}' : (integer)$_POST['frequencyID'];
        $data['spec_detail_info'] = trim(strip_tags($_POST['specDetailInfo']));

        // RDA Content, Media anda Carrier Type
        foreach ($rda_cmc as $cmc => $cmc_name) {
            if (isset($_POST[$cmc . 'TypeID']) && $_POST[$cmc . 'TypeID'] <> 0) {
                $data[$cmc . '_type_id'] = filter_input(INPUT_POST, $cmc . 'TypeID', FILTER_SANITIZE_NUMBER_INT);
            }
        }

        $data['input_date'] = date('Y-m-d H:i:s');
        $data['last_update'] = date('Y-m-d H:i:s');

        // image uploading
        $images_disk = Storage::images();
        if (!empty($_FILES['image']) AND $_FILES['image']['size']) {
            // Title
            $img_title = $data['title'].'_'.date("YmdHis");
            if(strlen($data['title']) > 70){
                $img_title = substr($data['title'], 0, 70).'_'.date("YmdHis");
            }

            // create upload object
            $image_upload = $images_disk->upload('image', function($images) use($sysconf) {
                // Extension check
                $images->isExtensionAllowed($sysconf['allowed_images']);

                // File size check
                $images->isLimitExceeded($sysconf['max_image_upload']*1024);

                // destroy it if failed
                if (!empty($images->getError())) $images->destroyIfFailed();

                // remove exif data
                if (empty($images->getError())) $images->cleanExifInfo();

                $images->sanitizeImageWithGD();

            })->as('docs/' . strtolower('cover_'. preg_replace("/[^a-zA-Z0-9]+/", "-", $img_title)));

            
            if ($image_upload->getUploadStatus()) {
                $data['image'] = $dbs->escape_string($image_upload->getUploadedFileName());
                // write log
                writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'] . ' upload image file ' . $image_upload->getUploadedFileName());
                utility::jsToastr('Bibliography', __('Image Uploaded Successfully'), 'success');
            } else {
                // write log
                $data['image'] = NULL;
                writeLog('staff', $_SESSION['uid'], 'bibliography', 'ERROR : ' . $_SESSION['realname'] . ' FAILED TO upload image file ' . $image_upload->getUploadedFileName() . ', with error (' . $image_upload->getError() . ')');
                utility::jsToastr('Bibliography', __('Image Uploaded Failed').'<br/>'.$image_upload->getError(), 'error');
            }
        } else if (!empty($_POST['base64picstring'])) {
            list($filedata, $filedom) = explode('#image/type#', $_POST['base64picstring']);
            $filedata = base64_decode(trim($filedata), true);
            $fileinfo = $filedata !== false ? getimagesizefromstring($filedata) : false;
            $fileMime = $fileinfo ? ($fileinfo['mime'] ?? '') : '';
            $filedom = $fileinfo ? ltrim(strtolower(image_type_to_extension($fileinfo[2], false)), '.') : strtolower($filedom);
            $filedom = $fileMime && !$filedom && strpos($fileMime, 'image/') === 0 ? substr($fileMime, 6) : $filedom;
            $fileMimeLower = strtolower($fileMime);

            $sizeAllowed = $filedata !== false && (strlen($filedata) <= ($sysconf['max_image_upload'] * 1024));
            $allowedMimes = array_map('strtolower', (array)$sysconf['allowed_images_mimetype']);
            $allowedExts = array_map('strtolower', (array)$sysconf['allowed_images']);
            $mimeAllowed = $fileMime && in_array($fileMimeLower, $allowedMimes, true);
            $extAllowed = $filedom && in_array($filedom, $allowedExts, true);
            $valid = $fileinfo && $sizeAllowed && $mimeAllowed && $extAllowed;
            $new_filename = strtolower('cover_'
                . preg_replace("/[^a-zA-Z0-9]+/", "_", substr($data['title'], 0,70)) . '-' . date('this')
                . '.' . $filedom);

            if ($valid) {
                @$images_disk->put('docs/' . $new_filename, $filedata);
                
                if ($images_disk->isExists('docs/' . $new_filename))
                {
                    $data['image'] = $dbs->escape_string($new_filename);
                    if (!defined('UPLOAD_SUCCESS')) define('UPLOAD_SUCCESS', 1);
                    $upload_status = UPLOAD_SUCCESS;
                }
            } else {
                utility::jsToastr('Bibliography', __('Image Uploaded Failed').'<br/>'.__('Cropped image data is invalid, uses disallowed type, or exceeds max size.'), 'error');
            }
        }

        // create sql op object
        $sql_op = new simbio_dbop($dbs);
        if (isset($_POST['updateRecordID'])) {
            $_POST['updateRecordID'] = (integer)$_POST['updateRecordID'];

            if ($_POST['updateRecordID'] < 1) { die("Invalid updateRecordID"); }
            if ($sysconf['log']['biblio']) {
                $_prevrawdata = api::biblio_load($dbs, $_POST['updateRecordID']);
            }

            // Remove previous popular biblio data from cache if opac use default template
            if ($sysconf['template']['theme'] == 'default' && $_POST['opacHide'] != $_POST['opacHideOrigin']) {
                require SB . 'api/v1/helpers/Cache.php';
                Cache::destroy('biblio_popular');
            }

            /* UPDATE RECORD MODE */
            // remove input date
            unset($data['input_date']);
            unset($data['uid']);
            // filter update record ID
            $updateRecordID = (integer)$_POST['updateRecordID'];
            Plugins::getInstance()->execute(Plugins::BIBLIOGRAPHY_BEFORE_UPDATE, ['data' => array_merge($data, ['biblio_id' => $updateRecordID])]);
            // update data
            $update = $sql_op->update('biblio', $data, 'biblio_id=' . $updateRecordID);
            // send an alert
            if ($update) {

                // execute registered hook
                Plugins::getInstance()->execute(Plugins::BIBLIOGRAPHY_AFTER_UPDATE, ['data' => array_merge($data, ['biblio_id' => $updateRecordID])]);

                // update custom data
                if (isset($custom_data)) {
                    // check if custom data for this record exists
                    $_sql_check_custom_q = sprintf('SELECT biblio_id FROM biblio_custom WHERE biblio_id=%d', $updateRecordID);
                    $check_custom_q = $dbs->query($_sql_check_custom_q);
                    if ($check_custom_q->num_rows) {
                        $update2 = @$sql_op->update('biblio_custom', $custom_data, 'biblio_id=' . $updateRecordID);
                    } else {
                        $custom_data['biblio_id'] = $updateRecordID;
                        @$sql_op->insert('biblio_custom', $custom_data);
                    }
                }
                if ($sysconf['bibliography_update_notification']) {
                    utility::jsToastr('Bibliography', __('Bibliography Data Successfully Updated'), 'success');
                }
                // auto insert catalog to UCS if enabled
                if ($sysconf['ucs']['enable']) {
                    echo '<script type="text/javascript">parent.ucsUpload(\'' . MWB . 'bibliography/ucs_upload.php\', \'itemID[]=' . $updateRecordID . '\', false);</script>';
                }
                // write log
                writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'] . ' update bibliographic data (' . $data['title'] . ') with biblio_id (' . $updateRecordID . ')');

                if ($sysconf['log']['biblio']) {
                    $_currrawdata = api::biblio_load($dbs, $updateRecordID);
                    api::bibliolog_compare($dbs, $updateRecordID, $_SESSION['uid'], $_SESSION['realname'], $data['title'], $_currrawdata, $_SESSION['_prevrawdata'][$updateRecordID]);
                    unset($_SESSION['_prevrawdata'][$updateRecordID]);
                }
                if ($sysconf['index']['engine']['enable']) {
                    api::update_to_index($_currrawdata);
                }
                // close window OR redirect main page
                if ($in_pop_up) {
                    $itemCollID = (integer)$_POST['itemCollID'];
                    echo '<script type="text/javascript">top.$(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url, {method: \'post\', addData: \'' . ($itemCollID ? 'itemID=' . $itemCollID . '&detail=true' : '') . '\'});</script>';
                    echo '<script type="text/javascript">top.closeHTMLpop();</script>';
                } else {
                    echo '<script type="text/javascript">top.$(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url);</script>';
                }
                // update index
                $indexer->updateIndex($updateRecordID);
            } else {
                utility::jsToastr('Bibliography', __('Bibliography Data FAILED to Updated. Please Contact System Administrator') . "\n" . $sql_op->error, 'error');
            }

        } else {

            // execute registered hook
            Plugins::getInstance()->execute(Plugins::BIBLIOGRAPHY_BEFORE_SAVE, ['data' => &$data]);

            /* INSERT RECORD MODE */
            // insert the data
            $insert = $sql_op->insert('biblio', $data);
            if ($insert) {
                // get auto id of this record
                $last_biblio_id = $sql_op->insert_id;

                // execute registered hook
                $data['biblio_id'] = $last_biblio_id;
                Plugins::getInstance()->execute(Plugins::BIBLIOGRAPHY_AFTER_SAVE, ['data' => &$data]);

                // add authors
                if ($_SESSION['biblioAuthor']) {
                    foreach ($_SESSION['biblioAuthor'] as $author) {
                        $sql_op->insert('biblio_author', array('biblio_id' => $last_biblio_id, 'author_id' => $author[0], 'level' => $author[1]));
                    }
                }
                // add topics
                if ($_SESSION['biblioTopic']) {
                    foreach ($_SESSION['biblioTopic'] as $topic) {
                        $sql_op->insert('biblio_topic', array('biblio_id' => $last_biblio_id, 'topic_id' => $topic[0], 'level' => $topic[1]));
                    }
                }
                // add attachment
                if ($_SESSION['biblioAttach']) {
                    foreach ($_SESSION['biblioAttach'] as $attachment) {
                        $sql_op->insert('biblio_attachment', array('biblio_id' => $last_biblio_id, 'file_id' => $attachment['file_id'], 'access_type' => $attachment['access_type']));
                    }
                }
                // biblio to biblio
                if ($_SESSION['biblioToBiblio']) {
                    foreach ($_SESSION['biblioToBiblio'] as $rel_biblio_id) {
                        $sql_op->insert('biblio_relation', array('biblio_id' => $last_biblio_id, 'rel_biblio_id' => $rel_biblio_id[0]));
                    }
                }
                // insert custom data
                if (isset($custom_data)) {
                    $custom_data['biblio_id'] = $last_biblio_id;
                    @$sql_op->insert('biblio_custom', $custom_data);
                }

                utility::jsToastr('Bibliography', __('New Bibliography Data Successfully Saved'), 'success');
                // write log
                writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'] . ' insert bibliographic data (' . $data['title'] . ') with biblio_id (' . $last_biblio_id . ')');
                if ($sysconf['log']['biblio']) {
                    $_rawdata = api::biblio_load($dbs, $last_biblio_id);
                    api::bibliolog_write($dbs, $last_biblio_id, $_SESSION['uid'], $_SESSION['realname'], $data['title'], 'create', 'description', $_rawdata, 'New data. Bibliography.');
                    api::bibliolog_compare($dbs, $last_biblio_id, $_SESSION['uid'], $_SESSION['realname'], $data['title'], $_rawdata, NULL);
                }
                if ($sysconf['index']['engine']['enable']) {
                    api::update_to_index($_rawdata);
                }
                // clear related sessions
                $_SESSION['biblioAuthor'] = array();
                $_SESSION['biblioTopic'] = array();
                $_SESSION['biblioAttach'] = array();
                $_SESSION['biblioToBiblio'] = array();

                // make index
                $indexer->makeIndex($last_biblio_id);
                $indexer->makeIndexWord($last_biblio_id);

                // auto insert catalog to UCS if enabled
                if ($sysconf['ucs']['enable'] && $sysconf['ucs']['auto_insert']) {
                    echo '<script type="text/javascript">parent.ucsUpload(\'' . MWB . 'bibliography/ucs_upload.php\', \'itemID[]=' . $last_biblio_id . '\');</script>';
                }
            } else {
                utility::jsToastr('Bibliography', __('Bibliography Data FAILED to Save. Please Contact System Administrator') . "\n" . $sql_op->error, 'error');
            }
        }

        // пакетно създаване на екземпляри – поредни инвентарни номера (= баркод), свързани с КДБФ
        if ($bg_batch) {
            $bg_biblio_id = isset($updateRecordID) ? (int)$updateRecordID : (int)($last_biblio_id ?? 0);
            if ($bg_biblio_id) {
                [$bg_created, $bg_failed] = \SLiMS\Bg\BatchItems::insert($bg_batch, $bg_biblio_id, (string)$data['call_number'], (int)$_SESSION['uid']);
                if ($bg_created) {
                    $bg_ranges = \SLiMS\Bg\Inventory::formatRanges($bg_created);
                    utility::jsToastr(__('Bibliography'), sprintf(__('Created items with inventory numbers %s'), $bg_ranges), 'success');
                    writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'] . ' create items ' . $bg_ranges . ' for biblio_id (' . $bg_biblio_id . ')');
                }
                if ($bg_failed) {
                    utility::jsToastr(__('Bibliography'), sprintf(__('These items were NOT created: %s'), \SLiMS\Bg\Inventory::formatRanges($bg_failed)), 'error');
                }
                // update items data into search_biblio
                $indexer->updateItems($bg_biblio_id);
            }
        }
        $bgv->flushWarnings(__('Bibliography'));

        #$isset = array_key_exists('last_biblio_id', get_defined_vars());
        if (array_key_exists('last_biblio_id', get_defined_vars())) {
            echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\'' . MWB . 'bibliography/index.php\', {method: \'post\', addData: \'itemID=' . (isset($updateRecordID) ? $updateRecordID : $last_biblio_id) . '&detail=true\'});</script>';
        }
        exit();
    }
    exit();
} else if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
    if (!($can_read AND $can_write)) {
        die();
    }
    if (!simbio_form_maker::isTokenValid()) {
        utility::jsToastr('Bibliography', __('Invalid form submission token!'), 'error');
        writeLog('staff', $_SESSION['uid'], 'system', 'Invalid form submission token, might be a CSRF attack from ' . $_SERVER['REMOTE_ADDR']);
        exit();
    }

    $indexer = new biblio_indexer($dbs);

    /* DATA DELETION PROCESS */
    // create sql op object
    $sql_op = new simbio_dbop($dbs);
    $failed_array = array();
    $error_num = 0;
    $still_have_item = array();
    if (!is_array($_POST['itemID'])) {
        // make an array
        $_POST['itemID'] = array((integer)$_POST['itemID']);
    }
    // loop array
    $http_query = '';
    foreach ($_POST['itemID'] as $itemID) {
        $itemID = (integer)$itemID;
        // check if this biblio data still have an item
        $_sql_biblio_item_q = sprintf('SELECT b.title, COUNT(item_id) FROM biblio AS b
      LEFT JOIN item AS i ON b.biblio_id=i.biblio_id
      WHERE b.biblio_id=%d GROUP BY title', $itemID);
        $biblio_item_q = $dbs->query($_sql_biblio_item_q);
        $biblio_item_d = $biblio_item_q->fetch_row();
        
        if (($biblio_item_d[1]??0) < 1) {

            if ($sysconf['log']['biblio']) {
                $_rawdata = api::biblio_load($dbs, $itemID);
                api::bibliolog_write($dbs, $itemID, $_SESSION['uid'], $_SESSION['realname'], $biblio_item_d[0]??'Item Undefined', 'delete', 'description', $_rawdata, 'Data bibliografi dihapus.');
            }

            // execute registered hook
            Plugins::getInstance()->execute(Plugins::BIBLIOGRAPHY_BEFORE_DELETE, [$itemID]);
            
            if (!$sql_op->delete('biblio', "biblio_id=$itemID")) {
                $error_num++;
            } else {

                // execute registered hook
                Plugins::getInstance()->execute(Plugins::BIBLIOGRAPHY_AFTER_DELETE, [$itemID]);

                // write log
                writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'] . ' DELETE bibliographic data (' . ($biblio_item_d[0]??'?') . ') with biblio_id (' . $itemID . ')');
                // delete related data
                $sql_op->delete('biblio_topic', "biblio_id=$itemID");
                $sql_op->delete('biblio_author', "biblio_id=$itemID");
                $sql_op->delete('biblio_attachment', "biblio_id=$itemID");
                $sql_op->delete('biblio_relation', "biblio_id=$itemID");

                # delete from index
                $indexer->deleteIndex($itemID);

                // delete serial data
                // check kardex if exist
                $_sql_serial_kardex_q = sprintf(' SELECT biblio_id, serial_id 
                FROM serial WHERE biblio_id=%d', $itemID);
                $serial_kardex_q = $dbs->query($_sql_serial_kardex_q);
                if ($serial_kardex_q) {
                    while ($data = $serial_kardex_q->fetch_row()) {
                        $sql_op->delete('kardex', "serial_id=" . $data[1]);
                    };
                }
                //delete serial data
                $sql_op->delete('serial', "biblio_id=$itemID");

                //delete custom data
                $sql_op->delete('biblio_custom', "biblio_id=$itemID");

                // add to http query for UCS delete
                $http_query .= "itemID[]=$itemID&";
                $_itemID[] = $itemID;
                $itemIDs = implode(',', $_itemID);
            }
        } else {
            $still_have_item[] = substr($biblio_item_d[0], 0, 45) . '... still have ' . $biblio_item_d[1] . ' copies';
            $error_num++;
        }
    }

    if ($still_have_item) {
        $titles = '';
        foreach ($still_have_item as $title) {
            $titles .= $title . "\n";
        }
        utility::jsToastr('Bibliography', __('Below data can not be deleted:') . "\n" . $titles, 'error');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\'' . $_SERVER['PHP_SELF'] . '\', {addData: \'' . ($_POST['lastQueryStr']??$_SERVER['HTTP_REFERER']??'') . '\'});</script>';
        exit();
    }
    // auto delete data on UCS if enabled
    if ($http_query && $sysconf['ucs']['enable'] && $sysconf['ucs']['auto_delete']) {
        Ucs::auto_delete($itemIDs);
    }
    // error alerting
    if ($error_num == 0) {
        utility::jsToastr('Bibliography', __('All Data Successfully Deleted'), 'success');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\'' . $_SERVER['PHP_SELF'] . '\', {addData: \'' . ($_POST['lastQueryStr']??$_SERVER['HTTP_REFERER']??'') . '\'});</script>';
    } else {
        utility::jsToastr('Bibliography', __('Some or All Data NOT deleted successfully!\nPlease contact system administrator'), 'warning');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\'' . $_SERVER['PHP_SELF'] . '\', {addData: \'' . ($_POST['lastQueryStr']??$_SERVER['HTTP_REFERER']??'') . '\'});</script>';
    }
    exit();
}
/* RECORD OPERATION END */
if (!$in_pop_up) {
    /* search form */
    ?>
    <div class="menuBox">
        <div class="menuBoxInner biblioIcon">
            <div class="per_title">
                <h2><?php echo __('Bibliographic'); ?></h2>
            </div>
            <div class="sub_section">
                <div class="btn-group">
                    <a href="<?php echo MWB; ?>bibliography/index.php"
                       class="btn btn-default"><?php echo __('Bibliographic List'); ?></a>
                    <a href="<?php echo MWB; ?>bibliography/index.php?action=detail"
                       class="btn btn-default"><?php echo __('Add New Bibliography'); ?></a>
                </div>
                <form name="search" action="<?php echo MWB; ?>bibliography/index.php" id="search" method="get"
                      class="form-inline"><?php echo __('Search'); ?>
                    <input type="text" name="keywords" id="keywords" class="form-control col-md-3"/>
                    <select name="field" class="form-control col-md-2">
                        <option value="0"><?php echo __('All Fields'); ?></option>
                        <option value="title"><?php echo __('Title/Series Title'); ?> </option>
                        <option value="subject"><?php echo __('Topics'); ?></option>
                        <option value="author"><?php echo __('Authors'); ?></option>
                        <option value="isbn"><?php echo __('ISBN/ISSN'); ?></option>
                        <option value="publisher"><?php echo __('Publisher'); ?></option>
                    </select>
                    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>"
                           class="s-btn btn btn-default"/>
                    <div class="btn btn-info" data-toggle="collapse" data-target="#advancedFilter"
                         aria-expanded="false"><?php echo __('Advanced Filter'); ?></div>
                    <div class="collapse" id="advancedFilter"
                         style="padding-top:10px;width:100%; text-align:left !important;">
                        <?php echo __('Hide in OPAC'); ?>&nbsp;
                        <select name="opac_hide" class="form-control col-md-2">
                            <option value=""><?php echo __('ALL'); ?></option>
                            <option value="0"><?php echo __('Show'); ?> </option>
                            <option value="1"><?php echo __('Hide'); ?></option>
                        </select>
                        <?php echo __('Promote To Homepage'); ?>&nbsp;
                        <select name="promoted" class="form-control col-md-2">
                            <option value=""><?php echo __('ALL'); ?></option>
                            <option value="0"><?php echo __('Don\'t Promote'); ?> </option>
                            <option value="1"><?php echo __('Promote'); ?></option>
                        </select>
                    </div>
                    <?php
                    // enable UCS?
                    if ($sysconf['ucs']['enable']) {
                        ?>
                        <a href="#"
                           onclick="ucsUpload('<?php echo MWB; ?>bibliography/ucs_upload.php', serializeChbox('dataList'))"
                           class="s-btn btn btn-default notAJAX"><?php echo __('Upload Selected Bibliographic data to Union Catalog Server*'); ?></a>
                        <?php
                    }
                    ?>
                </form>
            </div>
        </div>
    </div>
    <?php
    /* search form end */
}
/* main content */
if (isset($_GET['action']) && $_GET['action'] == 'history') {

    $biblioID = utility::filterData('biblioID', 'get', true, true, true);
    $table_spec = 'biblio_log AS bl';
    $criteria = 'bl.biblio_id=' . $biblioID;
    // create datagrid
    $datagrid = new simbio_datagrid();
    $datagrid->setSQLColumn('bl.date AS \'' . __('Date') . '\'',
        'bl.realname AS \'' . __('User Name') . '\'',
        'bl.additional_information AS \'' . __('Additional Information') . '\'');
    $datagrid->modifyColumnContent(2, 'callback{affectedDetail}');
    $datagrid->setSQLorder('bl.biblio_log_id DESC');
    $datagrid->sql_group_by = 'bl.date';
    $datagrid->setSQLCriteria($criteria);
    // set table and table header attributes
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';

    function affectedDetail($obj_db, $array_data)
    {
        $_q = $obj_db->query("SELECT action,affectedrow,title,additional_information FROM biblio_log WHERE `date` LIKE '" . $array_data[0] . "'");
        $str = '';
        $title = '';
        if ($_q->num_rows > 0) {
            while ($_data = $_q->fetch_assoc()) {
                $title = $_data['title'];
                $str .= ' - ' . $_data['action'] . ' ' . $_data['affectedrow'] . ' : <i>' . $_data['additional_information'] . '</i><br/>';
            }
        }
        return $title . '</br>' . $str;
    }

    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, false);

    $_q = $dbs->query("SELECT title FROM biblio WHERE biblio_id=" . $biblioID);
    if ($_q->num_rows > 0) {
        $_d = $_q->fetch_row();
        echo '<div class="infoBox">' . __('Biblio Log') . ' : <strong>' . $_d[0] . '</strong></div>';
    }

    echo $datagrid_result;
} elseif (isset($_POST['detail']) OR (isset($_GET['action']) AND $_GET['action'] == 'detail')) {
    # CHECK ACCESS
    if ($_SESSION['uid'] != 1) {
        if (!utility::haveAccess('bibliography.bibliography-add')) {
            die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
        }
    }

    if ((isset($_GET['action'])) AND ($_GET['action'] == 'detail')) {
        # ADV LOG SYSTEM - STIIL EXPERIMENTAL
        $log = new AlLibrarian('1153', array("username" => $_SESSION['uname'], "uid" => $_SESSION['uid'], "realname" => $_SESSION['realname']));
    } elseif ((isset($_GET['itemID'])) AND (isset($_GET['detail'])) AND ($_GET['detail'] == true)) {
        $log = new AlLibrarian('1155', array("username" => $_SESSION['uname'], "uid" => $_SESSION['uid'], "realname" => $_SESSION['realname'], "biblio_id" => $_GET['itemID']));
    }

    if (!($can_read AND $can_write)) {

        die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
    }
    /* RECORD FORM */
    // try query
    $itemID = (integer)isset($_POST['itemID']) ? $_POST['itemID'] : 0;
    $_sql_rec_q = sprintf('SELECT b.*, p.publisher_name, pl.place_name FROM biblio AS b
    LEFT JOIN mst_publisher AS p ON b.publisher_id=p.publisher_id
    LEFT JOIN mst_place AS pl ON b.publish_place_id=pl.place_id
    WHERE biblio_id=%d', $itemID);
    $rec_q = $dbs->query($_sql_rec_q);
    $rec_d = $rec_q->fetch_assoc();

    // create new instance
    $form = new FormAjaxWithCustomField('mainForm', $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'], 'post');
    $form->submit_button_attr = 'name="saveData" value="' . __('Save') . '" class="s-btn btn btn-default"';
    // form table attributes
    $form->table_attr = 'id="dataList" cellpadding="0" cellspacing="0"';
    $form->table_header_attr = 'class="alterCell"';
    $form->table_content_attr = 'class="alterCell2"';

    //custom button
    if (isset($itemID)) {
        $form->addCustomBtn('history', __('Log'), $_SERVER['PHP_SELF'] . '?action=history&ajaxLoad=true&biblioID=' . $itemID, ' class="s-btn btn btn-success"');
    }

    $visibility = 'makeVisible s-margin__bottom-1';
    // edit mode flag set
    if ($rec_q->num_rows > 0) {
        $form->edit_mode = true;
        // record ID for delete process
        if (!$in_pop_up) {
            // form record id
            $form->record_id = $itemID;
        } else {
            $form->addHidden('updateRecordID', $itemID);
            if (isset($_POST['itemCollID'])) {
                $form->addHidden('itemCollID', $_POST['itemCollID']);
            }
            $form->back_button = false;
        }
        // form record title
        $form->record_title = $rec_d['title'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="' . __('Update') . '" class="s-btn btn btn-primary"';
        // element visibility class toogle
        $visibility = 'makeHidden s-margin__bottom-1';

        // custom field data query
        $_sql_rec_cust_q = sprintf('SELECT * FROM biblio_custom WHERE biblio_id=%d', $itemID);
        $rec_cust_q = $dbs->query($_sql_rec_cust_q);
        $rec_cust_d = $rec_cust_q->fetch_assoc();
    } else {
        $_SESSION['biblioToBiblio'] = array();
    }

    // include custom fields file
    if (file_exists(MDLBS . 'bibliography/custom_fields.inc.php')) {
        include MDLBS . 'bibliography/custom_fields.inc.php';
    }

    /* Form Element(s) */
    // biblio title
    $form->addTextField('textarea', 'title', __('Title') . '*', stripslashes(($rec_d ?? [])['title'] ?? ''), 'rows="1" class="form-control"',
        __('Main title of collection. Separate child title with colon and pararel title with equal (=) sign.'));

    // biblio authors
    // $str_input = '<div class="'.$visibility.'"><a class="notAJAX button" href="javascript: openHTMLpop(\''.MWB.'bibliography/pop_author.php?biblioID='.$rec_d['biblio_id'].'\', 500, 200, \''.__('Authors/Roles').'\')">'.__('Add Author(s)').'</a></div>';
    $str_input = '<div class="' . $visibility . '"><a class="s-btn btn btn-default notAJAX openPopUp" href="' . MWB . 'bibliography/pop_author.php?biblioID=' . ($rec_d['biblio_id'] ?? '') . '" title="' . __('Authors/Roles') . '">' . __('Add Author(s)') . '</a></div>';
    $str_input .= '<iframe name="authorIframe" id="authorIframe" class="form-control auto-resize-iframe" style="width: 100%; min-height: 80px; max-height: 300px;" src="' . MWB . 'bibliography/iframe_author.php?biblioID=' . ($rec_d['biblio_id'] ?? '') . '&block=1"></iframe>';
    $form->addAnything(__('Author(s)'), $str_input);

    // modified by hendro wicaksono
    // biblio sor statement of responsibility
    $form->addTextField('text', 'sor', __('Statement of Responsibility'), $rec_d['sor'] ?? '', 'class="form-control" style="width: 50%;"', __('Main source of information to show who has written, composed, illustrated, or in other ways contributed to the existence of the item.'));
    // end of modification

    // biblio edition
    $form->addTextField('text', 'edition', __('Edition'), $rec_d['edition'] ?? '', 'class="form-control" style="width: 50%;"', __('A version of publication having substantial changes or additions.'));
    // biblio specific detail info/area
    $form->addTextField('textarea', 'specDetailInfo', __('Specific Detail Info'), $rec_d['spec_detail_info'] ?? '', 'rows="2" class="form-control', __('explain more details about an item e.g. scale within a map, running time in a movie dvd.'));
    // пакетно създаване на екземпляри – поредни инвентарни номера (= баркод), свързани с КДБФ
    $form->addAnything(__('Create copies'), \SLiMS\Bg\BatchItems::formHtml($sysconf, $visibility, isset($_GET['kdbfEntryID']) ? (int)$_GET['kdbfEntryID'] : null));
    // biblio item add
    if (!$in_pop_up AND $form->edit_mode) {
        // $str_input = '<div class="makeHidden s-margin__bottom-1"><a class="notAJAX button" href="javascript: openHTMLpop(\''.MWB.'bibliography/pop_item.php?inPopUp=true&action=detail&biblioID='.$rec_d['biblio_id'].'\', 650, 400, \''.__('Items/Copies').'\')">'.__('Add New Items').'</a></div>';
        $str_input = '<div class="makeHidden s-margin__bottom-1"><a class="s-btn btn btn-default notAJAX openPopUp" href="' . MWB . 'bibliography/pop_item.php?inPopUp=true&action=detail&biblioID=' . $rec_d['biblio_id'] . '" title="' . __('Items/Copies') . '" width="780" height="500">' . __('Add New Items') . '</a></div>';
        $str_input .= '<iframe name="itemIframe" id="itemIframe" class="form-control auto-resize-iframe" style="width: 100%; height: 100px; min-height: 80px; max-height: 400px;" src="' . MWB . 'bibliography/iframe_item_list.php?biblioID=' . $rec_d['biblio_id'] . '&block=1"></iframe>' . "\n";
        $form->addAnything(__('Item(s) Data'), $str_input);
    }
    // biblio gmd
    // get gmd data related to this record from database
    $gmd_q = $dbs->query('SELECT gmd_id, gmd_name FROM mst_gmd');
    $gmd_options = array();
    while ($gmd_d = $gmd_q->fetch_row()) {
        $gmd_options[] = array($gmd_d[0], $gmd_d[1]);
    }
    $form->addSelectList('gmdID', __('GMD'), $gmd_options, $rec_d['gmd_id'] ?? '', 'class="select2"', __('General material designation. The physical form of publication.'));

    // biblio RDA content, media, carrier type
    foreach ($rda_cmc as $cmc => $cmc_name) {
        $cmc_options = array();
        $cmc_q = $dbs->query('SELECT id, ' . $cmc . '_type FROM mst_' . $cmc . '_type');
        $cmc_options = array();
        $cmc_options[] = array(0, __('Not set'));
        while (isset($cmc_q->num_rows) && $cmc_q->num_rows > 0 && $cmc_d = $cmc_q->fetch_row()) {
            $cmc_options[] = array($cmc_d[0], $cmc_d[1]);
        }
        if (isset($rec_d[$cmc . '_type_id'])) {
            $form->addSelectList($cmc . 'TypeID', __($cmc_name), $cmc_options, $rec_d[$cmc . '_type_id'] ?? '', 'class="select2"', __('RDA ' . $cmc_name . ' designation.'));
        } else {
            $form->addSelectList($cmc . 'TypeID', __($cmc_name), $cmc_options, '', 'class="select2"', __('RDA ' . $cmc_name . ' designation.'));
        }
    }


    // biblio publish frequencies
    // get frequency data related to this record from database
    $freq_q = $dbs->query('SELECT frequency_id, frequency FROM mst_frequency');
    $freq_options[] = array('0', __('Not Applicable'));
    while ($freq_d = $freq_q->fetch_row()) {
        $freq_options[] = array($freq_d[0], $freq_d[1]);
    }
    $str_input = '<div class="form-inline">';
    $str_input .= simbio_form_element::selectList('frequencyID', $freq_options, $rec_d['frequency_id'] ?? '', 'class="select2 col-3"');
    $str_input .= '<div class="col">' . __('Use this for Serial publication') . '</div>';
    $str_input .= '</div>';
    $form->addAnything(__('Frequency'), $str_input);
    // biblio ISBN/ISSN
    $form->addTextField('text', 'isbn_issn', __('ISBN/ISSN'), htmlspecialchars($rec_d['isbn_issn'] ?? ''), 'class="form-control" style="width: 40%;" maxlength="100" data-bgval="isbn" data-bg-label="ISBN/ISSN" placeholder="978-619-7043-60-0"', __('Unique publishing number for each title of publication.') . ' ' . __('Several numbers are separated with ";", e.g. 978-… (т. 1); 978-… (т. 2)'));
    // ISBD област 8: цена / условия за доставяне (както е отпечатана в изданието)
    $form->addTextField('text', 'price_terms', __('Price/Terms (ISBD area 8)'), htmlspecialchars($rec_d['price_terms'] ?? ''), 'class="form-control" style="width: 40%;" maxlength="100" placeholder="14.95 лв."', __('The price printed in the book, e.g. 14.95 лв. (shown after the ISBN on the catalogue card)'));
    // biblio publisher
    $publ_options[] = array('NONE', '');
    if (isset($rec_d['publisher_id'])) {
        $publ_q = $dbs->query(sprintf('SELECT publisher_id, publisher_name FROM mst_publisher WHERE publisher_id=%d', $rec_d['publisher_id'] ?? ''));
        while ($publ_d = $publ_q->fetch_row()) {
            $publ_options[] = array($publ_d[0], $publ_d[1]);
        }
    }
    $form->addSelectList('publisherID', __('Publisher'), $publ_options, $rec_d['publisher_id'] ?? '', 'class="select2" data-src="' . SWB . 'admin/AJAX_lookup_handler.php" data-src-table="new:mst_publisher" data-src-cols="publisher_id:publisher_name"');
    // biblio publish year
    $form->addTextField('text', 'year', __('Publishing Year'), htmlspecialchars($rec_d['publish_year'] ?? ''), 'class="form-control" style="width: 40%;" maxlength="20" data-bgval="year" data-bg-label="' . __('Publishing Year') . '" placeholder="2019"' . ((int)($rec_d['frequency_id'] ?? 0) > 0 ? ' data-bg-serial="1"' : ''), __('Year of publication') . ': 2019, [2019], 2019?, [ок. 1900], 2010–2012, [б. г.]');
    // biblio publish place
    $plc_options[] = array('NONE', '');
    if (isset($rec_d['publish_place_id'])) {
        $plc_q = $dbs->query(sprintf('SELECT place_id, place_name FROM mst_place WHERE place_id=%d', $rec_d['publish_place_id'] ?? ''));
        while ($plc_d = $plc_q->fetch_row()) {
            $plc_options[] = array($plc_d[0], $plc_d[1]);
        }
    }
    $form->addSelectList('placeID', __('Publishing Place'), $plc_options, $rec_d['publish_place_id'] ?? '', 'class="select2" data-src="' . SWB . 'admin/AJAX_lookup_handler.php" data-src-table="new:mst_place" data-src-cols="place_id:place_name"');
    // ISBD област 4: място на печат : печатница
    $form->addTextField('text', 'printing', __('Printing House'), htmlspecialchars($rec_d['printing'] ?? ''), 'class="form-control" style="width: 50%;" maxlength="150" placeholder="Хасково : Полиграфюг АД"', __('Place of printing : printer, as given in the book (optional)'));
    // biblio collation
    $form->addTextField('text', 'collation', __('Collation'), htmlspecialchars($rec_d['collation'] ?? ''), 'class="form-control" style="width: 40%;" maxlength="100" data-bgval="collation" data-bg-label="' . __('Collation') . '" placeholder="208 с. : ил. ; 20 см"', __('Physical description of a publication e.g. publication length, width, page numbers, etc.') . ' ' . __('ISBD: pages : illustrations ; size + accompanying material'));
    // biblio series title
    $form->addTextField('textarea', 'seriesTitle', __('Series Title'), $rec_d['series_title'] ?? '', 'rows="1" class="form-control');
    // ISBD област 6: номер в поредицата
    $form->addTextField('text', 'series_number', __('Series Number'), htmlspecialchars($rec_d['series_number'] ?? ''), 'class="form-control" style="width: 20%;" maxlength="20"', __('Number of the book within the series'));
    // biblio classification
    $cls_options[] = array('NONE', '');
    if (isset($rec_d['classification'])) {
        $cls_options[] = array($rec_d['classification'], $rec_d['classification']);
    }
    $form->addSelectList('class', __('Classification'), $cls_options, $rec_d['classification'] ?? '', 'class="select2" data-src="' . SWB . 'admin/AJAX_lookup_handler.php" data-src-table="new:mst_topic" data-src-cols="classification:classification:topic"');
    // biblio call_number
    // авторски знак (по таблиците за авторски знаци) и сигнатура = УДК за подреждане + авторски знак
    $form->addTextField('text', 'author_mark', __('Author Mark'), htmlspecialchars($rec_d['author_mark'] ?? ''), 'class="form-control" style="width: 15%;" maxlength="10" data-bgval="authormark" data-bg-label="' . __('Author Mark') . '" placeholder="П47"', __('First letter of the heading word and the number from the author tables, e.g. П47'));
    $str_input = '<div class="form-inline">';
    $str_input .= '<input type="text" name="callNumber" id="callNumber" value="' . htmlspecialchars($rec_d['call_number'] ?? '') . '" class="form-control" style="width: 40%;" maxlength="50" placeholder="886.7-3 П47" />';
    $str_input .= '<button type="button" class="btn btn-default ml-2" id="bgComposeCallNumber">' . __('Compose call number') . '</button>';
    $str_input .= '</div><small class="form-text text-muted">' . __('Shelf UDC + author mark, e.g. 886.7-3 П47. The department prefix (e.g. Д) is added to each copy automatically.') . '</small>';
    $form->addAnything(__('Call Number'), $str_input);
    // biblio topics
    // $str_input = '<div class="'.$visibility.'"><a class="notAJAX button" href="javascript: openHTMLpop(\''.MWB.'bibliography/pop_topic.php?biblioID='.$rec_d['biblio_id'].'\', 500, 200, \''.__('Subjects/Topics').'\')">'.__('Add Subject(s)').'</a></div>';
    $str_input = '<div class="' . $visibility . ' s-margin__bottom-1"><a class="s-btn btn btn-default notAJAX openPopUp" href="' . MWB . 'bibliography/pop_topic.php?biblioID=' . ($rec_d['biblio_id'] ?? '') . '" title="' . __('Subjects/Topics') . '">' . __('Add Subject(s)') . '</a></div>';
    $str_input .= '<iframe name="topicIframe" id="topicIframe" class="form-control auto-resize-iframe" style="width: 100%; height: 100px; min-height: 80px; max-height: 300px;" src="' . MWB . 'bibliography/iframe_topic.php?biblioID=' . ($rec_d['biblio_id'] ?? '') . '&block=1"></iframe>';
    $form->addAnything(__('Subject(s)'), $str_input);
    // biblio language
    // get language data related to this record from database
    $lang_q = $dbs->query("SELECT language_id, language_name FROM mst_language");
    $lang_options = array();
    while ($lang_d = $lang_q->fetch_row()) {
        $lang_options[] = array($lang_d[0], $lang_d[1]);
    }
    $form->addSelectList('languageID', __('Language'), $lang_options, $rec_d['language_id'] ?? 'bg', 'class="select2"', __('Language use by publication.'));
    // biblio note
    $form->addTextField('textarea', 'notes', __('Abstract/Notes'), $rec_d['notes'] ?? '', 'class="form-control" style="width: 100%;" rows="3"', __('Insert here any abstract or notes from the publication.'));
    // biblio cover image
    $str_input = '<div class="row">';
    $str_input .= '<div class="col-2">';
    $str_input .= '<div id="imageFilename" class="s-margin__bottom-1">';
    $upper_dir = '';
    if ($in_pop_up) {
        $upper_dir = '../../';
    }
    $imageDisk = Storage::images();
    if (isset($rec_d['image']) && $imageDisk->isExists('docs/' . $rec_d['image'])) {
        $url = $upper_dir . '../lib/minigalnano/createthumb.php?filename=images/docs/'.urlencode($rec_d['image'] ?? '');
        $str_input .= '<a href="' . $url . '&width=220" class="openPopUp notAJAX" title="' . __('Click to enlarge preview') . '">';
        $str_input .= '<img src="' . $url .'&width=130" class="img-fluid rounded" alt="Image cover">';
        $str_input .= '</a>';
        $str_input .= '<a href="' . MWB . 'bibliography/index.php" postdata="removeImage=true&bimg=' . $itemID . '&img=' . ($rec_d['image'] ?? '') . '" loadcontainer="imageFilename" class="s-margin__bottom-1 mt-1 s-btn btn btn-danger btn-block makeHidden removeImage">' . __('Remove Image') . '</a>';
    } else {
        $str_input .= '<img src="' . $upper_dir . '../lib/minigalnano/createthumb.php?filename=images/default/image.png&width=130" class="img-fluid rounded" alt="Image cover">';
    }
    $str_input .= '</div>';
    $str_input .= '</div>';
    $str_input .= '<div class="custom-file col-7">';
    $str_input .= simbio_form_element::textField('file', 'image', '', 'class="custom-file-input" id="customFile"  accept="'.implode(',', $sysconf['allowed_images']).'"');
    $str_input .= '<label class="custom-file-label" for="customFile">' . __('Choose file') . '</label>';
    $str_input .= '<div style="padding: 10px;margin-left: -25px;">';
    $str_input .= '<div>' . __('Or download from url') . '</div>';
    $str_input .= '<div class="form-inline">
                  <input type="text" id="getUrl" class="form-control" style="width:190px" placeholder="Paste url address here">
                  <div class="input-group-append">
                  <button class="btn btn-default" type="button" id="getImage">' . __('Download') . ' <i class="fa fa-spin fa-cog hidden" id="imgLoader"></i></button>
                  <button class="btn btn-default openPopUp notAJAX ml-2" type="button" id="showEngine" onclick="toggle_search($(\'#title\').val());">' . __('Trying search in DuckduckGo') . '</button>
                  </div>
                  </div>';
    $str_input .= '</div>';
    $str_input .= '</div>';
    $str_input .= ' <div class="mt-2 ml-2">Maximum ' . $sysconf['max_image_upload'] . ' KB</div>';
    $str_input .= '</div>';
    $str_input .= '<textarea id="base64picstring" name="base64picstring" style="display: none;"></textarea>';
    $str_input .= '</div></div></div>';

    //for scanner
    if ($sysconf['scanner'] !== false) {
        $str_input .= '<p>' . __('or scan a cover') . '</p>';
        // $str_input .= '<textarea id="base64picstring" name="base64picstring" style="display: none;"></textarea>';

        if ($sysconf['scanner'] == 'html5') {
            $str_input .= '<input type="button" value="' . __('Show scan dialog') . '" class="button btn btn-default openPopUp" onclick="toggle_dialog();" />';
            $str_input .= '<input type="button" value="' . __('Reset') . '" class="button btn btn-danger openPopUp ml-1" onclick="scan_reset();" />';
            $str_input .= '<div id="scan_overlay" style="display: none; position: absolute; left: 0; top: 0; width: 100%; height: 100%; z-index: 1000; background: rgba(192, 194, 201, 0.5);">';
            $str_input .= '<div id="scan_dialog" title="' . __('Scan a cover') . '">';
            $str_input .= '<div id="scan_options_std" style="margin: 5px;"><label>' . __('Format:') . ' <select id="scan_type" onchange="scan_type();">';
            $str_input .= '<option value="png">PNG</option><option value="jpg">JPEG</option></select></label> ';
            $str_input .= '<input type="button" id="btn_getscan" class="button btn" onclick="scan()" value="' . __('Scan') . '" />';
            $str_input .= '<i style="margin-left: 10px; cursor: pointer; cursor: hand;" title="' . __('Click to show or hide options') . '" onclick="toggle_options()" class="fa fa-gear fa-2x"></i></div>';
            $str_input .= '<div id="scan_options" class="makeHidden s-margin__bottom-1" style="margin: 5px;">';
            $str_input .= '<p style="padding: 3px 0;"><label>' . __('History index:') . ' <input type="text" id="scan_history" value="1" style="width: 60px;" /></label> <input type="button" id="btn_getrecall" class="button btn" onclick="scan_recall" value="' . __('Recall') . '" /></p>';
            $str_input .= '<p style="padding: 3px 0;"><label>' . __('Host:') . ' <input type="text" id="scan_host" value="localhost" /></label> | <label>Port: <input type="text" id="scan_port" patter="\d*" maxlength="6" size="6" style="width: 60px;" value="8811" /></label> <input type="button" id="btn_getmachine" class="button btn" onclick="scan_init()" value="' . __('Get machine') . '" /></p>';
            $str_input .= '<p style="padding: 3px 0;"><label>' . __('Scanner:') . ' <select id="scan_machine" readonly><option>' . __('Default') . '</option></select></label></p>';
            $str_input .= '<p style="padding: 3px 0;">' . __('Resolution') . ', <label>' . __('Horizontal:') . ' <input type="text" id="scan_res_x" value="300" pattern="\d*" maxlength="3" size="3" style="width: 60px;" />dpi</label> - <label>' . __('Vertical:') . ' <input type="text" id="scan_res_y" value="300" pattern="\d*" maxlength="3" size="3" style="width: 60px;" />dpi</label></p>';
            $str_input .= '<p style="padding: 3px 0;">' . __('Capture') . ', <label>' . __('Width:') . ' <input type="text" id="scan_capture_w" value="2550" pattern="\d*" maxlength="4" size="4" style="width: 60px;" />px</label> - <label>' . __('Height:') . ' <input type="text" id="scan_capture_h" value="3507" pattern="\d*" maxlength="4" size="4" style="width: 60px;" />px</label></p>';
            $str_input .= '<p>' . __('Result') . ', <label>' . __('Max Width:') . ' <input type="text" id="scan_max_w" value="360" pattern="\d*" maxlength="3" size="3" style="width: 60px;" />px</label> - <label>' . __('Max Height:') . ' <input type="text" id="scan_max_h" value="480" pattern="\d*" maxlength="3" size="3" style="width: 60px;" />px</label></p></div>';
            $str_input .= '<div id="scan_container" style="margin: 5px;"><div style="height: 550px; width: 390px; overflow: auto; float: left;"><p>' . __('Scan result') . '</p><img id="my_imgdata" style="margin: auto;" /></div>';
            $str_input .= '<div style="padding-left: 10px; height: 550; width: 400px; overflow: auto; float: left;"><p>' . __('Preview') . ' <input type="button" class="button btn" value="' . __('Rotate Left') . '" onclick="scan_rotate(\'left\')" /> <input type="button" class="button btn" value="' . __('Rotate Right') . '" onclick="scan_rotate(\'right\')" /></p><canvas id="my_selected" style="border: 1px solid #CCC; margin: auto;"></canvas></div></div></div></div>';
        }
    }

    $form->addAnything(__('Image'), $str_input);

    // biblio file attachment
    // $str_input = '<div class="'.$visibility.'"><a class="notAJAX button" href="javascript: openHTMLpop(\''.MWB.'bibliography/pop_attach.php?biblioID='.$rec_d['biblio_id'].'\', 600, 300, \''.__('File Attachments').'\')">'.__('Add Attachment').'</a></div>';
    $str_input = '<div class="' . $visibility . ' s-margin__bottom-1"><a class="s-btn btn btn-default notAJAX openPopUp" href="' . MWB . 'bibliography/pop_attach.php?biblioID=' . ($rec_d['biblio_id'] ?? '') . '" width="780" height="500" title="' . __('File Attachments') . '">' . __('Add Attachment') . '</a></div>';
    $str_input .= '<iframe name="attachIframe" id="attachIframe" class="form-control auto-resize-iframe" style="width: 100%; height: 100px; min-height: 80px; max-height: 250px;" src="' . MWB . 'bibliography/iframe_attach.php?biblioID=' . ($rec_d['biblio_id'] ?? '') . '&block=1"></iframe>';
    $form->addAnything(__('File Attachment'), $str_input);

    // biblio relation
    $str_input = '<div class="' . $visibility . ' s-margin__bottom-1"><a class="s-btn btn btn-default notAJAX openPopUp" href="' . MWB . 'bibliography/pop_biblio_rel.php?biblioID=' . ($rec_d['biblio_id'] ?? '') . '" title="' . __('Biblio Relation') . '">' . __('Add Relation') . '</a></div>';
    $str_input .= '<iframe name="biblioIframe" id="biblioIframe" class="form-control auto-resize-iframe" style="width: 100%; height: 100px; min-height: 80px; max-height: 300px;" src="' . MWB . 'bibliography/iframe_biblio_rel.php?biblioID=' . ($rec_d['biblio_id'] ?? '') . '&block=1"></iframe>';
    $form->addAnything(__('Related Biblio Data'), $str_input);

    /**
     * Custom fields
     */
    $form->loadCustomField('biblio', 'biblio_id', $itemID);
    
    // get advance custom field based on plugin
    $js = '';
    Plugins::getInstance()->execute(Plugins::BIBLIOGRAPHY_CUSTOM_FIELD_FORM, ['form' => $form, 'js' => &$js, 'data' => $rec_cust_d ?? []]);

    // biblio hide from opac
    $hide_options[] = array('0', __('Show'));
    $hide_options[] = array('1', __('Hide'));
    $form->addHidden('opacHideOrigin', $rec_d['opac_hide']??0);
    $form->addRadio('opacHide', __('Hide in OPAC'), $hide_options, isset($rec_d['opac_hide']) && $rec_d['opac_hide'] ? '1' : '0');
    // biblio promote to front page
    $promote_options[] = array('0', __('Don\'t Promote'));
    $promote_options[] = array('1', __('Promote'));
    $form->addRadio('promote', __('Promote To Homepage'), $promote_options, isset($rec_d['promoted']) && $rec_d['promoted'] ? '1' : '0');
    // biblio labels
    $arr_labels = !empty($rec_d['labels']) ? unserialize($rec_d['labels']) : array();
    if ($arr_labels) {
        foreach ($arr_labels as $label) {
            $arr_labels[$label[0]] = $label[1];
        }
    }
    $str_input = '';
    // get label data from database
    // Modified by Eddy Subratha
    $label_q = $dbs->query("SELECT * FROM mst_label LIMIT 20");
    while ($label_d = $label_q->fetch_assoc()) {
        $checked = isset($arr_labels[$label_d['label_name']]) ? ' checked' : '';
        $url = isset($arr_labels[$label_d['label_name']]) ? $arr_labels[$label_d['label_name']] : '';
        $str_input .= '
      <div class="input-group s-labels__group noAutoFocus">
        <div class="input-group-prepend">
          <span class="input-group-text" style="width:150px">
            <input type="checkbox" name="labels[]" value="' . $label_d['label_name'] . '"' . $checked . ' aria-label="Labels">&nbsp;' . $label_d['label_desc'] . '
          </span>
          <span class="input-group-text s-labels__icon">
            <img src="../lib/minigalnano/createthumb.php?filename=' . IMG . '/labels/' . urlencode($label_d['label_image']) . '&amp;width=24" class="' . $label_d['label_name'] . '" id="' . $label_d['label_name'] . '" alt="' . $label_d['label_name'] . '"/>
          </span>
        </div>
        <input type="text" value="' . $url . '" placeholder="Enter a website link/URL to make this label clickable" title="Enter a website link/URL to make this label clickable" name="label_urls[' . $label_d['label_name'] . ']" id="label_urls[' . $label_d['label_name'] . ']" class="form-control" aria-label="Url for current label" style="border-left:none;">
        </div>';
    }
    $form->addAnything('Label', $str_input);
    // $form->addCheckBox('labels', 'Label', $label_options, explode(' ', $rec_d['labels']));

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="s-alert infoBox">'
            . __('You are going to edit biblio data') . ' : <b>' . $rec_d['title'] . '</b>  <br />' . __('Last Updated') . '&nbsp;' . date('d F Y h:i:s', strtotime($rec_d['last_update'])); //mfc
        if (isset($rec_d['image'])) {
            if (file_exists(IMGBS . 'docs/' . $rec_d['image'])) {
                $upper_dir = '';
                if ($in_pop_up) {
                    $upper_dir = '../../';
                }
                // Modified by Eddy Subratha
                // We removed image near the upload form
                // echo '<div id="biblioImage" class="s-biblio__cover"><img src="'.$upper_dir.'../lib/minigalnano/createthumb.php?`filename`=../../images/docs/'.urlencode($rec_d['image']).'&width=53" /></div>';
            }
        }
        echo '</div>' . "\n";
    }
    // print out the form object
    echo $form->printOut();
    echo \SLiMS\Bg\Validator::jsI18nScript();
    // javascript
    ?>
    <script type="text/javascript">
        $(document).ready(function () {
            // сигнатурата се попълва от класификацията само ако е празна (или е попълнена автоматично)
            var bgAutoCallNumber = null;
            $('#class').change(function () {
                var cn = $('#callNumber'), udc = String($(this).val() || '').replace('NEW:', '');
                if (cn.val() === '' || cn.val() === bgAutoCallNumber) {
                    bgAutoCallNumber = window.BgCat ? BgCat.composeCallNumber(udc, $('#author_mark').val()) : udc;
                    cn.val(bgAutoCallNumber);
                }
            });
            $('#bgComposeCallNumber').click(function () {
                var udc = String($('#class').val() || '').replace('NEW:', '');
                bgAutoCallNumber = window.BgCat ? BgCat.composeCallNumber(udc, $('#author_mark').val()) : (udc + ' ' + $('#author_mark').val()).trim();
                $('#callNumber').val(bgAutoCallNumber).focus();
            });

            $('.removeImage').click(function (e) {
                if (confirm('Are you sure you want to permanently remove this image?')) {
                    return true;
                } else {
                    return false;
                }
            });

            $(document).on('change', '.custom-file-input', function () {
                // $('#imageFilename img').attr('src',document.getElementById("image").files[0].name);
                var input = document.querySelector("#image");
                var fReader = new FileReader();
                fReader.readAsDataURL(input.files[0]);
                fReader.onloadend = function (event) {
                    var img = document.querySelector("#imageFilename img");
                    img.src = event.target.result;
                }
                let fileName = $(this).val().replace(/\\/g, '/').replace(/.*\//, '');
                $(this).parent('.custom-file').find('.custom-file-label').text(fileName);
            });

            $('#getImage').click(function () {
                $.post("<?php echo MWB ?>bibliography/scrape_image.php", {imageURL: $('#getUrl').val()})
                    .done(function (data) {
                        if (data.status == 'VALID') {
                            $('#base64picstring').val(data.image);
                            $('#imageFilename img').attr('src', data.message);
                        } else {
                            $('#base64picstring, #getUrl').val('');
                            parent.toastr.error("<?php echo __('Current url is not valid or your internet is down.') ?>", "Bibliography Image", {
                                "closeButton": true,
                                "debug": false,
                                "newestOnTop": false,
                                "progressBar": false,
                                "positionClass": "toast-top-right",
                                "preventDuplicates": false,
                                "onclick": null,
                                "showDuration": 300,
                                "hideDuration": 1000,
                                "timeOut": 5000,
                                "extendedTimeOut": 1000,
                                "showEasing": "swing",
                                "hideEasing": "linear",
                                "showMethod": "fadeIn",
                                "hideMethod": "fadeOut"
                            })
                        }
                    });
            });

            <?php
            if (isset($js) && !empty($js))
            {
                echo $js;
            }
            ?>
        });
    </script>
    <?php
} else {
    # CHECK ACCESS
    if ($_SESSION['uid'] != 1) {
        if (!utility::haveAccess('bibliography.bibliography-list')) {
            die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
        }
    }

    # ADV LOG SYSTEM - STIIL EXPERIMENTAL
    $log = new AlLibrarian('1151', array("username" => $_SESSION['uname'], "uid" => $_SESSION['uid'], "realname" => $_SESSION['realname']));

    require SIMBIO . 'simbio_UTILS/simbio_tokenizecql.inc.php';
    require MDLBS . 'bibliography/biblio_utils.inc.php';
    require LIB . 'biblio_list_model.inc.php';

    // number of records to show in list
    $biblio_result_num = ($sysconf['biblio_result_num'] > 100) ? 100 : $sysconf['biblio_result_num'];

    // create datagrid
    $datagrid = new simbio_datagrid();

    // index choice
    $search_engine = Engine::active();
    if (!in_array($search_engine, [DefaultEngine::class, \SLiMS\SearchEngine\BgCatalogEngine::class], true)) {
        if ($search_engine == SphinxSearchEngine::class) {
            require LIB . 'sphinx/sphinxapi.php';
            require LIB . 'biblio_list_sphinx.inc.php';
        } else {
            require LIB . 'biblio_list_index.inc.php';
        }

        // table spec
        $table_spec = 'search_biblio AS `index` LEFT JOIN item ON `index`.biblio_id=item.biblio_id';
        $str_criteria = 'index.biblio_id IS NOT NULL';
        if ($can_read AND $can_write) {
            $datagrid->setSQLColumn('index.biblio_id', 'index.title AS \'' . __('Title') . '\'', 'index.labels', 'index.image',
                'index.author',
                'index.edition AS \'' . __('Edition') . '\'',
                'index.isbn_issn AS \'' . __('ISBN/ISSN') . '\'',
                'IF(COUNT(item.item_id)>0, COUNT(item.item_id), \'<strong style="color: #f00;">' . __('None') . '</strong>\') AS \'' . __('Copies') . '\'',
                'index.last_update AS \'' . __('Last Update') . '\'');
            $datagrid->modifyColumnContent(1, 'callback{showTitleAuthors}');
        } else {
            $datagrid->setSQLColumn('index.title AS \'' . __('Title') . '\'', 'index.author', 'index.labels', 'index.image',
                'index.isbn_issn AS \'' . __('ISBN/ISSN') . '\'',
                'IF(COUNT(item.item_id)>0, COUNT(item.item_id), \'<strong style="color: #f00;">' . __('None') . '</strong>\') AS \'' . __('Copies') . '\'',
                'index.last_update AS \'' . __('Last Update') . '\'');
            $datagrid->modifyColumnContent(1, 'callback{showTitleAuthors}');
        }
        $datagrid->invisible_fields = array(1, 2, 3);
        $datagrid->setSQLorder('index.last_update DESC');

        // set group by
        $datagrid->sql_group_by = 'index.biblio_id';

    } else {
        require LIB . 'biblio_list.inc.php';

        // table spec
        $table_spec = 'biblio LEFT JOIN item ON biblio.biblio_id=item.biblio_id';
        $str_criteria = 'biblio.biblio_id IS NOT NULL';
        if ($can_read AND $can_write) {
            $datagrid->setSQLColumn('biblio.biblio_id', 'biblio.biblio_id AS bid',
                'biblio.title AS \'' . __('Title') . '\'',
                'biblio.isbn_issn AS \'' . __('ISBN/ISSN') . '\'',
                'IF(COUNT(item.item_id)>0, COUNT(item.item_id), \'<strong style="color: #f00;">' . __('None') . '</strong>\') AS \'' . __('Copies') . '\'',
                'biblio.last_update AS \'' . __('Last Update') . '\'');
            $datagrid->modifyColumnContent(2, 'callback{showTitleAuthors}');
        } else {
            $datagrid->setSQLColumn('biblio.biblio_id AS bid', 'biblio.title AS \'' . __('Title') . '\'',
                'biblio.isbn_issn AS \'' . __('ISBN/ISSN') . '\'',
                'IF(COUNT(item.item_id)>0, COUNT(item.item_id), \'<strong style="color: #f00;">' . __('None') . '</strong>\') AS \'' . __('Copies') . '\'',
                'biblio.last_update AS \'' . __('Last Update') . '\'');
            // modify column value
            $datagrid->modifyColumnContent(1, 'callback{showTitleAuthors}');
        }
        $datagrid->invisible_fields = array(0);
        $datagrid->setSQLorder('biblio.last_update DESC');

        // set group by
        $datagrid->sql_group_by = 'biblio.biblio_id';
    }

    $stopwords = "@\sAnd\s|\sOr\s|\sNot\s|\sThe\s|\sDan\s|\sAtau\s|\sAn\s|\sA\s@i";

    // is there any search
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $keywords = $dbs->escape_string(trim($_GET['keywords']));
        $keywords = preg_replace($stopwords, ' ', $keywords);
        $searchable_fields = array('title', 'author', 'subject', 'isbn', 'publisher');
        if ($_GET['field'] != '0' AND in_array($_GET['field'], $searchable_fields)) {
            $field = $_GET['field'];
            $search_str = $field . '=' . $keywords;
        } else {
            $search_str = '';
            foreach ($searchable_fields as $search_field) {
                $search_str .= $search_field . '=' . $keywords . ' OR ';
            }
            $search_str = substr_replace($search_str, '', -4);
        }
        $biblio_list = new biblio_list($dbs, $biblio_result_num);
        $criteria = $biblio_list->setSQLcriteria($search_str);
        $str_criteria .= ' AND (' . $criteria['sql_criteria'] . ')';
    }

    if (isset($_GET['opac_hide']) && $_GET['opac_hide'] != '') {
        $opac_hide = $dbs->escape_string($_GET['opac_hide']);
        $str_criteria .= ' AND opac_hide =' . $opac_hide;
    }

    if (isset($_GET['promoted']) && $_GET['promoted'] != '') {
        $promoted = $dbs->escape_string($_GET['promoted']);
        $str_criteria .= ' AND promoted =' . $promoted;
    }
    //debug
    //echo $str_criteria;

    $datagrid->setSQLcriteria($str_criteria);

    // set table and table header attributes
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];
    $datagrid->debug = true;

    // execute registered hook
    Plugins::run(Plugins::BIBLIOGRAPHY_BEFORE_DATAGRID_OUTPUT);

    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, $biblio_result_num, ($can_read AND $can_write));
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
        echo '<div class="infoBox">' . $msg . ' : "' . htmlentities($_GET['keywords']) . '"<div>' . __('Query took') . ' <b>' . $datagrid->query_time . '</b> ' . __('second(s) to complete') . '</div></div>'; //mfc
    }

    echo $datagrid_result;

    // печат от избраните заглавия: баркодове, етикети (A4) и каталожни картички за картотеката
    if ($can_read AND $can_write) {
        $bg_kinds = ['barcode' => __('Barcodes'), 'signature' => __('Call number labels'), 'combined' => __('Combined labels'), 'cards' => __('Catalogue cards')];
        $bg_group = '<div class="btn-group ml-2 bg-print-group" role="group"><span class="btn btn-light disabled">' . __('Print selected:') . '</span>';
        foreach ($bg_kinds as $bg_kind => $bg_label) {
            $bg_group .= '<button type="button" class="btn btn-info bg-print-sel" data-kind="' . $bg_kind . '">' . $bg_label . '</button>';
        }
        $bg_group .= '</div>';
        ?>
        <script type="text/javascript">
        (function ($) {
            var group = <?= json_encode($bg_group, JSON_UNESCAPED_UNICODE) ?>;
            $('#mainContent .datagrid-action-bar td:first-child').each(function () {
                if (!$(this).find('.bg-print-group').length) $(this).append(group);
            });
            $('#mainContent').off('click.bgprint').on('click.bgprint', '.bg-print-sel', function () {
                var ids = $('#mainContent input.selected-row:checked').map(function () { return this.value; }).get();
                if (!ids.length) {
                    top.toastr.warning(<?= json_encode(__('Select at least one title'), JSON_UNESCAPED_UNICODE) ?>, <?= json_encode(__('Bibliography'), JSON_UNESCAPED_UNICODE) ?>);
                    return;
                }
                var kind = $(this).data('kind');
                var url = kind === 'cards'
                    ? '<?= MWB ?>bibliography/bg_cards.php?ids='
                    : '<?= MWB ?>bibliography/bg_labels.php?source=biblio&template=' + kind + '&ids=';
                $('#mainContent').simbioAJAX(url + ids.join(','));
            });
        })(jQuery);
        </script>
        <?php
    }
}
/* main content end */
