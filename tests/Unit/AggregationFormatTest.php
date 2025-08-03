<?php

namespace Tests\Unit\Findable;

use Tests\TestCase;
use Findable\DTOs\SearchResultDTO;

class AggregationFormatTest extends TestCase
{
    public array $rawResponse = [
        'hits' => [
            'total' => ['value' => 55951],
            'hits' => []
        ],
        'aggregations' => [
            'TotalVoterCount' => [
                'value' => 55951
            ],
            'GenderCounts' => [
                'doc_count_error_upper_bound' => 0,
                'sum_other_doc_count' => 0,
                'buckets' => [
                    [
                        'key' => 'male',
                        'doc_count' => 26229
                    ],
                    [
                        'key' => 'female',
                        'doc_count' => 25756
                    ],
                    [
                        'key' => 'unknown',
                        'doc_count' => 3570
                    ]
                ]
            ],
            'TagPartyCounts' => [
                'doc_count_error_upper_bound' => 0,
                'sum_other_doc_count' => 0,
                'buckets' => [
                    [
                        'key' => 4,
                        'doc_count' => 26840,
                        'buckets' => [
                            'doc_count_error_upper_bound' => 0,
                            'sum_other_doc_count' => 0,
                            'buckets' => [
                                ['key' => 'REP', 'doc_count' => 11445],
                                ['key' => 'DEM', 'doc_count' => 7710],
                                ['key' => 'NAV', 'doc_count' => 5739]
                            ]
                        ]
                    ],
                    [
                        'key' => 56,
                        'doc_count' => 16842,
                        'buckets' => [
                            'doc_count_error_upper_bound' => 0,
                            'sum_other_doc_count' => 0,
                            'buckets' => [
                                ['key' => 'REP', 'doc_count' => 8421],
                                ['key' => 'DEM', 'doc_count' => 5614],
                                ['key' => 'NAV', 'doc_count' => 2807]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    public function testSingleValueAggregation()
    {
        $dto = new SearchResultDTO(
            hits: [],
            total: $this->rawResponse['hits']['total']['value'],
            raw_aggregations: $this->rawResponse['aggregations'],
            raw: $this->rawResponse,
            params: []
        );

        $this->assertEquals(
            55951,
            $dto->formattedAggregations['TotalVoterCount']
        );
    }

    public function testSimpleBucketAggregation()
    {
        $dto = new SearchResultDTO(
            hits: [],
            total: $this->rawResponse['hits']['total']['value'],
            raw_aggregations: $this->rawResponse['aggregations'],
            raw: $this->rawResponse,
            params: []
        );

        $expected = [
            'male' => 26229,
            'female' => 25756,
            'unknown' => 3570
        ];

        $this->assertEquals(
            $expected,
            $dto->formattedAggregations['GenderCounts']
        );
    }

    public function testNestedBucketAggregation()
    {
        $dto = new SearchResultDTO(
            hits: [],
            total: $this->rawResponse['hits']['total']['value'],
            raw_aggregations: $this->rawResponse['aggregations'],
            raw: $this->rawResponse,
            params: []
        );

        $expected = [
            [
                'key' => 4,
                'total' => 26840,
                'buckets' => [
                    ['key' => 'REP', 'doc_count' => 11445],
                    ['key' => 'DEM', 'doc_count' => 7710],
                    ['key' => 'NAV', 'doc_count' => 5739]
                ]
            ],
            [
                'key' => 56,
                'total' => 16842,
                'buckets' => [
                    ['key' => 'REP', 'doc_count' => 8421],
                    ['key' => 'DEM', 'doc_count' => 5614],
                    ['key' => 'NAV', 'doc_count' => 2807]
                ]
            ]
        ];

        $this->assertEquals(
            $expected,
            $dto->formattedAggregations['TagPartyCounts']
        );
    }
}