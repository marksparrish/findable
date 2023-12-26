<?php

namespace Findable;

use Elastic\Elasticsearch\Helper\Iterators\SearchHitIterator;
use Elastic\Elasticsearch\Helper\Iterators\SearchResponseIterator;
use Illuminate\Support\Collection;
use Findable\PaginateResults;
use Illuminate\Pagination\Paginator;

use Findable\Traits\FindableGetterTrait;
use Findable\Traits\FindableSetterTrait;
use Findable\Traits\FindableParamsTrait;
use Findable\Traits\FindableAggregationsFormatterTrait;

class FindableEngine
{
    use FindableGetterTrait, FindableSetterTrait, FindableParamsTrait, FindableAggregationsFormatterTrait;

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
    }

    public function paginate()
    {
        $this->setPage(Paginator::resolveCurrentPage($this->pageName));
        $this->search();

        $paginator = new PaginateResults($this->models, $this->total_hits, $this->getSize(), $this->getPage());

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

    public function get()
    {
        $this->search();
        return $this->models;
    }

    public function first()
    {
        $this->search();
        return $this->models->first();
    }
}
