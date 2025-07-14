<?php

namespace Findable\Traits;

use Illuminate\Support\Collection;

/**
 * Trait FindableGetterTrait
 *
 * Provides accessors for query components used in Elasticsearch queries.
 *
 * @package Findable\Traits
 *
 * @property string $index
 * @property object $model
 * @property int $size
 * @property int $page
 * @property int $from
 * @property bool $track_total_hits
 * @property string|null $scroll
 * @property Collection|null $must_query
 * @property Collection|null $should_query
 * @property Collection|null $must_not_query
 * @property Collection|null $filter
 * @property Collection|null $aggs
 * @property Collection|null $sort
 * @property Collection|null $rescore
 * @property string|null $collapse
 * @property array|null $script
 *
 */
trait FindableGetterTrait
{
    protected function getIndex(): string
    {
        return $this->index ?? $this->model->index;
    }

    protected function getSize(): int
    {
        return $this->size;
    }

    protected function getPage(): int
    {
        return $this->page;
    }

    protected function getFrom(): int
    {
        return $this->from;
    }

    protected function getTrackTotalHits(): bool
    {
        return $this->track_total_hits;
    }

    protected function getScroll(): ?string
    {
        return $this->scroll;
    }

    protected function getMustQuery(): array
    {
        return $this->must_query?->toArray() ?? [];
    }

    protected function getShouldQuery(): array
    {
        return $this->should_query?->toArray() ?? [];
    }

    protected function getMustNotQuery(): array
    {
        return $this->must_not_query?->toArray() ?? [];
    }

    protected function getFilter(): array
    {
        return $this->filter?->toArray() ?? [];
    }

    protected function getAggs(): array
    {
        return $this->aggs?->toArray() ?? [];
    }

    protected function getSort(): array
    {
        return $this->sort?->toArray() ?? [];
    }

    protected function getScript(): ?array
    {
        return $this->script;
    }

    protected function getRescore(): array
    {
        return $this->rescore?->toArray() ?? [];
    }

    protected function getCollapse(): array
    {
        return $this->collapse ? ['field' => $this->collapse] : [];
    }
}
