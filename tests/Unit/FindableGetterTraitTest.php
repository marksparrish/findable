<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Support\Collection;
use Findable\Traits\FindableGetterTrait;

class DummyModel
{
    public string $index;

    public function __construct()
    {
        $this->index = 'fallback_index';
    }
}

class DummyGetter
{
    use FindableGetterTrait;

    public int $size = 50;
    public int $page = 2;
    public int $from = 100;
    public bool $track_total_hits = true;

    public ?string $index = null;
    public Collection $aggs;
    public Collection $sort;
    public Collection $must_query;
    public Collection $should_query;
    public Collection $must_not_query;
    public Collection $filter;
    public Collection $rescore;

    public ?string $scroll = '5m';
    public ?array $script = ['scripted'];
    public string $collapse = 'some_field';

    public object $model;

    public function __construct()
    {
        $this->aggs = collect(['foo' => 'bar']);
        $this->sort = collect(['field' => 'desc']);
        $this->must_query = collect();
        $this->should_query = collect();
        $this->must_not_query = collect();
        $this->filter = collect();
        $this->rescore = collect();
        $this->model = new DummyModel;
    }

    public function callGetMustQuery(): array { return $this->getMustQuery(); }
    public function callGetShouldQuery(): array { return $this->getShouldQuery(); }
    public function callGetMustNotQuery(): array { return $this->getMustNotQuery(); }
    public function callGetFilter(): array { return $this->getFilter(); }
    public function callGetRescore(): array { return $this->getRescore(); }
    public function callGetAggs(): array { return $this->getAggs(); }
    public function callGetSort(): array { return $this->getSort(); }
    public function callGetCollapse(): array { return $this->getCollapse(); }
    public function callGetIndex(): string { return $this->getIndex(); }
    public function callGetScript(): ?array { return $this->getScript(); }
    public function callGetSize(): int { return $this->getSize(); }
    public function callGetPage(): int { return $this->getPage(); }
    public function callGetFrom(): int { return $this->getFrom(); }
    public function callGetScroll(): ?string { return $this->getScroll(); }
    public function callGetTrackTotalHits(): bool { return $this->getTrackTotalHits(); }
}

class FindableGetterTraitTest extends TestCase
{
    /** @test */
    public function it_returns_basic_values()
    {
        $getter = new DummyGetter;

        $this->assertEquals(50, $getter->callGetSize());
        $this->assertEquals(2, $getter->callGetPage());
        $this->assertEquals(100, $getter->callGetFrom());
        $this->assertTrue($getter->callGetTrackTotalHits());
        $this->assertEquals('5m', $getter->callGetScroll());
        $this->assertEquals(['scripted'], $getter->callGetScript());
    }

    /** @test */
    public function it_returns_collections_as_arrays()
    {
        $getter = new DummyGetter;

        $this->assertEquals(['foo' => 'bar'], $getter->callGetAggs());
        $this->assertEquals(['field' => 'desc'], $getter->callGetSort());
        $this->assertIsArray($getter->callGetMustQuery());
        $this->assertIsArray($getter->callGetShouldQuery());
        $this->assertIsArray($getter->callGetMustNotQuery());
        $this->assertIsArray($getter->callGetFilter());
        $this->assertIsArray($getter->callGetRescore());
    }

    /** @test */
    public function it_returns_index_from_property_or_model()
    {
        $getter = new DummyGetter;
        $this->assertEquals('fallback_index', $getter->callGetIndex());

        $getter->index = 'custom_index';
        $this->assertEquals('custom_index', $getter->callGetIndex());
    }

    /** @test */
    public function it_returns_collapse_structure()
    {
        $getter = new DummyGetter;
        $this->assertEquals(['field' => 'some_field'], $getter->callGetCollapse());
    }
}
