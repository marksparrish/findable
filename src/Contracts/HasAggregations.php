<?php

namespace Findable\Contracts;

interface HasAggregations
{
    /**
     * Get a specific aggregation by name
     *
     * @param string $name
     * @param bool $formatted Whether to return the formatted version
     * @return mixed
     */
    public function getAggregation(string $name, bool $formatted = true): mixed;

    /**
     * Check if an aggregation exists
     *
     * @param string $name
     * @return bool
     */
    public function hasAggregation(string $name): bool;

    /**
     * Get all aggregations in their formatted form
     *
     * @return array
     */
    public function getFormattedAggregations(): array;

    /**
     * Get a metric aggregation value
     *
     * @param string $name
     * @return float|int|null
     */
    public function getMetricValue(string $name): float|int|null;

    /**
     * Get bucket aggregation values
     *
     * @param string $name
     * @return array
     */
    public function getBucketValues(string $name): array;

    /**
     * Get the raw aggregations array
     *
     * @return array
     */
    public function getRawAggregations(): array;
}
