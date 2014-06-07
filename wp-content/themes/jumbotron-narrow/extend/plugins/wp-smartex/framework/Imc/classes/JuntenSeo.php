<?php

class JuntenSeo
{
    public static $title = "Mon petit site kohanast";


    public static function title($prefix = NULL, $suffix = NULL) {
        echo $prefix . self::$title . $suffix;
    }
    
    public function __get($name) {
        return I18n::get($name);
    }
    
    public function __isset($name) {
        return TRUE;
    }
    
    public static function limit_chars($text) {
    	return wp_trim_words($text);
    }
}
?>
