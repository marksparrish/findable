<?php

namespace Findable\Formatters;

class AggregationFormatter
{
    public function format(array $aggregations): array
    {
        $formatted = [];

        foreach ($aggregations as $name => $aggregation) {
            $formatted[$name] = $this->formatSingleAggregation($aggregation);
        }

        return $formatted;
    }

    protected function formatSingleAggregation(array $aggregation): mixed
    {
        // Simple metric aggregation (direct value)
        if (isset($aggregation['value'])) {
            return $aggregation['value'];
        }

        // Bucket aggregation
        if (isset($aggregation['buckets'])) {
            return $this->formatBucketAggregation($aggregation);
        }

        return null;
    }

    protected function formatBucketAggregation(array $aggregation): array
    {
        $result = [];
        foreach ($aggregation['buckets'] as $bucket) {
            if (isset($bucket['buckets'])) {
                // Handle nested buckets
                $result[] = [
                    'key' => $bucket['key'],
                    'total' => $bucket['doc_count'],
                    'buckets' => $bucket['buckets']['buckets']
                ];
            } else {
                // Simple key-value bucket
                $result[$bucket['key']] = $bucket['doc_count'];
            }
        }
        return $result;
    }
}