<?php


namespace Hao;


use Hao\db\BaseQuery;

class Builder
{

    private $query;

    private $model;

    public function __construct(BaseQuery $query,$model)
    {
        $this->query = $query;
        $this->model = $model;
    }


    public function get()
    {
        $items = $this->query->get();
        $model = $this->model;
        return new Collection(array_map(function ($item) use ($items, $model) {
            $model->attributes = $model->original = (array) $item;
            return $model;
        }, $items));
    }


    public function first()
    {
        $items = $this->query->take(1)->get();
        $item = !empty($items) ? reset($items) : null;
        $this->model->attributes = $this->model->original = (array) $item;
        return $this->model;
    }

    public function __call(string $method, array $parameters)
    {
        $this->query->{$method}(...$parameters);
        return $this;
    }
}