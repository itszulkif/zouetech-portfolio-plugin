<?php
/**
 * Featured Showcase style registry.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Featured_Showcase_Styles
 */
class Zouetech_Portfolio_Featured_Showcase_Styles {

	/**
	 * Default style slug.
	 *
	 * @var string
	 */
	const DEFAULT = 'card-style-1';

	/**
	 * Registered showcase styles (slug => label).
	 *
	 * Add new styles here when templates are ready.
	 *
	 * @since 1.0.0
	 * @return array<string, string>
	 */
	public static function get_options() {
		$styles = array(
			'card-style-1' => __( 'Style 1 (Default)', 'zouetech-portfolio' ),
			'card-style-2' => __( 'Style 2', 'zouetech-portfolio' ),
			'card-style-3' => __( 'Style 3', 'zouetech-portfolio' ),
			'card-style-4' => __( 'Style 4', 'zouetech-portfolio' ),
			'card-style-5' => __( 'Style 5', 'zouetech-portfolio' ),
		);

		/**
		 * Filter registered Featured Portfolio Showcase styles.
		 *
		 * @since 1.0.0
		 * @param array<string, string> $styles Style slug => label.
		 */
		return apply_filters( 'ztp_featured_showcase_styles', $styles );
	}

	/**
	 * Sanitize and validate a style slug.
	 *
	 * @since 1.0.0
	 * @param string $slug Raw slug.
	 * @return string
	 */
	public static function sanitize( $slug ) {
		$slug    = self::normalize_slug( $slug );
		$options = self::get_options();

		if ( isset( $options[ $slug ] ) && self::template_exists( $slug ) ) {
			return $slug;
		}

		return self::DEFAULT;
	}

	/**
	 * Normalize legacy and current slugs to the active naming convention.
	 *
	 * @since 1.0.0
	 * @param string $slug Raw slug.
	 * @return string
	 */
	public static function normalize_slug( $slug ) {
		$slug = sanitize_key( (string) $slug );

		$legacy = array(
			'style-1' => 'card-style-1',
			'style-2' => 'card-style-2',
			'style-3' => 'card-style-3',
			'style-4' => 'card-style-4',
			'style-5' => 'card-style-5',
		);

		if ( isset( $legacy[ $slug ] ) ) {
			return $legacy[ $slug ];
		}

		return $slug;
	}

	/**
	 * Whether a style template file exists.
	 *
	 * @since 1.0.0
	 * @param string $slug Style slug.
	 * @return bool
	 */
	public static function template_exists( $slug ) {
		$slug = self::normalize_slug( $slug );
		$file = self::get_template_path( $slug );
		return is_readable( $file );
	}

	/**
	 * Absolute path to a style template.
	 *
	 * @since 1.0.0
	 * @param string $slug Style slug.
	 * @return string
	 */
	public static function get_template_path( $slug ) {
		$slug = self::normalize_slug( $slug );
		return ZTP_PLUGIN_DIR . 'includes/elementor/widgets/featured-showcase/templates/' . $slug . '.php';
	}

	/**
	 * CSS handle for a style.
	 *
	 * @since 1.0.0
	 * @param string $slug Style slug.
	 * @return string
	 */
	public static function get_style_handle( $slug ) {
		return 'ztp-featured-showcase-' . self::normalize_slug( $slug );
	}

	/**
	 * JS handle for a style.
	 *
	 * @since 1.0.0
	 * @param string $slug Style slug.
	 * @return string
	 */
	public static function get_script_handle( $slug ) {
		return 'ztp-featured-showcase-' . self::normalize_slug( $slug ) . '-js';
	}

	/**
	 * Relative asset paths for optional per-style CSS/JS.
	 *
	 * @since 1.0.0
	 * @param string $slug Style slug.
	 * @return array{css: string, js: string}
	 */
	public static function get_asset_paths( $slug ) {
		$slug = self::normalize_slug( $slug );
		return array(
			'css' => 'assets/elementor/featured-showcase/css/ztp-featured-showcase-' . $slug . '.css',
			'js'  => 'assets/elementor/featured-showcase/js/ztp-featured-showcase-' . $slug . '.js',
		);
	}

	/**
	 * Register per-style assets if files exist.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function register_assets() {
		foreach ( array_keys( self::get_options() ) as $slug ) {
			$paths = self::get_asset_paths( $slug );

			$css_file = ZTP_PLUGIN_DIR . $paths['css'];
			if ( is_readable( $css_file ) ) {
				wp_register_style(
					self::get_style_handle( $slug ),
					ZTP_PLUGIN_URL . $paths['css'],
					array( 'ztp-featured-showcase' ),
					(string) filemtime( $css_file )
				);
			}

			$js_file = ZTP_PLUGIN_DIR . $paths['js'];
			if ( is_readable( $js_file ) ) {
				wp_register_script(
					self::get_script_handle( $slug ),
					ZTP_PLUGIN_URL . $paths['js'],
					array( 'ztp-featured-showcase' ),
					(string) filemtime( $js_file ),
					true
				);
			}
		}
	}
}
