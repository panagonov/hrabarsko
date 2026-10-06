<?php
/**
 * Проверка на полетата във формулярите за въвеждане (заглавие, екземпляр, КДБФ, акт).
 *
 * Грешките спират записа, предупрежденията само се показват след успешен запис.
 * Ключът на всяка грешка е id на полето във формуляра, за да може да се маркира.
 */

namespace SLiMS\Bg;

use utility;

class Validator
{
    /** @var array<string,string> */
    private array $errors = [];
    /** @var array<string,string> */
    private array $warnings = [];

    public function error(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) $this->errors[$field] = $message;
    }

    public function warn(string $field, string $message): void
    {
        if (!isset($this->warnings[$field])) $this->warnings[$field] = $message;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function warnings(): array
    {
        return $this->warnings;
    }

    /* ------------------------------------------------------------------
     * Чисти функции (без база данни)
     * ------------------------------------------------------------------ */

    /** Оставя само цифрите и X (главно). */
    public static function isbnDigits(string $s): string
    {
        return strtoupper(preg_replace('/[^0-9Xx]/', '', $s));
    }

    public static function isbn10Valid(string $d): bool
    {
        if (!preg_match('/^\d{9}[\dX]$/', $d)) return false;
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $v = ($d[$i] === 'X') ? 10 : (int)$d[$i];
            $sum += $v * (10 - $i);
        }
        return $sum % 11 === 0;
    }

    public static function isbn13Valid(string $d): bool
    {
        if (!preg_match('/^97[89]\d{10}$/', $d)) return false;
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += (int)$d[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        return $sum % 10 === 0;
    }

    public static function issnValid(string $d): bool
    {
        if (!preg_match('/^\d{7}[\dX]$/', $d)) return false;
        $sum = 0;
        for ($i = 0; $i < 7; $i++) {
            $sum += (int)$d[$i] * (8 - $i);
        }
        $check = (11 - $sum % 11) % 11;
        $last = ($d[7] === 'X') ? 10 : (int)$d[7];
        return $check === $last;
    }

    /* ------------------------------------------------------------------
     * Проверки на полета
     * ------------------------------------------------------------------ */

    /**
     * ISBN / ISSN – едно или няколко, разделени с „;“, всяко с незадължително уточнение „(т. 1)“.
     * Връща нормализирания текст или null, ако полето е празно.
     */
    public function isbnIssn(string $field, ?string $raw): ?string
    {
        $raw = trim((string)$raw);
        if ($raw === '') return null;

        $out = [];
        foreach (preg_split('/\s*;\s*/u', $raw) as $part) {
            if ($part === '') continue;
            $qualifier = '';
            if (preg_match('/^(.*?)\s*(\([^()]{1,40}\))\s*$/u', $part, $m)) {
                $part = $m[1];
                $qualifier = $m[2];
            }
            $part = preg_replace('/^\s*IS[BS]N\s*:?\s*/iu', '', $part);
            if (!preg_match('/^[0-9Xx\- ]+$/', $part)) {
                $this->error($field, sprintf(__('ISBN/ISSN "%s" contains invalid characters (only digits, X, hyphens and spaces are allowed)'), $part));
                continue;
            }
            $digits = self::isbnDigits($part);
            $len = strlen($digits);
            if ($len === 13) {
                $ok = self::isbn13Valid($digits);
            } elseif ($len === 10) {
                $ok = self::isbn10Valid($digits);
            } elseif ($len === 8) {
                $ok = self::issnValid($digits);
            } else {
                $this->error($field, sprintf(__('"%s" is not an ISBN (10 or 13 digits) or ISSN (8 digits)'), $part));
                continue;
            }
            if (!$ok) {
                if (mb_stripos($qualifier, 'грешен') !== false) {
                    $this->warn($field, sprintf(__('The check digit of "%s" is wrong (kept because it is marked as wrong)'), $part));
                } else {
                    $this->error($field, sprintf(__('The check digit of "%s" is wrong. If the book really has this wrong number printed, add "(грешен)" after it'), $part));
                    continue;
                }
            }
            $number = preg_replace('/-{2,}/', '-', str_replace(' ', '-', strtoupper(trim($part, " -"))));
            $out[] = trim($number . ' ' . $qualifier);
        }
        $result = implode('; ', $out);
        $this->maxLen($field, $result, 100, 'ISBN/ISSN');
        return $result === '' ? null : $result;
    }

    /** Година на издаване: 2019, [2019], 2019?, [2019?], [ок. 1900], 2010–2012. */
    public function publishYear(string $field, ?string $raw, bool $serial = false, bool $required = true): string
    {
        $raw = trim((string)$raw);
        $level = $serial ? 'warn' : 'error';
        if ($raw === '') {
            if ($required) $this->$level($field, __('Publishing year is required (write [б. г.] if the book has no year)'));
            return '';
        }
        $patterns = [
            '/^\d{4}$/u',
            '/^\d{4}\?$/u',
            '/^\[\d{4}\??\]$/u',
            '/^\[(?:ок\.|около|ca\.?|c\.)\s*\d{4}\??\]$/iu',
            '/^\d{4}\s*[-–]\s*\d{4}$/u',
            '/^\[б\.\s*г\.\]$/iu',
        ];
        $matched = false;
        foreach ($patterns as $p) {
            if (preg_match($p, $raw)) { $matched = true; break; }
        }
        if (!$matched) {
            $this->$level($field, __('Publishing year must look like 2019, [2019], 2019?, [ок. 1900], 2010–2012 or [б. г.]'));
            return $raw;
        }
        preg_match_all('/\d{4}/', $raw, $m);
        $max = (int)date('Y') + 1;
        $prev = 0;
        foreach ($m[0] as $y) {
            $y = (int)$y;
            if ($y < 1450 || $y > $max) {
                $this->$level($field, sprintf(__('Publishing year must be between 1450 and %d'), $max));
                break;
            }
            if ($y < $prev) {
                $this->$level($field, __('The second year of a range cannot be before the first one'));
                break;
            }
            $prev = $y;
        }
        return str_replace('-', '–', preg_replace('/\s*[-–]\s*/u', '-', $raw));
    }

    /** Цена: 12.50 или 12,50 → "12.50". */
    public function price(string $field, $raw, bool $required = true): ?string
    {
        $v = preg_replace('/[\s\x{00A0}]+/u', '', (string)$raw);
        $v = str_replace(',', '.', $v);
        if ($v === '') {
            if ($required) $this->error($field, __('Price is required (for donations enter an estimated value)'));
            return null;
        }
        if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $v)) {
            $this->error($field, __('Price must be a number with up to 2 decimals, e.g. 12.50'));
            return null;
        }
        if ((float)$v == 0.0) {
            $this->warn($field, __('Price is 0 – for donations enter an estimated value'));
        }
        return number_format((float)$v, 2, '.', '');
    }

    public function currency(string $field, $raw, ?string $refDate = null): string
    {
        $v = strtoupper(trim((string)$raw));
        if ($v === '' || $v === '0') return Inventory::defaultCurrency($refDate);
        if (!in_array($v, Inventory::CURRENCIES, true)) {
            $this->error($field, __('Currency must be EUR or BGN'));
            return Inventory::defaultCurrency($refDate);
        }
        return $v;
    }

    /** Инвентарен номер = баркод: цяло положително число без водещи нули. */
    public function inventoryNo(string $field, $raw): ?string
    {
        $v = trim((string)$raw);
        if ($v === '') {
            $this->error($field, __('Inventory number is required'));
            return null;
        }
        if (!preg_match('/^[1-9]\d{0,8}$/', $v)) {
            $this->error($field, sprintf(__('Inventory number "%s" must be a whole number without leading zeros (e.g. 1234)'), $v));
            return null;
        }
        return $v;
    }

    public function positiveInt(string $field, $raw, string $label, int $max = 999999999, bool $required = true): ?int
    {
        $v = trim((string)$raw);
        if ($v === '') {
            if ($required) $this->error($field, sprintf(__('%s is required'), $label));
            return null;
        }
        if (!preg_match('/^\d{1,9}$/', $v) || (int)$v < 1 || (int)$v > $max) {
            $this->error($field, sprintf(__('%s must be a whole number between 1 and %d'), $label, $max));
            return null;
        }
        return (int)$v;
    }

    public function nonNegativeInt(string $field, $raw, string $label): int
    {
        $v = trim((string)$raw);
        if ($v === '') return 0;
        if (!preg_match('/^\d{1,9}$/', $v)) {
            $this->error($field, sprintf(__('%s must be a whole number (0 or more)'), $label));
            return 0;
        }
        return (int)$v;
    }

    /**
     * Дата във вид ГГГГ-ММ-ДД. Връща нормализираната дата или null при празно поле.
     */
    public function date(string $field, $raw, string $label, bool $required = false, ?string $notAfter = null, ?string $notBefore = null, bool $warnOnly = false): ?string
    {
        $v = trim((string)$raw);
        if ($v === '' || $v === '0000-00-00') {
            if ($required) $this->error($field, sprintf(__('%s is required'), $label));
            return null;
        }
        $d = \DateTime::createFromFormat('!Y-m-d', $v);
        if (!$d || $d->format('Y-m-d') !== $v) {
            $this->error($field, sprintf(__('%s is not a valid date (YYYY-MM-DD)'), $label));
            return null;
        }
        $level = $warnOnly ? 'warn' : 'error';
        if ($notAfter !== null && $v > $notAfter) {
            $this->$level($field, sprintf(__('%s cannot be after %s'), $label, $notAfter));
        }
        if ($notBefore !== null && $v < $notBefore) {
            $this->$level($field, sprintf(__('%s cannot be before %s'), $label, $notBefore));
        }
        return $v;
    }

    /** УДК – само предупреждение. */
    public function udc(string $field, ?string $raw, string $label = 'УДК'): void
    {
        $v = trim((string)$raw);
        if ($v === '') return;
        if (!preg_match('/^[0-9](?:[0-9.\-:+\/\'\s]|\([^()]*\)|"[^"]*"|=[0-9.\-]+|\[[^\[\]]*\])*$/u', $v)) {
            $this->warn($field, sprintf(__('%s "%s" does not look like a UDC index (e.g. 886.7-31 or 351.82:332.2(497.2))'), $label, $v));
        }
    }

    /** Авторски знак: главна буква + 1–3 цифри (П47) – само предупреждение. */
    public function authorMark(string $field, ?string $raw): void
    {
        $v = trim((string)$raw);
        if ($v === '') return;
        if (!preg_match('/^[А-ЯЁA-Z]\d{1,3}$/u', $v)) {
            $this->warn($field, sprintf(__('Author mark "%s" usually is a capital letter followed by 1–3 digits (e.g. П47)'), $v));
        }
    }

    /** Обем по ISBD – само предупреждение за печатни текстове. */
    public function collation(string $field, ?string $raw, bool $isText): void
    {
        $v = trim((string)$raw);
        if ($v === '' || !$isText) return;
        if (mb_strpos($v, 'с.') === false || mb_strpos($v, 'см') === false) {
            $this->warn($field, __('Physical description usually looks like "208 с. : ил. ; 20 см"'));
        }
    }

    public function maxLen(string $field, ?string $val, int $max, string $label): void
    {
        if ($val !== null && mb_strlen($val) > $max) {
            $this->error($field, sprintf(__('%s is too long (max %d characters)'), $label, $max));
        }
    }

    public function required(string $field, $val, string $label): void
    {
        if (trim((string)$val) === '' || (string)$val === '0') {
            $this->error($field, sprintf(__('%s is required'), $label));
        }
    }

    public function oneOf(string $field, $val, array $allowed, string $label): void
    {
        if (!in_array($val, $allowed, true)) {
            $this->error($field, sprintf(__('%s has an invalid value'), $label));
        }
    }

    /* ------------------------------------------------------------------
     * Показване в админ панела
     * ------------------------------------------------------------------ */

    /** Показва грешките, маркира полетата и спира изпълнението (нищо не се записва). */
    public function failAndExit(string $title): void
    {
        utility::jsToastr($title, implode("\n", array_values($this->errors)), 'error');
        echo $this->markFieldsScript();
        exit();
    }

    /** Показва предупрежденията (след успешен запис). */
    public function flushWarnings(string $title): void
    {
        if ($this->warnings) {
            utility::jsToastr($title, implode("\n", array_values($this->warnings)), 'warning');
        }
    }

    private function markFieldsScript(): string
    {
        $ids = json_encode(array_keys($this->errors));
        return '<script type="text/javascript">(function(d){if(!d)return;'
            . 'd.querySelectorAll(".is-invalid").forEach(function(e){e.classList.remove("is-invalid");});'
            . $ids . '.forEach(function(id){var e=d.getElementById(id);if(e){e.classList.add("is-invalid");}});'
            . 'var l=d.querySelector(".loader");if(l){l.style.display="none";}'
            . '})(parent.document);</script>';
    }

    /** Преведените съобщения за js/bg_catalog.js */
    public static function jsI18nScript(): string
    {
        $t = [
            'invno' => __('Inventory number must be a whole number without leading zeros (e.g. 1234)'),
            'invnoJump' => __('This number is more than 100 above the next free number (%s). Continue?'),
            'isbnChars' => __('Only digits, X, hyphens and spaces are allowed'),
            'isbnLen' => __('An ISBN has 10 or 13 digits, an ISSN has 8'),
            'isbnCheck' => __('Wrong check digit – check the number. If the book really has a wrong number printed, add "(грешен)"'),
            'year' => __('Publishing year must look like 2019, [2019], 2019?, [ок. 1900], 2010–2012 or [б. г.]'),
            'yearRange' => __('Publishing year must be between 1450 and %s'),
            'price' => __('Price must be a number with up to 2 decimals, e.g. 12.50'),
            'priceZero' => __('Price is 0 – for donations enter an estimated value'),
            'udc' => __('Does not look like a UDC index (e.g. 886.7-31)'),
            'authormark' => __('Usually a capital letter followed by 1–3 digits (e.g. П47)'),
            'collation' => __('Usually looks like "208 с. : ил. ; 20 см"'),
            'datePast' => __('The date cannot be in the future'),
            'date' => __('Not a valid date'),
            'posint' => __('Must be a whole number greater than 0'),
            'ranges' => __('Use numbers and ranges, e.g. 101-130, 135'),
            'required' => __('This field is required'),
            'title' => __('Please correct the marked fields'),
        ];
        return '<script type="text/javascript">window.BgCatI18n = ' . json_encode($t, JSON_UNESCAPED_UNICODE) . ';</script>';
    }
}
