<?php

if(!class_exists("Init")) {
    define("LITHIUM_PLUGIN_ROOT", implode(DIRECTORY_SEPARATOR, array(SX_FRAMEWORK_PATH,  "Imc")));

	require_once LITHIUM_PLUGIN_ROOT . "/classes/Init.php";
}

if(!session_id())
    session_start();

new Init("wordpress");