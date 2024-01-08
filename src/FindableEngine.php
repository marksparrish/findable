<?php

namespace Findable;

use Illuminate\Support\Collection;
use Findable\Traits\FindableGetterTrait;
use Findable\Traits\FindableSetterTrait;
use Findable\Traits\FindableParamsTrait;
use Illuminate\Pagination\LengthAwarePaginator;

class FindableEngine
{
    use FindableGetterTrait, FindableSetterTrait, FindableParamsTrait;


    public $model;
    private $elasticsearchService;

    private $results;
    private $raw;
    private $params;
    private $total_hits;
    private ?Collection $models = null;
    private ?Collection $aggregations = null;

    public function __construct($model)
    {
        $this->model = $model;
        $this->elasticsearchService = app('elasticsearch.client');
    }

    // this method sets all of the search results
    // to the $results property
    // it is a private method and is called by the paginate() and get() methods
    private function performSearch()
    {
        // buiid the params array
        $this->setParams();

        // perform the search using the elasticsearch service with the params array
        $this->setResults($this->elasticsearchService->search($this->params));


        $this->setRaw(); // this method sets the raw results and must be first
        $this->setModels();  // this method sets the models
        $this->setTotalHits(); // this method sets the total hits
        $this->setAggregations(); // this method sets the aggregations
    }

    public function paginate($perPage = 15, $pageName = 'page', $page = null, $options = [])
    {
        $this->setPage(LengthAwarePaginator::resolveCurrentPage($pageName));
        if ($this->collapse) {
            $this->setSize(5000);
        } else {
            $this->setSize($perPage);
        }
        $this->setFrom(($this->getPage() - 1) * $perPage);

        $this->performSearch();

        $paginator = new FindablePaginationClass(
            $this->models->take($perPage),
            $this->collapse ? $this->models->count() : $this->total_hits,
            $perPage,
            LengthAwarePaginator::resolveCurrentPage($pageName),
            $pageName,
        );

        // need to see if aggregations are set and if
        // so then add them to the query
        if ($this->aggregations) {
            $formatedAggregations = [];

            foreach ($this->aggregations as $key => $value) {
                if (method_exists($this, 'format' . $key)) {
                    $formatedAggregations[$key] = $this->{'format' . $key}($value);
                } else {
                    $formatedAggregations[$key] = $value;
                }
            }
            $paginator->aggregations = $formatedAggregations;
        }
        if ($this->raw) {
            $paginator->raw = $this->raw;
        }
        if ($this->params) {
            $paginator->params = $this->params;
        }

        return $paginator;
    }

    // returns the search results but just the models
    public function get()
    {
        $this->performSearch();
        return $this->models;
    }

    // returns the first model in the search results
    public function first()
    {
        $this->performSearch();
        return $this->models->first();
    }
}
