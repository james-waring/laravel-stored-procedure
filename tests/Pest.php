<?php

use Illuminate\Support\Facades\DB;
use function Pest\Laravel\artisan;

uses(Tests\TestCase::class)->in(__DIR__);

// dump('Booting Pest.php...');

// pest()->extend(Tests\TestCase::class)->beforeAll(function () {

//     dump('Trying DB...');

    
// });

// afterAll(function () {
//     $dbName = 'stored_procedure_testing';

//     // Drop database
//     DB::statement("DROP DATABASE IF EXISTS `$dbName`");

//     dump("🗑️ Database [$dbName] dropped.");
// });

// it('has created a database and switched to it', function () {
//     expect(DB::connection()->getDatabaseName())->toBe('stored_procedure_testing');
// });