<?php

namespace Findable\Traits;

use Illuminate\Support\Collection;

trait FindableSetterTrait
{
    /**
     * @param  string  $index
     * @return $this
     */
    public function setIndex(string $index)
    {
        $this->index = $index;

        return $this;
    }
    /**
     * @param  int  $size
     * @return $this
     */
    public function setSize(int $size)
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
    public function setTrackTotalHits(bool $track_total_hits = true)
    {
        $this->track_total_hits = $track_total_hits;

        return $this;
    }

    public function setScroll(bool $scroll = false)
    {
        $this->scroll = $scroll;

        return $this;
    }

    public function setPage(int $page = 1)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @param  mixed  $key
     * @param  mixed  $array
     */
    public function setMustQuery($queries)
    {
        $queries = is_array($queries) ? $queries : [$queries];
        $this->must_query = $this->must_query ?: new Collection();
        foreach ($queries as $query) {
            $this->must_query->push([
                array_key_first($query) => $query[array_key_first($query)]
            ]);
        }
        return $this;
    }

    /**
     * @param  mixed  $key
     * @param  mixed  $array
     */
    public function setShouldQuery($queries): self
    {
        $queries = is_array($queries) ? $queries : [$queries];
        $this->should_query = $this->should_query ?: new Collection();
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
     */
    public function setMustNotQuery($queries): self
    {
        $queries = is_array($queries) ? $queries : [$queries];
        $this->must_not_query = $this->must_not_query ?: new Collection();
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
     */
    public function setFilter($queries): self
    {
        $queries = is_array($queries) ? $queries : [$queries];
        $this->filter = $this->filter ?: new Collection();
        foreach ($queries as $query) {
            $this->filter->push([
                array_key_first($query) => $query[(array_key_first($query))]
            ]);
        }
        return $this;
    }

    /**
     * @param  mixed  $key
     * @param  mixed  $array
     */
    public function setAggs($queries)
    {
        $queries = is_array($queries) ? $queries : [$queries];
        $this->aggs = $this->aggs ?: new Collection();
        foreach ($queries as $query) {
            $this->aggs->put(array_key_first($query), $query[(array_key_first($query))]);
        }
        return $this;
    }

    /**
     * @param  mixed  $key
     * @param  mixed  $array
     */
    public function setSort($queries)
    {
        $queries = is_array($queries) ? $queries : [$queries];
        $this->sort = $this->sort ?: new Collection();
        foreach ($queries as $query) {
            $this->sort->push([
                array_key_first($query) => $query[(array_key_first($query))]
            ]);
        }
        return $this;
    }

    public function setCollapse($field)
    {
        $this->collapse = $field;
        return $this;
    }

    public function setRescore($array)
    {
        $this->rescore = $this->rescore ?: new Collection();
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
