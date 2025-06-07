<?php

namespace JLW\StoredProcedure;

use PDO;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class StoredProcedureBuilder
{
    private $pdo;
    private $hydrate_models = [];
    
    public function __construct(
        public string $procedure_name, 
        public array $parameters = [],
        public int $total_result_sets = 1,
        public string $model_class = ''
    ) {
        return $this;
    }
    
    public function get()
    {
       $this->callProcedure();

       return $this->callResultsProcedure();
    }

    public function paginate($per_page = 15)
    {
        $this->callProcedure();

        return $this->callResultsProcedure(true, $per_page);
    }

    public function setHydrateModels(array $models)
    {
        throw_if(
            $this->total_result_sets == 1,
            new \Exception('You can only hydrate models if you are calling a more than one result set')
        );

        throw_if(
            $this->total_result_sets - 1 !== count($models),
            new \Exception('You must provide a model for each result set excluding the first result set, nulls are allowed')
        );

        $this->hydrate_models = $models;

        return $this;
    }

    private function callProcedure()
    {
        $this->pdo = DB::connection()->getPdo();

        $bindings = implode(',', array_fill(0, count($this->parameters), '?'));
        
        $procedure_call_stmt = $this->pdo->prepare("CALL {$this->procedure_name}({$bindings})");
        $procedure_call_stmt->execute($this->parameters);

        do {
            $procedure_call_stmt->fetchAll();
        } while ($procedure_call_stmt->nextRowset());
    }

    private function callResultsProcedure($paginate = false, $per_page = 15)
    {
        $page = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $offset = ($page - 1) * $per_page;
        $results_sets = [];
        $total_rows = 0;

        $results_procedure_name = config('stored-procedure.results_procedure_name');
        $results_call_stmt = $this->pdo->prepare("CALL {$results_procedure_name}(?, ?, ?, ?)");
        $results_call_stmt->execute([
            config('stored-procedure.temporary_pagination_table_prefix'),
            $this->total_result_sets,
            $per_page,
            $offset
        ]);

        for ($i = 0; $i < $this->total_result_sets + 1; $i++) {
            $rows = $results_call_stmt->fetchAll(PDO::FETCH_ASSOC);
            $rows = $rows ? $rows : [];

            if ($i == 0) {
                $total_rows = $rows[0]['total_rows'];
            } else {
                if ($i == 1) {
                    $rows = $this->model_class::hydrate($rows);

                    if ($paginate) {
                        $rows = new LengthAwarePaginator($rows, $total_rows, 100, 1);
                    }
                } else if (isset($this->hydrate_models[$i - 2]) && $this->hydrate_models[$i - 2] !== null) {
                    $rows = $this->hydrate_models[$i - 2]::hydrate($rows);
                }

                $results_sets[] = $rows;
            }

            $results_call_stmt->nextRowset();
        }

        return $this->total_result_sets == 1 ? $results_sets[0] : $results_sets;    
    }
}