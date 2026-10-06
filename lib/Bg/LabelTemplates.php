<?php
/**
 * Съдържанието на един етикет: баркод, сигнатурен етикет или комбиниран.
 * Размерите са в мм спрямо клетката (PrintSheet).
 */

namespace SLiMS\Bg;

class LabelTemplates
{
    public static function types(): array
    {
        return [
            'barcode' => __('Barcode label'),
            'signature' => __('Call number (spine) label'),
            'combined' => __('Combined: call number + barcode'),
        ];
    }

    private static function h($v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }

    /** CSS за съдържанието на етикетите (височината на баркода зависи от клетката). */
    public static function css(array $g, array $o): string
    {
        $inner = $g['h'] - 2 * (float)($o['padding'] ?? 1.5);
        $barH = max(6, min(14, $inner * 0.45));
        return '.lb{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;text-align:center;line-height:1.1}'
            . '.lb .lib{font-size:6pt;white-space:nowrap;overflow:hidden;max-width:100%}'
            . '.lb .bc{display:block;width:100%;height:' . round($barH, 1) . 'mm;margin:0.5mm 0}'
            . '.lb .code{font-size:9pt;font-weight:bold;letter-spacing:0.5pt}'
            . '.lb .ttl{font-size:6.5pt;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%}'
            . '.sig{display:flex;flex-direction:column;justify-content:center;height:100%;font-weight:bold;line-height:1.15}'
            . '.sig .fit{font-size:12pt;text-align:center}'
            . '.cmb{display:flex;height:100%;align-items:center}'
            . '.cmb .sig{width:40%;border-right:0.2mm solid #999;padding-right:1mm}'
            . '.cmb .sig .fit{font-size:10pt}'
            . '.cmb .lb{width:60%;padding-left:1mm}';
    }

    public static function barcode(array $r, array $o): string
    {
        $code = (string)$r['item_code'];
        try {
            $svg = Barcode::svg($code);
        } catch (\Throwable $e) {
            $svg = '<div style="color:#c00;font-size:7pt">' . self::h(__('Cannot encode this code')) . '</div>';
        }
        $s = '<div class="lb">';
        if (($o['header_text'] ?? '') !== '') $s .= '<div class="lib">' . self::h($o['header_text']) . '</div>';
        $s .= $svg . '<div class="code">' . self::h($code) . '</div>';
        if (!empty($o['show_title'])) {
            $len = max(10, (int)($o['title_len'] ?? 30));
            $t = (string)$r['title'];
            if (mb_strlen($t) > $len) $t = mb_substr($t, 0, $len - 1) . '…';
            $s .= '<div class="ttl">' . self::h($t) . '</div>';
        }
        if (!empty($o['show_callnumber'])) {
            $sig = LabelData::signature($r);
            $s .= '<div class="ttl">' . self::h(trim($sig['udc'] . ' ' . $sig['line2'])) . '</div>';
        }
        return $s . '</div>';
    }

    public static function signature(array $r, array $o): string
    {
        $sig = LabelData::signature($r);
        $lines = array_filter([$sig['udc'], $sig['line2'], $sig['volume']], fn($l) => $l !== '');
        return '<div class="sig"><div class="fit" data-min="6">' . implode('<br>', array_map([self::class, 'h'], $lines)) . '</div></div>';
    }

    public static function combined(array $r, array $o): string
    {
        return '<div class="cmb">' . self::signature($r, $o) . self::barcode($r, array_merge($o, ['show_title' => false, 'show_callnumber' => false])) . '</div>';
    }

    public static function render(string $type, array $r, array $o): string
    {
        switch ($type) {
            case 'signature': return self::signature($r, $o);
            case 'combined': return self::combined($r, $o);
            default: return self::barcode($r, $o);
        }
    }
}
