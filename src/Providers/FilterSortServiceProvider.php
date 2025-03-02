<?php

namespace LaravelWakeUp\FilterSort\Providers;

use Illuminate\Support\ServiceProvider;

class FilterSortServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/laravel-filter-sort.php',
            'laravel-filter-sort'
        );
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/laravel-filter-sort.php' => config_path('laravel-filter-sort.php'),
        ], 'laravel-filter-sort-config');
    }
}
