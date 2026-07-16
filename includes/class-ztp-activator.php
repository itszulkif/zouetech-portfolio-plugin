<?php
/**
 * Fired during plugin activation.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Activator
 */
class Zouetech_Portfolio_Activator {

	/**
	 * Run activation tasks.
	 *
	 * Registers rewrite rules after CPT/taxonomies exist (when those modules land),
	 * stores a version option, and flushes permalinks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function activate() {
		// Ensure CPT and taxonomies are registered before flushing (Module 2+).
		if ( class_exists( 'Zouetech_Portfolio_CPT' ) ) {
			$cpt = new Zouetech_Portfolio_CPT();
			$cpt->register();
		}

		if ( class_exists( 'Zouetech_Portfolio_Category' ) ) {
			$category = new Zouetech_Portfolio_Category();
			$category->register();
		}

		if ( class_exists( 'Zouetech_Portfolio_Tag' ) ) {
			$tag = new Zouetech_Portfolio_Tag();
			$tag->register();
		}

		update_option( 'ztp_version', ZTP_VERSION );
		update_option( 'ztp_activated_at', time() );

		if ( ! wp_next_scheduled( 'ztp_check_github_updates' ) ) {
			wp_schedule_event( time() + 120, 'ztp_fifteen_minutes', 'ztp_check_github_updates' );
		}

		flush_rewrite_rules();
	}
}
