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
        return new Collection(array_map(function ($item) use ($items) {
            $model = $this->model->newFromBuilder((array) $item);
            $model->relations = $this->getRelations($model);
            return $model;
        }, $items));
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
        if ($item){
            $this->model->relations = $this->getRelations($this->model);
        }
        return $this->model;
    }

    /**
     * 获取关联查询数据
     */
    public function getRelations($model)
    {
        $relations = [];
        foreach ($this->eagerLoad as $name => $constraints){
            $relation = $model->{$name}();
            $constraints($relation);
            $relations[$name] = $relation->get();
        }
        return $relations;
    }

    public function __call(string $method, array $parameters)
    {
        $this->query->{$method}(...$parameters);
        return $this;
    }
}