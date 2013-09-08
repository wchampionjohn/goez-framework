<?php

namespace Goez;

defined('APP_PATH') || define('APP_PATH', dirname(__DIR__));
defined('LIB_PATH') || define('LIB_PATH', APP_PATH . '/vendor');

Loader::load(array(
    LIB_PATH,
    get_include_path(),
));

Application::start(APP_PATH);