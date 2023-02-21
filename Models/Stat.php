<?php

namespace App\Models;

use \Base;
use \Base\Model\BaseModel;
use \Base\Base\Request;
use \Base\Base\DB;

class Stat extends BaseModel
{
    const TABLE = 'stats';
    
    protected $columns = array(
        'id' => 'int(10) unsigned',
        
        'rout' => 'varchar(255)',
        'duration' => 'float',
        'duration_db' => 'float',
        'cp' => 'float',
        'memory' => 'float',
        'count' => 'int(11)',
        'count_db' => 'int(11)',
        'duration_all' => 'float',
        'duration_db_all' => 'float',
        'cp_all' => 'float',
        'memory_all' => 'float',
        'count_db_all' => 'int(11)',
        'duration_average' => 'float',
        'duration_db_average' => 'float',
        'cp_average' => 'float',
        'memory_average' => 'float',
        'count_db_average' => 'int(11)',
        'percent_duration' => 'float',
        'percent_duration_db' => 'float',
        'percent_cp' => 'float',
        'percent_memory' => 'float',
        'percent_count_db' => 'float',
        
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
    );

    public function build()
    {
    }
}
