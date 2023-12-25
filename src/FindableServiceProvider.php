<?php

namespace Findable;

use Illuminate\Support\ServiceProvider;

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

}
