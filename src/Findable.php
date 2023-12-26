<?php

namespace Findable;

trait Findable
{
    public static function finder()
    {
        return new FindableEngine(new static); // 'new static' creates an instance of the model using the trait
    }
}
