<?php

namespace Findable;

use Illuminate\Support\ServiceProvider;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Client;
use Findable\Exceptions\FindableException;
use Findable\Console\Commands\CheckElasticsearchConnection;

/**
 * Class FindableServiceProvider
 *
 * Registers the Findable package with Laravel. This includes:
 * - Publishing the configuration file
 * - Merging default config values
 * - Registering the Elasticsearch client as a singleton
 * - Binding the FindableEngine into the container for ad hoc usage
 * - Tagging the engine for clarity and future resolution
 *
 * Supports both:
 * - Secure connections via HTTPS + CA bundle
 * - Insecure local development via HTTP (no cert required)
 *
 * Expected config keys in config/findable.php:
 * - scheme: 'http' or 'https'
 * - host: hostname or IP
 * - port: Elasticsearch port (usually 9200)
 * - user: basic auth username (optional)
 * - password: basic auth password (optional)
 * - ca: path to CA bundle for HTTPS connections (optional)
 *
 * @package Findable
 */
class FindableServiceProvider extends ServiceProvider
{
    /**
     * Register the package services, including the Elasticsearch client and engine bindings.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge package config into the application's config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/findable.php',
            'findable'
        );

        // Register the Elasticsearch client singleton
        $this->app->singleton('elasticsearch.client', function (): Client {
            $config = config('findable');

            $host = "{$config['scheme']}://{$config['host']}:{$config['port']}";

            $builder = ClientBuilder::create()
                ->setHosts([$host])
                ->setBasicAuthentication($config['user'], $config['password']);

            // Set CA bundle if using HTTPS
            if ($config['scheme'] === 'https') {
                $caPath = $config['ca'];

                if (!is_null($caPath)) {
                    if (!file_exists($caPath) || !is_readable($caPath)) {
                        throw new FindableException("CA certificate is missing or unreadable at path: {$caPath}");
                    }

                    $builder->setCABundle($caPath);
                }
            }

            return $builder->build();
        });

        // Register the FindableEngine binding (model optional)
        $this->app->bind(FindableEngine::class, function () {
            return new FindableEngine(); // Ad hoc index can be set via setIndex()
        });

        // Tag the engine binding for clarity
        $this->app->tag(FindableEngine::class, ['findable.engine']);
    }

    /**
     * Bootstrap package services such as config publishing.
     *
     * @return void
     */
    public function boot(): void
    {
        // Allow publishing of the config file to the host app
        $this->publishes([
            __DIR__ . '/../config/findable.php' => config_path('findable.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckElasticsearchConnection::class,
            ]);
        }
    }
}
