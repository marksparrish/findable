<?php

namespace Findable;

use Illuminate\Support\ServiceProvider;
use Elastic\Elasticsearch\ClientBuilder;

class FindableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/findable.php' => config_path('findable.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__ . '/../config/findable.php',
            'findable'
        );
    }

    public function register()
    {
        $this->app->singleton('elasticsearch.client', function ($app) {
            $scheme = config('findable.scheme');
            $host = config('findable.host');
            $port = config('findable.port');
            $user = config('findable.user');
            $password = config('findable.password');
            $ca = config('findable.ca');

            return ClientBuilder::create()
                ->setHosts(["{$scheme}://{$host}:{$port}"])
                ->setBasicAuthentication($user, $password)
                ->setCABundle($ca)
                ->build();
        });
    }
}
