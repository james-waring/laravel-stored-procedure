<?php

namespace JLW\StoredProcedure;

use JLW\StoredProcedure\StoredProcedureBuilder;

trait StoredProcedureTrait
{
    public static function CallStoredProcedure(
        string $procedure_name, 
        array $parameters = [], 
        int $total_result_sets = 1
    ) {
        return new StoredProcedureBuilder($procedure_name, $parameters, $total_result_sets, self::class);
    }
}