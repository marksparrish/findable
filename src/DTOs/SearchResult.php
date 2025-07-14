<?php

namespace Findable\DTOs;

/**
 * Class SearchResult
 *
 * Wraps a raw Elasticsearch response into a structured, typed object.
 */
class SearchResult
{
    public function __construct(
        public array $hits,
        public int $total,
        public array $aggregations,
        public array $raw,
        public array $params,
    ) {}
}
