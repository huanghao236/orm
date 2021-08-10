<?php


namespace Hao;

use ArrayAccess;
use IteratorAggregate;
use ArrayIterator;

class Collection implements IteratorAggregate
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
}