<?php
/**
 * Public-facing functionality (no frontend design).
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Public
 *
 * Reserved for future public hooks. Elementor owns frontend presentation.
 */
class Zouetech_Portfolio_Public {

	/**
	 * Register public hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_hooks() {
		// Intentionally minimal — no frontend templates or assets by design.
	}
}
