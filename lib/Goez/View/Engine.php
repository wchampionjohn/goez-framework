<?php
/**
 * Goez
 *
 * @package    Goez
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 * @version    $Id$
 */

namespace GoEz\View;

/**
 * View Engine
 *
 * @package    Goez
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 */
interface Engine
{
    /**
     * 指定樣版變數
     *
     * @param mixed $name
     * @param mixed $value (可省略)
     */
    public function assign($name, $value = null);

    /**
     * 取得解析後的樣版內容
     *
     * @param string $file
     * @return string
     */
    public function fetch($file);
}