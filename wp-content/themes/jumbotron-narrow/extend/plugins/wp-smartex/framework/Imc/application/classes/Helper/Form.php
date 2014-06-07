<?php

class Helper_Form {

    public static function attr($data = array()){
        $attr_str = '';
        $json_opt = defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 256;
        foreach($data as $attr => $value){
            if(gettype($value) == 'array') $value = '\'' . json_encode($value, $json_opt) . '\'';
            else $value = '"' . (String)$value .'"';
            $attr_str .= $attr . '=' . $value . ' ';
        }
        return $attr_str;
    }

    public static function select($name, $options, $value = NULL, $attrs = array()){
        $select = '<select name="' . $name . '" ' . self::attr($attrs) . ' >';
        $select .= '<option>- Choisissez ici -</option>';
        foreach($options as $opt_value => $opt_label){
            $select .= '<option value="' . $opt_value . '" ' . (($opt_value == $value) ? 'selected="selected"' : '') . ' >' . $opt_label . '</option>';
        }
        $select .= '</select>';

        return $select;
    }

    public static function radio($name, $options, $value = NULL, $attrs = array()){
        $input = '';
        foreach($options as $opt_value){
            if($opt_value == $value && $opt_value != null) $ck = array('checked' => 'checked');
            else $ck = array();
            $input .= '<input type="radio" ';
            $input .= 'name="' . $name . '" ';
            $input .= 'value="' . $opt_value . '" ';
            $input .= ' ' . self::attr(array_merge($attrs, $ck));
            $input .= '/>&nbsp;' . $opt_value;
            $input .= '&nbsp;&nbsp;&nbsp;&nbsp;';
        }

        return $input;
    }

    public static function checkbox($name, $options, $value = array(), $attrs = array()){
        $input = '';
        foreach($options as $opt_value){
            if($opt_value == '' || is_null($opt_value)) continue;
            if(($opt_value == $value || in_array($opt_value, $value)) && $opt_value != null) $ck = array('checked' => 'checked');
            else $ck = array();
            $input .= '<input type="checkbox" ';
            $input .= 'name="' . $name . '" ';
            $input .= 'value="' . $opt_value . '" ';
            $input .= ' ' . self::attr(array_merge($attrs, $ck));
            $input .= '/>&nbsp;' . $opt_value;
            $input .= '<br/>';
        }

        return $input;
    }

    public static function input($name, $value = NULL, $attrs = array()){
        $input = '<input type="text" ';
        $input .= 'name="' . $name . '" ';
        $input .= 'value="' . $value . '" ';
        $input .= ' ' . self::attr($attrs);
        $input .= '/>';

        return $input;
    }

} 