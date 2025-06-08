<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use JLW\StoredProcedure\StoredProcedureServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;


abstract class TestCase extends BaseTestCase
{
    protected static $setUpHasRunOnce = false;

    protected function getPackageProviders($app)
    {
        return [
            StoredProcedureServiceProvider::class,
        ];
    }

    protected function setUp() : void
    {
        parent::setUp();

        return;

        if (! static::$setUpHasRunOnce) {
            // dump('Trying DB...');
            config()->set('database.connections.mysql.database', 'mysql');

            // Unique database name (safe for parallel)
            $dbName = 'stored_procedure_testing';

            // Drop database if it exists
            DB::statement("DROP DATABASE IF EXISTS `$dbName`");

            // Create database if not exists
            DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName`");

            // Now tell Laravel to use this database
            config()->set('database.connections.mysql.database', $dbName);

            // Reconnect to apply config
            DB::purge('mysql');
            DB::reconnect('mysql');

            // Run migrations
            Artisan::call('migrate');

            // dump("✅ Database [$dbName] created and migrated.");

            static::$setUpHasRunOnce = true;
        }
    }
}
