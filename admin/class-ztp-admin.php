<?php
/**
 * Admin-facing functionality.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Admin
 *
 * Enqueues admin assets only on portfolio screens.
 */
class Zouetech_Portfolio_Admin {

	/**
	 * Register admin hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue admin CSS/JS only on portfolio-related screens.
	 *
	 * @since 1.0.0
	 * @param string $hook_suffix Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( ! $this->is_portfolio_screen( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_style(
			'ztp-admin',
			ZTP_PLUGIN_URL . 'assets/admin/css/ztp-admin.css',
			array(),
			ZTP_VERSION
		);

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$is_edit = $screen && in_array( $screen->base, array( 'post', 'post-new' ), true );

		if ( $is_edit ) {
			wp_enqueue_media();
			wp_enqueue_script(
				'ztp-admin',
				ZTP_PLUGIN_URL . 'assets/admin/js/ztp-admin.js',
				array( 'jquery', 'jquery-ui-sortable' ),
				ZTP_VERSION,
				true
			);
		}
	}

	/**
	 * Whether the current screen is a portfolio edit/list screen.
	 *
	 * @since 1.0.0
	 * @param string $hook_suffix Current admin page hook.
	 * @return bool
	 */
	private function is_portfolio_screen( $hook_suffix ) {
		unset( $hook_suffix );

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( $screen && isset( $screen->post_type ) && Zouetech_Portfolio_CPT::POST_TYPE === $screen->post_type ) {
			return true;
		}

		$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( Zouetech_Portfolio_CPT::POST_TYPE === $post_type ) {
			return true;
		}

		// Editing an existing portfolio post (post.php?post=ID).
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $post_id && Zouetech_Portfolio_CPT::POST_TYPE === get_post_type( $post_id ) ) {
			return true;
		}

		return false;
	}
}
