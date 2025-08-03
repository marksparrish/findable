<?php

namespace Tests\Unit\Findable;

use PHPUnit\Framework\TestCase;
use Findable\DTOs\SearchResultDTO;

class SearchResultTest extends TestCase
{
    /** @test */
    public function it_initializes_with_all_expected_properties()
    {
        $hits = [['id' => 1], ['id' => 2]];
        $total = 2;
        $raw_aggregations = ['genders' => ['buckets' => []]];
        $raw = ['hits' => ['total' => ['value' => 2]]];
        $params = ['index' => 'people'];

        $dto = new SearchResultDTO(
            hits: $hits,
            total: $total,
            raw_aggregations: $raw_aggregations,
            raw: $raw,
            params: $params
        );

        $this->assertEquals($hits, $dto->hits);
        $this->assertEquals($total, $dto->total);
        $this->assertEquals($raw_aggregations, $dto->raw_aggregations);
        $this->assertEquals($raw, $dto->raw);
        $this->assertEquals($params, $dto->params);
    }
}