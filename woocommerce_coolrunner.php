<?php
/*
	Plugin Name: Coolrunner Shipping
	Plugin URI: 
	Description: Shipping service of Coolrunner
	Version: 1.0
	Author: Coolrunner
	Author URI: https://coolrunner.dk/
	Text Domain: coolrunner
 	Domain Path: 
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//Check if woocommerce is active so it doesnt crash
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if (! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	return;
}
//Define plugin path
if ( !defined( 'COOLRUNNER_PLUGIN_DIR' ) ) {
    define( 'COOLRUNNER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

add_action('plugins_loaded', 'coolrunner_load_textdomain');
function coolrunner_load_textdomain() {
	load_plugin_textdomain( 'coolrunner-shipping-plugin', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
}

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'my_plugin_action_links' );
function my_plugin_action_links( $links ) {
	$links[] = '<a href="'. admin_url( 'admin.php?page=wc-settings&tab=coolrunner') .'">' . __( 'Settings', 'coolrunner-shipping-plugin' ) . '</a>';
	$links[] = '<a href="https://coolrunner.dk/om-coolrunner/" target="_blank">' . __( 'Read more about Coolrunner', 'coolrunner-shipping-plugin' ) . '</a>';
	return $links;
}

add_action( 'wp_enqueue_scripts', 'ajax_enqueue_scripts' );
function ajax_enqueue_scripts() {
	if( is_checkout() ) {	

		
		wp_enqueue_style( 'coolrunner', plugins_url( '/assets/css/coolrunner.css', __FILE__ ) );
		wp_enqueue_script( 'coolrunner', plugins_url( '/assets/js/coolrunner.js', __FILE__ ), array('jquery'), '1.0', true );


		wp_localize_script( 'coolrunner', 'post_search', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'map_url' => plugins_url( '/assets/js/maps.js', __FILE__ )
		));


	}
}

add_action( 'wp_enqueue_scripts', 'fancybox_enqueue_scripts' );
function fancybox_enqueue_scripts() {
		//fancybox
		wp_enqueue_style( 'fancybox-css',  plugins_url( '/fancybox/dist/jquery.fancybox.min.css', __FILE__ ) );
		wp_enqueue_script('fancybox-js',  plugins_url( '/fancybox/dist/jquery.fancybox.min.js', __FILE__ ), array('jquery'), '1.0', true);
}



include( COOLRUNNER_PLUGIN_DIR . 'includes/curl.php' );
include( COOLRUNNER_PLUGIN_DIR . 'includes/admin/class-coolrunner-settings.php' );
include( COOLRUNNER_PLUGIN_DIR . 'includes/admin/class-coolrunner-shipping.php');
include( COOLRUNNER_PLUGIN_DIR . 'includes/functions.php');