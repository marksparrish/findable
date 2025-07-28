<?php

namespace Findable;

use Illuminate\Support\Facades\Facade;

/**
 * Class Findable
 *
 * Laravel Facade for FindableEngine access.
 * Lets you build queries directly using static calls.
 *
 * Example:
 *     Findable::for(\App\Models\Voter::class)->setAggs([...])->paginate();
 *
 * @package Findable
 *
 * @method static FindableEngine (string $modelClass)
 */
class Findable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'findable.engine';
    }

    /**
     * Instantiate a FindableEngine for a given model class.
     *
     * @param string $modelClass
     * @return FindableEngine
     */
    public static function for(string $modelClass): FindableEngine
    {
        return new FindableEngine(new $modelClass());
    }
}
