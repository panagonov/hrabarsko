<?php
/**
 * Търсачка за читателите (OPAC) по модела на COBISS+:
 * до 3 условия с И / ИЛИ / И НЕ, полета автор, заглавие, рубрика, издател, място, година,
 * ISBN, УДК, поредица, език, оригинално заглавие; ограничения години от–до, език, вид материал.
 *
 * Търси с LIKE в нормализирания текст (search_biblio.n_*), затова работи с кирилица
 * и с кратки думи (мир, ад), без настройки на MariaDB. Фасетите четат живите таблици.
 */

namespace SLiMS\SearchEngine;

use PDO;
use SLiMS\DB;

class BgCatalogEngine extends SearchBiblioEngine
{
    public array $searchable_fields = [
        'title', 'author', 'isbn', 'subject', 'location', 'gmd', 'colltype', 'publisher', 'callnumber', 'publishyear', 'class', 'notes',
    ];

    /** @var array<string,mixed> */
    private array $params = [];
    private int $pn = 0;

    private function bind($value, int $type = PDO::PARAM_STR): string
    {
        $name = ':p' . $this->pn++;
        $this->params[$name] = [$value, $type];
        return $name;
    }

    private function autoTruncate(): bool
    {
        $v = config('bg_search_auto_truncate', true);
        return !in_array($v, [false, 0, '0', 'false'], true);
    }

    /** Условие за една дума/фраза в нормализирана колона. */
    private function termSql(array $n, bool $negated): string
    {
        $col = 'sb.' . BgQueryParser::FIELDS[$n['field']];
        $auto = $this->autoTruncate() && !$negated;
        if (!empty($n['phrase'])) {
            $last = end($n['tokens']);
            $words = implode(' ', array_column($n['tokens'], 't'));
            $pattern = '% ' . $words . (($last['prefix'] ?? false) ? '%' : ' %');
            return $col . ' LIKE ' . $this->bind($pattern);
        }
        $parts = [];
        foreach ($n['tokens'] as $t) {
            // автоматично отрязване само за думи от 3+ знака (иначе „c“ би намерило всичко с „c…“)
            $pattern = '% ' . $t['t'] . (($t['prefix'] || ($auto && mb_strlen($t['t']) >= 3)) ? '%' : ' %');
            $parts[] = $col . ' LIKE ' . $this->bind($pattern);
        }
        return '(' . implode(' AND ', $parts) . ')';
    }

    private function nodeSql(array $n, bool $negated): string
    {
        switch ($n['type']) {
            case 'term':
                return $this->termSql($n, $negated);
            case 'year':
                return 'COALESCE(sb.year_int, 0) BETWEEN ' . $this->bind($n['from'], PDO::PARAM_INT) . ' AND ' . $this->bind($n['to'], PDO::PARAM_INT);
            case 'isbn':
                return 'sb.n_isbn LIKE ' . $this->bind('% ' . $n['value'] . '%');
            case 'udc':
                $v = trim(BgText::udc($n['value']));
                if ($v === '') return '1=0';
                if ($v[0] === '(' || $v[0] === '"' || $v[0] === '=') return 'sb.n_class LIKE ' . $this->bind('%' . $v . '%');
                return 'sb.n_class LIKE ' . $this->bind('% ' . $v . ($n['prefix'] ? '%' : ' %'));
            case 'lang':
                $in = implode(',', array_map(fn($id) => $this->bind($id), $n['ids']));
                return "COALESCE(b.language_id, '') IN ($in)";
            case 'callnumber':
                return 'sb.call_number LIKE ' . $this->bind($n['value'] . '%');
            case 'gmdName':
                return 'b.gmd_id IN (SELECT gmd_id FROM mst_gmd WHERE gmd_name = ' . $this->bind($n['value']) . ')';
            case 'locationName':
                return 'EXISTS (SELECT 1 FROM item i JOIN mst_location l ON l.location_id = i.location_id WHERE i.biblio_id = sb.biblio_id AND l.location_name = ' . $this->bind($n['value']) . ')';
            case 'colltypeName':
                return 'EXISTS (SELECT 1 FROM item i JOIN mst_coll_type ct ON ct.coll_type_id = i.coll_type_id WHERE i.biblio_id = sb.biblio_id AND ct.coll_type_name = ' . $this->bind($n['value']) . ')';
        }
        return '1=1';
    }

    /** Условията се изчисляват отляво надясно: ((р1 оп2 р2) оп3 р3). */
    private function rowsSql(BgCriteria $c): string
    {
        $sql = '';
        foreach ($c->rows as $r) {
            if ($sql === '') {
                // водещо „И НЕ“ без условие отляво – търси се всичко освен
                $sql = $r['op'] === 'not' ? 'NOT (' . $this->nodeSql($r['node'], true) . ')' : $this->nodeSql($r['node'], false);
                continue;
            }
            switch ($r['op']) {
                case 'or': $sql = '(' . $sql . ' OR ' . $this->nodeSql($r['node'], false) . ')'; break;
                case 'not': $sql = '(' . $sql . ' AND NOT (' . $this->nodeSql($r['node'], true) . '))'; break;
                default: $sql = '(' . $sql . ' AND ' . $this->nodeSql($r['node'], false) . ')';
            }
        }
        return $sql;
    }

    private function limitsSql(BgCriteria $c): array
    {
        $w = [];
        if (!empty($c->limits['years'])) {
            $w[] = 'sb.year_int BETWEEN ' . $this->bind($c->limits['years'][0], PDO::PARAM_INT) . ' AND ' . $this->bind($c->limits['years'][1], PDO::PARAM_INT);
        }
        if (!empty($c->limits['lang'])) {
            $w[] = "COALESCE(b.language_id, '') IN (" . implode(',', array_map(fn($v) => $this->bind($v), $c->limits['lang'])) . ')';
        }
        if (!empty($c->limits['gmd'])) {
            $w[] = 'b.gmd_id IN (' . implode(',', array_map(fn($v) => $this->bind((int)$v, PDO::PARAM_INT), $c->limits['gmd'])) . ')';
        }
        return $w;
    }

    /** Фасетите вляво (филтър в OPAC). */
    private function facetsSql(): array
    {
        $w = [];
        $queries = method_exists($this->filter, 'toArray') ? $this->filter->toArray() : [];
        foreach ($queries as $q) {
            [$key, $value] = $q;
            if ($key === 'boolean' || $key === 'sort') continue;
            $list = json_decode((string)$value, true);
            $list = is_array($list) ? $list : [$value];
            switch ($key) {
                case 'years':
                    [$a, $b] = array_map('intval', explode(';', (string)$value) + [0, 0]);
                    [$min, $max] = array_map('intval', $this->getYears() ?: [0, 0]);
                    if ($a <= $min && $b >= $max) break; // целият обхват – не изключваме записите без година
                    $w[] = 'sb.year_int BETWEEN ' . $this->bind(min($a, $b), PDO::PARAM_INT) . ' AND ' . $this->bind(max($a, $b), PDO::PARAM_INT);
                    break;
                case 'availability':
                    $w[] = "EXISTS (SELECT 1 FROM item i WHERE i.biblio_id = sb.biblio_id AND (i.item_status_id IS NULL OR i.item_status_id NOT IN ('WO','MIS'))
                             AND NOT EXISTS (SELECT 1 FROM loan l WHERE l.item_code = i.item_code AND l.is_lent = 1 AND l.is_return = 0))";
                    break;
                case 'attachment':
                    $mimes = [];
                    foreach ($list as $t) {
                        if ($t === 'pdf') $mimes[] = 'application/pdf';
                        if ($t === 'audio') $mimes[] = 'audio/mpeg';
                        if ($t === 'video') { $mimes[] = 'video/x-flv'; $mimes[] = 'video/mp4'; }
                    }
                    if ($mimes) {
                        $w[] = 'sb.biblio_id IN (SELECT DISTINCT bat.biblio_id FROM biblio_attachment bat JOIN files f ON f.file_id = bat.file_id WHERE f.mime_type IN ('
                            . implode(',', array_map(fn($m) => $this->bind($m), $mimes)) . '))';
                    }
                    break;
                case 'colltype':
                    $w[] = 'EXISTS (SELECT 1 FROM item i WHERE i.biblio_id = sb.biblio_id AND i.coll_type_id IN (' . implode(',', array_map(fn($v) => $this->bind((int)$v, PDO::PARAM_INT), $list)) . '))';
                    break;
                case 'location':
                    $w[] = 'EXISTS (SELECT 1 FROM item i WHERE i.biblio_id = sb.biblio_id AND i.location_id IN (' . implode(',', array_map(fn($v) => $this->bind((string)$v), $list)) . '))';
                    break;
                case 'gmd':
                    $w[] = 'b.gmd_id IN (' . implode(',', array_map(fn($v) => $this->bind((int)$v, PDO::PARAM_INT), $list)) . ')';
                    break;
                case 'lang':
                    $w[] = "COALESCE(b.language_id, '') IN (" . implode(',', array_map(fn($v) => $this->bind((string)$v), $list)) . ')';
                    break;
            }
        }
        return $w;
    }

    function buildSQL()
    {
        $this->params = [];
        $this->pn = 0;
        $c = $this->criteria instanceof BgCriteria ? $this->criteria : BgQueryParser::fromRequest([]);

        $select = 'SELECT sb.biblio_id, sb.title, sb.author, sb.topic, sb.image, sb.isbn_issn, sb.publisher, sb.publish_place, sb.publish_year, sb.labels, sb.input_date';
        $file_path = SB . config('template.dir', 'template') . DS . config('template.theme', 'default') . DS . 'custom_frontpage_record.inc.php';
        if (file_exists($file_path)) {
            include $file_path;
            $this->custom_fields = $custom_fields ?? [];
            foreach ($this->custom_fields as $field => $field_opts) {
                if ($field_opts[0] == 1 && !in_array($field, ['availability', 'isbn_issn'])) $select .= ", sb.$field";
            }
        }

        $where = ['sb.opac_hide = 0'];
        if (($r = $this->rowsSql($c)) !== '') $where[] = $r;
        $where = array_merge($where, $this->limitsSql($c), $this->facetsSql());
        $from = ' FROM search_biblio AS sb JOIN biblio AS b ON b.biblio_id = sb.biblio_id WHERE ' . implode(' AND ', $where);
        $countParams = $this->params;

        // подреждане
        $sort = $this->filter->sort ?? '';
        $order = 'sb.input_date DESC';
        switch ($sort) {
            case 'recently-added': $order = 'sb.input_date DESC'; break;
            case 'publish-year-newest': $order = 'sb.year_int DESC, sb.title ASC'; break;
            case 'publish-year-oldest': $order = 'COALESCE(sb.year_int, 9999) ASC, sb.title ASC'; break;
            case 'title-asc': $order = 'sb.title ASC'; break;
            case 'title-desc': $order = 'sb.title DESC'; break;
            case 'most-loaned': $order = '(SELECT COUNT(*) FROM loan l JOIN item i ON i.item_code = l.item_code WHERE i.biblio_id = sb.biblio_id) DESC, sb.title ASC'; break;
            default:
                // най-подходящи: дума в заглавието ×3, в автора ×2, другаде ×1
                $score = [];
                foreach (array_slice($c->highlight, 0, 5) as $word) {
                    $p = '% ' . $word . '%';
                    $score[] = '(sb.n_title LIKE ' . $this->bind($p) . ')*3 + (sb.n_author LIKE ' . $this->bind($p) . ')*2 + (sb.n_all LIKE ' . $this->bind($p) . ')';
                }
                if ($score) {
                    $select .= ', (' . implode(' + ', $score) . ') AS bg_score';
                    $order = 'bg_score DESC, sb.year_int DESC, sb.title ASC';
                }
        }
        $listParams = $this->params;

        $this->execute = $listParams;
        return [
            'count' => 'SELECT COUNT(sb.biblio_id)' . $from,
            'query' => $select . $from . ' ORDER BY ' . $order . ' LIMIT ' . (int)$this->limit . ' OFFSET ' . (int)$this->offset,
            'count_params' => $countParams,
            'query_params' => $listParams,
        ];
    }

    function getDocuments()
    {
        $start = microtime(true);
        $sql = $this->buildSQL();
        $this->dump(['count' => $sql['count'], 'query' => $sql['query']]);
        try {
            $db = DB::getInstance();
            $count = $db->prepare($sql['count']);
            foreach ($sql['count_params'] as $k => [$v, $t]) $count->bindValue($k, $v, $t);
            $query = $db->prepare($sql['query']);
            foreach ($sql['query_params'] as $k => [$v, $t]) $query->bindValue($k, $v, $t);
            if (!$count->execute() || !$query->execute()) {
                $info = $count->errorInfo()[2] ?? $query->errorInfo()[2] ?? 'unknown error';
                throw new \RuntimeException((string)$info);
            }
            $this->num_rows = (int)(($count->fetch(PDO::FETCH_NUM))[0] ?? 0);
            $this->documents = $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
            $this->num_rows = 0;
            $this->documents = [];
        }
        $this->query_time = round(microtime(true) - $start, 5);
    }
}
