<?php

namespace Findable\Formatters;

class AggregationFormatter
{
    /**
     * Format the entire aggregations response
     */
    public function format(array $aggregations): array
    {
        $formatted = [];

        foreach ($aggregations as $name => $aggregation) {
            $formatted[$name] = $this->formatSingleAggregation($aggregation);
        }

        return $formatted;
    }

    /**
     * Format a single aggregation based on its type
     */
    protected function formatSingleAggregation(array $aggregation): mixed
    {
        // Simple metric aggregation (direct value)
        if (isset($aggregation['value'])) {
            return $this->formatMetricAggregation($aggregation);
        }

        // Bucket aggregation
        if (isset($aggregation['buckets'])) {
            return $this->formatBucketAggregation($aggregation);
        }

        // Nested sub-aggregations
        if (is_array($aggregation) && !isset($aggregation['value'], $aggregation['buckets'])) {
            return $this->format($aggregation);
        }

        return null;
    }

    /**
     * Format bucket aggregations into a simplified array structure
     */
    protected function formatBucketAggregation(array $aggregation): array
    {
        return array_map(function ($bucket) {
            $result = [
                'key' => $bucket['key'],
                'count' => $bucket['doc_count']
            ];

            // Handle nested buckets if they exist
            if (isset($bucket['buckets'])) {
                $result['nested'] = $this->formatBucketAggregation($bucket);
            }

            return $result;
        }, $aggregation['buckets']);
    }

    /**
     * Format metric aggregations (like sum, avg, max, etc.)
     */
    protected function formatMetricAggregation(array $aggregation): int|float
    {
        return $aggregation['value'];
    }
}