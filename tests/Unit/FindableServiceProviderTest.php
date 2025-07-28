<?php

namespace Tests\Unit;

use Tests\TestCase;
use Elastic\Elasticsearch\Client;

class FindableServiceProviderTest extends TestCase
{
    /** @test */
    public function test_client_can_be_resolved()
    {
        $client = app('elasticsearch.client');
        $this->assertInstanceOf(Client::class, $client);
    }
}