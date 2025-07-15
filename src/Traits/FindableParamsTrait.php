<?php

namespace Findable\Traits;

use Illuminate\Support\Collection;

/**
 * Trait FindableParamsTrait
 *
 * Stores the internal state used when building an Elasticsearch query.
 * Acts as the shared parameter container used by the Getter and Setter traits.
 *
 * @package Findable\Traits
 *
 * @property string|null $index
 * @property object|null $model
 * @property int $size
 * @property int $page
 * @property int $from
 * @property bool $track_total_hits
 * @property string|null $scroll
 * @property Collection $must_query
 * @property Collection $should_query
 * @property Collection $must_not_query
 * @property Collection $filter
 * @property Collection $aggs
 * @property Collection $sort
 * @property array|null $script
 * @property Collection $rescore
 * @property string|null $collapse
 */
trait FindableParamsTrait
{
    // Core request options
    protected ?string $index = null;
    protected int $size = 10;
    protected int $page = 1;
    protected int $from = 0;
    protected bool $track_total_hits = true;
    protected ?string $scroll = null;

    // Query components
    protected Collection $must_query;
    protected Collection $should_query;
    protected Collection $must_not_query;
    protected Collection $filter;

    // Additional features
    protected Collection $aggs;
    protected Collection $sort;
    protected ?array $script = null;
    protected Collection $rescore;
    protected ?string $collapse = null;

    /**
     * Initializes all collection-based fields to empty collections.
     * Should be called in the constructor of the using class.
     */
    protected function initializeQueryParams(): void
    {
        $this->size = config('findable.default_size', 10);
        $this->page = 1;
        $this->from = 0;
        $this->track_total_hits = config('findable.default_track_total_hits', true);
        $this->scroll = null;

        $this->must_query = collect();
        $this->should_query = collect();
        $this->must_not_query = collect();
        $this->filter = collect();
        $this->aggs = collect();
        $this->sort = collect();
        $this->rescore = collect();
    }
}
