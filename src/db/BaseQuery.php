<?php
namespace Hao\db;

class BaseQuery
{
    use concerns\Query;
    use concerns\SqlGrammar;
    use concerns\Connector;

    public function __construct($table)
    {
        $this->from = $table;
    }
    
}