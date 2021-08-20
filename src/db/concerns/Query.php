<?php
namespace Hao\db\concerns;

use Hao\facade\Config;
use Illuminate\Database\Query\Builder;

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
     * limit
     * @var string
     */
    public $limit;

    /**
     * 查询的表
     * @var string
     */
    public $from;

    /**
     * 绑定参数类型
     *
     * @var array
     */
    public $bindings = [
        'set' => [],
        'where' => []
    ];

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

        if ($value) {
            $this->addBinding($value, 'where');
        }

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
        $this->wheres[] = ['type' => 'raws', 'sql' => $sql, 'boolean' => $boolean];
        return $this;
    }


    /**
     * 向查询中添加一个In条件
     */
    public function whereIn(string $column,$value,$boolean = 'and')
    {
        $type = 'Ins';
        $this->wheres[] = compact(
            'type', 'column', 'operator', 'value', 'boolean'
        );

        if ($value) {
            if (is_array($value)){
                $this->addBinding($value, 'where');
            }else{
                $this->addBinding([$value], 'where');
            }

        }

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

    public function take($value)
    {
        return $this->limit($value);
    }


    public function limit($value)
    {
        $this->limit = $value;

        return $this;
    }

    /**
     * 查询
     * @return mixed
     */
    public function get()
    {
        return $this->sqlImplement($this->compileSelect(),'select');
    }

    /**
     * 修改
     * @param array $value
     */
    public function update(array $value)
    {
        return $this->sqlImplement($this->compileUpdate($value),'update');
    }

    /**
     * 新增
     */
    public function insert(array $value)
    {
        return $this->sqlImplement($this->compileInsert($value),'insert');
    }

    /**
     * 向查询添加绑定参数
     * @param  mixed  $value
     * @param  string  $type
     * @return $this
     *
     */
    public function addBinding($value, $type = 'where')
    {
        if (! array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}.");
        }

        if (is_array($value)) {
            $this->bindings[$type] = array_values(array_merge($this->bindings[$type], $value));
        } else {
            $this->bindings[$type][] = $value;
        }

        return $this;
    }
}