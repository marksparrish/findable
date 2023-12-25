<?php

namespace Findable;

use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;

class ElasticsearchClientProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('elasticsearch.client', function ($app) {
            return $this->createClient();
        });
    }

    private function createClient()
    {
        $scheme = config('findable.elastic.scheme');
        $host = config('findable.elastic.host');
        $port = config('findable.elastic.port');
        $user = config('findable.elastic.user');
        $password = config('findable.elastic.password');
        $ca = config('findable.elastic.ca');

        return ClientBuilder::create()
            ->setHosts(["{$scheme}://{$host}:{$port}"])
            ->setBasicAuthentication($user, $password)
            ->setCABundle($ca)
            ->build();
    }
}
