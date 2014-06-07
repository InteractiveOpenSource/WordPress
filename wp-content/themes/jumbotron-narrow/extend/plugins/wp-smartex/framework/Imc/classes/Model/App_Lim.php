<?php

class Model_App_Lim extends Lim{

    protected $_request_retaled_data;
    public $related_data;

    protected $_hooks = array();

    protected $_meta_query = array();

    public function __construct($id=NULL) {

        $this->hook('pre-construct');

        parent::__construct($id);

        $this->hook('post-construct');

        if(!is_null($id)){
            $this->set_related();
        }
    }

    public function set_related(){
        if(is_array($this->_request_retaled_data)){
            foreach($this->_request_retaled_data as $key){
                if(!is_null($this->getValue($key)))
                    $this->related_data[$key] = Lim::factory($key, get_post($this->getValue($key)));
                else $this->related_data[$key] = NULL;
            }
        }
    }

    public function get_related($object_name, $key = NULL, $default = NULL){
        $ret = $default;
        if(array_key_exists($object_name, $this->related_data)){
            if(is_null($key)) $ret = $this->related_data[$object_name];
            else {
                if(!is_null($this->related_data[$object_name])){
                    $array_key = explode('/', $key);
                    $ret = $this->related_data[$object_name];
                    while(count($array_key) > 0){
                        if(is_object($ret)){
                            $ob_vars = get_object_vars($ret);
                            $ret = array_key_exists($array_key[0], $ob_vars) ? $ob_vars[$array_key[0]] : $default;
                        }else {
                            $ret = array_key_exists($array_key[0], $ret) ? $ret[$array_key[0]] : $default;
                        }
                        array_splice($array_key, 0, 1);
                    }
                }
            }
        }
        return $ret;
    }

    public function list_of($key){
        $lim = Model_App_Lim::factory($key);
        $lim->add_meta_query(strtolower($this->object_name()), $this->pk());
        return $lim->find_all();
    }

    public function find_all($args = array()){

        $args = array_merge(array(
            'numberposts' => $this->limit,
            'offset' => $this->offset,
            'category' => $this->category,
            'orderby' => $this->orderby,
            'order' => $this->order,
            'include' => $this->include,
            'exclude' => $this->exclude,
            'post_type' => strtolower($this->object_name())
        ), $args);

        //Deprecated
        if(count($this->fields) > 0){
            //$args['meta_key'] = wp_basename(LITHIUM_PLUGIN_ROOT) . "." . strtolower($this->object_name());
            //$args['meta_value'] = $this->meta_value;
        }

        if(count($this->_meta_query) > 0){
            $args['meta_query'] = $this->_meta_query;
        }

        $posts = get_posts($args);

        $ret = array();
        foreach ($posts as $post) {
            $ret[] = self::factory(strtolower($this->object_name()), $post);
        }
        return $ret;
    }

    public function register_hook($key, $callable, $param = NULL){
        if(is_callable($callable)){
            if(array_key_exists($key, $this->_hooks) && is_array($this->_hooks[$key])){
                $this->_hooks[$key][] = array(
                    'call' => $callable,
                    'params' => $param
                );
            }else{
                $this->_hooks[$key] = array(
                    array(
                        'call' => $callable,
                        'params' => $param
                    )
                );
            }
        }
    }

    public function hook($key){
        if(array_key_exists($key, $this->_hooks) && is_array($this->_hooks[$key])){
            foreach($this->_hooks[$key] as $callback){
                call_user_func($callback['call'], $callback['params']);
            }
        }
    }

    public function add_meta_query($key, $value = '', $compare = '='){
        $this->_meta_query[] = array(
            'key' => wp_basename(LITHIUM_PLUGIN_ROOT).".".strtolower($this->object_name()) . "." . $key,
            'value' => $value,
            'compare' => $compare,
        );
    }

    public function save() {
        parent::save();
        $metas = $this->as_array();
        foreach($metas as $key => $value){
            update_post_meta($this->post->ID, wp_basename(LITHIUM_PLUGIN_ROOT).".".strtolower($this->object_name()) . "." . $key, $value);
        }
    }

}