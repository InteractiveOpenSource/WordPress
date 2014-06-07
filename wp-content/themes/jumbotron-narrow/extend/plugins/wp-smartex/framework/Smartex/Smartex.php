<?php
/**
 * Smartex Project
 * @author : malaDev <irzhy.ran@gmail.com>
 * @Licence : MIT
 *
 * Smartex Class
 * Main class of the plugin, router, and main controller of actions in the framework
 */

namespace Smartex;


class Smartex {

    static function run(){
        do_action('smartex_init');

        add_filter('smartex_route', function(){
            return Smartex::Pathto(SX_ABSPATH, 'application', 'functions.php');
        });

        add_action('smartex_load', function(){
            include_once SX_FRAMEWORK_PATH . '/Imc/application/inc/autoload.php';
        });

        do_action('smartex_run');
    }

    static function Init(){
        do_action('smartex_init');
    }

    static function Sequence($relpathfile){
        add_action('smartex_run', function() use($relpathfile){
            require_once SX_FRAMEWORK_PATH . DIRECTORY_SEPARATOR . $relpathfile . '.php';
        });
    }

    static function Load($name){
        add_action('smartex_run', function() use($name){
            require_once SX_ABSPATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . $name;
        });
    }

    static function Pathto(){
        $arg = func_get_args();
        return implode(DIRECTORY_SEPARATOR, $arg);
    }

} 