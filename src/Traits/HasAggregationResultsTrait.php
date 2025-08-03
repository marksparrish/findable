<?php

namespace Findable\Traits;

use Findable\Formatters\AggregationFormatter;

trait HasAggregationResultsTrait
{
    protected ?AggregationFormatter $aggregationFormatter = null;

    /**
     * Get the aggregation formatter instance
     */
    protected function formatter(): AggregationFormatter
    {
        if (!$this->aggregationFormatter) {
            $this->aggregationFormatter = new AggregationFormatter();
        }

        return $this->aggregationFormatter;
    }

    /**
     * Format simple metric aggregations that have a direct value
     */
    public function formatSimpleCounts(array $aggregation): int|float
    {
        return $this->formatter()->formatMetricAggregation($aggregation);
    }

    /**
     * Format single level bucket aggregations into key-value pairs
     */
    public function formatSingleLevelBucketCounts(array $aggregation): array
    {
        return $this->formatter()->formatBucketAggregation($aggregation);
    }

    /**
     * Format nested bucket aggregations with their full structure
     */
    public function formatNestedBucketCounts(array $aggregation): array
    {
        return $this->formatter()->format($aggregation);
    }

    /**
     * Format and get top N items from a bucket aggregation
     */
    public function getTopBucketCounts(array $aggregation, int $limit = 10): array
    {
        $formatted = $this->formatter()->formatBucketAggregation($aggregation);
        return array_slice($formatted, 0, $limit);
    }

    /**
     * Format the complete aggregations response
     */
    public function formatAggregations(array $aggregations): array
    {
        return $this->formatter()->format($aggregations);
    }
}
