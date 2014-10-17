<?php

/*
Plugin Name: Download Manager Add-Ons for Harker
Plugin URI: http://www.wpdownloadmanager.com/
Description: Download Manager Add-ons developed by The Harker School
Author: Joe Banks
Version: 1.0
Author URI: http://www.harker.org/
*/

require_once( dirname( __FILE__ ) . '/inc/helper.php' );
require_once( dirname( __FILE__ ) . '/inc/datatable.php' );

include_once( dirname( __FILE__ ) . '/admin/metabox.php' );
include_once( dirname( __FILE__ ) . '/admin/post-list.php' );
include_once( dirname( __FILE__ ) . '/admin/taxonomy.php' );
include_once( dirname( __FILE__ ) . '/admin/link.php' );
include_once( dirname( __FILE__ ) . '/admin/settings.php' );

register_activation_hook( __FILE__, 'hkr_wpdm_activate' );

function hkr_wpdm_activate() {
    // do something
}

add_action( 'wp_enqueue_scripts', 'hkr_wpdm_enqueue' );

function hkr_wpdm_enqueue() {
    wp_enqueue_style( 'hkr-wpdm-style', plugins_url('css/style.css', __FILE__) );
}

?>