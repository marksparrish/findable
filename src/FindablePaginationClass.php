<?php

namespace Findable;

use Illuminate\Pagination\LengthAwarePaginator;
use Findable\Formatters\AggregationFormatter;

/**
 * Class FindablePaginationClass
 *
 * Extends Laravel's LengthAwarePaginator to add Elasticsearch-specific metadata:
 * - Raw response from the ES client
 * - Request parameters sent
 * - Parsed top-level aggregations
 *
 * This allows Livewire components and views to access:
 * - Standard paginated items via $paginator->items()
 * - Aggregations via $paginator->aggregations
 * - Request body and raw results for debugging or reuse
 *
 * @property array $raw
 * @property array $params
 * @property array $aggregations
 *
 * @package Findable
 */
class FindablePaginationClass extends LengthAwarePaginator
{
    /**
     * Raw Elasticsearch response.
     *
     * @var array
     */
    public array $raw = [];

    /**
     * The request parameters (body and index).
     *
     * @var array
     */
    public array $params = [];

    /**
     * Top-level aggregations.
     *
     * @var array
     */
    public array $aggregations = [];

    /**
     * Create a new paginated response with ES metadata attached.
     *
     * @param iterable $items        Paginated hits (typically $response['hits']['hits'])
     * @param int $total             Total hits
     * @param int $perPage           Per-page limit
     * @param int $currentPage       Current page number
     * @param string $pageName       Query string page key
     */
    public function __construct($items, $total, $perPage, $currentPage, $pageName = 'page')
    {
        parent::__construct($items, $total, $perPage, $currentPage, [
            'path' => static::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * Get the raw hits array (same as items()).
     */
    public function hits(): array
    {
        return $this->items->all();
    }

    /**
     * Get formatted aggregations
     */
    public function formattedAggregations(): array
    {
        return (new AggregationFormatter())->format($this->aggregations);
    }

}
