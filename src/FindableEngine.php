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


        $this->setModels();  // this method sets the models
        $this->setRaw(); //
        $this->setTotalHits(); // this method sets the total hits
        $this->setAggregations(); // this method sets the aggregations
    }

    public function paginate($perPage = 15, $pageName = 'page', $page = null, $options = [])
    {
        $this->setPage(LengthAwarePaginator::resolveCurrentPage($pageName));
        $this->setSize($perPage);
        $this->setFrom(($this->getPage() - 1) * $perPage);

        $this->performSearch();

        $paginator = new FindablePaginationClass(
            $this->models,
            $this->total_hits,
            $this->getSize(),
            LengthAwarePaginator::resolveCurrentPage($pageName),
        );

        return $paginator
            ->setAggregations($this->aggregations ?? [])
            ->setRaw($this->raw ?? null)
            ->setParams($this->params ?? null);
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
