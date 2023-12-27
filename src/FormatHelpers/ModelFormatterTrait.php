<?php

namespace Findable\FormatHelpers;

trait ModelFormatterTrait
{
    public function formatMultiLevelBucketCounts($buckets)
    {
        // Transform the array and calculate totals
        $bucket = [];
        foreach ($buckets['buckets'] as $item) {
            $currentLevel = &$bucket;
            foreach ($item['key'] as $key => $value) {
                if (!isset($currentLevel[$value])) {
                    $currentLevel[$value] = [];
                }
                $currentLevel = &$currentLevel[$value];
            }

            $currentLevel = $item['doc_count'];
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
