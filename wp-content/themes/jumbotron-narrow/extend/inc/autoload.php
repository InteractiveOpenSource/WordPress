<?php

function __extend_autoload($classname){
    $file = implode(DIRECTORY_SEPARATOR, array(dirname(dirname(__FILE__)), 'classes', $classname . '.php'));
    if(is_file($file)) require_once $file;
}
spl_autoload_register('__extend_autoload');