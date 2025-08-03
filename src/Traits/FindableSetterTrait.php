<?php

namespace Findable\Traits;

use Illuminate\Support\Collection;

/**
 * Trait FindableSetterTrait
 *
 * Handles setter methods for Elasticsearch query parameters.
 * Used internally by the Findable engine to build query state.
 *
 * @package Findable\Traits
 *
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
trait FindableSetterTrait
{
    public function setSize(int $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function setPage(int $page): static
    {
        $this->page = $page;
        return $this;
    }

    public function setFrom(int $from): static
    {
        $this->from = $from;
        return $this;
    }

    public function setTrackTotalHits(bool $track): static
    {
        $this->track_total_hits = $track;
        return $this;
    }

    public function setScroll(?string $scroll): static
    {
        $this->scroll = $scroll;
        return $this;
    }

    public function setMustQuery(array $must): static
    {
        $this->must_query = collect($must);
        return $this;
    }

    public function setShouldQuery(array $should): static
    {
        $this->should_query = collect($should);
        return $this;
    }

    public function setMustNotQuery(array $mustNot): static
    {
        $this->must_not_query = collect($mustNot);
        return $this;
    }

    public function setFilter(array $filter): static
    {
        $this->filter = collect($filter);
        return $this;
    }

    public function setAggs(array $aggs): static
    {
        if (array_is_list($aggs)) {
            $merged = [];
            foreach ($aggs as $agg) {
                $merged = array_merge($merged, $agg);
            }
            $this->aggs = collect($merged);
        } else {
            $this->aggs = collect($aggs);
        }
        return $this;
    }

    public function setSort(array $sort): static
    {
        $this->sort = collect($sort);
        return $this;
    }

    public function setScript(?array $script): static
    {
        $this->script = $script;
        return $this;
    }

    public function setRescore(array $rescore): static
    {
        $this->rescore = collect($rescore);
        return $this;
    }

    public function setCollapse(string $field): static
    {
        $this->collapse = $field;
        return $this;
    }

    /**
     * Dynamically assign a property based on a key-value pair.
     *
     * This method safely assigns scalar or collection-based query parameters.
     * For keys that are internally expected to be Laravel Collections,
     * the input array will be wrapped in a Collection to ensure consistency.
     *
     * Only keys listed in $collectionKeys will be wrapped — this avoids
     * unintentionally converting scalars like 'size' or complex objects like 'script'.
     */
    public function setParam(string $key, mixed $value): static
    {
        // Keys that should be cast to Laravel Collections
        $collectionKeys = [
            'must_query',      // array of "must" filters
            'should_query',    // array of "should" filters
            'must_not_query',  // array of "must not" filters
            'filter',          // array of additional filters
            'aggs',            // aggregation config
            'sort',            // sorting fields
            'rescore',         // optional rescore clauses
        ];

        if (in_array($key, $collectionKeys, true) && is_array($value)) {
            $this->{$key} = collect($value);
        } else {
            $this->{$key} = $value;
        }

        return $this;
    }

    /**
     * Bulk-assign multiple values using setParam internally.
     */
    public function setParams(array $params): static
    {
        foreach ($params as $key => $value) {
            $this->setParam($key, $value);
        }

        return $this;
    }
}
