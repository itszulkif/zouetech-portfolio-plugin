<?php
/**
 * Elementor integration: widgets + dynamic tags.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Elementor
 */
class Zouetech_Portfolio_Elementor {

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_widgets' ) ); // Older API.

		$tags = new Zouetech_Portfolio_Elementor_Dynamic_Tags();
		$tags->register_hooks();
	}

	/**
	 * Register Zouetech Portfolio widget category.
	 *
	 * @since 1.0.0
	 * @param \Elementor\Elements_Manager $elements_manager Elements manager.
	 * @return void
	 */
	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'zouetech-portfolio',
			array(
				'title' => __( 'Zouetech Portfolio', 'zouetech-portfolio' ),
				'icon'  => 'fa fa-briefcase',
			)
		);
	}

	/**
	 * Register widgets (guard against double registration).
	 *
	 * @since 1.0.0
	 * @param mixed $widgets_manager Widgets manager.
	 * @return void
	 */
	public function register_widgets( $widgets_manager = null ) {
		static $done = false;
		if ( $done ) {
			return;
		}

		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return;
		}

		require_once ZTP_PLUGIN_DIR . 'includes/elementor/widgets/featured-showcase/class-ztp-featured-showcase-styles.php';
		require_once ZTP_PLUGIN_DIR . 'includes/elementor/widgets/featured-showcase/class-ztp-featured-showcase-query.php';
		require_once ZTP_PLUGIN_DIR . 'includes/elementor/widgets/featured-showcase/class-ztp-featured-showcase-renderer.php';
		require_once ZTP_PLUGIN_DIR . 'includes/elementor/widgets/featured-showcase/class-ztp-widget-featured-showcase.php';

		if ( ! class_exists( 'Zouetech_Portfolio_Widget_Featured_Showcase' ) ) {
			return;
		}

		if ( ! $widgets_manager && class_exists( '\Elementor\Plugin' ) ) {
			$widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
		}

		if ( ! $widgets_manager ) {
			return;
		}

		$widget = new Zouetech_Portfolio_Widget_Featured_Showcase();

		if ( method_exists( $widgets_manager, 'register' ) ) {
			$widgets_manager->register( $widget );
		} elseif ( method_exists( $widgets_manager, 'register_widget_type' ) ) {
			$widgets_manager->register_widget_type( $widget );
		} else {
			return;
		}

		$done = true;
	}
}
