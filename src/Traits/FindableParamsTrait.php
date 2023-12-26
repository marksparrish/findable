<?php

namespace Findable\Traits;

use Illuminate\Support\Collection;


trait FindableParamsTrait
{

    private $index;
    /**
     * Sets the track_total_hits value to default of true
     *
     * @var bool
     */
    private bool $track_total_hits = true;
    /**
     * Sets the scroll value to default of true
     *
     * @var bool
     */
    private bool $scroll = false;

    /**
     * How many results should be returned.
     *
     * @var int
     */
    private int $size = 10;

    /**
     * Sets the from value to default of 0
     *
     * @var int
     */
    private int $from = 0;

    /**
     * Collection storage for the Must Queries on Index for the models.
     *
     * @var array
     */
    private ?Collection $must_query = null;

    /**
     * Should Queries on Index for the models.
     *
     * @var array
     */
    private ?Collection $should_query = null;

    /**
     * Must Not Queries on Index for the models.
     *
     * @var array
     */
    private ?Collection $must_not_query = null;

    /**
     * Filters on Index for the models.
     *
     * @var array
     */
    private ?Collection $filter = null;

    /**
     * Aggregations on Index for the models.
     *
     * @var array
     */
    private ?Collection $aggs = null;

    /**
     * Sort on Index for the models.
     *
     * @var array
     */
    private ?Collection $sort = null;

    /**
     * Rescore on Index for the models.
     * This is a better sorting method
     * @var array
     */
    private ?Collection $rescore = null;
    /**
     * Sets the scripts value to default of null
     *
     * @var bool
     */
    private ?array $script = null;

    /** @return array  */
    private function setParams()
    {
        $params = [];
        $params['index'] = $this->getIndex();
        $params['from'] = $this->getFrom();
        $params['size'] = $this->getSize();
        $params['track_total_hits'] = $this->getTrackTotalHits();
        if ($this->getScroll()) {
            $params['scroll'] = '5m';
        }

        // only add must_query key to params when there are queries
        if ($this->must_query) {
            $params['body']['query']['bool']['must'] = $this->getMustQuery();
        }

        // only add should_query key to params when there are queries
        if ($this->should_query) {
            $params['body']['query']['bool']['should'] = $this->getShouldQuery();
        }

        // only add must_not_query key to params when there are queries
        if ($this->must_not_query) {
            $params['body']['query']['bool']['must_not'] = $this->getMustNotQuery();
        }

        // only add filter key to params when there are filters
        if ($this->filter) {
            $params['body']['query']['bool']['filter'] = $this->getFilter();
        }

        // only add aggregations key to params when there are aggregations
        if ($this->aggs) {
            $params['body']['aggs'] = $this->getAggs();
        }

        // only add aggregations key to params when there are aggregations
        if ($this->sort) {
            $params['body']['sort'] = $this->getSort();
        }

        // only add a script if there is a script to add.  Only one script can be added.
        if ($this->script) {
            $params['body']['script'] = $this->getScript();
        }

        // only add a rescore if there is a rescore to add.  Only one rescore can be added.
        if ($this->rescore) {
            $params['body']['rescore'] = $this->getRescore();
        }
        $this->params = $params;
    }
}
