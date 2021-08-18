<?php
namespace Hao\Concerns;

use Hao\Collection;

trait HasRelationships
{
    public $foreignKey;

    public $localKey;

    public $type;

    public function hasOne($class, $foreignKey, $localKey)
    {
        return $this->relationExecution($class, $foreignKey,$localKey,'one');
    }

    public function hasMany($class, $foreignKey, $localKey)
    {
        return $this->relationExecution($class, $foreignKey,$localKey,'many');
    }

    /**
     * 关联执行
     * @param $foreignKey
     * @param $localKey
     */
    private function relationExecution($class, $foreignKey, $localKey, $type)
    {
        $this->foreignKey = $foreignKey;//关联字段
        $this->localKey = $localKey;//查询value字段
        $this->type = $type;//关联类型
        $localValue = $this->items[$localKey] ?? array_column($this->items,$localKey);//获取查询value
        $instance = $this->newRelatedInstance($class);//获取当前model
        $where = $instance->table.'.'.$foreignKey;
        return $instance->query()->whereIn($where,$localValue);
    }

    /**
     * 将对象的KEY修改为关联查询字段的值
     */
    public function buildDictionary(Collection $results)
    {
        $foreignKey = $this->foreignKey;
        return $results->mapToDictionary(function ($result)use($foreignKey){
            return [$result->{$foreignKey} => $result];
        });
    }


    /**
     * 获取当前Model
     * @param $class
     * @return mixed
     */
    protected function newRelatedInstance($class)
    {
        return new $class;
    }
}