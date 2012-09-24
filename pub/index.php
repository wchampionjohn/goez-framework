<?php
/**
 * 定義常數
 *
 */
defined('APP_ENV') || define('APP_ENV', (getenv('APP_ENV') ? getenv('APP_ENV') : 'production'));
defined('APP_ROOT_PATH') || define('APP_ROOT_PATH', dirname(__DIR__));
define('APP_LIB_PATH', APP_ROOT_PATH . '/lib');
define('APP_ETC_PATH', APP_ROOT_PATH . '/etc');

/**
 * 設定載入路徑
 *
 */
$includePath = array(APP_LIB_PATH, '.');
set_include_path(join(PATH_SEPARATOR, $includePath));

/**
 * 自動載入類別
 *
 */
require_once 'Goez/Loader.php';
Goez\Loader::autoload();

/**
 * 執行
 *
 */
Goez\Bootstrap::run(APP_ETC_PATH . '/config.ini', APP_ENV);