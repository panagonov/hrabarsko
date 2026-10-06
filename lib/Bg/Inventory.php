<?php
/**
 * Инвентарни номера, валути и сигнатури.
 *
 * Инвентарният номер е самият баркод (item.item_code): цяло число, започва от 1,
 * не се използва повторно (Наредба № 3 от 18.11.2014 г., чл. 16–17 и 28).
 */

namespace SLiMS\Bg;

class Inventory
{
    /** Фиксиран курс на лева към еврото */
    public const EUR_RATE = 1.95583;
    public const CURRENCIES = ['EUR', 'BGN'];
    /** От тази дата цените по подразбиране са в евро */
    public const EURO_DATE = '2026-01-01';
    /** Максимален брой номера в един интервал (печат, акт, пакетно въвеждане) */
    public const MAX_RANGE = 1000;

    /* ------------------------------------------------------------------
     * Валути
     * ------------------------------------------------------------------ */

    public static function defaultCurrency(?string $date = null): string
    {
        $date = $date ?: date('Y-m-d');
        return $date >= self::EURO_DATE ? 'EUR' : 'BGN';
    }

    public static function toEur($amount, ?string $currency): float
    {
        $amount = (float)$amount;
        return strtoupper((string)$currency) === 'EUR' ? $amount : $amount / self::EUR_RATE;
    }

    public static function toBgn($amount, ?string $currency): float
    {
        $amount = (float)$amount;
        return strtoupper((string)$currency) === 'EUR' ? $amount * self::EUR_RATE : $amount;
    }

    public static function currencyLabel(?string $currency): string
    {
        return strtoupper((string)$currency) === 'EUR' ? '€' : 'лв.';
    }

    /** 1234.5 → "1 234,50" */
    public static function fmt($value): string
    {
        return number_format((float)$value, 2, ',', ' ');
    }

    /* ------------------------------------------------------------------
     * Инвентарни номера
     * ------------------------------------------------------------------ */

    /** Последният номер от хартиената инвентарна книга (за ретроспективно въвеждане). */
    public static function floor(): int
    {
        return (int)config('bg_inventory_floor', 0);
    }

    public static function maxUsed(): int
    {
        return (int)Db::value("SELECT MAX(CAST(item_code AS UNSIGNED)) FROM item WHERE item_code REGEXP '^[1-9][0-9]*$'");
    }

    /** Следващият свободен инвентарен номер. */
    public static function nextNo(): int
    {
        return max(self::maxUsed(), self::floor()) + 1;
    }

    /** Кои от дадените номера вече са заети. */
    public static function takenNumbers(array $numbers): array
    {
        $numbers = array_values(array_unique(array_map('strval', $numbers)));
        if (!$numbers) return [];
        $taken = [];
        foreach (array_chunk($numbers, 500) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            foreach (Db::all("SELECT item_code FROM item WHERE item_code IN ($in)", $chunk) as $r) {
                $taken[] = (int)$r['item_code'];
            }
        }
        sort($taken);
        return $taken;
    }

    /**
     * "101-130, 135; 140" → [101..130, 135, 140]
     * @return int[]|null null при грешен запис
     */
    public static function parseRanges(string $text, ?string &$error = null): ?array
    {
        $text = trim(str_replace(['–', '—'], '-', $text));
        if ($text === '') { $error = __('Enter inventory numbers'); return null; }
        $out = [];
        foreach (preg_split('/[\s,;]+/u', $text) as $tok) {
            if ($tok === '') continue;
            if (preg_match('/^(\d{1,9})-(\d{1,9})$/', $tok, $m)) {
                $a = (int)$m[1]; $b = (int)$m[2];
                if ($a < 1 || $b < $a) { $error = sprintf(__('Wrong range "%s"'), $tok); return null; }
                if ($b - $a + 1 > self::MAX_RANGE) { $error = sprintf(__('Range "%s" is too large (max %d numbers)'), $tok, self::MAX_RANGE); return null; }
                for ($i = $a; $i <= $b; $i++) $out[$i] = $i;
            } elseif (preg_match('/^\d{1,9}$/', $tok) && (int)$tok > 0) {
                $out[(int)$tok] = (int)$tok;
            } else {
                $error = sprintf(__('"%s" is not a number or a range'), $tok);
                return null;
            }
            if (count($out) > self::MAX_RANGE) { $error = sprintf(__('Too many numbers (max %d)'), self::MAX_RANGE); return null; }
        }
        ksort($out);
        return array_values($out);
    }

    /** [101,102,103,135] → "101–103, 135" */
    public static function formatRanges(array $numbers): string
    {
        $numbers = array_values(array_unique(array_map('intval', $numbers)));
        sort($numbers);
        $parts = [];
        $start = $prev = null;
        foreach ($numbers as $n) {
            if ($start === null) { $start = $prev = $n; continue; }
            if ($n === $prev + 1) { $prev = $n; continue; }
            $parts[] = $start === $prev ? (string)$start : $start . '–' . $prev;
            $start = $prev = $n;
        }
        if ($start !== null) $parts[] = $start === $prev ? (string)$start : $start . '–' . $prev;
        return implode(', ', $parts);
    }

    /** Незаети номера в интервала [from, to]. */
    public static function gaps(int $from, int $to): array
    {
        if ($to < $from || $to - $from > 200000) return [];
        $used = [];
        foreach (Db::all("SELECT CAST(item_code AS UNSIGNED) n FROM item WHERE item_code REGEXP '^[1-9][0-9]*$' AND CAST(item_code AS UNSIGNED) BETWEEN ? AND ?", [$from, $to]) as $r) {
            $used[(int)$r['n']] = true;
        }
        $gaps = [];
        for ($i = $from; $i <= $to; $i++) {
            if (!isset($used[$i])) $gaps[] = $i;
        }
        return $gaps;
    }

    /**
     * Може ли екземплярът да бъде изтрит. Номерата не се изтриват (чл. 16–17, 28) –
     * разрешено е само за запис, въведен по грешка днес, без история.
     * @return string|null причината за отказ или null, ако може
     */
    public static function canDelete(int $itemId): ?string
    {
        $item = Db::row('SELECT item_code, input_date, writeoff_id FROM item WHERE item_id = ?', [$itemId]);
        if (!$item) return null;
        $label = $item['item_code'];
        $policy = __('Inventory numbers are not deleted (Ordinance No 3, art. 16–17 and 28). To remove a book from the fund, use a write-off act; to fix a mistake, correct the record and describe the correction in "Notes".');
        if (substr((string)$item['input_date'], 0, 10) !== date('Y-m-d')) {
            return sprintf(__('Item %s was not entered today.'), $label) . ' ' . $policy;
        }
        if ((int)Db::value('SELECT COUNT(*) FROM loan WHERE item_code = ?', [$item['item_code']]) > 0) {
            return sprintf(__('Item %s has loan history.'), $label) . ' ' . $policy;
        }
        if (!empty($item['writeoff_id'])) {
            return sprintf(__('Item %s is included in a write-off act.'), $label) . ' ' . $policy;
        }
        if (Db::tableExists('item_check_log') && (int)Db::value('SELECT COUNT(*) FROM item_check_log WHERE item_id = ?', [$itemId]) > 0) {
            return sprintf(__('Item %s has stock-take records.'), $label) . ' ' . $policy;
        }
        return null;
    }

    /** Може ли номерът (item_code) да се промени – само в деня на въвеждане. */
    public static function canChangeCode(?string $inputDate): bool
    {
        return substr((string)$inputDate, 0, 10) === date('Y-m-d');
    }

    /**
     * Автоматична бележка при промяна на защитени полета на стар запис
     * („поправките се отразяват в графа Забележки“).
     */
    public static function protectedChangeNote(array $old, array $new, string $user): ?string
    {
        if (substr((string)($old['input_date'] ?? ''), 0, 10) === date('Y-m-d')) return null;
        $labels = [
            'price' => __('price'),
            'price_currency' => __('currency'),
            'received_date' => __('entry date'),
            'kdbf_entry_id' => __('KDBF entry'),
            'kdbf_legacy_ref' => __('paper KDBF ref.'),
            'volume' => __('volume'),
            'location_id' => __('department'),
        ];
        $changes = [];
        foreach ($labels as $key => $label) {
            if (!array_key_exists($key, $new)) continue;
            $o = (string)($old[$key] ?? '');
            $n = (string)($new[$key] ?? '');
            if ($n === 'literal{NULL}') $n = '';
            if ($key === 'price') {
                if ($o !== '' && $n !== '' && (float)$o == (float)$n) continue;
            }
            if ($o !== $n) $changes[] = $label . ': ' . ($o === '' ? '—' : $o) . ' → ' . ($n === '' ? '—' : $n);
        }
        if (!$changes) return null;
        return date('d.m.Y') . ' (' . $user . '): ' . implode('; ', $changes);
    }

    /* ------------------------------------------------------------------
     * Сигнатура: [префикс на отдела] УДК авторски знак
     * (локална таблица на префиксите, както в COBISS – COMARC/H 99X d)
     * ------------------------------------------------------------------ */

    /** @return array<string,string> location_id => префикс */
    public static function locationPrefixes(): array
    {
        $raw = (string)config('bg_location_prefix', 'DO=Д;CH=Ч;KR=Кр');
        return self::parsePrefixMap($raw);
    }

    public static function parsePrefixMap(string $raw): array
    {
        $map = [];
        foreach (preg_split('/[;\n]+/u', $raw) as $pair) {
            if (strpos($pair, '=') === false) continue;
            [$k, $v] = array_map('trim', explode('=', $pair, 2));
            if ($k !== '' && $v !== '') $map[strtoupper($k)] = $v;
        }
        return $map;
    }

    public static function locationPrefix(?string $locationId): string
    {
        $map = self::locationPrefixes();
        return $map[strtoupper((string)$locationId)] ?? '';
    }

    /** Сигнатура на екземпляра: префиксът на отдела + сигнатурата на заглавието. */
    public static function composeCallNumber(?string $locationId, ?string $biblioCallNumber): string
    {
        $cn = trim((string)$biblioCallNumber);
        $prefix = self::locationPrefix($locationId);
        if ($prefix === '' || $cn === '') return $cn;
        if (mb_strpos($cn, $prefix . ' ') === 0) return $cn;
        return $prefix . ' ' . $cn;
    }

    /**
     * Части на сигнатурата за етикети и картички (по образеца от наръчника и COBISS):
     * ред 1 – УДК за подреждане; ред 2 – [префикс] авторски знак; ред 3 – том.
     */
    public static function signatureParts(?string $locationId, ?string $callNumber, ?string $authorMark, ?string $volume = null): array
    {
        $prefix = self::locationPrefix($locationId);
        $tokens = preg_split('/\s+/u', trim((string)$callNumber), -1, PREG_SPLIT_NO_EMPTY);
        // махаме префикса на отдела, ако вече е в сигнатурата на екземпляра
        if ($prefix !== '' && $tokens && $tokens[0] === $prefix) array_shift($tokens);
        $udc = $tokens ? array_shift($tokens) : '';
        $mark = trim((string)$authorMark);
        $rest = $tokens;
        if ($mark !== '') {
            $rest = array_values(array_filter($rest, fn($t) => $t !== $mark));
        } elseif ($rest) {
            $mark = array_pop($rest);
        }
        $line2 = trim(implode(' ', array_filter(array_merge([$prefix], $rest, [$mark]), fn($t) => $t !== '')));
        $vol = trim((string)$volume);
        if ($vol !== '' && preg_match('/^\d+$/', $vol)) $vol = 'Т. ' . $vol;
        return ['udc' => $udc, 'line2' => $line2, 'prefix' => $prefix, 'mark' => $mark, 'volume' => $vol];
    }
}
