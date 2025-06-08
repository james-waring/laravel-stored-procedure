<?php

use Tests\TestModel;
use Illuminate\Support\Facades\DB;
use function Pest\Laravel\artisan;
use Illuminate\Support\Facades\Process;

beforeEach(function() {
    artisan('stored-procedure:install');

    // loadDump('dump-users-1000000.sql');

    DB::unprepared('DROP PROCEDURE IF EXISTS get_users;');

    DB::unprepared("
        CREATE PROCEDURE get_users()
        BEGIN
            DROP TABLE IF EXISTS sp_temporary_results_1;
            CREATE TEMPORARY TABLE sp_temporary_results_1 AS (SELECT * FROM users where name like 'Z%');

            DROP TABLE IF EXISTS sp_temporary_results_2;
            CREATE TEMPORARY TABLE sp_temporary_results_2 AS (SELECT * FROM users where name like 'J%');
        END
    ");
});

it('benchmarks stored procedure vs eloquent', function () {
    $spAvg = benchmark(fn() => 
        TestModel::CallStoredProcedure('get_users', [], 2)->paginate(1000), 10);

    $eloquentAvg = benchmark(fn() => TestModel::whereLike('name', 'Z%')->paginate(1000), 10);

    dump('Total users: ' . TestModel::count());
    dump('Stored Procedure avg: ' . $spAvg . ' seconds');
    dump('Eloquent avg: ' . $eloquentAvg . ' seconds');
});


function benchmark(callable $fn, int $iterations = 10): float
{
    $total = 0;

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $fn();
        $total += (microtime(true) - $start);
    }

    return $total / $iterations;
}

function loadDump($file = 'dump-users-100000.sql')
{
    $user = env('DB_USERNAME');
    $password = env('DB_PASSWORD');
    $database = env('DB_DATABASE');
    $host = '-h '.env('DB_HOST');

    $path = 'tests/dumps/'.$file;
    Process::timeout(600)->run("mysql -u{$user} -p{$password} $host $database < {$path}");
}