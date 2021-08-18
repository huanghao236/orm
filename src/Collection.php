<?php


namespace Hao;

use ArrayAccess;
use Hao\Concerns\Arrayable;
use IteratorAggregate;
use ArrayIterator;

class Collection implements IteratorAggregate,Arrayable
{

    protected $items = [];

    /**
     * @param  mixed  $items
     * @return void
     */
    public function __construct($items = [])
    {
        $this->items = $this->getArrayableItems($items);
    }

    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        }
        return (array) $items;
    }

    /**
     * 获取项目的迭代器(迭代器：用于遍历对象中的属性)
     * 使用IteratorAggregate接口实现getIterator()方法直接返回迭代器对象，获取遍历之后的对象信息
     * @return ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * 对象转数组
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->items);
    }

    /**
     * 将关联查询字段数据相同的集合在一起
     */
    public function mapToDictionary(callable $callback)
    {
        $data = [];
        foreach ($this->items as $key => $val){
            //执行匿名函数，返回指定键名对应的值
            $arr = $callback($val);
            $k = key($arr);
            //获取数组内部指针为第一个的数组
            $value = reset($arr);
            //若不存在这个键名，则赋予空数组
            if (!isset($data[$k])){
                $data[$k] = [];
            }
            $data[$k][] = $value;
        }
        return $data;
    }
}