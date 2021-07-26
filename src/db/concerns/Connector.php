<?php


namespace Hao\db\concerns;

use PDO;

trait Connector
{


    /**
     * PDO连接参数
     * @var array
     */
    protected $params = [
        //强制列名为指定的大小写           //保留数据库驱动返回的列名
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        //错误报告                      //抛出 exceptions 异常
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        //转换 NULL 和空字符串           //不转换
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        //提取的时候将数值转换为字符串
        PDO::ATTR_STRINGIFY_FETCHES => false,
        //启用或禁用预处理语句的模拟
        PDO::ATTR_EMULATE_PREPARES  => false,
    ];



    /**
     * 创建数据库链接实例
     */
    private function createPdo()
    {
        //mysql:host=127.0.0.1;port=3306;dbname=wx;charset=utf8
        return new PDO();
    }
}