<?php
error_reporting(E_ALL | E_STRICT);

date_default_timezone_set('Asia/Taipei');

define('APP_ROOT_PATH', realpath(dirname(__FILE__) . '/../'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APP_ROOT_PATH . '/lib'),
    get_include_path(),
)));

/**
 * 自動載入類別
 *
 */
require_once 'Goez/Loader.php';
Goez\Loader::autoload();
