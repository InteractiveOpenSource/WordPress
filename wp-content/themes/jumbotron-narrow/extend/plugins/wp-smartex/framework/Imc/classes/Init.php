<?php
class Init {

    public function __construct($driver = "standalone") {
        define("DRIVER", $driver);
        $init_class = "Lib_" . ucfirst(DRIVER) . "_Init";
        $init_file = LITHIUM_PLUGIN_ROOT . "/classes/" . str_replace('_', '/', $init_class) . ".php";
        try{
            if(is_file($init_file)){
                require_once $init_file;
                new $init_class();
            }else trigger_error('Initialization failed [' . $init_class . '] :: ' . $init_file . ' not found', E_USER_ERROR);
        }catch (Exception $e){
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }

}

Interface Interface_Init
{
    public static function find_file($directory, $file);

    public static function auto_load($class);

    public static function handler($error);
}