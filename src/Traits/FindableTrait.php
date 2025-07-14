<?php

namespace Findable\Traits;

use Findable\FindableEngine;

/**
 * Trait FindableTrait
 *
 * Adds a static `finder()` method to any Eloquent model,
 * allowing for fluent Elasticsearch query construction.
 *
 * @package Findable\Traits
 */
trait FindableTrait
{
    /**
     * Instantiate a new FindableEngine for this model instance.
     *
     * @return FindableEngine
     */
    public static function finder(): FindableEngine
    {
        return new FindableEngine(new static());
    }
}
