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
     * 数据库连接信息
     * @var array
     */
    private $config;

    /**
     * 数据库连接实例
     * @var PDO
     */
    private $createPdo;

    /**
     * 执行SQL
     * @param string $sql
     * @return array
     * @throws \Exception
     */
    public function sqlImplement(string $sql,$sqlType)
    {

        try {
            $statement = $this->connect()->prepare($sql);
            //为语句设置默认的获取模式
            $statement->setFetchMode(PDO::FETCH_OBJ);
            $this->bindValues($statement,$this->bindings);
            $statement->execute();
            if ($sqlType == 'select'){
                return $statement->fetchAll();
            }elseif($sqlType == 'update' || $sqlType == 'delete'){
                if ($statement->rowCount() > 0){
                    return true;
                }
                return false;
            }else{
                return $this->connect()->lastInsertId();
            }
        }catch (\Exception $e){
            throw $e;
        }
    }

    /**
     * 连接数据库
     * @throws \Exception
     */
    public function connect(): PDO
    {
        try {
            if (empty($this->config)){
                $this->config = config('database.connections.mysql');
            }
            // 连接参数
            if (isset($this->config['params']) && is_array($this->config['params'])) {
                $params = $this->config['params'] + $this->params;
            } else {
                $params = $this->params;
            }
            return $this->createPdo($this->parseDsn($this->config),$this->config,$params);
        }catch (\Exception $e){
            throw $e;
        }
    }

    /**
     * 创建数据库链接实例
     */
    private function createPdo($dsn,$config,$params)
    {
        if (empty($this->createPdo)){
            $this->createPdo = new PDO($dsn,$config['username'], $config['password'],$params);
        }
        return $this->createPdo;
    }

    /**
     * 绑定参数
     * @param object $statement
     * @param $bindings
     */
    public function bindValues($statement, $bindings)
    {
        $ke = 0;
        foreach ($bindings as $key => $value) {
            if (is_array($value) && $value){
                $value = array_values($value);
                foreach ($value as $k => $v){
                    $statement->bindValue(
                        is_string($k) ? $k : $ke + 1,
                        $v,
                        is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR
                    );
                    $ke++;
                }
            }
        }
    }


    /**
     * 解析pdo连接的dsn信息
     * @return string mysql:host=127.0.0.1;port=3306;dbname=wx;charset=utf8
     */
    protected function parseDsn($config)
    {
        if (!empty($config['hostport'])) {//端口号
            $dsn = 'mysql:host=' . $config['hostname'] . ';port=' . $config['hostport'];
        } else {
            $dsn = 'mysql:host=' . $config['hostname'];
        }
        $dsn .= ';dbname=' . $config['database'];

        if (!empty($this->config['charset'])) {
            $dsn .= ';charset=' . $config['charset'];
        }
        return $dsn;
    }
}