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

    private $model;
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

    // provide a basic search method
    public function setMustMatchAll()
    {
        $this->setMustQuery('match_all', ['boost' => 1]);

        return $this;
    }

    protected function runSearch()
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
        $this->runSearch();

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

    public function all()
    {
        ini_set('memory_limit', '256M');
        // $client is Elasticsearch\Client instance
        if (!$this->elasticsearchService->isAvailable()) {
            // Inform the user that Elasticsearch is currently not available
            return response()->json(['error' => 'Elasticsearch service is currently not available. Please try again later.'], 503);
        }
        $this->setScroll(true);
        $this->setSize(5000);
        $this->setParams();
        $pages = new SearchResponseIterator($this->elasticsearchService->getClient(), $this->params);
        $hits = new SearchHitIterator($pages);

        $this->models = collect([]);
        foreach ($hits as $hit) {
            $model = new $this->model();
            $model->fill($hit['_source']);
            $this->models->push($model);
        }
        return $this->models;
    }

    public function get()
    {
        $this->runSearch();
        return $this->models;
    }

    public function first()
    {
        $this->runSearch();
        return $this->models->first();
    }
}
