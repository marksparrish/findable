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

    private $page = 1;
    private $pageName = 'page';


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
    private function search()
    {
        $this->setParams();
        $this->setResults($this->elasticsearchService->search($this->params));
        $this->setRaw();
        $this->setTotalHits();
        $this->setModels();
        $this->setAggregations();

        return $this;
    }

    public function paginate($perPage = 15)
    {
        $this->setSize($perPage);
        $this->search();
        return new FindablePaginationClass(
            $this->models,
            $this->total_hits,
            $this->getSize(),
            LengthAwarePaginator::resolveCurrentPage(),
        );
    }
    //     $this->setPage(LengthAwarePaginator::resolveCurrentPage($this->pageName));
    //     $this->search();

    //     $paginator = $this->createPaginator($this->models, $this->total_hits, $this->getSize());

    //     if ($this->aggregations) {
    //         foreach ($this->aggregations as $key => $value) {
    //             $paginator->aggregations[$key] = $value;
    //         }
    //     }

    //     if ($this->raw) {
    //         $paginator->raw = $this->raw;
    //     }
    //     if ($this->params) {
    //         $paginator->params = $this->params;
    //     }
    //     return $paginator;
    // }

    // returns the search results but just the models
    public function get()
    {
        $this->search();
        return $this->models;
    }

    // returns the first model in the search results
    public function first()
    {
        $this->search();
        return $this->models->first();
    }
}
