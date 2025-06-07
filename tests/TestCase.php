<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use JLW\StoredProcedure\StoredProcedureServiceProvider;

abstract class TestCase extends BaseTestCase
{
    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            StoredProcedureServiceProvider::class,
        ];
    }
}
