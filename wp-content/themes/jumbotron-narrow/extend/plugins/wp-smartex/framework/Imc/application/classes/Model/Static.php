<?php
/**
 * Created by PhpStorm.
 * User: Imiary
 * Date: 04/06/14
 * Time: 17:25
 */

abstract class Model_Static {

    public static $filter_fields = array(
        'prix' => 'Prix',
        'prime_de_bienvenue' => 'Prime de bienvenue'
    );

    public static $order_map = array(
        'ASC' => 'Ascendant',
        'DESC' => 'Déscendant'
    );

    public static function get_label_order($name){ return array_key_exists($name, self::$order_map) ? self::$order_map[$name] : ''; }
    public static function get_label_field($name){ return array_key_exists($name, self::$filter_fields) ? self::$filter_fields[$name] : ''; }

} 