<?php
/**
 * Plugin Name: AnyWhere Elementor Pro (Premium)
 * Description: Global layouts to use with shortcodes, global post layouts for single and archive pages. Supports CPT and ACF
 * Plugin URI: https://www.elementoraddons.com/anywhere-elementor-pro/
 * Author: WP Vibes
 * Version: 2.27
 * Update URI: https://api.freemius.com
 * Author URI: https://wpvibes.com/
 * Elementor tested up to: 3.19
 * Elementor Pro tested up to: 3.19
 * License:      GNU General Public License v2 or later
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ae-pro
 * Domain Path: includes/languages/
 *
 * @package Aepro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



define( 'AE_PRO_VERSION', '2.27' );
define( 'AE_PRO_URL', plugins_url( '/', __FILE__ ) );
define( 'AE_PRO_PATH', plugin_dir_path( __FILE__ ) );
define( 'AE_PRO_BASE', plugin_basename( __FILE__ ) );
define( 'AE_PRO_FILE', __FILE__ );

define( 'AE_PRO_SCRIPT_SUFFIX', defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' );

define( 'AEP_PHP_VERSION_REQUIRED', '5.6' );


add_action( 'plugins_loaded', 'ae_pro_load_plugin_textdomain' );


if ( version_compare( PHP_VERSION, AEP_PHP_VERSION_REQUIRED, '<' ) ) {

	add_action( 'admin_notices', 'aep_php_fail' );
	add_action( 'admin_init', 'aep_deactivate' );
	return;
}

/**
 * Handles admin notice for PHP version requirements
 *
 * @since 0.1.0
 */
function aep_php_fail() {
	global $php_version_required;

	$class = 'notice notice-error';
	/* translators: %s: PHP version Error */
	$message = sprintf( __( 'AnyWhere Elementor Pro needs at least PHP version %s to work properly. We deactivated the plugin for now.', 'ae-pro' ), AEP_PHP_VERSION_REQUIRED );
	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}

/**
 * Handles plugin deactivate actions.
 *
 * @since 1.0
 */
function aep_deactivate() {
	deactivate_plugins( plugin_basename( __FILE__ ) );
}


/**
 * Handles plugin activation actions.
 *
 * @since 1.0
 */
function aep_activate() {

	if ( ! is_plugin_active( 'elementor/elementor.php' ) ) {
		return;
	}
	\Elementor\Plugin::$instance->files_manager->clear_cache();
}
register_activation_hook( __FILE__, 'aep_activate' );



global $ae_template;
$ae_template = get_option( 'template' );


/**
 * Loads text domain
 *
 * @since 1.0
 */
function ae_pro_load_plugin_textdomain() {
	load_plugin_textdomain( 'ae-pro' );
}

if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! function_exists( '_is_elementor_installed' ) ) {

	/**
	 * Check if Elementor is installed
	 *
	 * @return bool
	 */
	function _is_elementor_installed() {
		$file_path         = 'elementor/elementor.php';
		$installed_plugins = get_plugins();

		return isset( $installed_plugins[ $file_path ] );
	}
}


add_action('elementor/element/before_parse_css', 'disable_selective_loading');
function disable_selective_loading(){
	add_filter('ae/skin_control_loading', 'ae_return_true');
}

add_action('elementor/element/parse_css', 'enable_selective_loading');
function enable_selective_loading(){
	remove_filter('ae/skin_control_loading', 'ae_return_true');
}

function ae_return_true(){
	return true;
}


if ( ! is_plugin_active( 'elementor/elementor.php' ) ) {
	return;
} else {
	require_once AE_PRO_PATH . 'vendor/autoload.php';

	require_once AE_PRO_PATH . 'includes/license-manager/freemius.php';

	require AE_PRO_PATH . 'includes/bootstrap.php';
}
