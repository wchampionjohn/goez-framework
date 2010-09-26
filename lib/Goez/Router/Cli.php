<?php
/**
 * Goez
 *
 * @package    Goez
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 * @version    $Id$
 */

namespace Goez\Router;

/**
 * CLI Router
 *
 * @package    Goez
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 */
class Cli extends \Goez\Router
{
    /**
     * 解析參數
     *
     * 主要用來取得目前參數上的 controller 和 action 。
     *
     * 使用者可以覆寫這個方法，用自己的方式取得 controller 和 action
     */
    protected function _parseUrl()
    {
        global $argv;
        $parsedArgv = Cli::parseArgs($argv);

        if (isset($parsedArgv['controller'])) {
            $this->_controller = strtolower($parsedArgv['controller']);
        } elseif (isset($parsedArgv['c'])) {
            $this->_controller = strtolower($parsedArgv['c']);
        }
        if (isset($parsedArgv['action'])) {
            $this->_action = strtolower($parsedArgv['action']);
        } elseif (isset($parsedArgv['a'])) {
            $this->_action = strtolower($parsedArgv['a']);
        }

        foreach ($parsedArgv as $key => $value) {
            $this->_request->setParam($key, $value);
        }
    }
}