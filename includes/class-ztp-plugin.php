<?php
/**
 * The core plugin class.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio
 *
 * Orchestrates CPT, taxonomies, meta, admin, public, and Elementor bridges.
 */
class Zouetech_Portfolio {

	/**
	 * Singleton instance.
	 *
	 * @var Zouetech_Portfolio|null
	 */
	private static $instance = null;

	/**
	 * Admin subsystem.
	 *
	 * @var Zouetech_Portfolio_Admin|null
	 */
	private $admin = null;

	/**
	 * Public subsystem.
	 *
	 * @var Zouetech_Portfolio_Public|null
	 */
	private $public = null;

	/**
	 * CPT component.
	 *
	 * @var Zouetech_Portfolio_CPT|null
	 */
	private $cpt = null;

	/**
	 * Category taxonomy.
	 *
	 * @var Zouetech_Portfolio_Category|null
	 */
	private $category = null;

	/**
	 * Tag taxonomy.
	 *
	 * @var Zouetech_Portfolio_Tag|null
	 */
	private $tag = null;

	/**
	 * Meta registry.
	 *
	 * @var Zouetech_Portfolio_Meta_Registry|null
	 */
	private $meta_registry = null;

	/**
	 * Get the singleton instance.
	 *
	 * @since 1.0.0
	 * @return Zouetech_Portfolio
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor — private to enforce singleton.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->load_textdomain();
		$this->define_hooks();
	}

	/**
	 * Prevent cloning.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Prevent unserializing.
	 *
	 * @since 1.0.0
	 * @throws \Exception When unserialize is attempted.
	 * @return void
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize singleton.' );
	}

	/**
	 * Load plugin text domain for translations.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function load_textdomain() {
		load_plugin_textdomain(
			'zouetech-portfolio',
			false,
			dirname( ZTP_PLUGIN_BASENAME ) . '/languages'
		);
	}

	/**
	 * Register WordPress hooks for all subsystems.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function define_hooks() {
		$this->cpt           = new Zouetech_Portfolio_CPT();
		$this->category      = new Zouetech_Portfolio_Category();
		$this->tag           = new Zouetech_Portfolio_Tag();
		$this->meta_registry = new Zouetech_Portfolio_Meta_Registry();

		$this->cpt->register_hooks();
		$this->category->register_hooks();
		$this->tag->register_hooks();
		$this->meta_registry->register_hooks();

		add_action( 'init', array( $this, 'init_components' ), 5 );

		if ( is_admin() ) {
			$this->admin = new Zouetech_Portfolio_Admin();
			$this->admin->register_hooks();

			$meta_boxes = new Zouetech_Portfolio_Meta_Boxes();
			$meta_boxes->register_hooks();

			$columns = new Zouetech_Portfolio_Admin_Columns();
			$columns->register_hooks();
		}

		$this->public = new Zouetech_Portfolio_Public();
		$this->public->register_hooks();

		add_action( 'elementor/loaded', array( $this, 'register_elementor' ) );

		// If Elementor already loaded before this plugin.
		if ( did_action( 'elementor/loaded' ) ) {
			$this->register_elementor();
		}
	}

	/**
	 * Initialize components that must run on `init`.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_components() {
		/**
		 * Fires when Zouetech Portfolio initializes components on `init`.
		 *
		 * @since 1.0.0
		 * @param Zouetech_Portfolio $plugin Main plugin instance.
		 */
		do_action( 'ztp_init_components', $this );
	}

	/**
	 * Bootstrap Elementor widgets + Dynamic Tags.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_elementor() {
		$elementor = new Zouetech_Portfolio_Elementor();
		$elementor->register_hooks();
	}

	/**
	 * Get plugin version.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_version() {
		return ZTP_VERSION;
	}
}
