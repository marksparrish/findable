<?php

namespace Findable;

use Elastic\Elasticsearch\Helper\Iterators\SearchResponseIterator;
use Elastic\Elasticsearch\Helper\Iterators\SearchHitIterator;
use Elastic\Elasticsearch\Client;
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
     * @param Client $client
     * @param object|string|null $model
     */
    public function __construct(Client $client, protected object|string|null $model = null)
    {
        $this->initializeQueryParams();

        if (is_object($this->model) && property_exists($this->model, 'index')) {
            $this->index = $this->model->index;
        }

        $this->client = $client;
    }

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

        return new SearchResultDTO(
            hits: $items,
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
        // need to generate the from value
        $page = $this->getPage() || 1;
        $size = $this->getSize();
        $from = ($page - 1) * $size;

        $this->setFrom($from);
        $result = $this->search();

        $paginator = new FindablePaginationClass(
            items: $result->hits,
            total: $result->total,
            perPage: $size,
            currentPage: $page
        );

        // add to the paginator the raw response and the params and the aggregations if they exist
        $paginator->raw = $result->raw;
        $paginator->params = $result->params;
        $paginator->setAggregations($result->raw_aggregations);

        return $paginator;
    }

    // this updates all the documents that match the filter with a single value
    // e.g. update all documents that match the term "test" with the value "test2"
    // a script is then used to update the document
    // 
    public function updateByQuery(array $overrides = []): array
    {
        if (!$this->getIndex()) {
            throw new \Findable\Exceptions\FindableException(
                "FindableEngine requires an index to be set before querying."
            );
        }
        $body = $this->buildRequestBody();

        // Remove fields not supported by _update_by_query
        unset(
            $body['from'],
            $body['size'],
            $body['sort'],
            $body['aggs'],
            $body['rescore'],
            $body['collapse'],
            $body['track_total_hits']
        );

        // If no query was built, default to match_all to avoid empty body.query
        if (empty($body['query'])) {
            $body['query'] = ['match_all' => (object)[]];
        }

        $params = array_merge([
            'index' => $this->getIndex(),
            'body'  => $body,
            // Useful defaults/overrides for update_by_query:
            // 'conflicts' => 'proceed',
            // 'refresh'   => true,
            // 'max_docs'  => 10000,          // limit total docs to update
            // 'scroll_size' => 1000,         // per-batch size
        ], $overrides);

        $response = $this->client->updateByQuery($params);

        // Keep normalization simple if your client already returns arrays
        return is_array($response) ? $response : (array) $response;
    }

    /**
     * Convenience method to return an empty SearchResultDTO.
     *
     * Example:
     *   Model::findable()->emptySearchResult();
     *
     * @param array $params Optional metadata/params to include in the result.
     * @return SearchResultDTO
     */
    public function emptySearchResult(array $params = []): SearchResultDTO
    {
        return new SearchResultDTO(
            hits: [],
            total: 0,
            raw_aggregations: [],
            raw: [],
            params: $params
        );
    }

    /**
     * Hydrate Elasticsearch hits into model instances
     */
    protected function hydrateHits(array $hits): array
    {
        return array_map(function ($hit) {
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
        }, $hits);
    }

    /**
     * Stream all hits using Elasticsearch scroll + iterators.
     *
     * Best for exports, reindexing, or batch jobs. Not for UI pagination.
     *
     * @param int $size   Batch size per scroll request
     * @param string $scroll Keepalive duration (e.g. "2m")
     * @return SearchHitIterator
     */
    public function stream(int $size = 500, string $scroll = '2m'): SearchHitIterator
    {
        if (!$this->getIndex()) {
            throw new FindableException("FindableEngine requires an index to be set before streaming.");
        }

        $params = $this->buildRequestBody();
        // override the size and scroll
        $params['size'] = $size;
        $params['scroll'] = $scroll;

        $pages = new SearchResponseIterator($this->client, $params);
        return new SearchHitIterator($pages);
    }
}
