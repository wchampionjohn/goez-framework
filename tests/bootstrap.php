<?php
error_reporting(E_ALL | E_STRICT);

date_default_timezone_set('Asia/Taipei');

define('APP_ROOT_PATH', realpath(dirname(__FILE__) . '/../'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APP_ROOT_PATH . '/lib'),
    get_include_path(),
)));

function autoload1($className)
{
    $className = str_replace('_', '/', $className);
    @include_once "$className.php";
    if (!class_exists($className, false) && !interface_exists($className, false)) {
        return false;
    }
    return true;
}

spl_autoload_register('autoload1');

function autoload2($className)
{
    $className = str_replace('\\', '/', $className);
    @include_once "$className.php";
    if (!class_exists($className, false) && !interface_exists($className, false)) {
        return false;
    }
    return true;
}

spl_autoload_register('autoload2');

function transDS($path)
{
    return str_replace('\\', '/', $path);
}