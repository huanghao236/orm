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
        'limit',


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
     * 将update编译成SQL语句
     */
    public function compileUpdate($values)
    {
        $where = $this->compileWheres();
        $compile = '';
        foreach ($values as $key => $value){
            if ($compile){
                $compile .= ','.$this->wrapValue($key).' = ?';
            }else{
                $compile = $this->wrapValue($key).' = ?';
            }
            $this->bindings['set'][] = $value;
        }
        return "update {$this->wrapValue($this->from)} set {$compile} {$where}";
    }

    /**
     * 将Insert编译成SQL语句
     */
    public function compileInsert($values)
    {
        //组装insert value部分
        $compileValue = '';
        foreach ($values as $key => $value){
            if (is_array($value)){
                $value = array_values($value);
                if ($compileValue){
                    $compileValue .= ',('.$this->parameterize($value).')';
                }else{
                    $compileValue = '('.$this->parameterize($value).')';
                }
                $this->bindings['insert'][] = $value;
            }else{
                if ($compileValue){
                    $compileValue .= ',?';
                }else{
                    $compileValue .= '(?';
                }
                $this->bindings['insert'][] = $value;
            }
        }

        //组装insert 字段部分
        if (count($values) == count($values, 1)){
            $Keys = array_keys($values);
            $compileValue = $compileValue.')';
        }else{
            $Keys = array_keys($values[0]);
        }
        $compileKey = '';
        foreach ($Keys as $v){
            if ($compileKey){
                $compileKey .= ','.$this->wrapValue($v);
            }else{
                $compileKey .= $this->wrapValue($v);
            }
        }
        $compileKey = '('.$compileKey.')';

        return "insert into {$this->wrapValue($this->from)} {$compileKey} value {$compileValue}";
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

    /**
     * where条件
     * @param $where
     * @return string
     */
    protected function whereBasic($where)
    {

        $operator = str_replace('?', '??', $where['operator']);

        return $this->wrapValue($where['column']).' '.$operator.' '.'?';
    }

    /**
     * 原生SQL条件
     * @param $where
     * @return mixed
     */
    protected function whereRaws($where)
    {
        return $where['sql'];
    }

    /**
     * In查询条件
     * @param $where
     * @return mixed
     */
    protected function whereIns($where)
    {
        if (!is_array($where['value'])){
            $where['value'] = [$where['value']];
        }
        return $this->wrapValue($where['column']).' in ('.$this->parameterize($where['value']).')';
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


    protected function compileLimit()
    {
        return 'limit '.(int) $this->limit;
    }

    protected function wrapValue($value)
    {

        if ($value !== '*') {
            if (strpos($value, '.') === false){
                return '`'.$value.'`';
            }else{
                $value = explode('.',$value);
                $arr = [];
                foreach ($value as $val){
                    $arr[] = '`'.$val.'`';
                }
                $value = implode('.',$arr);
            }
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

    public function parameterize(array $values)
    {
        return implode(', ', array_map([$this, 'parameter'], $values));
    }

    protected function parameter()
    {
        return '?';
    }
}