<?php

define('APP_BASEPATH', dirname(dirname(__FILE__)));

function __application_autoload($classname){
    $filepath = implode(DIRECTORY_SEPARATOR, array( APP_BASEPATH, 'classes', str_replace('_', DIRECTORY_SEPARATOR, $classname) . '.php'));
    if(is_file($filepath)) require_once $filepath;
}
spl_autoload_register('__application_autoload');