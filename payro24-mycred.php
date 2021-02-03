<?php
/*
Plugin Name: payro24 myCRED
Version: 1.1.1
Description: payro24 payment gateway for myCRED
Author: payro24
Author URI: https://payro24.ir
Text Domain: payro24-mycred
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function payro24_mycred_load_textdomain() {
    load_plugin_textdomain( 'payro24-mycred', FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'payro24_mycred_load_textdomain' );

require_once( plugin_dir_path( __FILE__ ) . 'class-mycred-gateway-payro24.php' );
