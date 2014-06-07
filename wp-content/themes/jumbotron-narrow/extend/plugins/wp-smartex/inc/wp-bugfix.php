<?php
/**
 * Smartex Project
 * Fix bug on 3.9 version
 */

if(!class_exists('wp_atom_server'))
    require_once ABSPATH . WPINC . '/pluggable-deprecated.php';

if(!class_exists('WP_User_Search'))
    require_once ABSPATH . '/wp-admin/includes/deprecated.php';

if ( ! class_exists( 'Featured_Content' ) && is_file(get_template_directory() . '/inc/featured-content.php'))
    require_once get_template_directory() . '/inc/featured-content.php';
