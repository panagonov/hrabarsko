<?php
/**
 * Пакетно създаване на екземпляри от формуляра за заглавие:
 * поредни инвентарни номера (= баркод), свързани със запис в КДБФ.
 */

namespace SLiMS\Bg;

class BatchItems
{
    /**
     * Проверява данните за пакетното създаване. Връща null, ако не се създават екземпляри.
     */
    public static function validate(Validator $v, array $post, int $maxBatch): ?array
    {
        $total = trim((string)($post['totalItems'] ?? ''));
        if ($total === '' || $total === '0') return null;

        $count = $v->positiveInt('totalItems', $total, __('Number of copies'), $maxBatch);
        $start = $v->inventoryNo('invStart', $post['invStart'] ?? '');
        $b = [
            'count' => (int)$count,
            'start' => (int)$start,
            'numbers' => [],
            'location_id' => trim((string)($post['locationID'] ?? '')),
            'site' => trim(strip_tags((string)($post['itemSite'] ?? ''))),
            'coll_type_id' => (int)($post['collTypeID'] ?? 0),
            'item_status_id' => trim((string)($post['itemStatusID'] ?? '0')),
            'source' => (int)($post['source'] ?? 1),
            'supplier_id' => (int)($post['supplierID'] ?? 0),
            'order_no' => trim(strip_tags((string)($post['ordNo'] ?? ''))),
            'invoice' => trim(strip_tags((string)($post['invoice'] ?? ''))),
            'volume' => trim(strip_tags((string)($post['itemVolume'] ?? ''))),
            'item_notes' => trim(strip_tags((string)($post['itemNotes'] ?? ''))),
            'kdbf_entry_id' => (int)($post['kdbfEntryID'] ?? 0),
            'kdbf_legacy_ref' => trim(strip_tags((string)($post['kdbfLegacyRef'] ?? ''))),
        ];
        $today = date('Y-m-d');
        $b['received_date'] = $v->date('recvDate', $post['recvDate'] ?? '', __('Entry date'), true, $today);
        $b['order_date'] = $v->date('orDate', $post['orDate'] ?? '', __('Order Date'), false, $today);
        $b['invoice_date'] = $v->date('invcDate', $post['invcDate'] ?? '', __('Invoice Date'), false, $today);
        $b['price'] = $v->price('price', $post['price'] ?? '', true);
        $b['price_currency'] = $v->currency('priceCurrency', $post['priceCurrency'] ?? '', $b['received_date']);

        $v->required('locationID', $b['location_id'], __('Location'));
        if ($b['coll_type_id'] < 1) $v->error('collTypeID', sprintf(__('%s is required'), __('Collection Type')));
        if ($b['item_status_id'] === 'WO') $v->error('itemStatusID', __('Status "Written off" is set only by an approved write-off act'));
        $v->maxLen('itemSite', $b['site'], 50, __('Shelf Location'));
        $v->maxLen('ordNo', $b['order_no'], 20, __('Order Number'));
        $v->maxLen('invoice', $b['invoice'], 20, __('Invoice'));
        $v->maxLen('itemVolume', $b['volume'], 30, __('Volume'));
        $v->maxLen('kdbfLegacyRef', $b['kdbf_legacy_ref'], 40, __('Paper KDBF reference'));
        if ($b['order_date'] && $b['received_date'] && $b['order_date'] > $b['received_date']) {
            $v->warn('orDate', __('Order date is after the entry date'));
        }

        // връзка с КДБФ (задължителна за новите постъпления)
        if ($b['kdbf_entry_id'] > 0) {
            $entry = Kdbf::entry($b['kdbf_entry_id']);
            if (!$entry) {
                $v->error('kdbfEntryID', __('The selected KDBF entry does not exist'));
            } elseif ($b['received_date'] && $b['received_date'] < $entry['entry_date']) {
                $v->warn('recvDate', __('Entry date is before the date of the KDBF entry'));
            }
        } elseif ($b['kdbf_legacy_ref'] === '' && $start) {
            if ((int)$start > Inventory::floor()) {
                $v->error('kdbfEntryID', __('Choose the KDBF entry (acquisition) for these copies'));
            } else {
                $v->warn('kdbfEntryID', __('No KDBF entry – fill in the reference from the paper KDBF if you have it'));
            }
        }

        if ($count && $start) {
            $b['numbers'] = range((int)$start, (int)$start + (int)$count - 1);
            $taken = Inventory::takenNumbers($b['numbers']);
            if ($taken) {
                $v->error('invStart', sprintf(__('These inventory numbers are already used: %s'), Inventory::formatRanges($taken)));
            }
            $next = Inventory::nextNo();
            if ((int)$start > $next + 100 && empty($post['confirmBigJump'])) {
                $v->error('invStart', sprintf(__('Inventory number %d is far above the next free number %d – please confirm'), (int)$start, $next));
            }
        }
        return $b;
    }

    /**
     * Създава екземплярите. Връща [създадени номера, неуспешни номера].
     */
    public static function insert(array $b, int $biblioId, string $biblioCallNumber, int $uid): array
    {
        $created = [];
        $failed = [];
        $callNumber = Inventory::composeCallNumber($b['location_id'], $biblioCallNumber);
        $now = date('Y-m-d H:i:s');
        $sql = 'INSERT INTO item (biblio_id, item_code, inventory_code, call_number, volume, coll_type_id, location_id, site,
                    item_status_id, source, supplier_id, order_no, order_date, received_date, invoice, invoice_date,
                    price, price_currency, item_notes, kdbf_entry_id, kdbf_legacy_ref, input_date, last_update, uid)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        foreach ($b['numbers'] as $n) {
            $code = (string)$n;
            try {
                Db::q($sql, [
                    $biblioId, $code, $code, $callNumber, $b['volume'] ?: null, $b['coll_type_id'], $b['location_id'], $b['site'],
                    $b['item_status_id'], $b['source'], (string)$b['supplier_id'], $b['order_no'], $b['order_date'], $b['received_date'],
                    $b['invoice'], $b['invoice_date'], $b['price'], $b['price_currency'], $b['item_notes'] ?: null,
                    $b['kdbf_entry_id'] ?: null, $b['kdbf_legacy_ref'] ?: null, $now, $now, $uid,
                ]);
                $created[] = $n;
            } catch (\Throwable $e) {
                $failed[] = $n;
            }
        }
        if ($b['kdbf_entry_id']) $_SESSION['bg_last_kdbf_entry'] = $b['kdbf_entry_id'];
        return [$created, $failed];
    }

    /** Падащ списък с КДБФ записите (текущата и предходната година). */
    public static function kdbfOptionsHtml(?int $selected): string
    {
        if (!Db::tableExists('kdbf_entry')) return '';
        $year = (int)date('Y');
        $rows = Db::all('SELECT e.*, s.supplier_name FROM kdbf_entry e LEFT JOIN mst_supplier s ON s.supplier_id = e.supplier_id
                          WHERE e.entry_year >= ? OR e.entry_id = ? ORDER BY e.entry_year DESC, e.entry_no DESC', [$year - 1, (int)$selected]);
        $methods = Kdbf::acqMethods();
        $html = '<option value="0">— ' . __('not selected') . ' —</option>';
        foreach ($rows as $r) {
            $label = Kdbf::entryLabel($r) . ' · ' . ($methods[$r['acq_method']] ?? $r['acq_method'])
                . ($r['source_name'] ? ' · ' . $r['source_name'] : ($r['supplier_name'] ? ' · ' . $r['supplier_name'] : ''));
            $html .= sprintf('<option value="%d" data-date="%s" data-method="%s" data-supplier="%d" data-docno="%s" data-docdate="%s" data-currency="%s"%s>%s</option>',
                $r['entry_id'], $r['entry_date'], $r['acq_method'], (int)$r['supplier_id'], htmlspecialchars((string)$r['doc_no']),
                (string)$r['doc_date'], $r['currency'], ((int)$r['entry_id'] === (int)$selected ? ' selected' : ''), htmlspecialchars($label));
        }
        return $html;
    }

    /** Блокът за пакетно създаване във формуляра за заглавие. */
    public static function formHtml(array $sysconf, string $visibility, ?int $preselectEntry): string
    {
        $next = Inventory::nextNo();
        $h = fn($s) => htmlspecialchars((string)$s);
        $row = function (string $label, string $control) {
            return '<div class="form-group divRow p-1"><div class="col-3">' . $label . '</div>' . $control . '</div>';
        };
        $select = function (string $name, array $options, $selected, string $attr = '') use ($h) {
            $o = '<select name="' . $name . '" id="' . $name . '" ' . $attr . '>';
            foreach ($options as [$val, $label]) {
                $o .= '<option value="' . $h($val) . '"' . ((string)$val === (string)$selected ? ' selected' : '') . '>' . $h($label) . '</option>';
            }
            return $o . '</select>';
        };

        $locations = [['', '-- ' . __('Location') . ' --']];
        foreach (Db::all('SELECT location_id, location_name FROM mst_location ORDER BY location_name') as $r) $locations[] = [$r['location_id'], $r['location_name']];
        $colls = [['', '-- ' . __('Collection Type') . ' --']];
        foreach (Db::all('SELECT coll_type_id, coll_type_name FROM mst_coll_type ORDER BY coll_type_name') as $r) $colls[] = [$r['coll_type_id'], $r['coll_type_name']];
        $statuses = [['0', __('Available')]];
        foreach (Db::all("SELECT item_status_id, item_status_name FROM mst_item_status WHERE item_status_id <> 'WO'") as $r) $statuses[] = [$r['item_status_id'], $r['item_status_name']];
        $suppliers = [['0', __('Not Applicable')]];
        foreach (Db::all('SELECT supplier_id, supplier_name FROM mst_supplier ORDER BY supplier_name') as $r) $suppliers[] = [$r['supplier_id'], $r['supplier_name']];
        $currencies = [];
        foreach ($sysconf['currencies'] as $c) $currencies[] = is_array($c) ? $c : [$c, $c];
        $today = date('Y-m-d');
        $preselectEntry = $preselectEntry ?: (int)($_SESSION['bg_last_kdbf_entry'] ?? 0);

        $s = '<div data-bg-when="totalItems">';
        $s .= '<div class="form-inline">';
        $s .= '<label class="mr-2" for="invStart">' . __('Inventory No. from') . '</label>';
        $s .= '<input type="text" class="form-control col-2 noAutoFocus" name="invStart" id="invStart" value="' . $next . '" inputmode="numeric" maxlength="9" data-bgval="invno" data-bg-next="' . $next . '" data-bg-label="' . $h(__('Inventory No. from')) . '" />&nbsp;';
        $s .= '<label class="mr-2 ml-2" for="totalItems">' . __('Number of copies') . '</label>';
        $s .= '<input type="number" min="0" max="' . (int)$sysconf['max_insert_batch'] . '" step="1" class="form-control col-2 noAutoFocus" name="totalItems" id="totalItems" placeholder="0" />&nbsp;';
        $s .= '<div class="' . $visibility . '"><div class="btn btn-info" data-toggle="collapse" data-target="#batchItemDetail" aria-expanded="false" aria-controls="batchItemDetail">' . __('Options') . '</div></div>';
        $s .= '</div>';
        $s .= '<small class="form-text text-muted">' . sprintf(__('The inventory number is also the barcode. Next free number: %d'), $next) . '</small>';
        $s .= '<div class="collapse' . ($preselectEntry && !empty($_GET['kdbfEntryID']) ? ' show' : '') . '" id="batchItemDetail" style="padding:10px;width:100%; text-align:left !important;">';

        $kdbf = self::kdbfOptionsHtml($preselectEntry);
        if ($kdbf !== '') {
            $s .= $row(__('KDBF entry'), '<select name="kdbfEntryID" id="kdbfEntryID" class="form-control col-6">' . $kdbf . '</select>');
        }
        $s .= $row(__('Paper KDBF reference'), '<input type="text" name="kdbfLegacyRef" id="kdbfLegacyRef" maxlength="40" class="form-control col-4" placeholder="' . $h(__('e.g. 12/1987 – for books entered before the system')) . '" />');
        $s .= $row(__('Location') . ' *', $select('locationID', $locations, '', 'class="form-control col-4"'));
        $s .= $row(__('Shelf Location'), '<input type="text" name="itemSite" id="itemSite" maxlength="50" class="form-control col-4" />');
        $s .= $row(__('Collection Type') . ' *', $select('collTypeID', $colls, '', 'class="form-control col-4"'));
        $s .= $row(__('Item Status'), $select('itemStatusID', $statuses, '0', 'class="form-control col-4"'));
        $s .= $row(__('Source'), $select('source', [['1', __('Buy')], ['2', __('Prize/Grant')]], '1', 'class="form-control col-4"'));
        $s .= $row(__('Volume'), '<input type="text" name="itemVolume" id="itemVolume" maxlength="30" class="form-control col-2" placeholder="' . $h(__('e.g. 2')) . '" />');
        $s .= $row(__('Order Date'), '<input type="date" name="orDate" id="orDate" value="" max="' . $today . '" class="form-control col-4" data-bgval="date-past" data-bg-label="' . $h(__('Order Date')) . '" />');
        $s .= $row(__('Entry date') . ' *', '<input type="date" name="recvDate" id="recvDate" value="' . $today . '" max="' . $today . '" class="form-control col-4" data-bgval="date-past" data-bg-required="1" data-bg-label="' . $h(__('Entry date')) . '" />');
        $s .= $row(__('Order Number'), '<input type="text" name="ordNo" id="ordNo" maxlength="20" class="form-control col-4" />');
        $s .= $row(__('Invoice'), '<input type="text" name="invoice" id="invoice" maxlength="20" class="form-control col-4" />');
        $s .= $row(__('Invoice Date'), '<input type="date" name="invcDate" id="invcDate" value="" max="' . $today . '" class="form-control col-4" data-bgval="date-past" data-bg-label="' . $h(__('Invoice Date')) . '" />');
        $s .= $row(__('Supplier'), $select('supplierID', $suppliers, '0', 'class="form-control col-4"'));
        $s .= $row(__('Price') . ' *', '<input type="text" name="price" id="price" value="" inputmode="decimal" maxlength="11" class="form-control col-3" data-bgval="price" data-bg-required="1" data-bg-label="' . $h(__('Price')) . '" placeholder="12.50" />'
            . $select('priceCurrency', $currencies, Inventory::defaultCurrency(), 'class="form-control col-2"'));
        $s .= $row(__('Item Notes'), '<input type="text" name="itemNotes" id="itemNotes" class="form-control col-8" />');
        $s .= '</div></div>';
        $s .= <<<'JS'
<script type="text/javascript">
(function ($) {
  // данните от записа в КДБФ попълват дата, доставчик, документ и валута
  $(document).off('change.bgkdbf').on('change.bgkdbf', '#kdbfEntryID', function () {
    var o = $(this).find('option:selected');
    if (!o.val() || o.val() === '0') return;
    if (o.data('date')) $('#recvDate').val(o.data('date'));
    if (o.data('supplier')) $('#supplierID').val(String(o.data('supplier')));
    if (o.data('docno')) $('#invoice').val(o.data('docno'));
    if (o.data('docdate')) $('#invcDate').val(o.data('docdate'));
    if (o.data('currency')) $('#priceCurrency').val(o.data('currency'));
    $('#source').val(o.data('method') === 'BUY' ? '1' : '2');
  });
  $(function () { if ($('#kdbfEntryID').val() && $('#kdbfEntryID').val() !== '0') $('#kdbfEntryID').trigger('change'); });
})(jQuery);
</script>
JS;
        return $s;
    }
}
