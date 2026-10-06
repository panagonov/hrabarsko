<?php
/**
 * Code 128 като SVG (векторен – отпечатва се остро при всякакъв размер на етикета).
 * Цифрите се кодират в набор C (по две на символ), останалото – в набор B.
 * Таблицата с шарките е същата като в lib/Zend/Barcode/Object/Code128.php.
 */

namespace SLiMS\Bg;

class Barcode
{
    private const MAP = [
        '11011001100', '11001101100', '11001100110', '10010011000', '10010001100', '10001001100', '10011001000', '10011000100',
        '10001100100', '11001001000', '11001000100', '11000100100', '10110011100', '10011011100', '10011001110', '10111001100',
        '10011101100', '10011100110', '11001110010', '11001011100', '11001001110', '11011100100', '11001110100', '11101101110',
        '11101001100', '11100101100', '11100100110', '11101100100', '11100110100', '11100110010', '11011011000', '11011000110',
        '11000110110', '10100011000', '10001011000', '10001000110', '10110001000', '10001101000', '10001100010', '11010001000',
        '11000101000', '11000100010', '10110111000', '10110001110', '10001101110', '10111011000', '10111000110', '10001110110',
        '11101110110', '11010001110', '11000101110', '11011101000', '11011100010', '11011101110', '11101011000', '11101000110',
        '11100010110', '11101101000', '11101100010', '11100011010', '11101111010', '11001000010', '11110001010', '10100110000',
        '10100001100', '10010110000', '10010000110', '10000101100', '10000100110', '10110010000', '10110000100', '10011010000',
        '10011000010', '10000110100', '10000110010', '11000010010', '11001010000', '11110111010', '11000010100', '10001111010',
        '10100111100', '10010111100', '10010011110', '10111100100', '10011110100', '10011110010', '11110100100', '11110010100',
        '11110010010', '11011011110', '11011110110', '11110110110', '10101111000', '10100011110', '10001011110', '10111101000',
        '10111100010', '11110101000', '11110100010', '10111011110', '10111101110', '11101011110', '11110101110', '11010000100',
        '11010010000', '11010011100', '1100011101011',
    ];
    private const START_B = 104;
    private const START_C = 105;
    private const CODE_B = 100;
    private const CODE_C = 99;
    private const STOP = 106;
    /** Празно поле от всяка страна (в модули), нужно на четеца */
    public const QUIET = 10;

    /** Стойностите на символите (със старт, контролна сума и стоп). */
    public static function symbols(string $text): array
    {
        if ($text === '' || !preg_match('/^[\x20-\x7E]+$/', $text)) {
            throw new \InvalidArgumentException('Code128: only printable ASCII is supported');
        }
        $values = [];
        $len = strlen($text);
        if (ctype_digit($text) && $len >= 2) {
            // при нечетен брой цифри последната се кодира в набор B
            $even = $len - ($len % 2);
            $values[] = self::START_C;
            for ($i = 0; $i < $even; $i += 2) $values[] = (int)substr($text, $i, 2);
            if ($len % 2) {
                $values[] = self::CODE_B;
                $values[] = ord($text[$len - 1]) - 32;
            }
        } else {
            $values[] = self::START_B;
            for ($i = 0; $i < $len; $i++) $values[] = ord($text[$i]) - 32;
        }
        $sum = $values[0];
        for ($i = 1, $n = count($values); $i < $n; $i++) $sum += $values[$i] * $i;
        $values[] = $sum % 103;
        $values[] = self::STOP;
        return $values;
    }

    /** Модулите (1 = черна лента) без празните полета. */
    public static function modules(string $text): string
    {
        return implode('', array_map(fn($v) => self::MAP[$v], self::symbols($text)));
    }

    /**
     * SVG с viewBox – ширината и височината се задават от CSS (в мм).
     * Празните полета от двете страни са включени.
     */
    public static function svg(string $text, int $heightUnits = 40): string
    {
        $bits = self::modules($text);
        $width = strlen($bits) + 2 * self::QUIET;
        $rects = '';
        $x = self::QUIET;
        $n = strlen($bits);
        for ($i = 0; $i < $n;) {
            if ($bits[$i] === '1') {
                $start = $i;
                while ($i < $n && $bits[$i] === '1') $i++;
                $rects .= '<rect x="' . ($x + $start) . '" y="0" width="' . ($i - $start) . '" height="' . $heightUnits . '"/>';
            } else {
                $i++;
            }
        }
        return '<svg xmlns="http://www.w3.org/2000/svg" class="bc" viewBox="0 0 ' . $width . ' ' . $heightUnits . '" preserveAspectRatio="none" shape-rendering="crispEdges">'
            . '<rect x="0" y="0" width="' . $width . '" height="' . $heightUnits . '" fill="#fff"/><g fill="#000">' . $rects . '</g></svg>';
    }

    /** Ширина на един модул в мм при дадена ширина на баркода (за проверка на четимостта). */
    public static function moduleWidthMm(string $text, float $widthMm): float
    {
        return $widthMm / (strlen(self::modules($text)) + 2 * self::QUIET);
    }
}
