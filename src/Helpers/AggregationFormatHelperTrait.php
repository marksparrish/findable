<?php

namespace Findable\Helpers;

trait AggregationFormatHelperTrait
{
    public function formatMultiLevelBucketCounts($buckets)
    {
        // Transform the array and calculate totals
        $bucket = [];
        foreach ($buckets['buckets'] as $item) {
            $bucket[$item['key']]['total'] = $item['doc_count'];

            foreach ($item['buckets']['buckets'] as $value) {
                $bucket[$item['key']][$value['key']] = $value['doc_count'];
            }
        }
        // Output the transformed array with totals
        return $bucket;
    }

    public function formatSingleLevelBucketCounts($buckets)
    {
        // Transform the array and calculate totals
        $bucket = [];
        foreach ($buckets['buckets'] as $item) {
            $bucket[$item['key']] = $item['doc_count'];
        }

        // Output the transformed array with totals
        return $bucket;
    }

    public function formatSimpleCounts($aggregation)
    {
        return $aggregation['value'];
    }
}
