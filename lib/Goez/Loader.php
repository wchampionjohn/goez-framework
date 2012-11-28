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
 * 自動載入類別
 *
 * @package    Goez
 * @copyright  Copyright (c) 2008-2012 Wabow Information Inc. (http://www.wabow.com)
 * @license    New BSD License
 */
class Loader
{
    /**
     * 私有初始化
     *
     */
    private function __construct() {}

    /**
     * 自動載入
     *
     * 自動載入類別檔案
     */
    public static function autoload()
    {
        $loader = new self();
        spl_autoload_register(array($loader, 'loadClass'));
    }

    /**
     * 載入類別
     *
     * 將類別名稱 Xx_Yyy_Zzz 或 Xx\Yyy\Zzz 轉為 Xx/Yyy/Zzz.php 後載入
     *
     * @param string $className
     * @throws Excetion
     */
    public function loadClass($className)
    {
        if (class_exists($className, false) || interface_exists($className, false)) {
            return true;
        }

        $fileName = str_replace(array('\\', '_'), '/', $className) . '.php';

        $loader = new self();

        set_error_handler(array($loader, 'errorHandler'));
        include_once $fileName;
        restore_error_handler();

        if (!class_exists($className, false) && !interface_exists($className, false)) {
            return false;
        }

        return true;
    }

    /**
     * 錯誤處理
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param array $errcontext
     */
    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
    }
}

