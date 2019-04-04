<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 29.01.17
 * Time: 15:18
 */
/*
Plugin Name: uCalc
Plugin URI: http://wordpress.org/plugins/uCalc/
Description: Create an adaptive service calculator by using a simple drag-and-drop builder.
Author: uKit Group
Version: 2.0.0
Author URI: https://ukit.group/
Text Domain: uCalc
Domain Path: /languages
*/
define('UCALC_PLUGIN', true);
if (!defined('UCALC_PLUGIN_NAME'))
    define('UCALC_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));

if (!defined('UCALC_PLUGIN_DIR')) {
    define('UCALC_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . UCALC_PLUGIN_NAME);
}
if (!defined('UCALC_PLUGIN_VER')) {
    define('UCALC_PLUGIN_VER', '1.0');
}
if (!defined('UCALC_POPUP_FILE')) {
    define('UCALC_POPUP_FILE', UCALC_PLUGIN_NAME . '/assets/views/popup.php');
}
if (!defined('UCALC_JSON_FILE')) {
    define('UCALC_JSON_FILE', UCALC_PLUGIN_NAME . '/assets/views/json.php');
}
function my_plugin_load_plugin_textdomain() {
    load_plugin_textdomain( 'uCalc', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'my_plugin_load_plugin_textdomain' );
if (!class_exists('uCalc_Core')) {
    require_once(UCALC_PLUGIN_DIR . '/includes/uCalc_Core.php');
    require_once(UCALC_PLUGIN_DIR . '/includes/uCalcPage.php');
    require_once(UCALC_PLUGIN_DIR . '/includes/uCalcWidget.php');
    require_once(UCALC_PLUGIN_DIR . '/includes/uCalcMCE.php');
    $uCalc_plugin = new uCalc_Core();
}

if (function_exists('register_uninstall_hook')) {
    register_uninstall_hook(__FILE__, 'uCalc_deinstall');
}

function uCalc_deinstall() {
    delete_option('ucalc_api_key');
}
