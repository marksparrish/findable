<?php

namespace Findable\DTOs;

class SearchResultDTO
{
    public readonly array $formatted_aggregations;

    public function __construct(
        public readonly array $hits,
        public readonly int $total,
        public readonly array $raw_aggregations,
        public readonly array $raw,
        public readonly array $params,
    ) {
        $this->formatted_aggregations = !empty($raw_aggregations)
            ? (new \Findable\Formatters\AggregationFormatter())->format($raw_aggregations)
            : [];
    }
}