<?php

use Tests\TestModel;

it('can call CallStoredProcedure static function from model', function () {
    expect(method_exists(TestModel::class, 'CallStoredProcedure'))->toBeTrue();
});

// it('can read default config values', function () {
//     expect(config('stored-procedure.temporary_pagination_table_prefix'))
//         ->toEqual('sp_temporary_results_');

//     expect(config('stored-procedure.results_procedure_name'))
//         ->toEqual('sp_results');
// });

it('throws an exception if trying to hydrate models when expecting a single result set', function () {
    expect(fn() => 
        TestModel::CallStoredProcedure('test_procedure', ['hello world'])
            ->setHydrateModels([TestModel::class])
    )->toThrow(\Exception::class, 'You can only hydrate models if you are calling a more than one result set');
});

it('throws an exception if trying to hydrate models with a different number of result sets to models', function () {
    expect(fn() => 
        TestModel::CallStoredProcedure('test_procedure', ['hello world'], 3)
            ->setHydrateModels([TestModel::class])
    )->toThrow(\Exception::class, 'You must provide a model for each result set excluding the first result set, nulls are allowed');
});