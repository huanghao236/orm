<?php
namespace Hao\db\concerns;

use Hao\facade\Config;

trait Query
{

    /**
     * 查询的where约束
     * @var array
     */
    public $wheres = [];

    /**
     * 查询的字段
     * @var array
     */
    public $columns = [];

    /**
     * 查询的表
     * @var string
     */
    public $from;

    /**
     * 向查询中添加一个基本where子句
     * @param string $column
     * @param mixed $operator
     * @param string $value
     * @param string $boolean
     */
    public function where(string $column,$operator = null,string $value = null,$boolean = 'and')
    {
        $type = 'Basic';

        if (is_null($value)){
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = compact(
            'type', 'column', 'operator', 'value', 'boolean'
        );
        return $this;
    }

    /**
     * 向查询中添加一个原生SQL语句的where子句
     * @param string $sql      SQL语句
     * @param string $boolean
     * @return $this
     */
    public function whereRaw(string $sql,$boolean = 'and')
    {
        $this->wheres[] = ['type' => 'raw', 'sql' => $sql, 'boolean' => $boolean];
        return $this;
    }

    public function orWhere()
    {
        return '这是orWhere';
    }

    public function orWhereRaw()
    {
        return '这是orWhereRaw';
    }

    public function whereIn()
    {
        return '这是whereIn';
    }

    public function whereBetween()
    {
        return '这是whereBetween';
    }

    /**
     * 设置要选择的列
     * @param string[] $columns
     * @return string
     */
    public function select($columns = ['*'])
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        foreach ($columns as $column){
            $this->columns[] = $column;
        }

        return $this;
    }

    /**
     * 设置要选择的列
     * @param string $sql
     * @return string
     */
    public function selectRaw(string $sql)
    {
        $columns = explode(',',$sql);
        foreach ($columns as $column){
            $this->columns[] = $column;
        }
        return $this;
    }


    public function join()
    {
        return '这是join';
    }

    public function leftJoin()
    {
        return '这是leftJoin';
    }

    public function rightJoin()
    {
        return '这是rightJoin';
    }


    public function groupBy()
    {
        return '这是groupBy';
    }

    public function orderBy()
    {
        return '这是orderBy';
    }

    public function having()
    {
        return '这是having';
    }


    public function get()
    {
        $sql = $this->toSql();
        $this->sqlSelect($sql);
    }


    public function toSql()
    {
        return $this->compileSelect();
    }
}