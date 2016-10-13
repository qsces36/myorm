<?php
/**
 * Created by PhpStorm.
 * User: Cumin
 * Date: 2016/7/30
 * Time: 19:00
 */
namespace Cumin;

use Cumin\Db\Pdo;

class DB
{
    const INSERT = 1;
    const SELECT = 2;
    const UPDATE = 3;
    const DELETE = 4;

    //比较运算符
    const WHERE_STR = false;
    const WHERE_CO_EQ = '<=>';
    const WHERE_CO_NEQ = '<>';
    const WHERE_CO_GT = '>';
    const WHERE_CO_EGT = '>=';
    const WHERE_CO_LT = '<';
    const WHERE_CO_ELT = '<';
    const WHERE_CO_LIKE = 'LIKE';
    const WHERE_CO_BETWEEN = 'BETWEEN';
    const WHERE_CO_NOT_BETWEEN = 'NOT BETWEEN';
    const WHERE_CO_IN = 'IN';
    const WHERE_CO_NOT_IN = 'NOT IN';
    const WHERE_CO_REGEXP = 'REGEXP';

    //排序
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    /**
     * @var Pdo
     */
    private static $db;
    //测试模式
    private $debug = false;

    //最后执行的sql语句
    private $sql = '';

    //插入、更新数据
    private $data = [];
    //需要查询的数据段
    private $field = '*';
    //需要查询的表
    private $table = '';
    //查询条件
    private $where = '';
    //排序方式
    private $order = '';
    //数据查询条数
    private $limit = '';
    //数据页数
    private $page = '';
    //数据分组
    private $group = '';

    //是否只查询单条记录
    private $one = false;
    //内置的比较运算符
    private $where_co = [self::WHERE_CO_EQ, self::WHERE_CO_NEQ, self::WHERE_CO_GT, self::WHERE_CO_EGT, self::WHERE_CO_LT, self::WHERE_CO_ELT,
        self::WHERE_CO_LIKE, self::WHERE_CO_BETWEEN, self::WHERE_CO_NOT_BETWEEN, self::WHERE_CO_IN, self::WHERE_CO_NOT_IN, self::WHERE_CO_REGEXP];

    //最后查询id
    private static $last_id = 0;
    //影响行数
    private static $row_count = 0;

    //错误
    private static $error = '';

    public function __construct($config = null)
    {
        if (!isset($config)) {
            $config = $this->_loadConfig();
        }
        self::$db = new Pdo($config);
    }

    private static function _loadConfig()
    {
        $config = [];

        return $config;
    }

    /**
     * 实例化数据库对象
     *
     * @param null $config
     *
     * @return DB
     */
    public static function M($config = null)
    {
        if ($config === null) {
            $config = self::_loadConfig();
        }
        $db = new DB($config);

        return $db;
    }

    /**
     * 开启测试模式，结果只输出sql语句而不执行
     *
     * @return $this
     */
    public function debug()
    {
        $this->debug = true;

        return $this;
    }

    /**
     * 存入需要查询的字段
     *
     * @param $field
     *
     * @return $this
     */
    public function field($field)
    {
        if (is_array($field)) {
            $this->field = implode(',', $field);
        } elseif (is_string($field)) {
            $this->field = $field;
        } else {
            $this->field = '*';
        }

        return $this;
    }

    /**
     * 存入查询表
     *
     * @param $table
     *
     * @return $this
     */
    public function table($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * 存入数据
     *
     * @param $data
     *
     * @return $this
     */
    public function data($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * 存入查询条件
     *
     * @param $where array|string 查询条件
     *
     * @return $this
     */
    public function where($where)
    {
        $this->_parseWhere($where);

        return $this;
    }

    /**
     * 存入需要查询的数据条数
     *
     * @param $start int 开始条数
     * @param $count int 结束条数
     *
     * @return $this
     */
    public function limit($start, $count)
    {
        $this->limit = "LIMIT {$start},{$count}";

        return $this;
    }

    public function order($order)
    {
        if ($order == self::ORDER_ASC || $order == self::ORDER_DESC) {
            $this->order = $order;
        } else {
            $this->order = self::ORDER_ASC;
        }

        return $this;
    }

    /**
     * 执行INSERT
     *
     * @return string
     */
    public function insert()
    {
        $this->_parseSql(self::INSERT);

        if ($this->_execute()) {
            return (int)self::$last_id;
        } else {
            return false;
        }
    }

    /**
     * 执行SELECT查询
     *
     * @return string
     */
    public function select()
    {
        $this->_parseSql(self::SELECT);

        return $this->_query();
    }

    public function find()
    {
        $this->_parseSql(self::SELECT);
        $this->one = true;

        return $this->_query();
    }

    /**
     * 执行UPDATE查询
     *
     * @return string
     */
    public function update()
    {
        $this->_parseSql(self::UPDATE);

        return $this->_execute();
    }

    /**
     * 执行DELETE查询
     *
     * @return string
     */
    public function delete()
    {
        $this->_parseSql(self::DELETE);

        return $this->_execute();
    }

    public function count()
    {
        $this->field = "COUNT(1) AS C";
        $this->_parseSql(self::SELECT);
        $this->one = true;

        return (int)$this->_query()['C'];
    }

    public function execute($sql)
    {
        $this->sql = $sql;

        return $this->_execute();
    }

    public function query($sql)
    {
        $this->sql = $sql;

        return $this->_query();
    }

    /**
     * 解析操作
     *
     * @param $operate
     */
    private function _parseSql($operate)
    {
        $sql = '';
        switch ($operate) {
            case self::INSERT:
                $insert_sql = $this->_parseInsert($this->data);
                $sql        = "INSERT INTO `{$this->table}` {$insert_sql}";
                break;
            case self::SELECT:
                $sql = "SELECT {$this->field} FROM `{$this->table}` {$this->where} {$this->order} {$this->limit}";
                break;
            case self::UPDATE:
                $update_sql = $this->_parseUpdate($this->data);
                $sql        = "UPDATE `{$this->table}` SET {$update_sql} {$this->where}";
                break;
            case self::DELETE:
                $sql = "DELETE FROM `{$this->table}` {$this->where}";
                break;
        }
        $this->sql = $sql;
    }

    /**
     * 解析插入数据，返回SQL语句
     *
     * @param $data
     *
     * @return bool|string
     */
    private function _parseInsert($data)
    {
        if (!$data) {
            return false;
        }
        $tmp_key   = [];
        $tmp_value = [];

        foreach ($data as $key => $value) {
            $tmp_key[]   = "`{$key}`";
            $tmp_value[] = "'{$value}'";
        }

        $key_str   = implode(',', $tmp_key);
        $value_str = implode(',', $tmp_value);

        $insert_sql = "({$key_str}) VALUES ({$value_str})";

        return $insert_sql;
    }

    /**
     * 解析where，直接赋值到成员变量
     *
     * @param $where
     */
    private function _parseWhere($where)
    {
        if (!$where) {
            return;
        }
        $this->where = 'WHERE ';
        if (is_array($where)) {
            foreach ($where as $field => $value) {
                //解析field是否有表名关联
                if (strpos($field, '.') > 0) {
                    list($tableAlias, $bindField) = explode('.', $field);
                    $tableAlias .= '.';
                } else {
                    $tableAlias = '';
                    $bindField  = $field;
                }

                //解析value
                if (is_array($value)) {
                    list($exp, $bindValue) = $value;
                    if (in_array($exp, $this->where_co)) {

                    } elseif ($exp === self::WHERE_STR) {
                        $exp = '';
                    } else {
                        $exp = '<=>';
                    }
                    $this->where .= "{$tableAlias}`{$bindField}` {$exp} '{$bindValue}'";
                } else {
                    $this->where .= "{$tableAlias}`{$bindField}` <=> '{$value}'";
                }

                $this->where .= ' AND ';
            }
            $this->where = substr($this->where, 0, -5);

        } elseif (is_string($where)) {
            $this->where .= $where;
        }
    }

    /**
     * 解析更新数据，返回SQL语句
     *
     * @param $data
     *
     * @return bool|string
     */
    private function _parseUpdate($data)
    {
        if (!$data) {
            return false;
        }

        $update_sql = '';

        foreach ($data as $key => $value) {
            $update_sql .= "`{$key}`='{$value}',";
        }
        $update_sql = substr($update_sql, 0, -1);

        return $update_sql;
    }

    /**
     * 执行sql语句
     *
     * @return bool
     */
    private function _execute()
    {
        if ($this->debug) {
            return $this->sql;
        } else {
            $result = self::$db->exec($this->sql);

            self::$last_id   = self::$db->getLastId();
            self::$row_count = self::$db->getRowCount();
            self::$error     = self::$db->getError();

            return $result;
        }
    }

    /**
     * 执行sql语句
     *
     * @return array|bool|mixed
     */
    private function _query()
    {
        if ($this->debug) {
            return $this->sql;
        } else {

            if ($this->one) {
                $data = self::$db->find($this->sql);
            } else {
                $data = self::$db->select($this->sql);
            }

            self::$error = self::$db->getError();

            return $data;
        }
    }
}
