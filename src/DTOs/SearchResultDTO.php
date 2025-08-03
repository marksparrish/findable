<?php

namespace Findable\DTOs;

class SearchResultDTO
{
    public readonly array $formattedAggregations;

    public function __construct(
        public readonly array $hits,
        public readonly int $total,
        public readonly array $raw_aggregations,
        public readonly array $raw,
        public readonly array $params,
    ) {
        $this->formattedAggregations = !empty($raw_aggregations)
            ? (new \Findable\Formatters\AggregationFormatter())->format($raw_aggregations)
            : [];
    }
}