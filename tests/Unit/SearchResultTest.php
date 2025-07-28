<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Findable\DTOs\SearchResult;

class SearchResultTest extends TestCase
{
    /** @test */
    public function it_initializes_with_all_expected_properties()
    {
        $hits = [['id' => 1], ['id' => 2]];
        $total = 2;
        $aggregations = ['genders' => ['buckets' => []]];
        $raw = ['hits' => ['total' => ['value' => 2]]];
        $params = ['index' => 'people'];

        $dto = new SearchResult(
            hits: $hits,
            total: $total,
            aggregations: $aggregations,
            raw: $raw,
            params: $params
        );

        $this->assertEquals($hits, $dto->hits);
        $this->assertEquals($total, $dto->total);
        $this->assertEquals($aggregations, $dto->aggregations);
        $this->assertEquals($raw, $dto->raw);
        $this->assertEquals($params, $dto->params);
    }
}
