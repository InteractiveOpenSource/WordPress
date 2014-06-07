<?php

class Extend {

    /**
     * @param $plugin_init_file
     */
    public static function add_plugin($plugin_init_file){

        if(!preg_match('/\.php/', $plugin_init_file)) $plugin_init_file .= '.php';

        if(is_file(FS::path_to(_JMB_PLUGINS_DIR, $plugin_init_file)))
            require_once FS::path_to(_JMB_PLUGINS_DIR, $plugin_init_file);
        else trigger_error(sprintf("Loading %s failed", $plugin_init_file));
    }

} 