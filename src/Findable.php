<?php

namespace Findable;

trait Findable
{
    public static function finder()
    {
        $modelInstance = app(static::class);
        return new FindableEngine($modelInstance);
    }
}
