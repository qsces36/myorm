<?php
/**
 * Created by PhpStorm.
 * User: Cumin
 * Date: 2016/7/30
 * Time: 16:44
 */
namespace Cumin\Db;

use Cumin\AppException;

class Pdo
{
    /**
     * @var \PDO
     */
    private $db;

    private $config;
    private $dns = '{driver}:dbname={dbname};host={host};port={port};charset={charset}';
    private $option = [
        'must' => [
            'driver',
            'host',
            'port',
            'dbname',
            'username',
            'password',
            'charset',
        ],
    ];

    private $last_id = null;
    private $row_count = 0;

    /**
     * @var \PDOStatement
     */
    private $stmt = '';
    private $error = '';

    function __construct($config)
    {
        $this->config = $config;
        $this->_parseConfig();
        $this->_link();
    }

    private function _parseConfig()
    {
        foreach ($this->option['must'] as $option) {
            if (!isset($this->config[$option])) {
                throw new AppException('Config Missing:' . $option);
            }
        }

        $this->dns = strtr($this->dns,
            [
                '{driver}'  => $this->config['driver'],
                '{dbname}'  => $this->config['dbname'],
                '{host}'    => $this->config['host'],
                '{port}'    => $this->config['port'],
                '{charset}' => $this->config['charset'],
            ]
        );
    }

    private function _link()
    {
        try {
            $this->db = new \PDO($this->dns, $this->config['username'], $this->config['password']);
            $this->db->setAttribute(\PDO::ATTR_TIMEOUT, 1);
            $this->db->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
            $this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new AppException('Connection Failed:' . $e->getMessage() . '|' . $this->config . '|' . var_export($this->config, true));
        }
    }

    private function _prepare($sql)
    {
        $this->error = '';
        $stmt        = $this->db->prepare($sql);
        if ($stmt === false) {
            $this->error = 'prepare false';

            return false;
        }
        $this->stmt = $stmt;

        return true;
    }

    private function _getStmtError()
    {
        $error = $this->stmt->errorInfo();
        if (!is_null($error[1])) {
            $this->error = implode('\t', $error);
        }
    }

    public function bind($bind)
    {
        if (!is_array($bind)) {
            $bind = [];
        }
        foreach ($bind as $k => $v) {
            if (is_array($v)) {
                $this->stmt->bindValue($k, $v['value'], $v['type']);
            } else {
                $this->stmt->bindValue($k, $v);
            }
        }

        return $this->stmt->execute();
    }

    public function find($sql, $bind = [])
    {
        if (!$this->_prepare($sql)) {
            return false;
        }
        if (!$this->bind($bind)) {
            $this->_getStmtError();

            return false;
        }
        $data = $this->stmt->fetch();
        $this->_getStmtError();

        return $data;
    }

    public function select($sql, $bind = [])
    {
        if (!$this->_prepare($sql)) {
            return false;
        }
        if (!$this->bind($bind)) {
            $this->_getStmtError();

            return false;
        }
        $data = $this->stmt->fetchAll();
        $this->_getStmtError();

        return $data;
    }

    public function exec($sql, $bind = [])
    {
        if (!$this->_prepare($sql)) {
            return false;
        }
        if (!$this->bind($bind)) {
            $this->_getStmtError();

            return false;
        }
        $this->last_id   = $this->db->lastInsertId();
        $this->row_count = $this->stmt->rowCount();

        return true;
    }

    public function getLastId()
    {
        return $this->last_id;
    }

    public function getRowCount()
    {
        return $this->row_count;
    }

    public function getError()
    {
        return $this->error;
    }

    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }

    public function commit()
    {
        return $this->db->commit();
    }

    public function rollBack()
    {
        return $this->db->rollBack();
    }
}