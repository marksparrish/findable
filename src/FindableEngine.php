<?php

namespace Findable;

use Elastic\Elasticsearch\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Findable\Traits\FindableParamsTrait;
use Findable\Traits\FindableGetterTrait;
use Findable\Traits\FindableSetterTrait;
use Findable\DTOs\SearchResultDTO;
use Findable\Exceptions\FindableException;

/**
 * Class FindableEngine
 *
 * The core class that builds and executes Elasticsearch queries.
 * It uses internal traits to manage query parameters, formats, and fluent chaining.
 *
 * Supports both model-driven and ad hoc index-driven querying.
 *
 * @example
 * $engine = Model::finder()
 *     ->setSize(0)
 *     ->setAggs([...])
 *     ->paginate();
 *
 * @method string getIndex()
 * @method int getSize()
 * @method int getPage()
 * @method int getFrom()
 * @method bool getTrackTotalHits()
 * @method string|null getScroll()
 * @method array getMustQuery()
 * @method array getShouldQuery()
 * @method array getMustNotQuery()
 * @method array getFilter()
 * @method array getAggs()
 * @method array getSort()
 * @method array|null getScript()
 * @method array getRescore()
 * @method array getCollapse()
 *
 * @method $this setSize(int $size)
 * @method $this setPage(int $page)
 * @method $this setFrom(int $from)
 * @method $this setTrackTotalHits(bool $track)
 * @method $this setScroll(?string $scroll)
 * @method $this setMustQuery(array $must)
 * @method $this setShouldQuery(array $should)
 * @method $this setMustNotQuery(array $mustNot)
 * @method $this setFilter(array $filter)
 * @method $this setAggs(array $aggs)
 * @method $this setSort(array $sort)
 * @method $this setScript(?array $script)
 * @method $this setRescore(array $rescore)
 * @method $this setCollapse(string $field)
 * @method $this setParam(string $key, mixed $value)
 * @method $this setParams(array $params)
 *
 * @package Findable
 */
class FindableEngine
{
    use FindableParamsTrait;
    use FindableGetterTrait;
    use FindableSetterTrait;

    protected Client $client;

    /**
     * Initialize with an optional model or manually configured index.
     *
     * @param Client|null $client
     * @param object|string|null $model
     */
    public function __construct(?Client $client = null, protected object|string|null $model = null)
    {
        $this->initializeQueryParams();

        if (is_object($this->model) && property_exists($this->model, 'index')) {
            $this->index = $this->model->index;
        }

        $this->client = $client ?? App::make('elasticsearch.client');
    }
    
    // ... other methods remain the same ...

    /**
     * Manually set the target Elasticsearch index.
     *
     * @param string $index
     * @return $this
     * @noinspection PhpUnused
     */
    public function setIndex(string $index): static
    {
        $this->index = $index;
        return $this;
    }

    /**
     * Reset the internal query state.
     * Clears filters, aggs, sort, pagination, etc., and resets to config defaults.
     *
     * @return $this
     */
    public function reset(): static
    {
        $this->initializeQueryParams();
        return $this;
    }

    /**
     * Builds the full Elasticsearch request body from the current state.
     * Includes query clauses, aggs, sorting, script, rescore, collapse, etc.
     *
     * @return array
     * @throws \RuntimeException
     * @throws \Throwable
     */
    protected function buildRequestBody(): array
    {
        $body = [
            'query' => [
                'bool' => array_filter([
                    'must'     => $this->getMustQuery(),
                    'should'   => $this->getShouldQuery(),
                    'must_not' => $this->getMustNotQuery(),
                    'filter'   => $this->getFilter(),
                ]),
            ],
            'track_total_hits' => $this->getTrackTotalHits(),
            'from'             => $this->getFrom(),
            'size'             => $this->getSize(),
        ];

        if ($aggs = $this->getAggs()) {
            $body['aggs'] = $aggs;
        }

        if ($sort = $this->getSort()) {
            $body['sort'] = $sort;
        }

        if ($script = $this->getScript()) {
            $body['script'] = $script;
        }

        if ($rescore = $this->getRescore()) {
            $body['rescore'] = $rescore;
        }

        if ($collapse = $this->getCollapse()) {
            $body['collapse'] = $collapse;
        }

        return $body;
    }

    // ... other methods remain the same ...

    public function search(): SearchResultDTO
    {
        if (!$this->getIndex()) {
            throw new FindableException("FindableEngine requires an index to be set before querying.");
        }

        $params = [
            'index' => $this->getIndex(),
            'body' => $this->buildRequestBody(),
        ];

        if ($scroll = $this->getScroll()) {
            $params['scroll'] = $scroll;
        }

        $response = $this->client->search($params);
        $raw = $response->asArray();

        $items = $this->hydrateHits($raw['hits']['hits'] ?? []);
        if (!empty($this->relations)) {
            $items = $this->loadRelations($items);
        }

        return new SearchResultDTO(
            hits: $items->all(),
            total: is_array($raw['hits']['total'] ?? null)
                ? $raw['hits']['total']['value']
                : ($raw['hits']['total'] ?? 0),
            raw_aggregations: $raw['aggregations'] ?? [],
            raw: $raw,
            params: $params,
        );
    }

    public function paginate(): FindablePaginationClass
    {
        $page = $this->getPage();
        $size = $this->getSize();
        $from = ($page - 1) * $size;

        $this->setFrom($from);
        $result = $this->search();

        $paginator = new FindablePaginationClass(
            items: collect($result->hits),
            total: $result->total,
            perPage: $size,
            currentPage: $page
        );

        $paginator->raw = $result->raw;
        $paginator->params = $result->params;
        $paginator->setAggregations($result->raw_aggregations);

        return $paginator;
    }

    /**
     * Hydrate Elasticsearch hits into model instances
     */
    protected function hydrateHits(array $hits): Collection
    {
        return collect($hits)->map(function ($hit) {
            if (!is_object($this->model)) {
                return $hit;
            }

            $model = clone $this->model;
            $model->fill($hit['_source']);
            $model->setAttribute($model->getKeyName(), $hit['_id']);

            if (isset($hit['_score'])) {
                $model->documentScore = $hit['_score'];
            }

            return $model;
        });
    }

    /**
     * Load model relationships for a collection of hits
     */
    protected function loadRelations(Collection $items): Collection
    {
        if (empty($this->relations) || !is_object($this->model)) {
            return $items;
        }

        $ids = $items->pluck($this->model->getKeyName())->toArray();
        $modelClass = get_class($this->model);
        $dbModels = $modelClass::with($this->relations)
            ->whereIn($this->model->getKeyName(), $ids)
            ->get()
            ->keyBy($this->model->getKeyName());

        return $items->filter(function ($model) use ($dbModels) {
            return !$this->skipMissingModels || isset($dbModels[$model->getKey()]);
        })->map(function ($model) use ($dbModels) {
            $id = $model->getKey();
            if (isset($dbModels[$id])) {
                if (isset($model->documentScore)) {
                    $dbModels[$id]->documentScore = $model->documentScore;
                }
                return $dbModels[$id];
            }
            return $model;
        });
    }
}