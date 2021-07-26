<?php
/**
 * SQL语法控制器
 */
namespace Hao\db\concerns;

trait SqlGrammar
{

    /**
     * The components that make up a select clause.
     *
     * @var string[]
     */
    protected $selectComponents = [
        'columns',
        'from',
        'wheres',


    ];


    /**
     * 将select查询编译成SQL
     */
    public function compileSelect()
    {
        $sql = [];
        foreach ($this->selectComponents as $component){
            if (isset($this->$component)){
                $method = 'compile'.ucfirst($component);
                $sql[$component] = $this->$method();
            }
        }
        return implode(' ', array_filter($sql, function ($value) {
            return (string) $value !== '';
        }));
    }

    /**
     * 组装 where 条件
     * @return array
     */
    protected function compileWheres()
    {
        $sql = [];
        foreach ($this->wheres as $where){
            $sql[] = $where['boolean'].' '.$this->{"where{$where['type']}"}($where);
        }
        if (count($sql) > 0){
            return 'where '.$this->removeLeadingBoolean(implode(' ', $sql));
        }
        return '';
    }

    protected function whereBasic($where)
    {

        $operator = str_replace('?', '??', $where['operator']);

        return $this->wrapValue($where['column']).' '.$operator.' '.'?';
    }


    protected function whereRaws($where)
    {
        return $where['sql'];
    }

    /**
     * 组装 from 部分
     * @return string
     */
    protected function compileFrom()
    {
        return 'from '.$this->wrapValue($this->from);
    }

    /**
     * 组装查询的字段信息
     * @return string
     */
    protected function compileColumns()
    {
        if (empty($this->columns)){
            return 'select *';
        }
        return 'select '.implode(',',$this->columns);
    }


    protected function wrapValue($value)
    {
        if ($value !== '*') {
            return '`'.$value.'`';
        }
        return $value;
    }


    /**
     * Remove the leading boolean from a statement.
     *
     * @param  string  $value
     * @return string
     */
    protected function removeLeadingBoolean($value)
    {
        return preg_replace('/and |or /i', '', $value, 1);
    }
}