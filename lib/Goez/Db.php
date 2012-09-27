<?php
/**
 * Goez
 *
 * @package    Goez
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 * @version    $Id$
 */

namespace Goez;

/**
 * 資料庫抽象類別
 *
 * @package    Goez
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 */
class Db
{
    /**
     * @var \PDO
     */
    protected $_connection = null;

    /**
     * @var string
     */
    protected $_pdoType = 'mysql';

    /**
     * @var array
     */
    protected $_config = array();

    /**
     * 識別符
     *
     * @var string
     */
    protected $_identifier = '`';

    /**
     * @var int
     */
    protected $_fetchMode = \PDO::FETCH_ASSOC;

    /**
     * 工廠方法
     *
     * 用法：
     *
     * <code>
     * $db = Goez\Db::factory('mysql', array(
     *     'username' => 'webuser',
     *     'password' => 'xxxxxxxx',
     *     'dbname' => 'test',
     *     'driver_options' => array(...),
     * ));
     * </code>
     *
     * 或是：
     *
     * <code>
     * $db = Goez\Db::factory(array(
     *     'driver' => 'mysql',
     *     'params' => array(
     *         'username' => 'webuser',
     *         'password' => 'xxxxxxxx',
     *         'dbname' => 'test',
     *         'driver_options' => array(),
     * )));
     * </code>
     *
     * @param mixed $pdoType
     * @param array $config
     * @return \Goez\Db
     */
    public static function factory($pdoType, $config = array())
    {
        // 處理第一個參數為陣列的狀況
        if (is_array($pdoType)) {
            if (isset($pdoType['params'])) {
                $config = $pdoType['params'];
            }
            if (isset($pdoType['type'])) {
                $pdoType = (string) $pdoType['type'];
            } else {
                $pdoType = 'mysql';
            }
        }

        if (!is_string($pdoType) || empty($pdoType)) {
            throw new \Exception('Driver name must be specified in a string.');
        }

        return new self($pdoType, $config);
    }

    /**
     * 建構式
     *
     * @param array $options
     */
    public function __construct($pdoType, array $config)
    {
        $this->_pdoType = $pdoType;
        $this->_config = array_merge(array(
            'driver_options' => array(),
        ), $config);
    }

    /**
     * 連接資料庫
     */
    protected function _connect()
    {
        if ($this->_connection) {
            return;
        }

        $dsn = $this->_dsn();

        try {
            $this->_connection = new \PDO(
                $dsn,
                $this->_config['username'],
                $this->_config['password'],
                $this->_config['driver_options']
            );
            $this->_connection->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_NATURAL);
            $this->_connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * 建立 DSN
     *
     * @return string
     */
    protected function _dsn()
    {
        $dsn = $this->_config;

        // 移除 DSN 不需要的參數
        unset($dsn['username']);
        unset($dsn['password']);
        unset($dsn['options']);
        unset($dsn['charset']);
        unset($dsn['persistent']);
        unset($dsn['driver_options']);

        // 把剩下的設定建立為 DSN
        foreach ($dsn as $key => $val) {
            $dsn[$key] = "$key=$val";
        }

        return $this->_pdoType . ':' . implode(';', $dsn);
    }

    /**
     * 取得最後的自動編號
     *
     * @param string $tableName
     * @return int
     */
    public function lastInsertId($tableName = null)
    {
        $this->_connect();
        return $this->_connection->lastInsertId();
    }

    /**
     * @param string $sql
     * @return \PDOStatement
     */
    public function prepare($sql)
    {
        $this->_connect();
        return $this->_connection->prepare($sql);
    }

    /**
     * 建立 SQL 查詢
     *
     * @param string $sql
     * @param array $bind
     * @return \PDOStatement
     */
    public function query($sql, $bind = array())
    {
        if (is_array($bind)) {
            foreach ($bind as $name => $value) {
                if (!is_int($name) && !preg_match('/^:/', $name)) {
                    $newName = ":$name";
                    unset($bind[$name]);
                    $bind[$newName] = $value;
                }
            }
        }

        try {
            $this->_connect();

            if (!is_array($bind)) {
                $bind = array($bind);
            }

            $stmt = $this->prepare($sql);
            $stmt->execute($bind);
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            return $stmt;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * 建立 SELECT 語法
     *
     * @param array $cols
     * @return \Goez\Db\Select
     */
    public function select($cols = null)
    {
        $select = new Db\Select($this);
        return $select->colnum($cols);
    }

    /**
     * 取得所有記錄
     *
     * @param string|\Goez\Db\Select $sql
     * @param array $bind
     * @param int $fetchMode
     * @return array
     */
    public function fetchAll($sql, $bind = array(), $fetchMode = null)
    {
        if ($sql instanceof Db\Select) {
            $sql = $sql->__toString();
        }

        if ($fetchMode === null) {
            $fetchMode = $this->_fetchMode;
        }

        $stmt = $this->query($sql, $bind);
        $result = $stmt->fetchAll($fetchMode);
        return $result;
    }

    /**
     * 取得一筆資料
     *
     * @param string|\Goez\Db\Select $sql
     * @param array $bind
     * @param int $fetchMode
     * @return array
     */
    public function fetchRow($sql, $bind = array(), $fetchMode = null)
    {
        if ($sql instanceof Db\Select) {
            $sql = $sql->__toString();
        }

        if ($fetchMode === null) {
            $fetchMode = $this->_fetchMode;
        }

        $stmt = $this->query($sql, $bind);
        $result = $stmt->fetch($fetchMode);
        return $result;
    }

    /**
     * 將使用者輸入的值適當地加上引號
     *
     * 如果輸入的是陣列，那麼裡面的項目會在加上引號後，回傳為一個用逗號分隔的字串。
     *
     * @param mixed $value
     * @return mixed
     */
    public function quote($value)
    {
        if (is_array($value)) {
            foreach ($value as &$val) {
                $val = $this->quote($val);
            }
            return implode(', ', $value);
        }
        return $this->_quote($value);
    }

    /**
     * 轉換條件式
     *
     * 利用一個問號做為佔位符，然後將使用者傳入的值做轉換，例如：
     *
     * <code>
     * $text = "WHERE date < ?";
     * $date = "2005-01-02";
     * $safe = $sql->quoteInto($text, $date);
     * // $safe = "WHERE date < '2005-01-02'"
     * </code>
     *
     * @param string  $text  帶有佔位符的字串
     * @param mixed   $value 需要適當用引號包含的值
     * @param integer $count (選用) 佔位符要取代的次數
     * @return string 取代後的字串
     */
    public function quoteInto($text, $value, $count = null)
    {
        if ($count === null) {
            return str_replace('?', $this->quote($value), $text);
        } else {
            while ($count > 0) {
                if (strpos($text, '?') != false) {
                    $text = substr_replace($text, $this->quote($value), strpos($text, '?'), 1);
                }
                --$count;
            }
            return $text;
        }
    }

    /**
     * 包裝識別符
     *
     * @param mixed $value
     * @return mixed
     */
    public function quoteIdentifier($value)
    {
        if (is_string($value)) {
            return $this->_identifier . $value . $this->_identifier;
        } elseif (is_array($value)) {
            $result = array();
            foreach ($value as $item) {
            	$result[] = $this->quoteIdentifier($item);
            }
            return $result;
        }
    }

    /**
     * 轉換條件式
     *
     * @param mixed $where
     * @return string
     */
    protected function _whereExpr($where)
    {
        if (empty($where)) {
            return $where;
        }
        if (!is_array($where)) {
            $where = array($where);
        }
        foreach ($where as $cond => &$term) {
            if (!is_int($cond)) {
                $term = $this->quoteInto($cond, $term);
            }
            $term = '(' . $term . ')';
        }

        $where = implode(' AND ', $where);
        return $where;
    }

    /**
     * 將值做適當地引號包含
     *
     * @param mixed $value
     * @return mixed
     */
    protected function _quote($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        $this->_connect();
        return $this->_connection->quote($value);
    }

    /**
     * @param int $mode
     */
    public function setFetchMode($mode)
    {
        switch ($mode) {
            case \PDO::FETCH_LAZY:
            case \PDO::FETCH_ASSOC:
            case \PDO::FETCH_NUM:
            case \PDO::FETCH_BOTH:
            case \PDO::FETCH_NAMED:
            case \PDO::FETCH_OBJ:
                $this->_fetchMode = $mode;
                break;
            default:
                throw new \PDOException("Invalid fetch mode '$mode' specified");
                break;
        }
    }

    /**
     * 新增紀錄
     *
     * 用法：
     *
     * <code>
     * $db->insert('users', array(
     *     'name' => 'John',
     *     'age' => 20,
     * ));
     * </code>
     *
     * @param string $table 資料表名稱
     * @param array $bind 欲新增的資料
     * @return int 新增筆數
     */
    public function insert($table, array $bind)
    {
        // 轉換欲新增的資料
        $cols = array();
        $vals = array();
        foreach ($bind as $col => $val) {
            $cols[] = $this->quoteIdentifier($col, true);
            $vals[] = '?';
        }

        // 建立 INSERT 語法
        $sql = "INSERT INTO "
             . $this->quoteIdentifier($table, true)
             . ' (' . implode(', ', $cols) . ') '
             . 'VALUES (' . implode(', ', $vals) . ')';

        // 執行 SQL 語法並回傳影響筆數
        $stmt = $this->query($sql, array_values($bind));
        $result = $stmt->rowCount();
        return $result;
    }

    /**
     * 更新紀錄
     *
     * 用法：
     *
     * <code>
     * $db->update('users', array(
     *     'name' => 'John',
     *     'age' => 21,
     * ), array(
     *     'id = ?' => 1,
     *     'age > 0',
     * ));
     * </code>
     *
     * 特別注意這裡第三個參數的用法，陣列的索引可以是帶有 ? 號的條件式。
     *
     * @param string $table 資料表名稱
     * @param array $bind 更新資料
     * @param array $where 條件式陣列，陣列的索引可以是帶有 ? 號的條件式。
     * @return int 更新筆數
     */
    public function update($table, $bind, $where)
    {
        // 轉換欲新增的資料
        $set = array();
        $i = 0;
        foreach ($bind as $col => $val) {
            unset($bind[$col]);
            $bind[':' . $col . $i] = $val;
            $val = ':' . $col . $i;
            $i ++;
            $set[] = $this->quoteIdentifier($col, true) . ' = ' . $val;
        }

        $where = $this->_whereExpr($where);

        // 建立 UPDATE 語法
        $sql = "UPDATE "
             . $this->quoteIdentifier($table, true)
             . ' SET ' . implode(', ', $set)
             . (($where) ? " WHERE $where" : '');

        // 執行 SQL 語法並回傳影響筆數
        $stmt = $this->query($sql, $bind);
        $result = $stmt->rowCount();
        return $result;
    }

    /**
     * 刪除紀錄
     *
     * 用法：
     *
     * <code>
     * $db->delete('users', array(
     *     'id = ?' => 1,
     * ));
     * </code>
     *
     * @param string $table 資料表名稱
     * @param array $where 條件式陣列，陣列的索引可以是帶有 ? 號的條件式。
     * @return int 更新筆數
     */
    public function delete($table, $where)
    {
        $where = $this->_whereExpr($where);

        // 建立 DELETE 語法
        $sql = "DELETE FROM "
             . $this->quoteIdentifier($table)
             . (($where) ? " WHERE $where" : '');

        // 執行 SQL 語法並回傳影響筆數
        $stmt = $this->query($sql);
        $result = $stmt->rowCount();
        return $result;
    }

    /**
     * @return \Goez\Db
     */
    public function beginTransaction()
    {
        $this->_connect();
        $this->_connection->beginTransaction();
        return $this;
    }

    /**
     * @return \Goez\Db
     */
    public function commit()
    {
        $this->_connect();
        $this->_connection->commit();
        return $this;
    }

    /**
     * @return \Goez\Db
     */
    public function rollBack()
    {
        $this->_connect();
        $this->_connection->rollBack();
        return $this;
    }

    /**
     * 是否已經連接資料庫
     *
     * @return bool
     */
    public function isConnected()
    {
        return ((bool) ($this->_connection instanceof \PDO));
    }

    /**
     * 強制關閉資料庫連線
     *
     * @return void
     */
    public function closeConnection()
    {
        $this->_connection = null;
    }

    /**
     * 取得資料庫版本
     *
     * @return string
     */
    public function getServerVersion()
    {
        $this->_connect();
        try {
            $version = $this->_connection->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (\PDOException $e) {
            // In case of the driver doesn't support getting attributes
            return null;
        }
        $matches = null;
        if (preg_match('/((?:[0-9]{1,2}\.){1,3}[0-9]{1,2})/', $version, $matches)) {
            return $matches[1];
        } else {
            return null;
        }
    }
}
