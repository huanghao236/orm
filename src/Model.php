<?php
namespace Hao;

use Hao\db\BaseQuery;

abstract class Model
{
    /**
     * 表名
     * @var string
     */
    protected $table;

    public $attributes;

    public $original;

    protected function query()
    {
        return new Builder(new BaseQuery($this->table),$this);
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