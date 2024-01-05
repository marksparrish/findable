<?php

namespace Findable\Traits;

/**
 * Trait setterTrait
 * @package Findable\Traits
 * @method void setIndex($index)
 * @method void setTrackTotalHits($track_total_hits)
 * @method void setSize($size)
 * @method void setFrom($from)
 * @method void setScroll($scroll)
 * @method void setPage($page)
 * @method void setPageName($pageName)
 *
 * @method void setMustQuery($key, $array)
 * @method void setShouldQuery($key, $array)
 * @method void setMustNotQuery($key, $array)
 * @method void setFilter($key, $array)
 * @method void setAggregation($key, $array)
 * @method void setSort($key, $array)
 * @method void setScript($script)
 *
 */

trait FindableSetterTrait
{
    /**
     * @param  string  $index
     * @return $this
     */
    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }
    /**
     * @param  int  $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @param  int  $from
     * @return $this
     */
    public function setFrom(int $from = 0)
    {
        $from = $this->size * ($this->page - 1);
        $this->from = $from;
        return $this;
    }

    /**
     * @param  bool  $track_total_hits
     * @return $this
     */
    public function setTrackTotalHits($track_total_hits = true)
    {
        $this->track_total_hits = $track_total_hits;

        return $this;
    }

    public function setScroll($scroll = false)
    {
        $this->scroll = $scroll;

        return $this;
    }

    public function setPage($page = 1)
    {
        $this->page = $page;

        return $this;
    }

    // /**
    // sets a must query in the search query
    //  * @param  mixed  $query
    //  * @return void
    //  */
    public function setMustQuery($queries)
    {
        // make query into an array if it is not already
        $queries = is_array($queries) ? $queries : [$queries];
        $this->must_query = $this->must_query ?: collect([]);
        foreach ($queries as $query) {
            $this->must_query->push([
                array_key_first($query) => $query[(array_key_first($query))]
            ]);
        }
        return $this;
    }

    /**
     * @param  mixed  $key
     * @param  mixed  $array
     * @return void
     */
    public function setShouldQuery($queries)
    {
        $queries = is_array($queries) ? $queries : [$queries];
        $this->should_query = $this->should_query ?: collect([]);
        foreach ($queries as $query) {
            $this->should_query->push([
                array_key_first($query) => $query[(array_key_first($query))]
            ]);
        }
        return $this;
    }

    /**
     * @param  mixed  $key
     * @param  mixed  $array
     * @return void
     */
    public function setMustNotQuery($queries)
    {
        $queries = is_array($queries) ? $queries : [$queries];
        $this->must_not_query = $this->must_not_query ?: collect([]);
        foreach ($queries as $query) {
            $this->must_not_query->push([
                array_key_first($query) => $query[(array_key_first($query))]
            ]);
        }
        return $this;
    }

    /**
     * @param  mixed  $key
     * @param  mixed  $array
     * @return void
     */
    public function setFilter($queries)
    {
        $queries = is_array($queries) ? $queries : [$queries];
        $this->filters = $this->filters ?: collect([]);
        foreach ($queries as $query) {
            $this->filters->push([
                array_key_first($query) => $query[(array_key_first($query))]
            ]);
        }
        return $this;
    }

    /**
     * @param  mixed  $key
     * @param  mixed  $array
     * @return void
     */
    public function setAggs($queries)
    {
        $queries = is_array($queries) ? $queries : [$queries];
        $this->aggs = $this->aggs ?: collect([]);
        foreach ($queries as $query) {
            $this->aggs->put(array_key_first($query), $query[(array_key_first($query))]);
        }
        return $this;
    }

    /**
     * @param  mixed  $key
     * @param  mixed  $array
     * @return void
     */
    public function setSort($queries)
    {
        $queries = is_array($queries) ? $queries : [$queries];
        $this->sort = $this->sort ?: collect([]);
        foreach ($queries as $query) {
            $this->sort->push([
                array_key_first($query) => $query[(array_key_first($query))]
            ]);
        }
        return $this;
    }

    public function setRescore($array)
    {
        $this->rescore = $this->rescore ?: collect([]);
        $this->rescore->push($array);
        return $this;
    }

    private function setResults($results)
    {
        $this->results = $results;
    }

    private function setRaw()
    {
        $this->raw = $this->results->asArray();
    }

    private function setTotalHits()
    {
        $this->total_hits = $this->results['hits']['total']['value'];
    }

    /** @return mixed  */
    private function setModels()
    {
        if ($this->size) {
            $documents = collect($this->raw['hits']['hits'])->pluck('_source');
            // $modelClass = get_class($this->model);

            $this->models = collect($documents)->map(function ($source) {
                $model = new $this->model;
                // creates model attibutes using the model's fillable array
                // you can limit the attributes that are created to those in the fillable array
                return $model->fill($source);
            });
        } else {
            $this->models = collect([]);
        }
    }

    private function setAggregations()
    {
        if (!isset($this->raw['aggregations'])) {
            $this->aggregations = null;
        } else {
            $this->aggregations = collect($this->raw['aggregations']);
        }
    }
}
