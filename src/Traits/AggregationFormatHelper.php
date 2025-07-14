<?php

namespace Findable\Traits;

/**
 * Trait AggregationFormatHelper
 *
 * Provides helper methods for formatting Elasticsearch aggregation structures.
 * These methods help simplify aggs like terms, metrics, and nested queries.
 *
 * @package Findable\Traits
 */
trait AggregationFormatHelper
{
    /**
     * Wrap a given field name in a standard "terms" aggregation format.
     */
    protected function formatTermsAggregation(string $field): array
    {
        return [
            'terms' => [
                'field' => $field,
            ],
        ];
    }

    /**
     * Format a raw aggregation block for a given name and type.
     *
     * @param string $name   Aggregation name
     * @param string $type   Type like 'avg', 'sum', etc.
     * @param string $field  Field to aggregate on
     * @return array
     */
    protected function formatMetricAggregation(string $name, string $type, string $field): array
    {
        return [
            $name => [
                $type => [
                    'field' => $field,
                ],
            ],
        ];
    }

    /**
     * Build a nested aggregation under a specific path.
     *
     * @param string $path
     * @param array $aggs
     * @return array
     */
    protected function formatNestedAggregation(string $path, array $aggs): array
    {
        return [
            'nested' => [
                'path' => $path,
            ],
            'aggs' => $aggs,
        ];
    }

    /**
     * Merge multiple aggregation definitions into one array.
     * Useful for combining different aggregation blocks.
     *
     * @param array ...$aggs
     * @return array
     */
    protected function mergeAggregations(array ...$aggs): array
    {
        return array_merge(...$aggs);
    }
}
