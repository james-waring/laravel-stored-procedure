<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use JLW\StoredProcedure\StoredProcedureTrait;

class TestModel extends Model {
    use StoredProcedureTrait;

    protected $table = 'users';
}