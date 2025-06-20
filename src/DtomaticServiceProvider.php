<?php

namespace Dtomatic;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Dtomatic\Mappers\ModelMapper;

class DtomaticServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register the ModelMapper as a singleton
        $this->app->singleton(ModelMapper::class, function () {
            return new ModelMapper();
        });

        // Merge the default package config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/dtomatic.php',
            'dtomatic'
        );

        // Register the alias for the Facade
        $loader = AliasLoader::getInstance();
        $loader->alias('ModelMapper', \Dtomatic\Facades\ModelMapper::class);
    }

    public function boot(): void
    {
        // Publish the config file to the Laravel application's config directory
        $this->publishes([
            __DIR__ . '/../config/dtomatic.php' => config_path('dtomatic.php'),
        ], 'dtomatic-config');
    }
}
