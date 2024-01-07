<?php

namespace Findable\Traits;

use Illuminate\Support\Collection;

/**
 * Trait getterTrait
 * @package App\Elastic\Traits
 * @method string getIndex()
 * @method boolean getTrackTotalHits()
 * @method int getSize()
 * @method int getFrom()
 * @method array getMustQuery()
 * @method array getShouldQuery()
 * @method array getMustNotQuery()
 * @method array getFilter()
 * @method array getAggregation()
 * @method array getSort()
 * @method array getScript()
 *
 */

trait FindableGetterTrait
{

    private function getIndex()
    {
        if (!is_null($this->index)) {
            return $this->index;
        } else {
            return $this->model->index;
        }
    }

    private function getSize()
    {
        return $this->size;
    }

    private function getPage()
    {
        return $this->page;
    }

    private function getFrom()
    {
        if ($this->from) {
            return $this->from;
        } else {
            $page = $this->getPage() ?: 1;
            return ($page - 1) * $this->size;
        }
    }

    private function getTrackTotalHits()
    {
        return $this->track_total_hits;
    }

    private function getScroll()
    {
        return $this->scroll;
    }

    private function getMustQuery()
    {
        return $this->must_query->toArray();
    }

    private function getShouldQuery()
    {
        return $this->should_query->toArray();
    }

    private function getMustNotQuery()
    {
        return $this->must_not_query->toArray();
    }

    private function getFilter()
    {
        return $this->filter->toArray();
    }

    private function getAggs()
    {
        return $this->aggs->toArray();
    }

    private function getSort()
    {
        return $this->sort->toArray();
    }

    private function getScript()
    {
        return $this->script;
    }

    private function getRescore()
    {
        return $this->rescore->toArray();
    }
}
