<?php
/*
 * Plugin Name:       Zouetech Portfolio
 * Description:       A dynamic portfolio showcase plugin with multiple card styles.
 * Version:           1.0.0
 * Author:            Zouetech Team
 * GitHub Plugin URI: itszulkif/zouetech-portfolio-plugin
 * Primary Branch:    main
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
