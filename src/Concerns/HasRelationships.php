<?php
namespace Hao\Concerns;

use Hao\Collection;

trait HasRelationships
{
    public function hasOne($class,$foreignKey = null, $localKey = null)
    {
        $instance = $this->newRelatedInstance($class);
        $where = $instance->table.'.'.$foreignKey;
        return $instance->query()->take(1)->where($where,$this->{$localKey});
    }

    public function hasMany($class,$foreignKey = null, $localKey = null)
    {
        $instance = $this->newRelatedInstance($class);
        $where = $instance->table.'.'.$foreignKey;
        $value = $this->{$localKey};
        return $instance->query()->where($where,$value);
    }



    protected function newRelatedInstance($class)
    {
        return new $class;
    }
}