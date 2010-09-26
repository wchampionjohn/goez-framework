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
 * Response 類別
 *
 * @package    Goez
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 */
class Response
{
    /**
     * 異常列表
     *
     * @var array
     */
    protected $_exceptions = array();

    /**
     * 是否顯示異常
     *
     * @var bool
     */
    protected $_renderExceptions = false;

    /**
     * 設定異常
     *
     * @param Exception $e
     */
    public function setException(Exception $e)
    {
        $this->_exceptions[] = $e;
    }

    /**
     * 取得所有異常
     *
     * @return array
     */
    public function getExceptions()
    {
        return $this->_exceptions;
    }

    /**
     * 是否為異常
     *
     * @return bool
     */
    public function isException()
    {
        return !empty($this->_exceptions);
    }

    /**
     * 設定並取得是否顯示異常
     *
     * @param bool $flag
     * @return bool
     */
    public function renderExceptions($flag = null)
    {
        if (null !== $flag) {
            $this->_renderExceptions = $flag ? true : false;
        }

        return $this->_renderExceptions;
    }

    /**
     * 送出回應
     *
     */
    public function sendResponse()
    {
        echo "Header sending...\n";
        $exception = '';
        if ($this->isException() && $this->renderExceptions()) {
            foreach ($this->getExceptions() as $e) {
                $exception .= $e->getMessage() . "\n";
            }
            echo $exception;
        }
        echo "Body sending...\n";
    }
}