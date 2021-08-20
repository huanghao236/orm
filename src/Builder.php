<?php


namespace Hao;


use App\Http\Models\Channel;
use Hao\db\BaseQuery;

class Builder
{

    private $query;

    private $model;

    private $eagerLoad = [];

    public function __construct(BaseQuery $query,$model)
    {
        $this->query = $query;
        $this->model = $model;
    }

    /**
     * 设置应立即加载的关系
     * @param $relations
     */
    public function with($relations)
    {
        $eagerLoad = is_string($relations) ? func_get_args() : $relations;
        $this->eagerLoad = array_merge($this->eagerLoad, $eagerLoad);
        return $this;
    }

    /**
     * 查询多条
     * @return Collection
     */
    public function get()
    {
        $items = $this->query->get();
        if ($items && $this->eagerLoad){
            $this->model->items = (array) $items;
            $models = array_map(function ($item) use ($items) {
                $model = $this->model->newFromBuilder((array) $item);
                return $model;
            }, $items);
            return new Collection($this->getRelations($this->model,'get',$models));
        }else{
            return new Collection(array_map(function ($item) use ($items) {
                $model = $this->model->newFromBuilder((array) $item);
                return $model;
            }, $items));
        }
    }


    /**
     * 查询单条
     * @return mixed
     */
    public function first()
    {
        $items = $this->query->take(1)->get();
        $item = !empty($items) ? reset($items) : null;
        $this->model->attributes = $this->model->original = (array) $item;
        if ($item && $this->eagerLoad){
            $this->model->items = (array) $item;
            $this->model->relations = $this->getRelations($this->model,'first');
        }
        return $this->model;
    }

    /**
     * 获取关联查询数据
     */
    public function getRelations($thisModel,$selectType,$models = [])
    {

        foreach ($this->eagerLoad as $name => $constraints){
            //执行hasOne或hasMany方法
            $relation = $thisModel->{$name}();
            //执行查询语句中的匿名方法
            $constraints($relation);
            //将关联查询字段数据相同的集合在一起
            $dictionary = $thisModel->buildDictionary($relation->get());
            if ($selectType == 'get'){
                foreach ($models as $model){
                    if (isset($dictionary[$model->{$thisModel->localKey}])){
                        $model->relations[$name] = $this->getRelationValue($thisModel->type,$dictionary,$model->{$thisModel->localKey});
                    }
                }
            }else{
                $models[$name] = $this->getRelationValue($thisModel->type,$dictionary,$thisModel->{$thisModel->localKey});
            }
        }
        return $models;
    }

    /**
     * 通过一种或多种的类型，获取关系的值
     */
    public function getRelationValue($type,$dictionary,$key)
    {
        $value = $dictionary[$key];
        return $type === 'one' ? reset($value) : new Collection($value);
    }

    public function __call(string $method, array $parameters)
    {
        $this->query->{$method}(...$parameters);
        return $this;
    }
}