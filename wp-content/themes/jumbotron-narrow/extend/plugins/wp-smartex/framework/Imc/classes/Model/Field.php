<?php

class Model_Field {

    public static $default_structure = array(
        'title' => null,
        'slug' => null,
        'type' => 'text'
    );

    public static $map_type = array(
        'text' => 'Texte',
        'bool' => 'Dichotomique (Oui/Non)',
        'numeric' => 'Numérique'
    );

    public $type, $title, $slug;

    public function __construct($args = array()){
        foreach(array_merge(self::$default_structure, $args) as $key => $value) $this->{$key} = $value;
        if(is_null($this->slug)) $this->slug = str_replace("-", "_", sanitize_title($this->title));
        return $this;
    }

    public static function factory($title, $type = 'text', $args = array()){
        $args = array_merge($args, array(
            'title' => ucfirst($title),
            'type' => $type
        ));
        return new self($args);
    }

    /**
     * @param $item
     * @return Model_Field
     * Adapter for option in WP
     */
    public static function option_adapter($item, $slug = null){
        if($item instanceof Model_Field) {
            if(!is_null($slug)) $item->slug = $slug;
            return $item;
        }else {
            if(!is_null($slug)) $item['slug'] = $slug;
            return new self((Array) $item);
        }
    }

    /**
     * @param $ops
     * @param bool $to_array
     * @return mixed
     * walk an array to transform it to an array of Model_Field
     */
    public static function option_adapter_walker(&$ops, $to_array = false){
        array_walk($ops, function(&$item, $slug, $arr){
            $item = Model_Field::option_adapter($item, $slug);
            if($arr) $item = (Array)$item;
        }, $to_array);
        return $ops;
    }

    public function __toArray(){
        return call_user_func('get_object_vars', $this);
    }

    public function get_all_values($ref = null, $builder = null){
        global $wpdb;
        $meta_name = implode('.', array(
            wp_basename(LITHIUM_PLUGIN_ROOT),
            $ref,
            $this->slug
        ));
        $values = array();
        $rows = $results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'postmeta WHERE meta_key = "' . $meta_name . '" GROUP BY meta_value', OBJECT );

        if(!is_callable($builder)) $builder = array('Model_Field', '_def_builder');
        foreach($rows as $rw) $values[] = call_user_func($builder, $rw);

        return array_values($values);
    }

    private static function _def_builder($row){return $row->meta_value;}

    public static function meta_name($name, $ref){
        return implode('.', array(
            wp_basename(LITHIUM_PLUGIN_ROOT),
            $ref,
            $name
        ));
    }
}