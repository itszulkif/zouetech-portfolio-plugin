<?php
/**
 * Fired during plugin deactivation.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Deactivator
 */
class Zouetech_Portfolio_Deactivator {

	/**
	 * Run deactivation tasks.
	 *
	 * Flushes rewrite rules. Does not delete content or meta.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
