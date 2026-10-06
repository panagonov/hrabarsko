<?php
/**
 * Каталожна картичка по образеца от наръчника на НБКМ (с. 49) и ISBD:
 *  – горе вляво сигнатура (УДК за подреждане / [префикс] авторски знак) или инвентарен номер;
 *  – редна дума в получер: автор (1–3 автори), условна/колективна редна дума или
 *    първата дума от заглавието (повече от 3 автори или без автор);
 *  – описание с условните разделителни знаци на ISBD;
 *  – ISBN : цена; долу вдясно пълният УДК индекс.
 */

namespace SLiMS\Catalog;

use SLiMS\Bg\Db;
use SLiMS\Bg\Inventory;

class BgCatalogCard
{
    private static function h($v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }

    /** Данните на записа: описание, автори, рубрики, екземпляри. */
    public static function load(int $biblioId): ?array
    {
        $b = Db::row('SELECT b.*, g.gmd_name, p.publisher_name, pl.place_name FROM biblio b
                       LEFT JOIN mst_gmd g ON g.gmd_id = b.gmd_id
                       LEFT JOIN mst_publisher p ON p.publisher_id = b.publisher_id
                       LEFT JOIN mst_place pl ON pl.place_id = b.publish_place_id
                      WHERE b.biblio_id = ?', [$biblioId]);
        if (!$b) return null;
        $b['authors'] = Db::all('SELECT a.author_name, a.authority_type, ba.level FROM biblio_author ba JOIN mst_author a ON a.author_id = ba.author_id
                                  WHERE ba.biblio_id = ? ORDER BY ba.level, a.author_id', [$biblioId]);
        $b['topics'] = array_column(Db::all('SELECT t.topic FROM biblio_topic bt JOIN mst_topic t ON t.topic_id = bt.topic_id WHERE bt.biblio_id = ? ORDER BY bt.level, t.topic', [$biblioId]), 'topic');
        $b['items'] = Db::all("SELECT i.item_code, i.location_id, i.call_number, i.volume, l.location_name FROM item i LEFT JOIN mst_location l ON l.location_id = i.location_id
                                WHERE i.biblio_id = ? AND (i.item_status_id IS NULL OR i.item_status_id <> 'WO') ORDER BY i.item_id", [$biblioId]);
        return $b;
    }

    /** „Фамилия, Име“ → „Име Фамилия“ (за сведението за отговорност). */
    public static function direct(string $name): string
    {
        if (preg_match('/^([^,]+),\s*(.+)$/u', trim($name), $m)) return trim($m[2]) . ' ' . trim($m[1]);
        return trim($name);
    }

    /**
     * Редната дума: ['type' => 'person'|'corporate'|'title', 'text' => ...]
     * Създатели са авторите на ниво 1–2 (основен и съавтор); редакторите, преводачите,
     * илюстраторите не се броят.
     */
    public static function heading(array $b): array
    {
        $authors = $b['authors'];
        foreach ($authors as $a) {
            if ((int)$a['level'] === 1 && in_array($a['authority_type'], ['o', 'c'], true)) {
                return ['type' => 'corporate', 'text' => $a['author_name']];
            }
        }
        $creators = array_values(array_filter($authors, fn($a) => (int)$a['level'] <= 2 && !in_array($a['authority_type'], ['o', 'c'], true)));
        if (count($creators) >= 1 && count($creators) <= 3) {
            return ['type' => 'person', 'text' => $creators[0]['author_name']];
        }
        return ['type' => 'title', 'text' => ''];
    }

    private static function clean(?string $s): string
    {
        return trim(preg_replace('/\s+/u', ' ', strip_tags((string)$s)));
    }

    /** Свързване на областите: „. – “, а ако предходната завършва с точка – само „ – “. */
    private static function join(array $areas): string
    {
        $out = '';
        foreach ($areas as $area) {
            $area = trim($area);
            if ($area === '') continue;
            if ($out === '') { $out = $area; continue; }
            $out .= preg_match('/[.!?…]$/u', $out) ? ' – ' : '. – ';
            $out .= $area;
        }
        return $out;
    }

    /** Описанието по ISBD (без редната дума и ISBN), като чист текст. */
    public static function description(array $b): string
    {
        $title = rtrim(self::clean($b['title']), ' /:;,.');
        $sor = self::clean($b['sor']);
        if ($sor === '') {
            // колективните/условните редни думи (закони, институции) не се повтарят след „/“
            $creators = array_values(array_filter($b['authors'], fn($a) => (int)$a['level'] <= 2 && !in_array($a['authority_type'], ['o', 'c'], true)));
            if ($creators) {
                $names = array_map(fn($a) => self::direct($a['author_name']), array_slice($creators, 0, 3));
                $sor = count($creators) > 3 ? $names[0] . ' [и др.]' : implode(', ', $names);
            }
        }
        $area1 = $title . ($sor !== '' ? ' / ' . $sor : '');

        $place = self::clean($b['place_name']);
        $publisher = self::clean($b['publisher_name']);
        $year = self::clean($b['publish_year']);
        $area4 = '';
        if ($place !== '' || $publisher !== '' || $year !== '') {
            $area4 = ($place !== '' ? $place : '[Б. м.]') . ' : ' . ($publisher !== '' ? $publisher : '[Б. и.]') . ', ' . ($year !== '' ? $year : '[Б. г.]');
            $printing = self::clean($b['printing'] ?? '');
            if ($printing !== '') $area4 .= ' (' . $printing . ')';
        }
        $series = self::clean($b['series_title']);
        $area6 = '';
        if ($series !== '') {
            $num = self::clean($b['series_number'] ?? '');
            $area6 = '(' . $series . ($num !== '' ? ' ; ' . $num : '') . ')';
        }
        return self::join([$area1, self::clean($b['edition']), $area4, self::clean($b['collation']), $area6]);
    }

    /** Кратките бележки (дългите анотации не се слагат на картичката). */
    public static function notes(array $b): string
    {
        $n = self::clean($b['notes']);
        return mb_strlen($n) <= 160 ? $n : '';
    }

    public static function isbnLine(array $b): string
    {
        $isbn = self::clean($b['isbn_issn']);
        $price = self::clean($b['price_terms'] ?? '');
        if ($isbn === '' && $price === '') return '';
        return ($isbn !== '' ? 'ISBN: ' . $isbn : '') . ($price !== '' ? ($isbn !== '' ? ' : ' : '') . $price : '');
    }

    /** Горе вляво: сигнатура (по образеца) или инвентарен номер. */
    public static function signatureHtml(array $b, string $source): string
    {
        $first = $b['items'][0] ?? null;
        if ($source === 'inventory' || ($source === 'auto' && trim((string)$b['call_number']) === '' && $first)) {
            return $first ? self::h($first['item_code']) : '';
        }
        $sig = Inventory::signatureParts($first['location_id'] ?? null, $b['call_number'], $b['author_mark'] ?? '');
        return implode('<br>', array_map([self::class, 'h'], array_filter([$sig['udc'], $sig['line2']], fn($l) => $l !== '')));
    }

    /**
     * HTML на картичките за един запис.
     * Комплектът за картотеката (Наредба № 3 и практиката на НБКМ):
     *  main – основна картичка за азбучния каталог (редна дума), с инвентарните номера;
     *  systematic – за систематичния каталог (подрежда се по УДК индекса най-горе);
     *  shelf – топографска (по сигнатура, с инвентарните номера и отделите);
     *  title / author / subject – допълнителни (за заглавие, съавтори, предметни рубрики).
     * @param array $opt card_types; signature_source: auto|call_number|inventory
     */
    public static function cards(array $b, array $opt): array
    {
        $heading = self::heading($b);
        $desc = self::description($b);
        $sig = self::signatureHtml($b, $opt['signature_source'] ?? 'auto');
        $notes = self::notes($b);
        $isbn = self::isbnLine($b);
        $udc = self::clean($b['classification']);
        $numbers = array_map(fn($i) => $i['item_code'], array_filter($b['items'], fn($i) => preg_match('/^\d+$/', (string)$i['item_code'])));
        $inv = $numbers ? 'Инв. №: ' . Inventory::formatRanges($numbers) : '';

        // основното описание с редната дума
        if ($heading['type'] === 'title') {
            // първата дума от заглавието е редна дума – в получер, на същия ред
            if (preg_match('/^(\P{L}*)([\p{L}\p{N}]+)(.*)$/us', $desc, $m)) {
                $mainBody = '<div class="ds">' . self::h($m[1]) . '<b>' . self::h($m[2]) . '</b>' . self::h($m[3]) . '</div>';
            } else {
                $mainBody = '<div class="ds">' . self::h($desc) . '</div>';
            }
        } else {
            $mainBody = '<div class="hd"><b>' . self::h($heading['text']) . '</b></div><div class="ds ind">' . self::h($desc) . '</div>';
        }
        $tail = ($notes !== '' ? '<div class="nt ind">' . self::h($notes) . '</div>' : '')
            . ($isbn !== '' ? '<div class="isbn">' . self::h($isbn) . '</div>' : '');

        $make = function (string $extraHeading, bool $withInv, string $tag = '', string $extraBody = '') use ($sig, $mainBody, $tail, $udc, $inv) {
            return '<div class="card">'
                . ($tag !== '' ? '<div class="ct">' . self::h($tag) . '</div>' : '')
                . ($sig !== '' ? '<div class="sig">' . $sig . '</div>' : '')
                . '<div class="body fit" data-min="7">'
                . ($extraHeading !== '' ? '<div class="ah"><b>' . self::h($extraHeading) . '</b></div>' : '')
                . $mainBody . $tail . $extraBody . '</div>'
                . ($withInv && $inv !== '' ? '<div class="inv">' . self::h($inv) . '</div>' : '')
                . ($udc !== '' ? '<div class="udc">' . self::h($udc) . '</div>' : '')
                . '</div>';
        };

        $types = $opt['card_types'] ?? ['main'];
        $cards = [];
        if (in_array('main', $types, true)) $cards[] = $make('', true);
        if (in_array('systematic', $types, true) && $udc !== '') {
            // систематичният каталог се подрежда по УДК – индексът е най-горе
            $cards[] = $make($udc, false, 'систематичен');
        }
        if (in_array('shelf', $types, true) && $b['items']) {
            // топографският каталог следва подредбата на рафтовете – по сигнатура, с всички екземпляри
            $lines = [];
            foreach ($b['items'] as $it) {
                $lines[] = $it['item_code'] . ($it['volume'] !== null && $it['volume'] !== '' ? ' (Т. ' . preg_replace('/^Т\.?\s*/u', '', $it['volume']) . ')' : '')
                    . ($it['location_name'] ? ' – ' . $it['location_name'] : '');
            }
            $cn = trim((string)($b['items'][0]['call_number'] ?: $b['call_number']));
            $cards[] = $make($cn, false, 'топографски', '<div class="invl">Инв. №: ' . self::h(implode('; ', $lines)) . '</div>');
        }
        if (in_array('title', $types, true) && $heading['type'] !== 'title') {
            $cards[] = $make(rtrim(self::clean($b['title']), ' /:;,.'), false, 'заглавие');
        }
        if (in_array('author', $types, true)) {
            $creators = array_values(array_filter($b['authors'], fn($a) => (int)$a['level'] <= 2 && !in_array($a['authority_type'], ['o', 'c'], true)));
            foreach (array_slice($creators, 1, 2) as $a) $cards[] = $make($a['author_name'], false, 'съавтор');
        }
        if (in_array('subject', $types, true)) {
            foreach ($b['topics'] as $t) $cards[] = $make($t, false, 'рубрика');
        }
        return $cards;
    }

    /**
     * Празна картичка с разграфени полета – за описване и класифициране на ръка
     * (когато няма компютър), после данните се въвеждат в програмата.
     */
    public static function blank(): string
    {
        $line = fn($label, $w = '') => '<div class="bl"><span>' . self::h($label) . '</span><i' . ($w ? ' style="width:' . $w . '"' : '') . '></i></div>';
        return '<div class="card blank">'
            . '<div class="bgrid">'
            . '<div class="bsig">' . $line('УДК (рафт)', '22mm') . $line('Отдел / авт. знак', '22mm') . '</div>'
            . '<div class="bmain">'
            . $line('Редна дума') . $line('Заглавие : подзаглавие / отговорност') . '<div class="bl"><i></i></div>'
            . $line('Изд.') . $line('Място : Издател, Година') . $line('Обем : ил. ; см') . $line('(Поредица ; №)')
            . $line('ISBN : цена') . $line('Рубрики / ключови думи')
            . '</div></div>'
            . '<div class="bfoot">' . $line('Инв. №', '30mm') . $line('УДК (пълен)', '35mm') . '</div>'
            . '</div>';
    }

    /** CSS за картичката 125 × 75 мм. */
    public static function css(int $fontPt = 10, bool $border = true): string
    {
        return '.cell{padding:0 !important}'
            . '.card{position:relative;box-sizing:border-box;width:100%;height:100%;padding:5mm 6mm 9mm 6mm;font-family:"Times New Roman","Liberation Serif","DejaVu Serif",serif;font-size:' . $fontPt . 'pt;line-height:1.25;'
            . ($border ? 'border:0.2mm solid #555;' : '') . '}'
            . '.card .sig{font-size:' . $fontPt . 'pt;line-height:1.2;margin-bottom:2mm;white-space:nowrap}'
            . '.card .body{font-size:' . $fontPt . 'pt}'
            . '.card .ah{margin-bottom:1mm}'
            . '.card .hd{margin-bottom:0.5mm}'
            . '.card .ind{text-indent:6mm}'
            . '.card .isbn{margin-top:1mm;padding-left:12mm}'
            . '.card .udc{position:absolute;right:6mm;bottom:3mm;font-size:' . $fontPt . 'pt}'
            . '.card .inv{position:absolute;left:6mm;bottom:3mm;font-size:' . max(7, $fontPt - 2) . 'pt;color:#333;max-width:55%}'
            . '.card .invl{margin-top:1.5mm;font-size:' . max(7, $fontPt - 1) . 'pt}'
            . '.card .ct{position:absolute;right:4mm;top:2mm;font-size:6.5pt;color:#888;font-family:Arial,sans-serif;text-transform:uppercase;letter-spacing:.3pt}'
            // празна картичка
            . '.card.blank{padding:4mm 5mm;font-family:Arial,sans-serif;font-size:7pt;color:#444}'
            . '.card.blank .bgrid{display:flex;gap:3mm}'
            . '.card.blank .bsig{width:30mm;flex:none;border-right:0.2mm dotted #aaa;padding-right:2mm}'
            . '.card.blank .bmain{flex:1}'
            . '.card.blank .bl{display:flex;align-items:flex-end;gap:1.5mm;height:5.6mm}'
            . '.card.blank .bl span{white-space:nowrap}'
            . '.card.blank .bl i{flex:1;border-bottom:0.2mm dotted #777;height:4mm}'
            . '.card.blank .bfoot{position:absolute;left:5mm;right:5mm;bottom:3mm;display:flex;justify-content:space-between}'
            // място за отвора на пръта в каталожното чекмедже (долу в средата)
            . '.card::after{content:"";position:absolute;left:50%;bottom:2mm;width:6mm;height:6mm;margin-left:-3mm}';
    }
}
