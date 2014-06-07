<?php
/*
Plugin Name: Wordpress Smart Extend
Plugin URI: https://github.com/InteractiveOpenSource/Wordpress-Smartex
Description: This plugin allows you to extend your Wordpress installation with easy way, you can use it as is with some minimal extensions or modify it to implant your own features. To read more about Wordpress Smart Extend, follow the url indicated beyound
Author: Irzhy Ranaivoarivony, Landry Rakotoarivelo
Version: 1.0
Licence: MIT
Author URI: http://maladev-experiences.mg
*/


include_once dirname(__FILE__) . '/inc/constant.php';
include_once dirname(__FILE__) . '/inc/wp-bugfix.php';
include_once dirname(__FILE__) . '/inc/settings.php';
include_once dirname(__FILE__) . '/inc/autoload.php';

if(is_file(SX_ABSPATH . '/bootstrap.php')) include_once dirname(__FILE__) . '/bootstrap.php';