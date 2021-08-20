<?php
namespace Hao;

use Hao\Concerns\Arrayable;
use Hao\db\BaseQuery;

abstract class Model implements Arrayable
{
    use Concerns\HasRelationships;
    /**
     * 表名
     * @var string
     */
    protected $table;

    public $attributes;

    public $original;

    public $relations;

    public $items;

    protected function query()
    {
        return new Builder(new BaseQuery($this->table),$this);
    }


    public function newFromBuilder($attributes = [])
    {
        $model = new static();
        $model->attributes = $model->original = $attributes;
        return $model;
    }

    /**
     * 对象转数组
     */
    public function toArray()
    {
        $item = [];
        // 合并关联数据
        $data = array_merge($this->attributes ?? [], $this->relations ?? []);
        foreach ($data as $k => $v){
            if ($v instanceof Arrayable){
                $item[$k] = $v->toArray();
            }else{
                $item[$k] = $v;
            }
        }
        return $item;
    }

    /**
     * 更新或者新增数据
     */
    public function save()
    {
        $query = new BaseQuery($this->table);
        if ($this->original){
            if (!isset($this->attributes['id'])){
                return true;
            }else{
                $value = $this->attributes;
                unset($value['id']);
                $this->original = $this->attributes;
                return $query->where('id',$this->attributes['id'])->update($value);
            }
        }else{
            //返回自增的主键值
            return $query->insert($this->attributes);
        }
    }


    public function __get($key)
    {
        return $this->attributes[$key] ?? $this->relations[$key] ?? $this->{$key}()->get() ?? '';
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * 获取不存在的动态方法
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        if (in_array($method, ['increment', 'decrement'])) {
            return $this->$method(...$parameters);
        }
        
        return $this->query()->{$method}(...$parameters);
    }

    /**
     * 获取不存在的静态防范
     * @param  string  $method    方法名
     * @param  array  $parameters 参数
     * @return mixed
     */
    public static function __callStatic(string $method, array $parameters)
    {
        return (new static)->$method(...$parameters);
    }

}