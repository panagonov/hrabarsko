<?php
/**
 * Откъде се вземат екземплярите за етикетите: интервали от инвентарни номера,
 * запис в КДБФ или съществуващите опашки за печат на SLiMS.
 */

namespace SLiMS\Bg;

class LabelData
{
    private const SELECT = "SELECT i.item_id, i.item_code, i.call_number, i.volume, i.location_id, b.biblio_id, b.title,
                                   b.call_number AS biblio_call_number, b.author_mark, b.classification
                              FROM item i JOIN biblio b ON b.biblio_id = i.biblio_id";

    /** @return array{items: array, missing: int[]} */
    public static function fromRanges(string $text, ?string &$error = null): array
    {
        $numbers = Inventory::parseRanges($text, $error);
        if ($numbers === null) return ['items' => [], 'missing' => []];
        $items = [];
        foreach (array_chunk($numbers, 500) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            foreach (Db::all(self::SELECT . " WHERE i.item_code IN ($in)", array_map('strval', $chunk)) as $r) $items[(int)$r['item_code']] = $r;
        }
        $ordered = [];
        $missing = [];
        foreach ($numbers as $n) {
            if (isset($items[$n])) $ordered[] = $items[$n]; else $missing[] = $n;
        }
        return ['items' => $ordered, 'missing' => $missing];
    }

    public static function fromKdbf(int $entryId): array
    {
        if (!Db::tableExists('kdbf_entry')) return [];
        return Db::all(self::SELECT . " WHERE i.kdbf_entry_id = ? ORDER BY CAST(i.item_code AS UNSIGNED), i.item_code", [$entryId]);
    }

    /** id на заглавия от адреса („1,2,3“) – само цели числа, най-много 500. */
    public static function parseIds($raw): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', preg_split('/[^0-9]+/', (string)$raw)))));
        return array_slice($ids, 0, 500);
    }

    /** Всички екземпляри (без отчислените) на избраните заглавия. */
    public static function fromBiblioIds(array $ids): array
    {
        if (!$ids) return [];
        $in = implode(',', array_fill(0, count($ids), '?'));
        return Db::all(self::SELECT . " WHERE i.biblio_id IN ($in) AND (i.item_status_id IS NULL OR i.item_status_id <> 'WO')
                        ORDER BY (i.item_code REGEXP '^[0-9]+$') DESC, CAST(i.item_code AS UNSIGNED), i.item_code", $ids);
    }

    /** Опашките от „Печат на баркодове“ (инв. №) и „Печат на етикети“ (id на екземпляр/заглавие). */
    public static function fromQueue(): array
    {
        $rows = [];
        $codes = array_values($_SESSION['barcodes'] ?? []);
        if ($codes) {
            $in = implode(',', array_fill(0, count($codes), '?'));
            foreach (Db::all(self::SELECT . " WHERE i.item_code IN ($in)", array_map('strval', $codes)) as $r) $rows[$r['item_id']] = $r;
        }
        $itemIds = array_map('intval', array_values($_SESSION['labels']['item'] ?? []));
        if ($itemIds) {
            $in = implode(',', array_fill(0, count($itemIds), '?'));
            foreach (Db::all(self::SELECT . " WHERE i.item_id IN ($in)", $itemIds) as $r) $rows[$r['item_id']] = $r;
        }
        $biblioIds = array_map('intval', array_values($_SESSION['labels']['biblio'] ?? []));
        if ($biblioIds) {
            $in = implode(',', array_fill(0, count($biblioIds), '?'));
            foreach (Db::all(self::SELECT . " WHERE i.biblio_id IN ($in)", $biblioIds) as $r) $rows[$r['item_id']] = $r;
        }
        usort($rows, fn($a, $b) => [ctype_digit((string)$a['item_code']) ? (int)$a['item_code'] : PHP_INT_MAX, $a['item_code']] <=> [ctype_digit((string)$b['item_code']) ? (int)$b['item_code'] : PHP_INT_MAX, $b['item_code']]);
        return array_values($rows);
    }

    public static function queueCount(): int
    {
        return count($_SESSION['barcodes'] ?? []) + count($_SESSION['labels']['item'] ?? []) + count($_SESSION['labels']['biblio'] ?? []);
    }

    /** Части на сигнатурата за етикета (по образеца от наръчника и COMARC/H 99X d). */
    public static function signature(array $r): array
    {
        $cn = $r['call_number'] ?: $r['biblio_call_number'];
        return Inventory::signatureParts($r['location_id'], $cn, $r['author_mark'], $r['volume']);
    }
}
