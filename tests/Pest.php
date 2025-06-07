<?php

use Illuminate\Support\Facades\DB;
use function Pest\Laravel\artisan;


beforeAll(function () {

    // Unique database name (safe for parallel)
    $dbName = 'stored_procedure_testing';

    // Create database if not exists
    DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName`");

    // Now tell Laravel to use this database
    config()->set('database.connections.mysql.database', $dbName);

    // Reconnect to apply config
    DB::purge('mysql');
    DB::reconnect('mysql');

    // Run migrations
    artisan()->call('migrate');

    dump("✅ Database [$dbName] created and migrated.");
});

afterAll(function () {
    $dbName = 'stored_procedure_testing';

    // Drop database
    DB::statement("DROP DATABASE IF EXISTS `$dbName`");

    dump("🗑️ Database [$dbName] dropped.");
});

uses(Tests\TestCase::class)->in(__DIR__);