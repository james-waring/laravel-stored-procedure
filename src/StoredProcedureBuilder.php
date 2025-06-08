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
       return $this->callResultsProcedure();
    }

    public function paginate($per_page = 15)
    {
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

    private function callResultsProcedure($paginate = false, $per_page = 15)
    {
        $this->pdo = DB::connection()->getPdo();

        if ($paginate) {
            $page = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
            $offset = ($page - 1) * $per_page;
            $results_sets = [];
            $total_rows = 0;
            
            $this->parameters[] = $per_page;
            $this->parameters[] = $offset;
        }

        $bindings = implode(',', array_fill(0, count($this->parameters), '?'));
        
        $results_call_stmt = $this->pdo->prepare("CALL {$this->procedure_name}({$bindings})");
        $results_call_stmt->execute($this->parameters);

        for ($i = 0; $i < $this->total_result_sets + 1; $i++) {
            $rows = $results_call_stmt->fetchAll(PDO::FETCH_ASSOC);
            $rows = $rows ? $rows : [];

            if ($i == 0) {
                if ($paginate) {
                    $total_rows = $rows[0]['total_rows'];
                } else {
                    $rows = $this->model_class::hydrate($rows);
                    $results_sets[] = $rows;
                }
            } else {
                $model_class = $paginate
                    ? ($i == 1 ? $this->model_class : ($this->hydrate_models[$i - 2] ?? null))
                    : ($this->hydrate_models[$i - 1] ?? null);
            
                if ($model_class !== null) {
                    $rows = $model_class::hydrate($rows);
                }
            
                if ($paginate && $i == 1) {
                    $rows = new LengthAwarePaginator($rows, $total_rows, 100, 1);
                }
            
                $results_sets[] = $rows;
            }

            $results_call_stmt->nextRowset();
        }

        return $this->total_result_sets == 1 ? $results_sets[0] : $results_sets;    
    }
}