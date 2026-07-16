<?php
/**
 * Plugin Name:       Zouetech Portfolio
 * Plugin URI:        https://zouetech.com/plugins/zouetech-portfolio
 * Description:       Professional portfolio projects Custom Post Type with taxonomies, meta fields, and Elementor Dynamic Tags compatibility.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Zouetech
 * Author URI:        https://zouetech.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       zouetech-portfolio
 * Domain Path:       /languages
 *
 * @package Zouetech_Portfolio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Current plugin version.
 */
define( 'ZTP_VERSION', '1.0.0' );

/**
 * Plugin root file.
 */
define( 'ZTP_PLUGIN_FILE', __FILE__ );

/**
 * Plugin directory path (with trailing slash).
 */
define( 'ZTP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL (with trailing slash).
 */
define( 'ZTP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'ZTP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Load the autoloader.
 */
require_once ZTP_PLUGIN_DIR . 'includes/class-ztp-autoloader.php';

Zouetech_Portfolio_Autoloader::register();

/**
 * Activation hook.
 */
function ztp_activate_plugin() {
	require_once ZTP_PLUGIN_DIR . 'includes/class-ztp-activator.php';
	Zouetech_Portfolio_Activator::activate();
}
register_activation_hook( __FILE__, 'ztp_activate_plugin' );

/**
 * Deactivation hook.
 */
function ztp_deactivate_plugin() {
	require_once ZTP_PLUGIN_DIR . 'includes/class-ztp-deactivator.php';
	Zouetech_Portfolio_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'ztp_deactivate_plugin' );

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 * @return Zouetech_Portfolio
 */
function ztp_run_plugin() {
	return Zouetech_Portfolio::instance();
}

/**
 * Helper: get portfolio meta by key.
 *
 * @since 1.0.0
 * @param int    $post_id Post ID.
 * @param string $key     Meta key (e.g. `_ztp_client_name`).
 * @param mixed  $default Default value.
 * @return mixed
 */
function ztp_get_project_meta( $post_id, $key, $default = '' ) {
	return Zouetech_Portfolio_Helpers::get_meta( $post_id, $key, $default );
}

/**
 * Initialize the plugin after all plugins are loaded.
 */
add_action(
	'plugins_loaded',
	static function () {
		ztp_run_plugin();
	}
);
