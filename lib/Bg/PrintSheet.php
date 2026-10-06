<?php
/**
 * Подреждане на етикети и каталожни картички върху A4 (в мм):
 *  – готови листове със стикери (позициите са фиксирани от производителя);
 *  – „плътно за рязане“: максимален брой на обикновена хартия, контур до контур,
 *    само с прави разрези от край до край („на един нож“), със завъртане на 90°,
 *    ако така се побират повече; избира се и по-добрата ориентация на листа;
 *  – започване от позиция N, тестова страница за калибриране.
 */

namespace SLiMS\Bg;

class PrintSheet
{
    /** Размер на каталожната картичка (мм) */
    public const CARD_W = 125;
    public const CARD_H = 75;

    /**
     * Готови листове: колони, редове, ширина и височина на етикета, ляво и горно поле,
     * хоризонтален и вертикален отстъп между етикетите, ориентация на страницата.
     */
    public static function presets(): array
    {
        return [
            'packed' => ['label' => __('Plain paper – packed for cutting (maximum per sheet)'), 'type' => 'packed', 'w' => 50, 'h' => 25, 'page' => 'A4'],
            '3x8' => ['label' => '3 × 8 = 24 (70 × 37 мм)', 'cols' => 3, 'rows' => 8, 'w' => 70, 'h' => 37, 'left' => 0, 'top' => 0.5, 'gapX' => 0, 'gapY' => 0, 'page' => 'A4'],
            '3x7' => ['label' => '3 × 7 = 21 (70 × 42,3 мм)', 'cols' => 3, 'rows' => 7, 'w' => 70, 'h' => 42.3, 'left' => 0, 'top' => 0.45, 'gapX' => 0, 'gapY' => 0, 'page' => 'A4'],
            '4x10' => ['label' => '4 × 10 = 40 (52,5 × 29,7 мм)', 'cols' => 4, 'rows' => 10, 'w' => 52.5, 'h' => 29.7, 'left' => 0, 'top' => 0, 'gapX' => 0, 'gapY' => 0, 'page' => 'A4'],
            '2x7' => ['label' => '2 × 7 = 14 (99,1 × 38,1 мм)', 'cols' => 2, 'rows' => 7, 'w' => 99.1, 'h' => 38.1, 'left' => 4.65, 'top' => 15.15, 'gapX' => 2.5, 'gapY' => 0, 'page' => 'A4'],
            '5x13' => ['label' => '5 × 13 = 65 (38,1 × 21,2 мм)', 'cols' => 5, 'rows' => 13, 'w' => 38.1, 'h' => 21.2, 'left' => 4.75, 'top' => 10.7, 'gapX' => 2.5, 'gapY' => 0, 'page' => 'A4'],
            'card_packed' => ['label' => __('Catalogue cards: packed on A4 for cutting – 5 per sheet'), 'type' => 'packed', 'w' => self::CARD_W, 'h' => self::CARD_H, 'page' => 'A4'],
            'card_a4_3' => ['label' => __('Catalogue cards: A4 portrait – 3'), 'cols' => 1, 'rows' => 3, 'w' => 125, 'h' => 75, 'left' => 42.5, 'top' => 26, 'gapX' => 0, 'gapY' => 10, 'page' => 'A4'],
            'card_a4_4' => ['label' => __('Catalogue cards: A4 landscape – 4'), 'cols' => 2, 'rows' => 2, 'w' => 125, 'h' => 75, 'left' => 18.5, 'top' => 25, 'gapX' => 10, 'gapY' => 10, 'page' => 'A4 landscape'],
            'card_single' => ['label' => __('Catalogue cards: one card per page (12.5 × 7.5 cm)'), 'cols' => 1, 'rows' => 1, 'w' => 125, 'h' => 75, 'left' => 0, 'top' => 0, 'gapX' => 0, 'gapY' => 0, 'page' => '125mm 75mm'],
        ];
    }

    public static function labelPresets(): array
    {
        return array_filter(self::presets(), fn($k) => strpos($k, 'card_') !== 0, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Геометрия на страницата: списък с клетки (x, y, w, h в мм на листа; rot = завъртяна на 90°).
     * @param array $opt margin – непечатаемо поле на принтера (мм), rotate – позволено завъртане
     */
    public static function geometry(string $preset, array $custom = [], float $offX = 0, float $offY = 0, array $opt = []): array
    {
        $all = self::presets();
        $offX = max(-20, min(20, $offX));
        $offY = max(-20, min(20, $offY));
        if ($preset === 'custom') {
            $g = [
                'label' => __('Custom size'),
                'cols' => max(1, min(10, (int)($custom['cols'] ?? 3))),
                'rows' => max(1, min(30, (int)($custom['rows'] ?? 8))),
                'w' => max(10, min(210, (float)($custom['w'] ?? 70))),
                'h' => max(5, min(297, (float)($custom['h'] ?? 37))),
                'left' => max(0, min(100, (float)($custom['left'] ?? 0))),
                'top' => max(0, min(100, (float)($custom['top'] ?? 0))),
                'gapX' => max(0, min(50, (float)($custom['gapX'] ?? 0))),
                'gapY' => max(0, min(50, (float)($custom['gapY'] ?? 0))),
                'page' => 'A4',
            ];
        } else {
            $g = $all[$preset] ?? $all['3x8'];
        }
        $g['preset'] = $preset;
        $g['offX'] = $offX;
        $g['offY'] = $offY;

        if (($g['type'] ?? '') === 'packed') {
            if ($preset === 'packed') {
                // етикетите на обикновена хартия: размерът е от „Друг размер“
                $g['w'] = max(10, min(200, (float)($custom['w'] ?? 50)));
                $g['h'] = max(5, min(287, (float)($custom['h'] ?? 25)));
            }
            $margin = max(0, min(20, (float)($opt['margin'] ?? 5)));
            $rotate = (bool)($opt['rotate'] ?? true);
            $g = array_merge($g, self::packedLayout((float)$g['w'], (float)$g['h'], $margin, $rotate, $offX, $offY));
            $g['label'] = sprintf(__('%d per sheet, %s × %s mm, packed for cutting'), count($g['cells']), self::num($g['w']), self::num($g['h']))
                . ($g['rotated'] ? ' · ' . __('some are rotated 90°') : '');
        } else {
            [$g['pageW'], $g['pageH']] = self::pageSize($g['page']);
            $g['cells'] = [];
            for ($row = 0; $row < $g['rows']; $row++) {
                for ($col = 0; $col < $g['cols']; $col++) {
                    $g['cells'][] = [
                        'x' => $g['left'] + $offX + $col * ($g['w'] + $g['gapX']),
                        'y' => $g['top'] + $offY + $row * ($g['h'] + $g['gapY']),
                        'w' => $g['w'], 'h' => $g['h'], 'rot' => false,
                    ];
                }
            }
            $g['rotated'] = false;
        }
        $g['perPage'] = count($g['cells']);
        return $g;
    }

    public static function pageSize(string $page): array
    {
        if ($page === 'A4 landscape') return [297, 210];
        if (preg_match('/^([\d.]+)mm ([\d.]+)mm$/', $page, $m)) return [(float)$m[1], (float)$m[2]];
        return [210, 297];
    }

    private static function num(float $v): string
    {
        return str_replace('.', ',', rtrim(rtrim(number_format($v, 1, '.', ''), '0'), '.'));
    }

    /* ------------------------------------------------------------------
     * Плътно нареждане с гилотинни разрези (всеки разрез минава от край до край)
     * ------------------------------------------------------------------ */

    /**
     * Най-много правоъгълници w×h (и h×w, ако е разрешено) в W×H само с прави разрези
     * от край до край. Динамично програмиране по „нормалните“ позиции на разрезите
     * (суми от размерите на етикета) – в стъпки от 0,1 мм.
     * @return array<int,array{x:float,y:float,w:float,h:float,rot:bool}>
     */
    public static function guillotine(float $W, float $H, float $w, float $h, bool $rotate = true): array
    {
        $U = 10;
        $W = (int)floor($W * $U + 1e-6);
        $H = (int)floor($H * $U + 1e-6);
        $w = (int)round($w * $U);
        $h = (int)round($h * $U);
        if ($w <= 0 || $h <= 0) return [];
        $rotate = $rotate && $w !== $h;
        $normal = function (int $L, array $sizes): array {
            $set = [0 => true];
            foreach ($sizes as $s) {
                foreach (array_keys($set) as $v) {
                    for ($x = $v + $s; $x <= $L; $x += $s) $set[$x] = true;
                }
            }
            $r = array_keys($set);
            sort($r);
            return $r;
        };
        $NW = $normal($W, $rotate ? [$w, $h] : [$w]);
        $NH = $normal($H, $rotate ? [$h, $w] : [$h]);
        $down = function (int $v, array $N): int {
            $best = 0;
            foreach ($N as $n) {
                if ($n > $v) break;
                $best = $n;
            }
            return $best;
        };
        $memo = [];
        $f = function (int $X, int $Y) use (&$f, &$memo, $NW, $NH, $down, $w, $h, $rotate): array {
            $key = $X . ':' . $Y;
            if (isset($memo[$key])) return $memo[$key];
            $best = [0, null];
            if ($w <= $X && $h <= $Y) $best = [1, ['p', false]];
            elseif ($rotate && $h <= $X && $w <= $Y) $best = [1, ['p', true]];
            if ($best[0] > 0) {
                foreach ($NW as $x) {
                    if ($x <= 0 || $x >= $X) continue;
                    $c = $f($x, $Y)[0] + $f($down($X - $x, $NW), $Y)[0];
                    if ($c > $best[0]) $best = [$c, ['v', $x]];
                }
                foreach ($NH as $y) {
                    if ($y <= 0 || $y >= $Y) continue;
                    $c = $f($X, $y)[0] + $f($X, $down($Y - $y, $NH))[0];
                    if ($c > $best[0]) $best = [$c, ['h', $y]];
                }
            }
            return $memo[$key] = $best;
        };
        $place = function (int $X, int $Y, int $ox, int $oy) use (&$place, $f, $NW, $NH, $down, $w, $h, $U): array {
            [$n, $plan] = $f($X, $Y);
            if ($n === 0 || $plan === null) return [];
            if ($plan[0] === 'p') {
                $rot = $plan[1];
                return [['x' => $ox / $U, 'y' => $oy / $U, 'w' => ($rot ? $h : $w) / $U, 'h' => ($rot ? $w : $h) / $U, 'rot' => $rot]];
            }
            if ($plan[0] === 'v') {
                $x = $plan[1];
                return array_merge($place($x, $Y, $ox, $oy), $place($down($X - $x, $NW), $Y, $ox + $x, $oy));
            }
            $y = $plan[1];
            return array_merge($place($X, $y, $ox, $oy), $place($X, $down($Y - $y, $NH), $ox, $oy + $y));
        };
        return $place($down($W, $NW), $down($H, $NH), 0, 0);
    }

    /**
     * По-добрата от двете ориентации на A4; блокът се центрира в листа.
     */
    public static function packedLayout(float $w, float $h, float $margin, bool $rotate, float $offX = 0, float $offY = 0): array
    {
        // най-много на лист; при равенство – без завъртане (по-лесно рязане), после вертикален лист
        $best = null;
        $score = fn(array $cells) => [count($cells), -count(array_filter($cells, fn($c) => $c['rot']))];
        foreach ($rotate ? [false, true] : [false] as $rot) {
            foreach (['A4' => [210, 297], 'A4 landscape' => [297, 210]] as $page => [$PW, $PH]) {
                $cells = self::guillotine($PW - 2 * $margin, $PH - 2 * $margin, $w, $h, $rot);
                if ($best === null || $score($cells) > $score($best['cells'])) {
                    $best = ['page' => $page, 'pageW' => $PW, 'pageH' => $PH, 'cells' => $cells];
                }
            }
        }
        $cells = $best['cells'];
        $usedW = $cells ? max(array_map(fn($c) => $c['x'] + $c['w'], $cells)) : 0;
        $usedH = $cells ? max(array_map(fn($c) => $c['y'] + $c['h'], $cells)) : 0;
        $left = ($best['pageW'] - $usedW) / 2 + $offX;
        $top = ($best['pageH'] - $usedH) / 2 + $offY;
        foreach ($cells as &$c) {
            $c['x'] += $left;
            $c['y'] += $top;
        }
        unset($c);
        // поредност за „започни от позиция“: отгоре надолу, отляво надясно
        usort($cells, fn($a, $b) => [round($a['y'], 1), round($a['x'], 1)] <=> [round($b['y'], 1), round($b['x'], 1)]);
        $best['cells'] = $cells;
        $best['rotated'] = (bool)array_filter($cells, fn($c) => $c['rot']);
        return $best;
    }

    private static function mm(float $v): string
    {
        return rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.') . 'mm';
    }

    /** Контурите за рязане – един общ слой, съседните етикети делят една и съща линия. */
    private static function contours(array $g, array $cells): string
    {
        $svg = '<svg class="cut" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $g['pageW'] . ' ' . $g['pageH'] . '" style="position:absolute;left:0;top:0;width:' . self::mm($g['pageW']) . ';height:' . self::mm($g['pageH']) . ';pointer-events:none">';
        foreach ($cells as $c) {
            $svg .= '<rect x="' . round($c['x'], 2) . '" y="' . round($c['y'], 2) . '" width="' . round($c['w'], 2) . '" height="' . round($c['h'], 2) . '" fill="none" stroke="#888" stroke-width="0.15"/>';
        }
        return $svg . '</svg>';
    }

    /**
     * Пълна HTML страница за печат.
     * @param string[] $cells вътрешният HTML на всеки етикет (по ред)
     * @param int $start позиция на първия етикет (1 = горе вляво), за започнати листове
     * @param array $opt padding (мм), border (контури за рязане)
     */
    public static function render(array $cells, array $g, int $start, string $title, string $cellCss = '', array $opt = []): string
    {
        $start = max(1, min($g['perPage'], $start));
        $padding = (float)($opt['padding'] ?? 1.5);
        $contour = !empty($opt['border']);
        $pages = [];
        $used = [];
        $idx = $start - 1;
        foreach ($cells as $html) {
            $page = intdiv($idx, $g['perPage']);
            $c = $g['cells'][$idx % $g['perPage']];
            $inner = 'width:' . self::mm($g['w']) . ';height:' . self::mm($g['h']) . ';padding:' . self::mm($padding);
            $pages[$page][] = '<div class="cell" style="left:' . self::mm($c['x']) . ';top:' . self::mm($c['y']) . ';width:' . self::mm($c['w']) . ';height:' . self::mm($c['h']) . '">'
                . '<div class="cin' . ($c['rot'] ? ' rot' : '') . '" style="' . $inner . '">' . $html . '</div></div>';
            $used[$page][] = $c;
            $idx++;
        }
        $body = '';
        foreach ($pages as $p => $list) {
            $body .= '<div class="sheet">' . implode('', $list) . ($contour ? self::contours($g, $used[$p]) : '') . '</div>';
        }
        if ($body === '') $body = '<div class="sheet"><p style="padding:20mm">' . htmlspecialchars(__('Nothing to print')) . '</p></div>';
        return self::page($title, $g, $body, $cellCss, count($cells));
    }

    /** Тестова страница за калибриране: мрежа през 1 и 10 мм, линийки, контури на клетките. */
    public static function calibration(array $g): string
    {
        $w = $g['pageW'];
        $h = $g['pageH'];
        $svg = '<svg class="grid" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $w . ' ' . $h . '" style="position:absolute;left:0;top:0;width:' . $w . 'mm;height:' . $h . 'mm">';
        for ($x = 0; $x <= $w; $x++) $svg .= '<line x1="' . $x . '" y1="0" x2="' . $x . '" y2="' . $h . '" stroke="' . ($x % 10 ? '#ddd' : '#888') . '" stroke-width="' . ($x % 10 ? 0.05 : 0.15) . '"/>';
        for ($y = 0; $y <= $h; $y++) $svg .= '<line x1="0" y1="' . $y . '" x2="' . $w . '" y2="' . $y . '" stroke="' . ($y % 10 ? '#ddd' : '#888') . '" stroke-width="' . ($y % 10 ? 0.05 : 0.15) . '"/>';
        for ($x = 10; $x < $w; $x += 10) $svg .= '<text x="' . ($x + 0.5) . '" y="3" font-size="2.2">' . $x . '</text>';
        for ($y = 10; $y < $h; $y += 10) $svg .= '<text x="0.5" y="' . ($y - 0.5) . '" font-size="2.2">' . $y . '</text>';
        // линийки по 100 мм за проверка на мащаба
        $svg .= '<line x1="20" y1="20" x2="120" y2="20" stroke="#c00" stroke-width="0.4"/><text x="20" y="18.5" font-size="3" fill="#c00">100 mm</text>';
        $svg .= '<line x1="20" y1="20" x2="20" y2="120" stroke="#c00" stroke-width="0.4"/>';
        foreach ($g['cells'] as $pos => $c) {
            $cx = $c['x'] + $c['w'] / 2;
            $cy = $c['y'] + $c['h'] / 2;
            $svg .= '<rect x="' . $c['x'] . '" y="' . $c['y'] . '" width="' . $c['w'] . '" height="' . $c['h'] . '" fill="none" stroke="#06c" stroke-width="0.3"/>'
                . '<line x1="' . ($cx - 2) . '" y1="' . $cy . '" x2="' . ($cx + 2) . '" y2="' . $cy . '" stroke="#06c" stroke-width="0.2"/>'
                . '<line x1="' . $cx . '" y1="' . ($cy - 2) . '" x2="' . $cx . '" y2="' . ($cy + 2) . '" stroke="#06c" stroke-width="0.2"/>'
                . '<text x="' . ($c['x'] + 1) . '" y="' . ($c['y'] + 3) . '" font-size="2.5" fill="#06c">' . ($pos + 1) . ($c['rot'] ? ' ↻' : '') . '</text>';
        }
        $svg .= '</svg>';
        return self::page(__('Calibration page'), $g, '<div class="sheet">' . $svg . '</div>', '', 0, true);
    }

    private static function page(string $title, array $g, string $body, string $css, int $count, bool $calibration = false): string
    {
        $size = $g['page'] === 'A4' ? 'A4 portrait' : $g['page'];
        $hint = __('Print at 100% / "Actual size", margins "None", without headers and footers.');
        $t = htmlspecialchars($title);
        $info = $calibration
            ? htmlspecialchars(__('Measure the red 100 mm lines. Put the printout over a sticker sheet against a window and enter the X/Y correction if the frames do not match.'))
            : htmlspecialchars(sprintf(__('%d labels'), $count) . ' · ' . $g['label']);
        return '<!DOCTYPE html><html lang="bg"><head><meta charset="utf-8"><title>' . $t . '</title><style>'
            . '@page{size:' . $size . ';margin:0}'
            . 'html,body{margin:0;padding:0}'
            . 'body{font-family:"DejaVu Sans","Liberation Sans",Arial,sans-serif;color:#000}'
            . '.sheet{position:relative;width:' . self::mm($g['pageW']) . ';height:' . self::mm($g['pageH']) . ';overflow:hidden;break-after:page;page-break-after:always;background:#fff}'
            . '.sheet:last-child{break-after:auto;page-break-after:auto}'
            . '.cell{position:absolute;overflow:hidden}'
            . '.cin{position:absolute;left:0;top:0;box-sizing:border-box;overflow:hidden}'
            // завъртане на 90° по часовниковата стрелка в клетка с разменени страни
            . '.cin.rot{transform-origin:0 0;transform:rotate(90deg) translateY(-100%)}'
            . '.toolbar{font:14px/1.4 Arial,sans-serif;padding:8px 12px;background:#fffbe6;border-bottom:1px solid #e0c060}'
            . '.toolbar button{font-size:14px;padding:4px 14px;margin-right:10px}'
            . '@media screen{body{background:#bbb}.sheet{margin:6mm auto;box-shadow:0 0 4px rgba(0,0,0,.4)}.cell{outline:0.2mm dashed #9ab}.cell.overflow{outline:0.6mm solid #d00}}'
            . '@media print{.toolbar{display:none}}'
            . $css
            . '</style></head><body>'
            . '<div class="toolbar"><button onclick="window.print()">' . htmlspecialchars(__('Print')) . '</button><b>' . $t . '</b> · ' . $info . '<br><small>' . htmlspecialchars($hint) . '</small></div>'
            . $body
            . '<script>(function(){document.querySelectorAll(".cin .fit").forEach(function(el){var box=el.closest(".cin"),fs=parseFloat(getComputedStyle(el).fontSize),min=parseFloat(el.getAttribute("data-min")||"6");'
            . 'while((box.scrollHeight>box.clientHeight+1||box.scrollWidth>box.clientWidth+1)&&fs>min){fs-=0.5;el.style.fontSize=fs+"px";}'
            . 'if(box.scrollHeight>box.clientHeight+1)box.closest(".cell").classList.add("overflow");});})();</script>'
            . '</body></html>';
    }
}
