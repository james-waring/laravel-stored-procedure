<?php

namespace JLW\StoredProcedure\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateResultsStoredProcedure extends Command
{
    protected $signature = 'stored-procedure:install';
    protected $description = 'Create the results stored procedure';

    public function handle()
    {
        $this->info('Creating results stored procedure...');

        $procedure_name = config('stored-procedure.results_procedure_name');
        $temporary_pagination_table_prefix = config('stored-procedure.temporary_pagination_table_prefix');

        DB::unprepared("DROP PROCEDURE IF EXISTS {$procedure_name};");

        DB::unprepared("
            CREATE PROCEDURE {$procedure_name}(IN _prefix VARCHAR(255), IN _total_results_sets INT, IN _per_page INT, IN _page INT)
            BEGIN
                -- DECLARE i INT DEFAULT 1;
                DECLARE total INT DEFAULT _total_results_sets;
                -- DECLARE sql_query VARCHAR(1000);

                SELECT COUNT(*) as total_rows FROM {$temporary_pagination_table_prefix}1;

                SELECT * FROM {$temporary_pagination_table_prefix}1 LIMIT _per_page OFFSET _page;

                /* WHILE i > 0 DO
                    IF i = 1 THEN
                        SET sql_query = CONCAT(
                            'SELECT * FROM ', _prefix, i,
                            ' LIMIT ', _per_page,
                            ' OFFSET ', _page
                        );
                    ELSE
                        SET sql_query = CONCAT(
                            'SELECT * FROM ', _prefix, i
                        );
                    END IF;

                    PREPARE stmt FROM sql_query;
                    EXECUTE stmt;
                    DEALLOCATE PREPARE stmt;

                    SET i = i - 1;

                END WHILE; */
            END
        ");
    }
}