<?php

use function Pest\Laravel\artisan;
use Illuminate\Support\Facades\DB;

it('runs stored-procedure:install command and outputs results', function () {
    $result = artisan('stored-procedure:install');
    $result->assertExitCode(0);

    $procedure_name = config('stored-procedure.results_procedure_name');
    $procedure_exists = DB::select("SHOW PROCEDURE STATUS WHERE Name = ?", [$procedure_name]);

    expect($procedure_exists)->not->toBeEmpty();
});