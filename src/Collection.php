<?php


namespace Hao;


class Collection
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
}