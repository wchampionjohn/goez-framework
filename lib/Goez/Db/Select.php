<?php
/**
 * Goez
 *
 * @package    Goez
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 * @version    $Id$
 */

namespace Goez\Db;

use Goez\Db;

/**
 * Select 語法產生器
 *
 * @package    Goez
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 */
class Select
{
    /**
     * SQL Parts
     *
     * @var array
     */
    protected $_parts = array(
        'DISTINCT' => false,
        'COLUMN' => array('*'),
        'TABLE' => 'TABLE',
        'DATA' => array(),
        'WHERE' => array(),
        'GROUP' => array(),
        'HAVING' => array(),
        'ORDER' => array(),
        'LIMIT_COUNT' => null,
        'LIMIT_OFFSET' => null,
    );

    /**
     * 資料庫連線物件
     *
     * @var Db
     */
    protected $_db = null;

    /**
     * @param Db $db
     */
    public function  __construct(Db $db = null)
    {
        $this->setDb($db);
    }

    /**
     * @param Db $db
     */
    public function setDb(Db $db)
    {
        $this->_db = $db;
    }

    /**
     * 選擇欄位
     *
     * @param mixied $cols
     * @return Db_Select
     */
    public function colnum($cols = null)
    {
        $cols = (array) $cols;
        if (!empty($cols)) {
            $this->_parts['COLUMN'] = $cols;
        }
        return $this;
    }

    /**
     * 設定 DISTINCT
     *
     * @param bool $flag
     * @return Db_Select
     */
    public function distinct($flag = true)
    {
        $this->_parts['DISTINCT'] = (bool) $flag;
        return $this;
    }

    /**
     * 設定資料表
     *
     * @param string $table
     * @return Db_Select
     */
    public function from($table)
    {
        $this->_parts['TABLE'] = $table;
        return $this;
    }

    /**
     * 設定條件式
     *
     * @param string $cond
     * @param mixed $value
     * @return Db_Select
     */
    public function where($cond, $value = null)
    {
        $this->_parts['WHERE'][] = $this->_where($cond, $value, true);
        return $this;
    }

    /**
     * 設定條件式
     *
     * @param string $cond
     * @param mixed $value
     * @return Db_Select
     */
    public function orWhere($cond, $value = null)
    {
        $this->_parts['WHERE'][] = $this->_where($cond, $value, false);
        return $this;
    }

    /**
     *
     * @param string $condition
     * @param mixed $value
     * @param bool $bool
     * @return string
     */
    protected function _where($condition, $value = null, $bool = true)
    {
        if ($value !== null) {
            $condition = $this->_db->quoteInto($condition, $value);
        }

        $cond = '';
        if ($this->_parts['WHERE']) {
            if ($bool === true) {
                $cond = 'AND ';
            } else {
                $cond = 'OR ';
            }
        }

        return $cond . "($condition)";
    }

    /**
     * 設定 HAVING 條件式
     *
     * @param mixed $cond
     * @return Db_Select
     */
    public function having($cond)
    {
        if (func_num_args() > 1) {
            $val = func_get_arg(1);
            $cond = $this->_db->quoteInto($cond, $val);
        }

        if ($this->_parts['HAVING']) {
            $this->_parts['HAVING'][] = "AND ($cond)";
        } else {
            $this->_parts['HAVING'][] = "($cond)";
        }

        return $this;
    }

    /**
     * 設定 HAVING 條件式
     *
     * @param mixed $cond
     * @return Db_Select
     */
    public function orHaving($cond)
    {
        if (func_num_args() > 1) {
            $val = func_get_arg(1);
            $cond = $this->_db->quoteInto($cond, $val);
        }

        if ($this->_parts['HAVING']) {
            $this->_parts['HAVING'][] = "OR ($cond)";
        } else {
            $this->_parts['HAVING'][] = "($cond)";
        }

        return $this;
    }

    /**
     * 設定 Group
     *
     * @param string $column
     * @return Db_Select
     */
    public function group($column)
    {
        $this->_parts['GROUP'][] = (string) $column;
        return $this;
    }

    /**
     * 設定 Order
     *
     * @param string $column
     * @return Db_Select
     */
    public function order($column, $dir = 'ASC')
    {
        $dir = strtoupper($dir);
        $dir = in_array($dir, array('ASC', 'DESC')) ? $dir : 'ASC';
        $this->_parts['ORDER'][(string) $column] = $dir;
        return $this;
    }

    /**
     * 設定 LIMIT
     *
     * @param int $count
     * @param int $offset
     * @return Db_Select
     */
    public function limit($count = null, $offset = null)
    {
        $this->_parts['LIMIT_COUNT']  = (int) $count;
        $this->_parts['LIMIT_OFFSET'] = (int) $offset;
        return $this;
    }

    /**
     * 設定分頁
     *
     * @param int $page
     * @param int $count
     * @return Db_Select
     */
    public function limitPage($page, $count)
    {
        $page = ($page > 0) ? $page : 1;
        $count = ($count > 0) ? $count : 1;
        $this->_parts['LIMIT_COUNT']  = (int) $count;
        $this->_parts['LIMIT_OFFSET'] = (int) $count * ($page - 1);
        return $this;
    }

    /**
     * 轉換成 SQL 語法
     *
     * @return string
     */
    public function __toString()
    {
        // COLUMN
        $sql = 'SELECT';
        if ($this->_parts['DISTINCT']) {
            $sql .= ' DISTINCT';
        }

        if ((1 === count($this->_parts['COLUMN'])) && ('*' === $this->_parts['COLUMN'][0])) {
            $columns = $this->_parts['COLUMN'];
        } else {
            $columns = array();
            foreach ($this->_parts['COLUMN'] as $col => $alias) {
                if (is_int($col)) {
                    $columns[] = $this->_db->quoteIdentifier($alias);
                } else {
                    $columns[] = $col . ' AS ' . $this->_db->quoteIdentifier($alias);
                }
            }
        }
        $sql .= ' ' . join(', ', $columns);

        // TABLE
        $sql .= ' FROM';
        $sql .= ' ' . $this->_db->quoteIdentifier($this->_parts['TABLE']);

        // WHERE
        if (!empty($this->_parts['WHERE'])) {
            $sql .= ' WHERE ';
            $sql .= implode(' ', $this->_parts['WHERE']);
        }

        // GROUP BY
        if (!empty($this->_parts['GROUP'])) {
            $sql .= ' GROUP BY ';
            $sql .= join(', ', array_map(array($this->_db, 'quoteIdentifier'), $this->_parts['GROUP']));
        }

        // HAVING
        if (!empty($this->_parts['HAVING'])) {
            $sql .= ' HAVING ' . implode(' ', $this->_parts['HAVING']);
        }

        // ORDER BY
        if (!empty($this->_parts['ORDER'])) {
            $sql .= ' ORDER BY ';
            $orders = array();
            foreach ($this->_parts['ORDER'] as $column => $dir) {
            	$orders[] = $this->_db->quoteIdentifier($column) . ' ' . $dir;
            }
            $sql .= join(', ', $orders);
        }

        // LIMIT
        if (!empty($this->_parts['LIMIT_COUNT']) && empty($this->_parts['LIMIT_OFFSET'])) {
            $sql .= ' LIMIT ' . (int) $this->_parts['LIMIT_COUNT'];
        } elseif (!empty($this->_parts['LIMIT_COUNT'])) {
            $sql .= ' LIMIT ' . (int) $this->_parts['LIMIT_OFFSET'] . ', ' . (int) $this->_parts['LIMIT_COUNT'];
        }

        return $sql;
    }
}
