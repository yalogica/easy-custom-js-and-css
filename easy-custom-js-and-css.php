<?php

/**
 * Plugin Name:       Easy Custom Js And Css
 * Plugin URI:        http://avirtum.com
 * Description:       Add your own custom css styles and javascript code with a powerful editor and take more control over themes and plugins appearance, apply styles and code based on site-specific parameters and settings like url, date, users, roles and etc.
 * Version:           1.1.2
 * Author:            Avirtum
 * Author URI:        http://avirtum.com/
 * License:           GPLv3
 * Text Domain:       easy_custom_js_and_css
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if(!defined('ABSPATH')) {
	exit;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('EASYJC_PLUGIN_NAME', 'easy_custom_js_and_css');
define('EASYJC_PLUGIN_VERSION', '1.1.2');
define('EASYJC_DB_VERSION', '1.0.1');


/**
 * The code that runs during plugin activation
 */
function easy_custom_js_and_css_activate() {
	require_once( plugin_dir_path( __FILE__ ) . 'includes/activator.php' );
	$activator = new EasyCustomJsAndCss_Activator();
	$activator->activate();
}
register_activation_hook( __FILE__, 'easy_custom_js_and_css_activate' );

/**
 * The code that runs during plugin deactivation
 */
function easy_custom_js_and_css_deactivate() {
	require_once( plugin_dir_path( __FILE__ ) . 'includes/deactivator.php' );
	$deactivator = new EasyCustomJsAndCss_Deactivator();
	$deactivator->deactivate();
}
register_deactivation_hook( __FILE__, 'easy_custom_js_and_css_deactivate' );

/**
 * The code that runs after plugins loaded
 */
function easy_custom_js_and_css_check_db() {
	require_once( plugin_dir_path( __FILE__ ) . 'includes/activator.php' );
	
	$activator = new EasyCustomJsAndCss_Activator();
	$activator->check_db();
}
add_action('plugins_loaded', 'easy_custom_js_and_css_check_db');


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
require_once( plugin_dir_path( __FILE__ ) . 'includes/plugin.php' );


function easy_custom_js_and_css_run() {
	$pluginBasename = plugin_basename(__FILE__);
	
	$plugin = new EasyCustomJsAndCss($pluginBasename);
	$plugin->run();
}
add_action('init', 'easy_custom_js_and_css_run');

