<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Findable\Traits\FindableSetterTrait;
use Illuminate\Support\Collection;

class DummySetter
{
    use FindableSetterTrait;

    public int $size;
    public int $from;
    public int $page;
    public Collection $aggs;
    public Collection $sort;
    public array $params = [];

    public function getParam(string $key)
    {
        return $this->params[$key] ?? null;
    }
}

class FindableSetterTraitTest extends TestCase
{
    /** @test */
    public function it_sets_individual_query_parameters()
    {
        $dummy = new DummySetter();
        $dummy->setSize(20)->setFrom(10)->setPage(2);

        $this->assertEquals(20, $dummy->size);
        $this->assertEquals(10, $dummy->from);
        $this->assertEquals(2, $dummy->page);
    }

    /** @test */
    public function it_sets_complex_query_parts()
    {
        $dummy = new DummySetter();
        $dummy->setAggs(['agg_key' => 'agg_value']);
        $dummy->setSort(['field' => 'asc']);

        $this->assertEquals(['agg_key' => 'agg_value'], $dummy->aggs->toArray());
        $this->assertEquals(['field' => 'asc'], $dummy->sort->toArray());
    }

    /** @test */
    public function it_supports_bulk_param_setting()
    {
        $dummy = new DummySetter();
        $dummy->setParams([
            'from' => 50,
            'size' => 25
        ]);

        $this->assertEquals(50, $dummy->from);
        $this->assertEquals(25, $dummy->size);
    }
}
