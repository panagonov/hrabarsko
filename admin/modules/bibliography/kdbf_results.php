<?php
/**
 * КДБФ част 3 – резултати от движението на фонда по години, начално наличие,
 * приключване на година и настройки (последен номер от хартиената инвентарна книга,
 * префикси на отделите в сигнатурата).
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

Ui::requireAccess('bibliography.kdbf-results');
$self = MWB . 'bibliography/kdbf_results.php';
$title = __('Annual Results (KDBF part 3)');

function bg_res_guard(string $title): void
{
    if (!Ui::canApprove() || !utility::havePrivilege('bibliography', 'w')) {
        Ui::toast($title, __('You are not allowed to change these settings'), 'error');
        exit();
    }
    if (!Ui::tokenValid()) {
        Ui::toast($title, __('Invalid form submission token!'), 'error');
        exit();
    }
}

function bg_save_setting(string $name, $value): void
{
    Db::q('INSERT INTO setting (setting_name, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)', [$name, serialize($value)]);
}

/* ------------------------------------------------------------------ начално наличие */
if (isset($_POST['saveBalance'])) {
    bg_res_guard($title);
    $v = new Validator();
    $y = $v->positiveInt('balanceYear', $_POST['balanceYear'] ?? '', __('Year'), 2100);
    $count = $v->nonNegativeInt('openingCount', $_POST['openingCount'] ?? '', __('Copies at the beginning of the year'));
    $value = $v->price('openingValue', $_POST['openingValue'] ?? '', true);
    $cur = $v->currency('currency', $_POST['currency'] ?? '', $y ? $y . '-01-01' : null);
    $notes = trim(strip_tags($_POST['notes'] ?? ''));
    if ($v->hasErrors()) $v->failAndExit($title);
    Db::q("INSERT INTO kdbf_year_balance (balance_year, opening_count, opening_value, currency, source, notes, last_update, uid) VALUES (?,?,?,?,'manual',?,NOW(),?)
           ON DUPLICATE KEY UPDATE opening_count=VALUES(opening_count), opening_value=VALUES(opening_value), currency=VALUES(currency), source='manual', notes=VALUES(notes), last_update=NOW(), uid=VALUES(uid)",
        [$y, $count, $value, $cur, $notes ?: null, (int)$_SESSION['uid']]);
    writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'] . ' set KDBF opening balance for ' . $y);
    Ui::toast($title, sprintf(__('Opening balance for %d saved'), $y), 'success');
    Ui::reload($self . '?year=' . $y);
    exit();
}

/* ------------------------------------------------------------------ приключване на година */
if (isset($_POST['closeYear'])) {
    bg_res_guard($title);
    $y = (int)$_POST['closeYear'];
    if ($y >= (int)date('Y')) {
        Ui::toast($title, __('Only a past year can be closed'), 'error');
        exit();
    }
    $r = Kdbf::closeYear($y, (int)$_SESSION['uid']);
    writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'] . ' close KDBF year ' . $y);
    Ui::toast($title, sprintf(__('Year %d is closed: %d copies, %s € carried over to %d'), $y, $r['closing']['count'], Inventory::fmt($r['closing']['eur']), $y + 1), 'success');
    Ui::reload($self . '?year=' . ($y + 1));
    exit();
}

/* ------------------------------------------------------------------ настройки */
if (isset($_POST['saveSettings'])) {
    bg_res_guard($title);
    $v = new Validator();
    $floor = trim((string)($_POST['inventoryFloor'] ?? '0'));
    if (!preg_match('/^\d{1,9}$/', $floor)) $v->error('inventoryFloor', __('Must be a whole number (0 or more)'));
    $map = Inventory::parsePrefixMap(str_replace(["\r\n", "\r"], "\n", (string)($_POST['locationPrefix'] ?? '')));
    $locations = array_column(Db::all('SELECT location_id FROM mst_location'), 'location_id');
    foreach ($map as $loc => $prefix) {
        if (!in_array($loc, array_map('strtoupper', $locations), true)) $v->error('locationPrefix', sprintf(__('Unknown department code "%s"'), $loc));
        if (mb_strlen($prefix) > 5 || preg_match('/\s/u', $prefix)) $v->error('locationPrefix', sprintf(__('Prefix "%s" must be short and without spaces'), $prefix));
    }
    if ($v->hasErrors()) $v->failAndExit($title);
    $pairs = [];
    foreach ($map as $k => $p) $pairs[] = $k . '=' . $p;
    bg_save_setting('bg_inventory_floor', (int)$floor);
    bg_save_setting('bg_location_prefix', implode(';', $pairs));
    writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'] . ' change KDBF/inventory settings');
    Ui::toast($title, __('Settings saved'), 'success');
    Ui::reload($self);
    exit();
}

/* ------------------------------------------------------------------ екран */
$year = (int)($_GET['year'] ?? date('Y'));
echo Ui::header($title, [],
    '<form action="' . Ui::h($self) . '" method="get" class="form-inline mt-2">' . __('Year') . '&nbsp;<input type="number" name="year" min="1900" max="2100" value="' . $year . '" class="form-control col-2" />&nbsp;<input type="submit" class="btn btn-default" value="' . __('Show') . '" /></form>');
echo Ui::submitFrame();

$r = Kdbf::yearResults($year);
$src = [
    'manual' => __('entered manually'),
    'closing' => __('from the closed previous year'),
    'computed' => __('computed from the previous year'),
    'none' => __('no data – enter the opening balance below'),
];
$line = function (string $label, array $d, string $note = '') {
    return '<tr><td>' . $label . ($note ? ' <small class="text-muted">(' . $note . ')</small>' : '') . '</td><td class="text-right">' . (int)$d['count'] . '</td>'
        . '<td class="text-right">' . Inventory::fmt($d['bgn']) . '</td><td class="text-right">' . Inventory::fmt($d['eur']) . '</td></tr>';
};
echo '<div class="p-3"><h5>' . sprintf(__('Movement of the library fund in %d'), $year) . '</h5>';
echo '<table class="table table-sm table-bordered col-lg-9"><thead><tr><th></th><th class="text-right">' . __('Copies') . '</th><th class="text-right">' . __('Value') . ' (лв.)</th><th class="text-right">' . __('Value') . ' (€)</th></tr></thead><tbody>';
echo $line(sprintf(__('At the beginning of %d'), $year), $r['opening'], Ui::h($src[$r['opening']['source']] ?? ''));
echo $line(__('+ Received'), $r['in']);
echo $line(__('− Written off'), $r['out']);
echo '<tr class="font-weight-bold">' . substr($line(sprintf(__('At the end of %d'), $year), $r['closing']), 4);
echo '</tbody></table>';
echo '<p><a class="btn btn-default notAJAX" target="_blank" href="' . MWB . 'reporting/customs/bg_kdbf_part3.php?year=' . $year . '&print=1">' . __('Print KDBF part 3') . '</a> '
    . '<a class="btn btn-default notAJAX" target="_blank" href="' . MWB . 'reporting/customs/bg_kdbf_part1.php?year=' . $year . '&print=1">' . __('Print KDBF part 1') . '</a> '
    . '<a class="btn btn-default notAJAX" target="_blank" href="' . MWB . 'reporting/customs/bg_kdbf_part2.php?year=' . $year . '&print=1">' . __('Print KDBF part 2') . '</a></p>';

if (Ui::canApprove()) {
    $bal = Db::row('SELECT * FROM kdbf_year_balance WHERE balance_year = ?', [$year]);
    $currencies = [];
    foreach ($sysconf['currencies'] as $c) $currencies[is_array($c) ? $c[0] : $c] = is_array($c) ? $c[1] : $c;
    echo '<h5 class="mt-4">' . sprintf(__('Opening balance for %d'), $year) . '</h5>';
    if ($bal && $bal['source'] === 'closing') echo '<div class="alert alert-warning">' . __('This balance comes from closing the previous year. Change it only if the paper KDBF says otherwise.') . '</div>';
    echo '<form method="post" action="' . Ui::h($self) . '" target="submitExec" class="col-lg-9">' . Ui::token('kdbfBalance')
        . '<input type="hidden" name="saveBalance" value="1" /><input type="hidden" name="balanceYear" value="' . $year . '" />'
        . Ui::row(__('Copies at the beginning of the year'), '<input type="number" min="0" step="1" name="openingCount" id="openingCount" value="' . Ui::h($bal['opening_count'] ?? '') . '" class="form-control col-3" />',
            __('From the paper KDBF part 3 (for the first year kept in the system)'))
        . Ui::row(__('Value'), '<div class="form-inline"><input type="text" inputmode="decimal" name="openingValue" id="openingValue" value="' . Ui::h($bal['opening_value'] ?? '') . '" class="form-control col-4 mr-2" data-bgval="price" />'
            . Ui::select('currency', $currencies, $bal['currency'] ?? Inventory::defaultCurrency($year . '-01-01'), 'class="form-control col-3"') . '</div>')
        . Ui::row(__('Notes'), '<input type="text" name="notes" value="' . Ui::h($bal['notes'] ?? '') . '" class="form-control" />')
        . '<div class="text-right"><input type="submit" class="btn btn-primary" value="' . __('Save') . '" /></div></form>';

    if ($year < (int)date('Y')) {
        echo '<form method="post" action="' . Ui::h($self) . '" target="submitExec" class="mt-3" onsubmit="return confirm(' . Ui::h(json_encode(__('Close the year? The end balance will become the opening balance of the next year.'))) . ')">'
            . Ui::token('kdbfClose') . '<input type="hidden" name="closeYear" value="' . $year . '" />'
            . '<input type="submit" class="btn btn-warning" value="' . Ui::h(sprintf(__('Close year %d'), $year)) . '" /></form>';
    }

    // настройки
    $map = Inventory::locationPrefixes();
    $lines = [];
    foreach ($map as $k => $p) $lines[] = $k . '=' . $p;
    $locs = [];
    foreach (Db::all('SELECT location_id, location_name FROM mst_location ORDER BY location_id') as $l) $locs[] = Ui::h($l['location_id'] . ' – ' . $l['location_name']);
    echo '<h5 class="mt-4">' . __('Settings') . '</h5>';
    echo '<form method="post" action="' . Ui::h($self) . '" target="submitExec" class="col-lg-9">' . Ui::token('kdbfSettings') . '<input type="hidden" name="saveSettings" value="1" />'
        . Ui::row(__('Last number of the paper inventory book'), '<input type="number" min="0" step="1" name="inventoryFloor" id="inventoryFloor" value="' . Inventory::floor() . '" class="form-control col-3" />',
            __('Numbers up to it are for entering the old books with their paper numbers; new acquisitions continue after it. 0 = numbering starts from 1.'))
        . Ui::row(__('Department prefixes in the call number'), '<textarea name="locationPrefix" id="locationPrefix" rows="4" class="form-control col-6">' . Ui::h(implode("\n", $lines)) . '</textarea>',
            __('One per line: department code=prefix, e.g. DO=Д. There is no national standard – each library keeps its own table (as in COBISS).') . '<br>' . __('Departments') . ': ' . implode('; ', $locs))
        . '<div class="text-right"><input type="submit" class="btn btn-primary" value="' . __('Save') . '" /></div></form>';
}
echo '</div>';
echo Validator::jsI18nScript();
