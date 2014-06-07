<?php
/**
 * Smartex Project
 * @author : malaDev <irzhy.ran@gmail.com>
 *
 * Class Autoload
 *
 * @Licence
 *

The MIT License (MIT)

Copyright (c) 2014 InteractiveOpenSource

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

 *
 */


class Autoload{

    private static $_autoloaders = array();

    public static function add($call){
        if(is_callable($call))
            array_push(self::$_autoloaders, $call);
    }

    private static function _autoload(){
        foreach(self::$_autoloaders as $autoloader){
            spl_autoload_register($autoloader, true);
        }
    }

    public static function init(){
        self::_autoload();
    }

}

Autoload::add(function($classname){
    $file_path = SX_FRAMEWORK_PATH . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $classname) . '.php';
    if(is_file($file_path))
        include_once $file_path;
    else trigger_error('No ' . $classname . ' found ', E_USER_NOTICE);
});

do_action('smartex_autoload');

Autoload::init();