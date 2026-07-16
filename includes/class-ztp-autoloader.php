<?php
/**
 * PSR-4 style autoloader for Zouetech Portfolio classes.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Autoloader
 *
 * Maps Zouetech_* class names to file paths under the plugin directory.
 */
class Zouetech_Portfolio_Autoloader {

	/**
	 * Class prefix.
	 *
	 * @var string
	 */
	const PREFIX = 'Zouetech_Portfolio_';

	/**
	 * Directory map: class fragment => relative path from plugin root.
	 *
	 * @var array<string, string>
	 */
	private static $class_map = array(
		'Zouetech_Portfolio'                        => 'includes/class-ztp-plugin.php',
		'Zouetech_Portfolio_Activator'              => 'includes/class-ztp-activator.php',
		'Zouetech_Portfolio_Deactivator'            => 'includes/class-ztp-deactivator.php',
		'Zouetech_Portfolio_Helpers'                => 'includes/helpers/class-ztp-helpers.php',
		'Zouetech_Portfolio_CPT'                    => 'includes/post-types/class-ztp-portfolio-cpt.php',
		'Zouetech_Portfolio_Category'               => 'includes/taxonomies/class-ztp-portfolio-category.php',
		'Zouetech_Portfolio_Tag'                    => 'includes/taxonomies/class-ztp-portfolio-tag.php',
		'Zouetech_Portfolio_Meta_Fields'            => 'includes/meta/class-ztp-meta-fields.php',
		'Zouetech_Portfolio_Meta_Registry'          => 'includes/meta/class-ztp-meta-registry.php',
		'Zouetech_Portfolio_Meta_Sanitizer'         => 'includes/meta/class-ztp-meta-sanitizer.php',
		'Zouetech_Portfolio_Elementor_Dynamic_Tags' => 'includes/elementor/class-ztp-elementor-dynamic-tags.php',
		'Zouetech_Portfolio_Elementor'              => 'includes/elementor/class-ztp-elementor.php',
		'Zouetech_Portfolio_Featured_Showcase_Styles' => 'includes/elementor/widgets/featured-showcase/class-ztp-featured-showcase-styles.php',
		'Zouetech_Portfolio_GitHub_Updater'         => 'includes/class-ztp-github-updater.php',
		'Zouetech_Portfolio_Admin'                  => 'admin/class-ztp-admin.php',
		'Zouetech_Portfolio_Meta_Boxes'             => 'admin/class-ztp-meta-boxes.php',
		'Zouetech_Portfolio_Admin_Columns'          => 'admin/class-ztp-admin-columns.php',
		'Zouetech_Portfolio_Public'                 => 'public/class-ztp-public.php',
	);

	/**
	 * Register the autoloader with SPL.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Autoload a class if it belongs to this plugin.
	 *
	 * @since 1.0.0
	 * @param string $class Fully qualified class name.
	 * @return void
	 */
	public static function autoload( $class ) {
		if ( 0 !== strpos( $class, 'Zouetech_Portfolio' ) ) {
			return;
		}

		if ( ! isset( self::$class_map[ $class ] ) ) {
			return;
		}

		$file = ZTP_PLUGIN_DIR . self::$class_map[ $class ];

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
}
