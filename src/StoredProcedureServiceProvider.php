<?php

namespace JLW\StoredProcedure;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;

class StoredProcedureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // $this->mergeConfigFrom(__DIR__.'/config/stored-procedure.php', 'stored-procedure');
    }

    public function boot(): void
    {
        // $this->publishes([
        //     __DIR__.'/config/stored-procedure.php' => config_path('stored-procedure.php'),
        // ], 'stored-procedure');

        // if ($this->app->runningInConsole()) {
        //     $this->commands([
        //         \JLW\StoredProcedure\Console\Commands\CreateResultsStoredProcedure::class,
        //     ]);
        // }
    }
}