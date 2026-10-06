<?php
/**
 * Общи функции за справките по Наредба № 3 (инвентарна книга, КДБФ, акт за отчисляване):
 * филтър + iframe в админ панела, печатна страница A4 хоризонтално, xlsx и CSV.
 */

if (!defined('INDEX_AUTH')) {
    die('can not access this file directly');
}

use SLiMS\Bg\Ui;

/** Справките се виждат от „Отчети“ и от „Библиография“ (КДБФ екраните). */
function bg_report_access(): void
{
    if (!utility::havePrivilege('reporting', 'r') && !utility::havePrivilege('bibliography', 'r')) {
        die('<div class="errorBox">' . __('You don\'t have enough privileges to access this area!') . '</div>');
    }
}

function bg_report_is_view(): bool
{
    return isset($_GET['reportView']) || isset($_GET['print']) || isset($_GET['format']);
}

/**
 * Екранът в админ панела: заглавие, форма за филтриране и iframe с резултата.
 */
function bg_report_filter_page(string $title, string $filterFields, string $note = ''): void
{
    $self = $_SERVER['PHP_SELF'];
    echo '<div class="menuBox"><div class="menuBoxInner"><div class="per_title"><h2>' . Ui::h($title) . '</h2></div>'
        . '<div class="infoBox">' . __('Report Filter') . ($note !== '' ? ' – ' . $note : '') . '</div>'
        . '<div class="sub_section"><form method="get" action="' . Ui::h($self) . '" target="reportView" class="form-inline notAJAX">'
        . $filterFields
        . '<input type="hidden" name="reportView" value="true" />'
        . '<input type="submit" name="applyFilter" value="' . __('Apply Filter') . '" class="btn btn-primary ml-2" /></form></div></div></div>'
        . '<iframe name="reportView" id="reportView" src="' . Ui::h($self) . '?reportView=true" frameborder="0" style="width: 100%; height: 600px;"></iframe>';
}

/**
 * Печатната страница: таблица с повтаряща се заглавна част, A4 хоризонтално (или вертикално).
 * @param string[] $header
 * @param array<int,array> $rows  стойностите се escape-ват тук; клетка ['html' => '...'] се показва както е
 */
function bg_report_output(string $title, string $subtitle, array $header, array $rows, string $footerHtml = '', bool $landscape = true, string $csvName = 'report'): void
{
    global $sysconf, $dbs;
    // CSV (UTF-8 с BOM и „;“ – отваря се директно в Excel)
    if (($_GET['format'] ?? '') === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-z0-9_-]+/i', '_', $csvName) . '_' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $header, ';');
        foreach ($rows as $r) {
            fputcsv($out, array_map(fn($c) => is_array($c) ? trim(strip_tags(str_replace(['<br>', '<br/>'], "\n", $c['csv'] ?? $c['html']))) : (string)$c, $r), ';');
        }
        fclose($out);
        exit();
    }

    // xlsx – през стандартния изход на SLiMS
    $xls = [$header];
    foreach ($rows as $r) {
        $xls[] = array_map(fn($c) => is_array($c) ? trim(strip_tags(str_replace(['<br>', '<br/>'], "\n", $c['csv'] ?? $c['html']))) : $c, $r);
    }
    unset($_SESSION['xlsquery']);
    $_SESSION['xlsdata'] = $xls;
    $_SESSION['tblout'] = $csvName;

    $qs = $_GET;
    unset($qs['format']);
    $csvUrl = $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge($qs, ['format' => 'csv']));

    ob_start();
    echo '<div class="doNotPrint mb-2">'
        . '<a href="#" class="s-btn btn btn-default" onclick="window.print(); return false;">' . __('Print Current Page') . '</a> '
        . '<a href="../xlsoutput.php" class="s-btn btn btn-default">' . __('Export to spreadsheet format') . '</a> '
        . '<a href="' . Ui::h($csvUrl) . '" class="s-btn btn btn-default">CSV</a></div>';
    echo '<div class="bg-report-head"><div class="bg-lib">' . Ui::h($sysconf['library_name'] ?? '') . ' · ' . Ui::h($sysconf['library_subname'] ?? '') . '</div>'
        . '<h3>' . Ui::h($title) . '</h3>' . ($subtitle !== '' ? '<div class="bg-sub">' . $subtitle . '</div>' : '') . '</div>';
    echo '<table class="bg-report"><thead><tr>';
    foreach ($header as $hcell) echo '<th>' . Ui::h($hcell) . '</th>';
    echo '</tr></thead><tbody>';
    if (!$rows) {
        echo '<tr><td colspan="' . count($header) . '" class="text-center">' . __('No data') . '</td></tr>';
    }
    foreach ($rows as $r) {
        echo '<tr>';
        foreach ($r as $c) {
            if (is_array($c)) {
                echo '<td' . (isset($c['class']) ? ' class="' . Ui::h($c['class']) . '"' : '') . '>' . $c['html'] . '</td>';
            } else {
                echo '<td>' . Ui::h($c) . '</td>';
            }
        }
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo $footerHtml;
    echo '<div class="bg-printed">' . sprintf(__('Printed on %s'), date('d.m.Y H:i')) . '</div>';
    $content = ob_get_clean();

    $page_title = $title;
    $css = '<style type="text/css">
        @page { size: A4 ' . ($landscape ? 'landscape' : 'portrait') . '; margin: 10mm; }
        body { font-family: "DejaVu Sans", Arial, sans-serif; }
        .bg-report-head h3 { font-size: 14pt; margin: 2mm 0; }
        .bg-lib { font-size: 9pt; color: #555; }
        .bg-sub { font-size: 9pt; margin-bottom: 2mm; }
        table.bg-report { width: 100%; border-collapse: collapse; font-size: 8pt; }
        table.bg-report th, table.bg-report td { border: 1px solid #444; padding: 1mm 1.2mm; vertical-align: top; }
        table.bg-report th { background: #eee; font-weight: bold; text-align: center; }
        table.bg-report thead { display: table-header-group; }
        table.bg-report tr { page-break-inside: avoid; }
        table.bg-report td.num { text-align: right; white-space: nowrap; }
        table.bg-report tr.total td { font-weight: bold; background: #f6f6f6; }
        .bg-printed { font-size: 7pt; color: #777; margin-top: 2mm; }
        .bg-sign { margin-top: 8mm; font-size: 10pt; }
        .bg-sign div { margin-top: 7mm; }
        .bg-draft { position: fixed; top: 40%; left: 20%; font-size: 72pt; color: rgba(200,0,0,.12); transform: rotate(-25deg); z-index: -1; }
        @media print { .doNotPrint { display: none !important; } }
    </style>';
    require SB . '/admin/' . $sysconf['admin_template']['dir'] . '/printed_page_tpl.php';
    exit();
}

function bg_num($v): array
{
    return ['html' => \SLiMS\Bg\Inventory::fmt($v), 'class' => 'num', 'csv' => number_format((float)$v, 2, ',', '')];
}

function bg_int($v): array
{
    return ['html' => (string)(int)$v, 'class' => 'num', 'csv' => (string)(int)$v];
}
