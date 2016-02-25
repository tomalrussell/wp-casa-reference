<?php
/*
Plugin Name:        CASA References
Plugin URI:         https://github.com/tomalrussell/wp-casa-reference
Description:        Academic references for posts and pages
Version:            1.0.0
Author:             Tom Russell
Author URI:         https://github.com/tomalrussell

License:            MIT License
License URI:        http://opensource.org/licenses/MIT
*/

namespace Casa\Reference;

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

function load(){
	$files = array(
		'associate.php',
		'create.php',
		'output.php',
		'public.php',
		'utils.php'
	);
	foreach($files as $file){
		require_once 'lib/'.$file;
	}

	if (is_admin()){
		add_action( 'admin_enqueue_scripts', __NAMESPACE__.'\\load_admin_style' );
	}
}

function load_admin_style() {
	wp_register_style( 'casa_reference_css', plugin_dir_url( __FILE__ ) . '/admin/casa-reference.css', false, '1.0.0' );
	wp_enqueue_style( 'casa_reference_css' );

	wp_register_style( 'casa_reference_chosen_css', plugin_dir_url( __FILE__ ) . '/admin/chosen.min.css', false, '1.0.0' );
	wp_enqueue_style( 'casa_reference_chosen_css' );

	wp_register_script( 'chosen_js', plugin_dir_url( __FILE__ ) . '/admin/chosen.jquery.min.js', false, '1.0.0' );
	wp_enqueue_script( 'casa_reference_js', plugin_dir_url( __FILE__ ) . '/admin/casa-reference.js', array('jquery','chosen_js'), '1.0.0' );
}

load();

function first_run() {
	// register post type
	Create\register();
	// set up URL structure for new 'reference' post type
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, __NAMESPACE__.'\\first_run' );

function last_run() {
	// clean up URL structure
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, __NAMESPACE__.'\\last_run' );