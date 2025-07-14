<?php

namespace Findable\Console\Commands;

use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Throwable;

/**
 * Class CheckElasticsearchConnection
 *
 * A CLI command to test the Elasticsearch connection configured via findable.php.
 *
 * Usage:
 *  php artisan findable:check
 *
 * This command verifies that:
 * - Elasticsearch is reachable
 * - Auth, host, port, and TLS are configured correctly
 * - Cluster info is returned successfully
 *
 * @package Findable\Console\Commands
 */
class CheckElasticsearchConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'findable:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the connection to the configured Elasticsearch instance';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        try {
            /** @var Client $client */
            $client = App::make('elasticsearch.client');

            $info = $client->info()->asArray();

            $this->info("✅ Elasticsearch connection successful!");
            $this->line("Cluster: " . $info['cluster_name']);
            $this->line("Version: " . $info['version']['number']);

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error("❌ Failed to connect to Elasticsearch.");
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }
}
