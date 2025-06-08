<?php

use Illuminate\Support\Facades\DB;
use Tests\TestModel;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use function Pest\Laravel\artisan;

beforeEach(function() {
    $temporary_pagination_table_prefix = 'sp_temporary_results_';

    DB::unprepared('DROP PROCEDURE IF EXISTS test_procedure;');
    DB::unprepared('DROP PROCEDURE IF EXISTS test_procedure_paginate;');
    
    $temps = '';
    for ($i = 1; $i <= 5; $i++) {
        $temps .= "create temporary table {$temporary_pagination_table_prefix}{$i} (value INT); ";
        $temps .= "INSERT INTO {$temporary_pagination_table_prefix}{$i} (value) VALUES (1), (2), (3), (4), (5), (6), (7), (8), (9), (10), (11), (12), (13), (14), (15), (16), (17), (18), (19), (20); ";
    }

    DB::unprepared("
        CREATE PROCEDURE test_procedure(IN param1 VARCHAR(255))
        BEGIN
            {$temps}

            SELECT * FROM {$temporary_pagination_table_prefix}1;
            SELECT * FROM {$temporary_pagination_table_prefix}2;
            SELECT * FROM {$temporary_pagination_table_prefix}3;
            SELECT * FROM {$temporary_pagination_table_prefix}4;
            SELECT * FROM {$temporary_pagination_table_prefix}5;
        END
    ");

    DB::unprepared("
        CREATE PROCEDURE test_procedure_paginate(IN param1 VARCHAR(255), IN _per_page INT, IN _page INT)
        BEGIN
            {$temps}

            SELECT COUNT(*) as total_rows FROM {$temporary_pagination_table_prefix}1;

            SELECT * FROM {$temporary_pagination_table_prefix}1 LIMIT _per_page OFFSET _page;
            SELECT * FROM {$temporary_pagination_table_prefix}2;
            SELECT * FROM {$temporary_pagination_table_prefix}3;
            SELECT * FROM {$temporary_pagination_table_prefix}4;
            SELECT * FROM {$temporary_pagination_table_prefix}5;
        END
    ");
});

it('can get single result set', function () {
    $result = TestModel::CallStoredProcedure('test_procedure', ['hello world'])->get();

    expect($result)->toBeCollection();
    expect($result[0] instanceof TestModel)->toBeTrue();
    expect($result[0]['value'] ?? null)->toEqual('1');
});

it('can get multiple result sets', function () {
    $result = TestModel::CallStoredProcedure('test_procedure', ['hello world'], 2)->get();

    expect($result)->toBeArray();
    expect($result[0][0] instanceof TestModel)->toBeTrue();
    expect($result[0][0]['value'] ?? null)->toEqual('1');
    expect($result[1][0]['value'] ?? null)->toEqual('1');
});

it('can get multiple result sets, paginate', function () {
    $result = TestModel::CallStoredProcedure('test_procedure_paginate', ['hello world'], 2)->paginate();

    expect($result)->toBeArray();
    expect($result[0] instanceof LengthAwarePaginator)->toBeTrue();
    expect($result[0][0] instanceof TestModel)->toBeTrue();
    expect($result[0][0]['value'] ?? null)->toEqual('1');
    expect($result[1][0]['value'] ?? null)->toEqual('1');
});

it('can get multiple result sets, paginate, second page', function () {
    $request = Request::create('/fake-url', 'GET', [
        'page' => 2,
    ]);

    app()->instance('request', $request);

    $result = TestModel::CallStoredProcedure('test_procedure_paginate', ['hello world'], 2)->paginate();
    
    expect($result)->toBeArray();
    expect($result[0] instanceof LengthAwarePaginator)->toBeTrue();
    expect($result[0][0]['value'] ?? null)->toEqual('16');
    expect($result[1][0]['value'] ?? null)->toEqual('1');
});

it('can get multiple result sets, paginate, third page', function () {
    $request = Request::create('/fake-url', 'GET', [
        'page' => 3,
    ]);

    app()->instance('request', $request);

    $result = TestModel::CallStoredProcedure('test_procedure_paginate', ['hello world'], 2)->paginate();
    
    expect($result)->toBeArray();
    expect($result[0] instanceof LengthAwarePaginator)->toBeTrue();
    expect($result[0][0] == null)->toBeTrue();
    expect($result[1][0]['value'] ?? null)->toEqual('1');
});

it('can get multiple result sets, paginate, 10 results per page', function () {
    $request = Request::create('/fake-url', 'GET', [
        'page' => 2,
    ]);

    app()->instance('request', $request);

    $result = TestModel::CallStoredProcedure('test_procedure_paginate', ['hello world'], 2)->paginate(10);
    
    expect($result)->toBeArray();
    expect($result[0] instanceof LengthAwarePaginator)->toBeTrue();
    expect($result[0][0]['value'] ?? null)->toEqual('11');
    expect($result[1][0]['value'] ?? null)->toEqual('1');
});

it('can hydrate models', function () {
    $request = Request::create('/fake-url', 'GET', [
        'page' => 2,
    ]);

    app()->instance('request', $request);

    $result = TestModel::CallStoredProcedure('test_procedure_paginate', ['hello world'], 2)
        ->setHydrateModels([TestModel::class])
        ->paginate(10);

    expect($result)->toBeArray();
    expect($result[0] instanceof LengthAwarePaginator)->toBeTrue();
    expect($result[0][0] instanceof TestModel)->toBeTrue();
    expect($result[0][0]['value'] ?? null)->toEqual('11');
    expect($result[1][0] instanceof TestModel)->toBeTrue();
    expect($result[1][0]['value'] ?? null)->toEqual('1');
});