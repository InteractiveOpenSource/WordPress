<?php

class Helper_Cookie {

    private static $options = array(
        'expire' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => false
    );

    public $name;
    private $value;

    public static function Init($name, $value = null){
        self::option('domain', preg_replace('/http(s)?:\/\//', '', site_url()));
        return new self($name, $value);
    }

    public function __construct($name, $value = null){
        $this->name = $name;
        if(is_null($value)) {
            $this->value = array_key_exists($name, $_COOKIE) ? $_COOKIE[$name] : null;
        } else $this->set_value($value);
    }

    public function set_value($value){
        call_user_func_array('setcookie', array_merge(array(
            'name' => $this->name,
            'value' => $value
        ), self::$options));
        $this->value = $value;
    }

    public function get($default = null){
        return !is_null($this->value) ? $this->value : $default;
    }

    public function set_option($key, $value = null){
        self::$options[$key] = $value;
    }

    public static function option($key, $value = null){
        if(is_null($value)) return array_key_exists($key, self::$options) ? self::$options[$key] : null;
        else self::$options[$key] = $value;

        return $value;
    }

}