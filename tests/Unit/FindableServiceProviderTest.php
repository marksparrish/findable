<?php

namespace Tests\Unit;

use Findable\FindableServiceProvider;
use Orchestra\Testbench\TestCase;

class FindableServiceProviderTest extends TestCase
{
    public function testClientCreation()
    {
        // Mock the environment variables or configuration if necessary

        // Retrieve the client from the service container
        $client = $this->app->make('findable.client');

        $this->assertNotNull($client);
        // Add more assertions to validate the client instance
    }

    public function testClientCreationFailure()
    {
        // Mock a scenario where the client creation should fail
        // For example, set invalid configuration values

        // Expectation: an error should be logged, or a specific behavior should occur
        // You can use Laravel's built-in log mocking to assert that an error was logged
    }
}
