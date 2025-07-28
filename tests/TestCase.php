<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Findable\FindableServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            FindableServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('findable.host', 'localhost');
        $app['config']->set('findable.port', 9200);
        $app['config']->set('findable.scheme', 'http');
    }
}
