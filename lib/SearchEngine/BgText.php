<?php
/**
 * Нормализиране на текст за търсене (кирилица и латиница):
 * малки букви, без пунктуация, думите разделени с един интервал и с интервал в двата края,
 * за да се търси с LIKE '% дума %' (цяла дума) или '% дума%' (начало на дума).
 * Ползва се и от индексатора (search_biblio.n_*), и от търсачката.
 */

namespace SLiMS\SearchEngine;

class BgText
{
    public static function base(?string $s): string
    {
        $s = (string)$s;
        if ($s === '') return '';
        if (class_exists('Normalizer')) $s = \Normalizer::normalize($s, \Normalizer::FORM_C) ?: $s;
        $s = strip_tags(html_entity_decode($s, ENT_QUOTES, 'UTF-8'));
        $s = mb_strtolower($s, 'UTF-8');
        $s = strtr($s, ['ѝ' => 'и', 'ё' => 'е']);
        $s = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $s);
        return trim(preg_replace('/\s+/u', ' ', $s));
    }

    /** Текст за индекса: ' дума1 дума2 ' (или '' ако е празно). */
    public static function norm(?string $s): string
    {
        $b = self::base($s);
        return $b === '' ? '' : ' ' . $b . ' ';
    }

    /** Думите от заявката (нормализирани). */
    public static function words(?string $s): array
    {
        $b = self::base($s);
        return $b === '' ? [] : explode(' ', $b);
    }

    /** ISBN/ISSN: само цифрите (и X); към ISBN-13 с 978 се добавя и ISBN-10, и обратно. */
    public static function isbn(?string $s): string
    {
        $out = [];
        foreach (preg_split('/[;,]/', (string)$s) as $part) {
            $part = preg_replace('/\([^)]*\)/', '', $part);
            $d = strtoupper(preg_replace('/[^0-9Xx]/', '', $part));
            if ($d === '') continue;
            $out[$d] = true;
            if (strlen($d) === 13 && substr($d, 0, 3) === '978') {
                $core = substr($d, 3, 9);
                $sum = 0;
                for ($i = 0; $i < 9; $i++) $sum += (int)$core[$i] * (10 - $i);
                $c = (11 - $sum % 11) % 11;
                $out[$core . ($c === 10 ? 'X' : (string)$c)] = true;
            } elseif (strlen($d) === 10) {
                $core = '978' . substr($d, 0, 9);
                $sum = 0;
                for ($i = 0; $i < 12; $i++) $sum += (int)$core[$i] * ($i % 2 ? 3 : 1);
                $out[$core . ((10 - $sum % 10) % 10)] = true;
            }
        }
        return $out ? ' ' . implode(' ', array_keys($out)) . ' ' : '';
    }

    /** УДК: „:“ и „+“ разделят индексите, „(“ и „"“ започват нов спомагателен индекс. */
    public static function udc(?string $s): string
    {
        $s = mb_strtolower(trim((string)$s), 'UTF-8');
        if ($s === '') return '';
        $s = str_replace([':', '+'], ' ', $s);
        $s = preg_replace('/\s*([(\["])/u', ' $1', $s);
        $s = trim(preg_replace('/\s+/u', ' ', $s));
        return ' ' . $s . ' ';
    }

    /** Първата правдоподобна година (1000–2099). */
    public static function year(?string $s): ?int
    {
        return preg_match('/(?<!\d)(1\d{3}|20\d{2})(?!\d)/', (string)$s, $m) ? (int)$m[1] : null;
    }

    /** Оригиналното заглавие от бележките („Ориг. загл.: …“). */
    public static function origTitle(?string $notes): string
    {
        if (preg_match('/Ориг(?:инално)?\.?\s*загл(?:авие)?\.?\s*:?\s*([^–;\n]+)/iu', strip_tags((string)$notes), $m)) {
            return trim($m[1]);
        }
        return '';
    }

    /**
     * Колоните n_* на search_biblio за един запис.
     * @param array $b ред от biblio (+ publisher, publish_place)
     */
    public static function indexColumns(array $b, array $authors, array $topics): array
    {
        $author = implode(' ', $authors) . ' ' . ($b['sor'] ?? '');
        $series = trim(($b['series_title'] ?? '') . ' ' . ($b['series_number'] ?? ''));
        $orig = self::origTitle($b['notes'] ?? '');
        $all = implode(' ', [
            $b['title'] ?? '', $series, $author, implode(' ', $topics), $b['publisher'] ?? '', $b['publish_place'] ?? '',
            strip_tags((string)($b['notes'] ?? '')), $b['edition'] ?? '', $b['publish_year'] ?? '',
        ]);
        return [
            'n_title' => self::norm($b['title'] ?? ''),
            'n_author' => self::norm($author),
            'n_subject' => self::norm(implode(' ', $topics)),
            'n_publisher' => self::norm($b['publisher'] ?? ''),
            'n_place' => self::norm($b['publish_place'] ?? ''),
            'n_series' => self::norm($series),
            'n_notes' => self::norm(strip_tags((string)($b['notes'] ?? ''))),
            'n_origtitle' => self::norm($orig),
            'n_class' => self::udc($b['classification'] ?? ''),
            'n_isbn' => self::isbn($b['isbn_issn'] ?? ''),
            'n_all' => ' ' . trim(implode(' ', array_filter([self::base($all), trim(self::udc($b['classification'] ?? '')), trim(self::isbn($b['isbn_issn'] ?? ''))]))) . ' ',
            'year_int' => self::year($b['publish_year'] ?? '') ?? 'literal{NULL}',
        ];
    }
}
