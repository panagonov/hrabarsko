<?php
/**
 * Малки JSON заявки за формулярите (само четене): следващ инвентарен №,
 * следващ № на запис в КДБФ / акт, възможен дубликат по ISBN.
 */

use SLiMS\Bg\Db;
use SLiMS\Bg\Inventory;
use SLiMS\Bg\Kdbf;
use SLiMS\Bg\Validator;

define('INDEX_AUTH', '1');
define('DB_ACCESS', 'fa');

require '../../../sysconfig.inc.php';
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';

header('Content-Type: application/json; charset=utf-8');
if (!utility::havePrivilege('bibliography', 'r')) {
    http_response_code(403);
    exit(json_encode(['error' => 'forbidden']));
}

$q = (string)($_GET['q'] ?? '');
$year = (int)($_GET['year'] ?? date('Y'));
if ($year < 1900 || $year > 2100) $year = (int)date('Y');

switch ($q) {
    case 'nextInv':
        echo json_encode(['value' => Inventory::nextNo()]);
        break;
    case 'nextEntryNo':
        echo json_encode(['value' => Kdbf::nextEntryNo($year)]);
        break;
    case 'nextActNo':
        echo json_encode(['value' => Kdbf::nextActNo($year)]);
        break;
    case 'isbnDup':
        $digits = Validator::isbnDigits((string)($_GET['digits'] ?? ''));
        $biblioId = (int)($_GET['biblioID'] ?? 0);
        $found = null;
        if (strlen($digits) >= 8) {
            $found = Db::row("SELECT biblio_id, title FROM biblio WHERE biblio_id <> ? AND REPLACE(REPLACE(UPPER(isbn_issn), '-', ''), ' ', '') LIKE ? LIMIT 1", [$biblioId, '%' . $digits . '%']);
        }
        echo json_encode(['duplicate' => $found], JSON_UNESCAPED_UNICODE);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'unknown query']);
}
