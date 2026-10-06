<?php
/**
 * Книга за движение на библиотечния фонд (КДБФ) – части 1, 2 и 3,
 * и актове за отчисляване (Наредба № 3 от 18.11.2014 г., чл. 13–15, 30–39).
 */

namespace SLiMS\Bg;

use RuntimeException;

class Kdbf
{
    /** Начин на постъпване (откъде и как) */
    public static function acqMethods(): array
    {
        return [
            'BUY' => __('Purchase'),
            'DON' => __('Donation'),
            'EXC' => __('Exchange'),
            'TRF' => __('Transfer from another library'),
            'SUB' => __('Subscription'),
            'REP' => __('Replacement of lost books (from readers)'),
            'OTH' => __('Other'),
        ];
    }

    /** Причини за отчисляване (чл. 30). Една причина на акт. */
    public static function writeoffReasons(): array
    {
        return [
            'OBS' => __('Outdated in content'),
            'DUP' => __('Surplus copies'),
            'PRF' => __('Not suitable for the library profile'),
            'WRN' => __('Physically worn out'),
            'RDR' => __('Damaged or not returned by readers'),
            'INV' => __('Missing at stock take'),
            'MED' => __('Unusable media'),
            'DIS' => __('Natural disaster'),
            'THF' => __('Theft (with police report)'),
        ];
    }

    /** Причини, при които подробностите (протокол №/дата) са задължителни. */
    public const REASONS_NEED_DETAILS = ['DIS', 'THF'];

    public static function statusLabels(): array
    {
        return [
            'draft' => __('Draft'),
            'approved' => __('Approved'),
            'cancelled' => __('Cancelled'),
        ];
    }

    /* ------------------------------------------------------------------
     * Разпределения (КДБФ ч. 1 и 2): по вид, по съдържание, по език
     * ------------------------------------------------------------------ */

    public static function kindColumns(): array
    {
        return ['books' => __('Books'), 'periodicals' => __('Periodicals'), 'av' => __('AV and electronic'), 'other' => __('Other')];
    }

    /** Колони „по съдържание“: УДК класове + художествена + детска. */
    public static function contentColumns(): array
    {
        return [
            '0' => '0', '1' => '1', '2' => '2', '3' => '3', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9',
            'fic' => __('Fiction'), 'chi' => __('Children'), 'none' => __('Unclassified'),
        ];
    }

    public static function langColumns(): array
    {
        return ['bg' => __('Bulgarian'), 'other' => __('Other languages')];
    }

    public static function kindOf(?string $gmdCode, ?string $collTypeName, int $frequencyId = 0): string
    {
        if ($frequencyId > 0 || mb_stripos((string)$collTypeName, 'период') !== false) return 'periodicals';
        $code = strtoupper((string)$gmdCode);
        if ($code === '' || $code === 'TE') return 'books';
        if (in_array($code, ['SO', 'VI', 'CD', 'DVD', 'ER', 'CF', 'CO', 'MV', 'MP'], true)) return 'av';
        return 'other';
    }

    public static function contentOf(?string $classification, ?string $collTypeName): string
    {
        $coll = mb_strtolower((string)$collTypeName);
        if (mb_strpos($coll, 'детск') !== false) return 'chi';
        if (mb_strpos($coll, 'художеств') !== false) return 'fic';
        $c = trim((string)$classification);
        if ($c === '' || !preg_match('/^\d/', $c)) return 'none';
        // 82 (нова УДК) и 83–89 (стара УДК за националните литератури, напр. 886.7) – художествена
        if (preg_match('/^(82|8[3-9])/', $c)) return 'fic';
        $d = $c[0];
        if ($d === '4') return '8'; // стар клас 4 (езикознание) – днес в 8
        return $d;
    }

    public static function langOf(?string $languageId): string
    {
        return strtolower((string)$languageId) === 'bg' ? 'bg' : 'other';
    }

    /**
     * Брой и стойност на екземплярите по колоните на КДБФ.
     * @param array $items редове с price, price_currency, gmd_code, coll_type_name, classification, language_id, frequency_id
     */
    public static function distribution(array $items): array
    {
        $res = [
            'count' => 0, 'bgn' => 0.0, 'eur' => 0.0,
            'kind' => array_fill_keys(array_keys(self::kindColumns()), 0),
            'content' => array_fill_keys(array_keys(self::contentColumns()), 0),
            'lang' => array_fill_keys(array_keys(self::langColumns()), 0),
        ];
        foreach ($items as $it) {
            $res['count']++;
            $cur = $it['price_currency'] ?: Inventory::defaultCurrency($it['received_date'] ?? null);
            $res['bgn'] += Inventory::toBgn($it['price'] ?? 0, $cur);
            $res['eur'] += Inventory::toEur($it['price'] ?? 0, $cur);
            $res['kind'][self::kindOf($it['gmd_code'] ?? '', $it['coll_type_name'] ?? '', (int)($it['frequency_id'] ?? 0))]++;
            $res['content'][self::contentOf($it['classification'] ?? '', $it['coll_type_name'] ?? '')]++;
            $res['lang'][self::langOf($it['language_id'] ?? '')]++;
        }
        $res['bgn'] = round($res['bgn'], 2);
        $res['eur'] = round($res['eur'], 2);
        return $res;
    }

    /** Общият SELECT за екземпляри с данните за разпределенията и отчетите. */
    public static function itemSelectSql(string $where): string
    {
        return "SELECT i.item_id, i.item_code, i.price, i.price_currency, i.received_date, i.volume, i.location_id,
                       i.item_status_id, i.kdbf_entry_id, i.writeoff_id, b.biblio_id, b.title, b.publish_year,
                       b.classification, b.language_id, b.frequency_id, g.gmd_code, ct.coll_type_name, l.location_name
                  FROM item i
                  JOIN biblio b ON b.biblio_id = i.biblio_id
                  LEFT JOIN mst_gmd g ON g.gmd_id = b.gmd_id
                  LEFT JOIN mst_coll_type ct ON ct.coll_type_id = i.coll_type_id
                  LEFT JOIN mst_location l ON l.location_id = i.location_id
                 WHERE $where";
    }

    /* ------------------------------------------------------------------
     * Номерация (започва от 1 всяка година)
     * ------------------------------------------------------------------ */

    public static function nextEntryNo(int $year): int
    {
        return (int)Db::value('SELECT COALESCE(MAX(entry_no), 0) + 1 FROM kdbf_entry WHERE entry_year = ?', [$year]);
    }

    public static function nextActNo(int $year): int
    {
        return (int)Db::value('SELECT COALESCE(MAX(act_no), 0) + 1 FROM kdbf_writeoff WHERE act_year = ?', [$year]);
    }

    public static function nextKdbfNo(int $year): int
    {
        return (int)Db::value('SELECT COALESCE(MAX(kdbf_no), 0) + 1 FROM kdbf_writeoff WHERE act_year = ?', [$year]);
    }

    /* ------------------------------------------------------------------
     * Част 1 – постъпления
     * ------------------------------------------------------------------ */

    public static function entry(int $entryId): ?array
    {
        return Db::row('SELECT * FROM kdbf_entry WHERE entry_id = ?', [$entryId]);
    }

    public static function entryLabel(?array $e): string
    {
        if (!$e) return '';
        return $e['entry_no'] . '/' . $e['entry_year'] . ' ' . __('from') . ' ' . date('d.m.Y', strtotime($e['entry_date']));
    }

    /** Колко са инвентирани, на каква стойност, интервали от номера. */
    public static function entrySummary(int $entryId): array
    {
        $entry = self::entry($entryId);
        $items = Db::all(self::itemSelectSql('i.kdbf_entry_id = ?'), [$entryId]);
        $dist = self::distribution($items);
        $numbers = array_map(fn($r) => $r['item_code'], array_filter($items, fn($r) => preg_match('/^\d+$/', (string)$r['item_code'])));
        $expected = $entry ? max(0, (int)$entry['total_count'] - (int)$entry['noninv_count']) : 0;
        return [
            'entry' => $entry,
            'items' => $items,
            'dist' => $dist,
            'ranges' => Inventory::formatRanges($numbers),
            'expected' => $expected,
            'remaining' => max(0, $expected - $dist['count']),
        ];
    }

    /* ------------------------------------------------------------------
     * Част 2 – актове за отчисляване
     * ------------------------------------------------------------------ */

    public static function act(int $id): ?array
    {
        return Db::row('SELECT * FROM kdbf_writeoff WHERE writeoff_id = ?', [$id]);
    }

    public static function actLabel(?array $a): string
    {
        if (!$a) return '';
        return $a['act_no'] . '/' . $a['act_year'] . ' ' . __('from') . ' ' . date('d.m.Y', strtotime($a['act_date']));
    }

    public static function actItems(int $id): array
    {
        return Db::all(self::itemSelectSql('i.writeoff_id = ?') . ' ORDER BY CAST(i.item_code AS UNSIGNED), i.item_code', [$id]);
    }

    public static function isOnLoan(string $itemCode): bool
    {
        return (int)Db::value('SELECT COUNT(*) FROM loan WHERE item_code = ? AND is_lent = 1 AND is_return = 0', [$itemCode]) > 0;
    }

    /**
     * Добавя екземпляри към чернова на акт.
     * @return array{added:int, errors:string[], warnings:string[]}
     */
    public static function addItemsToAct(int $actId, array $codes): array
    {
        $act = self::act($actId);
        $res = ['added' => 0, 'errors' => [], 'warnings' => []];
        if (!$act || $act['status'] !== 'draft') {
            $res['errors'][] = __('Items can be added only to a draft act');
            return $res;
        }
        foreach ($codes as $code) {
            $code = (string)$code;
            $it = Db::row('SELECT item_id, item_code, item_status_id, writeoff_id, received_date FROM item WHERE item_code = ?', [$code]);
            if (!$it) { $res['errors'][] = sprintf(__('%s: no such inventory number'), $code); continue; }
            if ($it['item_status_id'] === 'WO') { $res['errors'][] = sprintf(__('%s: already written off'), $code); continue; }
            if (!empty($it['writeoff_id']) && (int)$it['writeoff_id'] !== $actId) {
                $other = self::act((int)$it['writeoff_id']);
                if ($other && $other['status'] !== 'cancelled') {
                    $res['errors'][] = sprintf(__('%s: already in act %s'), $code, self::actLabel($other));
                    continue;
                }
            }
            if ((int)$it['writeoff_id'] === $actId) continue;
            if (self::isOnLoan($code)) { $res['errors'][] = sprintf(__('%s: is on loan – return it first'), $code); continue; }
            if ($it['received_date'] && $it['received_date'] > $act['act_date']) {
                $res['errors'][] = sprintf(__('%s: entered after the act date'), $code);
                continue;
            }
            if ($act['reason'] === 'INV' && $it['item_status_id'] !== 'MIS') {
                $res['warnings'][] = sprintf(__('%s: is not marked as missing'), $code);
            }
            Db::q('UPDATE item SET writeoff_id = ?, last_update = NOW() WHERE item_id = ?', [$actId, $it['item_id']]);
            $res['added']++;
        }
        Db::q('UPDATE kdbf_writeoff SET last_update = NOW() WHERE writeoff_id = ?', [$actId]);
        return $res;
    }

    public static function removeItemFromAct(int $actId, int $itemId): bool
    {
        $act = self::act($actId);
        if (!$act || $act['status'] !== 'draft') return false;
        Db::q('UPDATE item SET writeoff_id = NULL, last_update = NOW() WHERE item_id = ? AND writeoff_id = ?', [$itemId, $actId]);
        return true;
    }

    /**
     * Утвърждаване: екземплярите стават „Отчислен“, общият брой и стойност се записват,
     * актът получава № на вписване в КДБФ ч. 2. Безопасно е да се пусне повторно.
     */
    public static function approveAct(int $actId, string $approvedBy, string $approvedDate): void
    {
        $act = self::act($actId);
        if (!$act) throw new RuntimeException(__('Act not found'));
        if ($act['status'] === 'cancelled') throw new RuntimeException(__('A cancelled act cannot be approved'));
        $items = self::actItems($actId);
        if (!$items && (int)$act['noninv_count'] === 0) throw new RuntimeException(__('The act has no items'));
        $onLoan = [];
        foreach ($items as $it) {
            if (self::isOnLoan($it['item_code'])) $onLoan[] = $it['item_code'];
        }
        if ($onLoan) throw new RuntimeException(sprintf(__('These items are on loan and must be returned first: %s'), implode(', ', $onLoan)));

        // 1) екземплярите (без транзакции – първо те, после актът)
        Db::q("UPDATE item SET item_status_id = 'WO', last_update = NOW() WHERE writeoff_id = ?", [$actId]);
        // 2) актът
        $dist = self::distribution($items);
        $kdbfNo = $act['kdbf_no'] ?: self::nextKdbfNo((int)$act['act_year']);
        Db::q("UPDATE kdbf_writeoff SET status = 'approved', approved_by = ?, approved_date = ?, kdbf_no = ?,
                      kdbf_date = COALESCE(kdbf_date, ?), total_count = ?, total_value_bgn = ?, total_value_eur = ?, last_update = NOW()
                WHERE writeoff_id = ?",
            [$approvedBy, $approvedDate, $kdbfNo, $approvedDate, $dist['count'], $dist['bgn'], $dist['eur'], $actId]);
    }

    /** Анулиране на чернова: екземплярите се освобождават. */
    public static function cancelDraft(int $actId): void
    {
        $act = self::act($actId);
        if (!$act || $act['status'] !== 'draft') throw new RuntimeException(__('Only a draft act can be cancelled'));
        Db::q('UPDATE item SET writeoff_id = NULL, last_update = NOW() WHERE writeoff_id = ?', [$actId]);
        Db::q("UPDATE kdbf_writeoff SET status = 'cancelled', last_update = NOW() WHERE writeoff_id = ?", [$actId]);
    }

    /** Чернова на акт за липсващите при инвентаризация екземпляри. Връща id на акта или null. */
    public static function draftActFromStockTake(int $stockTakeId, int $uid): ?int
    {
        $codes = array_column(Db::all("SELECT item_code FROM item WHERE item_status_id = 'MIS' AND (writeoff_id IS NULL OR writeoff_id IN (SELECT writeoff_id FROM kdbf_writeoff WHERE status = 'cancelled'))"), 'item_code');
        if (!$codes) return null;
        $year = (int)date('Y');
        Db::q("INSERT INTO kdbf_writeoff (act_year, act_no, act_date, reason, status, stock_take_id, notes, input_date, last_update, uid)
               VALUES (?, ?, CURDATE(), 'INV', 'draft', ?, ?, NOW(), NOW(), ?)",
            [$year, self::nextActNo($year), $stockTakeId, __('Created automatically at the end of a stock take'), $uid]);
        $id = (int)Db::lastInsertId();
        self::addItemsToAct($id, $codes);
        return $id;
    }

    /* ------------------------------------------------------------------
     * Част 3 – резултати от движението
     * ------------------------------------------------------------------ */

    public static function firstYear(): ?int
    {
        $y = Db::value('SELECT MIN(y) FROM (SELECT MIN(balance_year) y FROM kdbf_year_balance UNION ALL SELECT MIN(entry_year) FROM kdbf_entry UNION ALL SELECT MIN(act_year) FROM kdbf_writeoff WHERE status = \'approved\') t');
        return $y ? (int)$y : null;
    }

    /** Постъпили през годината (инвентирани + неинвентирани). */
    public static function receivedInYear(int $year): array
    {
        $items = Db::all(self::itemSelectSql('i.kdbf_entry_id IN (SELECT entry_id FROM kdbf_entry WHERE entry_year = ?)'), [$year]);
        $dist = self::distribution($items);
        foreach (Db::all('SELECT noninv_count, noninv_value, currency FROM kdbf_entry WHERE entry_year = ?', [$year]) as $e) {
            $dist['count'] += (int)$e['noninv_count'];
            $dist['bgn'] += Inventory::toBgn($e['noninv_value'] ?? 0, $e['currency']);
            $dist['eur'] += Inventory::toEur($e['noninv_value'] ?? 0, $e['currency']);
        }
        return $dist;
    }

    /** Отчислени през годината (утвърдени актове). */
    public static function writtenOffInYear(int $year): array
    {
        $items = Db::all(self::itemSelectSql("i.writeoff_id IN (SELECT writeoff_id FROM kdbf_writeoff WHERE act_year = ? AND status = 'approved')"), [$year]);
        $dist = self::distribution($items);
        foreach (Db::all("SELECT noninv_count, noninv_value FROM kdbf_writeoff WHERE act_year = ? AND status = 'approved'", [$year]) as $a) {
            $dist['count'] += (int)$a['noninv_count'];
            $dist['bgn'] += (float)$a['noninv_value'];
            $dist['eur'] += Inventory::toEur($a['noninv_value'] ?? 0, 'BGN');
        }
        return $dist;
    }

    /**
     * Наличност в началото, постъпили, отчислени, наличност в края за годината.
     * Началото е от kdbf_year_balance или от края на предходната година.
     */
    public static function yearResults(int $year, int $depth = 0): array
    {
        $bal = Db::row('SELECT * FROM kdbf_year_balance WHERE balance_year = ?', [$year]);
        if ($bal) {
            $opening = [
                'count' => (int)$bal['opening_count'],
                'bgn' => Inventory::toBgn($bal['opening_value'], $bal['currency']),
                'eur' => Inventory::toEur($bal['opening_value'], $bal['currency']),
                'source' => $bal['source'],
            ];
        } else {
            $first = self::firstYear();
            if ($first === null || $year <= $first || $depth > 100) {
                $opening = ['count' => 0, 'bgn' => 0.0, 'eur' => 0.0, 'source' => 'none'];
            } else {
                $prev = self::yearResults($year - 1, $depth + 1);
                $opening = $prev['closing'] + ['source' => 'computed'];
            }
        }
        $in = self::receivedInYear($year);
        $out = self::writtenOffInYear($year);
        $closing = [
            'count' => $opening['count'] + $in['count'] - $out['count'],
            'bgn' => round($opening['bgn'] + $in['bgn'] - $out['bgn'], 2),
            'eur' => round($opening['eur'] + $in['eur'] - $out['eur'], 2),
        ];
        return ['year' => $year, 'opening' => $opening, 'in' => $in, 'out' => $out, 'closing' => $closing];
    }

    /** Приключване на година: краят ѝ се записва като начало на следващата. */
    public static function closeYear(int $year, int $uid): array
    {
        $r = self::yearResults($year);
        Db::q("INSERT INTO kdbf_year_balance (balance_year, opening_count, opening_value, currency, source, notes, last_update, uid)
               VALUES (?, ?, ?, 'EUR', 'closing', ?, NOW(), ?)
               ON DUPLICATE KEY UPDATE opening_count = VALUES(opening_count), opening_value = VALUES(opening_value),
                                       currency = 'EUR', source = 'closing', notes = VALUES(notes), last_update = NOW(), uid = VALUES(uid)",
            [$year + 1, $r['closing']['count'], $r['closing']['eur'], sprintf(__('Closing balance of %d'), $year), $uid]);
        return $r;
    }
}
