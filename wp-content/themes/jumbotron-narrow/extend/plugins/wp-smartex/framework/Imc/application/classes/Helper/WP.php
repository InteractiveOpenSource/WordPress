<?php

class Helper_WP {

    public static function style($handle, $src, $deps = array(), $ver = false, $media = 'all'){
        wp_enqueue_style( $handle, $src, $deps, $ver, $media );
    }

    public static function filter_inject_order($p_args){
        $_filter_order_by = Helper_Cookie::Init('imc_order_by')->get('prix');
        $_filter_order_sns = Helper_Cookie::Init('imc_order_sns')->get('ASC');

        $p_args['orderby'] = 'meta_value';
        $p_args['meta_key'] = Model_Field::meta_name($_filter_order_by, 'assurance');
        $p_args['order'] = $_filter_order_sns;

        return $p_args;
    }

    public static function filter_inject_meta($arg){
        $meta = array();
        foreach($_POST as $meta_key => $value){
            if(preg_match('/^meta_(\w+)(\-(between))?$/', $meta_key, $match)){
                $meta_name = $match[1];

                $meta[$meta_name] = array(
                    'key' => Model_Field::meta_name($meta_name, 'assurance'),
                    'value' => $value
                );

                if(count($match) == 2){
                    //cas meta non intervale
                    $meta[$meta_name]['compare'] = 'IN';
                }elseif(count($match) == 4){
                    //cas meta intervale
                    $meta[$meta_name]['compare'] = 'BETWEEN';
                    $meta[$meta_name]['type'] = 'NUMERIC';
                    if((int)$meta[$meta_name]['value'][0] == 0 && (int)$meta[$meta_name]['value'][1] == 0) unset($meta[$meta_name]);
                    elseif((int)$meta[$meta_name]['value'][0] == 0 && (int)$meta[$meta_name]['value'][1] > 0){
                        $meta[$meta_name]['compare'] = '<=';
                        $meta[$meta_name]['value'] = (int)$meta[$meta_name]['value'][1];
                    }elseif((int)$meta[$meta_name]['value'][0] > 0 && (int)$meta[$meta_name]['value'][1] == 0){
                        $meta[$meta_name]['compare'] = '>=';
                        $meta[$meta_name]['value'] = (int)$meta[$meta_name]['value'][0];
                    }
                }
            }
        }

        if(count($meta) > 0) $arg['meta_query'] = $meta;

        return $arg;
    }

} 