<?php

class Helper_MsgBox {

    public static function show($message){
        echo self::html($message);
        return self::html($message);
    }

    private static function html($message){
        $id = md5(microtime());
        $html = '';
        $html .= '<div id="msgbox-' . $id . '" class="update-nag">';
        $html .= $message;
        $html .= '</div>';
        return $html;
    }

}