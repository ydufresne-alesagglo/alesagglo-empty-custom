<?php
/*
Plugin Name: Ales Agglo Empty Custom
Plugin URI: 
Description: Empty Custom by Ales Agglomeration
Version: 1.0.0
Author: Ales Agglomeration
Author URI: https://www.ales.fr/
Author EMail: contact@alesagglo.fr
Text Domain: alesagglo-empty-custom
*/

defined('ABSPATH') || die();

define('AEC_SLUG', 'alesagglo-empty-custom');
define('AEC_PREFIX', 'aec_');

define('AEC_PATH', plugin_dir_path(__FILE__));
define('AEC_URL', plugin_dir_url(__FILE__));


/*
 *	activate / deactivate
 */
register_activation_hook(__FILE__, 'aec_activate');
function aec_activate() {
	flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'aec_deactivate');
function aec_deactivate() {
	flush_rewrite_rules();
}


/*
 *	uninstall
 */
register_uninstall_hook(__FILE__, 'aec_uninstall');
function aec_uninstall() {
	// delete all custom post types
}


/*
 *	load dependencies
 */
add_action('plugins_loaded', 'aec_load_dependencies');
function aec_load_dependencies() {
	require_once AEC_PATH . 'inc/tools.php';
	require_once AEC_PATH . 'inc/class-Custom.php';
	add_action('wp_enqueue_scripts', 'aec_register_scripts');
	if (is_admin()) {
		require_once AEC_PATH . 'inc/tools-admin.php';
		add_action('admin_enqueue_scripts', 'aec_register_admin_scripts');
	}
}
function aec_register_scripts() {
	wp_enqueue_script(AEC_PREFIX . 'scripts', AEC_URL . 'assets/js/scripts.js');
	wp_enqueue_style(AEC_PREFIX . 'styles', AEC_URL . 'assets/css/styles.css');
}
function aec_register_admin_scripts() {
	wp_enqueue_media();
	wp_enqueue_script(AEC_PREFIX . 'scripts-admin', AEC_URL . 'assets/js/scripts-admin.js');
	wp_localize_script(AEC_PREFIX . 'scripts-admin', 'settings', [
		'prefix' => AEC_PREFIX,
	]);
	wp_enqueue_style(AEC_PREFIX . 'styles-admin', AEC_URL . 'assets/css/styles-admin.css');
}


/*
 * init custom post type
 */
add_action('init', 'aec_init');
function aec_init() {
	if (class_exists('Custom')) {
		$plubli = new Custom();
		$plubli->register_custom_category();
		$plubli->register_custom();
		$plubli->register_hooks();
		$plubli->define_metabox();
	}
}


/*
 * get preview template
 */
function aec_get_custom_preview_template_part() {
	Custom::get_preview_template_part();
}
