<?php

namespace Findable;

use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;

class ElasticsearchClientProvider extends ServiceProvider
{
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
