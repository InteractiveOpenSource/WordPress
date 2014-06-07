<?php

abstract class Helper_Front {

    public static function get_post_thumb($post, $size = '', $default = NULL){
        if($post instanceof WP_Post) $post = $post->ID;
        if(is_null($post)) return $default;
        try{
            $thumb = wp_get_attachment_image_src( get_post_thumbnail_id($post), $size );
            return isset($thumb[0]) ? $thumb[0] : null;
        }catch (Exception $e){
            return $default;
        }
    }

} 