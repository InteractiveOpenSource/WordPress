<?php

include_once get_template_directory() . '/extend/init.php';

function jumbotron_template_uri($rel = null){
    if(is_null($rel)) return get_template_directory_uri();
    return sprintf("%s/%s", get_template_directory_uri(), $rel);
}