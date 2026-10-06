<?php
/**
 * Превръща заявката от OPAC в BgCriteria:
 *  1) редовете от разширеното търсене (f1/t1, op2/f2/t2, op3/f3/t3) + ограничения (years, lang, gmd);
 *  2) старите адреси на SLiMS (?title=…, ?author="…", ?subject=…, ?isbn=…, ?publishyear=…);
 *  3) простото поле: всички думи в „Ключови думи“, или команди като в COBISS
 *     (AU=Петрунова AND PY=2019, TI="под игото", UC=886.7*, PY=2010:2020).
 */

namespace SLiMS\SearchEngine;

use SLiMS\DB;

class BgQueryParser
{
    /** Кодовете на полетата (като в COBISS+) и колоните им в search_biblio */
    public const FIELDS = [
        'KW' => 'n_all', 'TI' => 'n_title', 'AU' => 'n_author', 'SU' => 'n_subject', 'PU' => 'n_publisher',
        'PP' => 'n_place', 'SE' => 'n_series', 'OT' => 'n_origtitle', 'NT' => 'n_notes',
        'UC' => 'n_class', 'BN' => 'n_isbn', 'PY' => 'year_int', 'LA' => 'language_id', 'CN' => 'call_number',
    ];
    private const ALIASES = ['CL' => 'SE', 'DC' => 'UC', 'SN' => 'BN', 'SP' => 'BN'];
    private const MAX_TOKENS = 20;

    public static function labels(): array
    {
        return [
            'KW' => __('Keywords (all fields)'), 'AU' => __('Author'), 'TI' => __('Title'), 'SU' => __('Subject heading'),
            'PU' => __('Publisher'), 'PP' => __('Place of publication'), 'PY' => __('Year of publication'), 'BN' => 'ISBN',
            'UC' => __('UDC'), 'SE' => __('Series'), 'LA' => __('Language'), 'OT' => __('Original title'),
            'NT' => __('Notes'), 'CN' => __('Call Number'),
        ];
    }

    public static function opLabels(): array
    {
        return ['and' => __('AND'), 'or' => __('OR'), 'not' => __('AND NOT')];
    }

    public static function fromRequest(array $get): BgCriteria
    {
        $c = new BgCriteria();
        $usedRows = false;
        for ($i = 1; $i <= 3; $i++) {
            $t = self::clean($get['t' . $i] ?? '');
            if ($t === '') continue;
            $f = self::field((string)($get['f' . $i] ?? 'KW'));
            $op = strtolower((string)($get['op' . $i] ?? 'and'));
            if (!in_array($op, ['and', 'or', 'not'], true)) $op = 'and';
            $node = self::node($f, $t);
            if ($node) { $c->addRow($op, $node); $usedRows = true; }
        }
        if (!$usedRows) {
            // старите адреси на SLiMS (връзки от резултатите, детайлите и старото разширено търсене)
            $legacy = ['title' => 'TI', 'author' => 'AU', 'subject' => 'SU', 'isbn' => 'BN', 'publisher' => 'PU',
                'publishyear' => 'PY', 'class' => 'UC', 'notes' => 'NT', 'callnumber' => 'CN'];
            foreach ($legacy as $param => $f) {
                $t = is_string($get[$param] ?? null) ? self::clean($get[$param]) : '';
                if ($t !== '') {
                    $node = self::node($f, $t);
                    if ($node) { $c->addRow('and', $node); $usedRows = true; }
                }
            }
            foreach (['gmd' => 'gmdName', 'location' => 'locationName', 'colltype' => 'colltypeName'] as $param => $type) {
                $t = is_string($get[$param] ?? null) ? self::clean($get[$param]) : '';
                if ($t !== '' && !ctype_digit($t) && $t !== '0') {
                    $c->addRow('and', ['type' => $type, 'value' => $t]);
                    $usedRows = true;
                }
            }
        }
        if (!$usedRows) {
            $kw = self::clean($get['keywords'] ?? '');
            if ($kw !== '') {
                $c->keywords = $kw;
                if (self::looksLikeCommand($kw)) {
                    self::parseCommand($kw, $c);
                } else {
                    $node = self::node('KW', $kw);
                    if ($node) $c->addRow('and', $node);
                }
            }
        }
        self::limits($get, $c);
        self::finish($c);
        return $c;
    }

    private static function clean($s): string
    {
        $s = trim(strip_tags((string)$s));
        $s = preg_replace('/\s+/u', ' ', $s);
        return mb_substr($s, 0, 200);
    }

    private static function field(string $f): string
    {
        $f = strtoupper(self::latin($f));
        $f = self::ALIASES[$f] ?? $f;
        return isset(self::FIELDS[$f]) ? $f : 'KW';
    }

    /** Кирилски букви, които изглеждат като латински (АU= → AU=). */
    private static function latin(string $s): string
    {
        return strtr($s, ['А' => 'A', 'В' => 'B', 'С' => 'C', 'Е' => 'E', 'Н' => 'H', 'К' => 'K', 'М' => 'M', 'О' => 'O', 'Р' => 'P', 'Т' => 'T', 'У' => 'Y', 'Х' => 'X',
            'а' => 'a', 'с' => 'c', 'е' => 'e', 'о' => 'o', 'р' => 'p', 'х' => 'x']);
    }

    /** Едно условие: поле + текст. */
    public static function node(string $f, string $t): ?array
    {
        $raw = $t;
        switch ($f) {
            case 'PY':
                $range = self::yearRange($t);
                return $range ? ['type' => 'year', 'from' => $range[0], 'to' => $range[1], 'raw' => $raw] : null;
            case 'BN':
                $d = strtoupper(preg_replace('/[^0-9Xx]/', '', $t));
                return $d !== '' ? ['type' => 'isbn', 'value' => $d, 'raw' => $raw] : null;
            case 'UC':
                $prefix = substr($t, -1) === '*';
                $v = trim(mb_strtolower(rtrim($t, '*')));
                return $v !== '' ? ['type' => 'udc', 'value' => $v, 'prefix' => $prefix, 'raw' => $raw] : null;
            case 'LA':
                $ids = self::languageIds($t);
                return ['type' => 'lang', 'ids' => $ids ?: ['__none__'], 'raw' => $raw];
            case 'CN':
                return ['type' => 'callnumber', 'value' => rtrim($t, '*'), 'raw' => $raw];
        }
        // текстово поле: фраза в кавички или думи (с * за начало на дума)
        $phrase = false;
        if (preg_match('/^"(.*)"\*?$/u', $t, $m)) {
            $phrase = true;
            $t = $m[1] . (substr($raw, -1) === '*' ? '*' : '');
        }
        $tokens = [];
        foreach (preg_split('/\s+/u', $t) as $w) {
            $prefix = substr($w, -1) === '*';
            foreach (BgText::words(rtrim($w, '*')) as $i => $part) {
                $tokens[] = ['t' => $part, 'prefix' => $prefix];
            }
            if (count($tokens) >= self::MAX_TOKENS) break;
        }
        if (!$tokens) return null;
        return ['type' => 'term', 'field' => $f, 'tokens' => $tokens, 'phrase' => $phrase && count($tokens) > 1, 'raw' => $raw];
    }

    /** 2019, 2010-2020, 2010:2020, 2010–2020, 201* (→ 2010–2019) */
    public static function yearRange(string $t): ?array
    {
        $t = trim($t);
        if (preg_match('/^(\d{4})\s*[-–:]\s*(\d{4})$/u', $t, $m)) {
            $a = (int)$m[1]; $b = (int)$m[2];
            return $a <= $b ? [$a, $b] : [$b, $a];
        }
        if (preg_match('/^\d{4}$/', $t)) return [(int)$t, (int)$t];
        if (preg_match('/^(\d{1,3})\*$/', $t, $m)) {
            $len = 4 - strlen($m[1]);
            return [(int)($m[1] . str_repeat('0', $len)), (int)($m[1] . str_repeat('9', $len))];
        }
        return null;
    }

    public static function languageIds(string $t): array
    {
        $map = ['bul' => 'bg', 'bg' => 'bg', 'eng' => 'en', 'en' => 'en', 'rus' => 'ru', 'ru' => 'ru', 'ger' => 'de', 'deu' => 'de', 'de' => 'de',
            'fre' => 'fr', 'fra' => 'fr', 'fr' => 'fr', 'spa' => 'es', 'es' => 'es'];
        $out = [];
        foreach (preg_split('/[\s,;]+/u', mb_strtolower(trim($t))) as $v) {
            if ($v === '') continue;
            if (isset($map[$v])) { $out[] = $map[$v]; continue; }
            try {
                $st = DB::getInstance()->prepare('SELECT language_id FROM mst_language WHERE language_id = ? OR language_name LIKE ?');
                if ($st && $st->execute([$v, $v . '%'])) foreach ($st->fetchAll(\PDO::FETCH_COLUMN) as $id) $out[] = $id;
            } catch (\Throwable $e) {
            }
        }
        return array_values(array_unique($out));
    }

    private static function looksLikeCommand(string $kw): bool
    {
        $l = self::latin($kw);
        return (bool)preg_match('/(^|[\s(])(KW|TI|AU|SU|PU|PP|PY|BN|UC|SE|CL|LA|OT|NT|CN|DC|SN|SP)=/iu', $l)
            || (bool)preg_match('/(^|\s)(AND|OR|NOT|ИЛИ|НЕ)(\s|$)/u', $kw);
    }

    /** AU=Петрунова AND PY=2019 · TI="под игото" OR SU=история · заглав* NOT роман */
    private static function parseCommand(string $kw, BgCriteria $c): void
    {
        preg_match_all('/[^\s"]*"[^"]*"\*?|\S+/u', $kw, $m);
        $op = 'and';
        $pending = [];
        $flush = function () use (&$pending, &$op, $c) {
            if (!$pending) return;
            $node = self::node('KW', implode(' ', $pending));
            if ($node) $c->addRow($op, $node);
            $pending = [];
            $op = 'and';
        };
        foreach ($m[0] as $tok) {
            $up = $tok;
            if (in_array($up, ['AND', 'И'], true)) { $flush(); $op = 'and'; continue; }
            if (in_array($up, ['OR', 'ИЛИ'], true)) { $flush(); $op = 'or'; continue; }
            if (in_array($up, ['NOT', 'НЕ'], true)) { $flush(); $op = 'not'; continue; }
            if (preg_match('/^([A-Za-zА-Яа-я]{2})=(.+)$/u', $tok, $mm) && isset(self::FIELDS[self::ALIASES[strtoupper(self::latin($mm[1]))] ?? strtoupper(self::latin($mm[1]))])) {
                $flush();
                $node = self::node(self::field($mm[1]), $mm[2]);
                if ($node) $c->addRow($op, $node);
                $op = 'and';
                continue;
            }
            $pending[] = $tok;
        }
        $flush();
    }

    /** Ограничения: години от–до, език, вид материал (винаги с И). */
    private static function limits(array $get, BgCriteria $c): void
    {
        if (is_string($get['years'] ?? null) && preg_match('/^(\d{3,4});(\d{3,4})$/', $get['years'], $m)) {
            $a = (int)$m[1]; $b = (int)$m[2];
            $c->limits['years'] = $a <= $b ? [$a, $b] : [$b, $a];
        }
        foreach (['lang', 'gmd'] as $k) {
            $v = $get[$k] ?? null;
            if ($v === null || $v === '' || $v === '0') continue;
            $vals = is_array($v) ? $v : explode(',', (string)$v);
            if ($k === 'gmd') {
                $vals = array_values(array_filter(array_map('intval', $vals)));
                if ($vals && count($vals) === count(array_filter(is_array($v) ? $v : explode(',', (string)$v), 'ctype_digit'))) $c->limits['gmd'] = $vals;
            } else {
                $vals = array_values(array_filter(array_map(fn($x) => preg_replace('/[^a-z_-]/i', '', (string)$x), $vals)));
                if ($vals) $c->limits['lang'] = $vals;
            }
        }
    }

    /** Текстът за читателя и думите за оцветяване. */
    private static function finish(BgCriteria $c): void
    {
        $labels = self::labels();
        $ops = self::opLabels();
        $parts = [];
        $words = [];
        foreach ($c->rows as $i => $r) {
            $n = $r['node'];
            $label = $labels[$n['field'] ?? ''] ?? '';
            switch ($n['type']) {
                case 'year': $label = $labels['PY']; break;
                case 'isbn': $label = 'ISBN'; break;
                case 'udc': $label = $labels['UC']; break;
                case 'lang': $label = $labels['LA']; break;
                case 'callnumber': $label = $labels['CN']; break;
                case 'gmdName': $label = __('Material type'); break;
                case 'locationName': $label = __('Location'); break;
                case 'colltypeName': $label = __('Collection Type'); break;
            }
            $text = $n['raw'] ?? ($n['value'] ?? '');
            $parts[] = ($i ? $ops[$r['op']] . ' ' : '') . ($label !== '' && ($n['field'] ?? '') !== 'KW' ? $label . ': ' : '') . $text;
            if ($n['type'] === 'term' && $r['op'] !== 'not') foreach ($n['tokens'] as $t) $words[] = $t['t'];
        }
        if (!empty($c->limits['years'])) $parts[] = '· ' . __('Years') . ' ' . $c->limits['years'][0] . '–' . $c->limits['years'][1];
        if (!empty($c->limits['lang'])) $parts[] = '· ' . __('Language') . ': ' . implode(', ', $c->limits['lang']);
        if (!empty($c->limits['gmd'])) $parts[] = '· ' . __('Material type') . ' (' . count($c->limits['gmd']) . ')';
        $c->display = implode(' ', $parts);
        $c->highlight = array_values(array_unique($words));
        if ($c->keywords === null || $c->keywords === '') $c->keywords = implode(' ', $c->highlight);
    }
}
