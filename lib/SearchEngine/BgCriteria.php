<?php
/**
 * Заявката за търсене като поредица от условия, свързани с И / ИЛИ / И НЕ
 * (изчисляват се отляво надясно: ((р1 оп2 р2) оп3 р3)), плюс ограничения
 * (години, език, вид материал), които винаги се свързват с И.
 */

namespace SLiMS\SearchEngine;

class BgCriteria extends Criteria
{
    /** @var array<int,array{op:string,node:array}> */
    public array $rows = [];
    /** @var array{years?:array{0:int,1:int}, lang?:string[], gmd?:int[]} */
    public array $limits = [];
    /** Текстът, който се показва на читателя („Автор: … И Година: …“) */
    public string $display = '';
    /** Думите за оцветяване в резултатите */
    public array $highlight = [];

    public function isEmpty(): bool
    {
        return !$this->rows && !$this->limits;
    }

    public function addRow(string $op, array $node): void
    {
        $this->rows[] = ['op' => $this->rows ? $op : 'and', 'node' => $node];
    }
}
