<?php
/**
 * GitHub auto-updater — installs new Releases automatically (no manual click).
 *
 * Flow:
 * 1. Publish a GitHub Release with a higher tag (e.g. v1.0.3).
 * 2. Cron checks GitHub regularly.
 * 3. Plugin auto-updates itself when a newer release is found.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_GitHub_Updater
 */
class Zouetech_Portfolio_GitHub_Updater {

	/**
	 * Cron hook name.
	 *
	 * @var string
	 */
	const CRON_HOOK = 'ztp_check_github_updates';

	/**
	 * Main plugin file path.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * GitHub repository slug (owner/repo).
	 *
	 * @var string
	 */
	private $repository;

	/**
	 * @since 1.0.1
	 * @param string $plugin_file Main plugin file.
	 * @param string $repository  GitHub repo slug.
	 */
	public function __construct( $plugin_file, $repository ) {
		$this->plugin_file = $plugin_file;
		$this->repository  = $repository;

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_information' ), 10, 3 );
		add_filter( 'upgrader_source_selection', array( $this, 'fix_source_folder' ), 10, 4 );

		// Always auto-update this plugin — no manual "update now" required.
		add_filter( 'auto_update_plugin', array( $this, 'force_auto_update' ), 10, 2 );
		add_filter( 'plugin_auto_update_setting_html', array( $this, 'auto_update_label' ), 10, 2 );

		add_action( self::CRON_HOOK, array( $this, 'cron_auto_update' ) );
		add_action( 'init', array( $this, 'ensure_cron_scheduled' ) );
	}

	/**
	 * Schedule hourly GitHub checks.
	 *
	 * @since 1.0.3
	 * @return void
	 */
	public function ensure_cron_scheduled() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + 60, 'hourly', self::CRON_HOOK );
		}
	}

	/**
	 * Clear cron on deactivation.
	 *
	 * @since 1.0.3
	 * @return void
	 */
	public static function clear_cron() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
		}
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	/**
	 * Force WordPress to auto-update this plugin only.
	 *
	 * @since 1.0.3
	 * @param bool|null $update Whether to update.
	 * @param object    $item   Update item.
	 * @return bool|null
	 */
	public function force_auto_update( $update, $item ) {
		$basename = plugin_basename( $this->plugin_file );

		if ( ! empty( $item->plugin ) && $basename === $item->plugin ) {
			return true;
		}

		if ( ! empty( $item->slug ) && dirname( $basename ) === $item->slug ) {
			return true;
		}

		return $update;
	}

	/**
	 * Show locked auto-update status (manual toggle removed).
	 *
	 * @since 1.0.3
	 * @param string $html        HTML.
	 * @param string $plugin_file Plugin file.
	 * @return string
	 */
	public function auto_update_label( $html, $plugin_file ) {
		if ( plugin_basename( $this->plugin_file ) !== $plugin_file ) {
			return $html;
		}

		return esc_html__( 'Auto-updates enabled (GitHub Releases)', 'zouetech-portfolio' );
	}

	/**
	 * Cron: refresh GitHub data and auto-install if newer.
	 *
	 * @since 1.0.3
	 * @return void
	 */
	public function cron_auto_update() {
		$release = $this->get_latest_release( true );
		if ( ! $release || empty( $release['version'] ) || empty( $release['package'] ) ) {
			return;
		}

		if ( version_compare( ZTP_VERSION, $release['version'], '>=' ) ) {
			return;
		}

		// Refresh WP update transient so auto-updater sees our package.
		delete_site_transient( 'update_plugins' );
		wp_update_plugins();

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( ! class_exists( 'Plugin_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/misc.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		$basename = plugin_basename( $this->plugin_file );
		$skin     = new Automatic_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );

		// Quiet upgrade from the GitHub package URL.
		$upgrader->upgrade( $basename );
	}

	/**
	 * Push update data into the plugins update transient.
	 *
	 * @since 1.0.1
	 * @param object $transient Update transient.
	 * @return object
	 */
	public function check_for_update( $transient ) {
		if ( ! is_object( $transient ) ) {
			return $transient;
		}

		if ( empty( $transient->checked ) ) {
			$transient->checked = array();
		}

		$basename = plugin_basename( $this->plugin_file );
		$release  = $this->get_latest_release();

		if ( ! $release || empty( $release['version'] ) || empty( $release['package'] ) ) {
			return $transient;
		}

		if ( version_compare( ZTP_VERSION, $release['version'], '<' ) ) {
			$transient->response[ $basename ] = (object) array(
				'id'            => $basename,
				'slug'          => dirname( $basename ),
				'plugin'        => $basename,
				'new_version'   => $release['version'],
				'url'           => 'https://github.com/' . $this->repository,
				'package'       => $release['package'],
				'icons'         => array(),
				'banners'       => array(),
				'banners_rtl'   => array(),
				'tested'        => get_bloginfo( 'version' ),
				'requires'      => '6.0',
				'auto_update'   => true,
			);
		} else {
			$transient->no_update[ $basename ] = (object) array(
				'slug'        => dirname( $basename ),
				'plugin'      => $basename,
				'new_version' => ZTP_VERSION,
				'url'         => 'https://github.com/' . $this->repository,
				'package'     => $release['package'],
			);
		}

		return $transient;
	}

	/**
	 * Provide plugin details on the update screen.
	 *
	 * @since 1.0.1
	 * @param false|object|array $result Plugin info result.
	 * @param string             $action API action.
	 * @param object             $args   API args.
	 * @return false|object
	 */
	public function plugin_information( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) ) {
			return $result;
		}

		$slug = dirname( plugin_basename( $this->plugin_file ) );
		if ( $args->slug !== $slug ) {
			return $result;
		}

		$release = $this->get_latest_release();
		if ( ! $release ) {
			return $result;
		}

		return (object) array(
			'name'          => 'Zouetech Portfolio',
			'slug'          => $slug,
			'version'       => $release['version'],
			'author'        => '<a href="https://zouetech.com/">Zouetech</a>',
			'homepage'      => 'https://zouetech.com/',
			'download_link' => $release['package'],
			'sections'      => array(
				'description' => __( 'Portfolio projects, Elementor Dynamic Tags, and a Featured Portfolio Showcase widget with multiple card styles.', 'zouetech-portfolio' ),
				'changelog'   => ! empty( $release['notes'] ) ? wp_kses_post( nl2br( $release['notes'] ) ) : '',
			),
		);
	}

	/**
	 * Rename GitHub zipball folder to the installed plugin folder name.
	 *
	 * @since 1.0.2
	 * @param string      $source        Extracted source path.
	 * @param string      $remote_source Remote source.
	 * @param WP_Upgrader $upgrader      Upgrader instance.
	 * @param array       $hook_extra    Extra data.
	 * @return string|WP_Error
	 */
	public function fix_source_folder( $source, $remote_source, $upgrader, $hook_extra ) {
		global $wp_filesystem;

		if ( empty( $hook_extra['plugin'] ) || plugin_basename( $this->plugin_file ) !== $hook_extra['plugin'] ) {
			return $source;
		}

		$desired = trailingslashit( $remote_source ) . dirname( plugin_basename( $this->plugin_file ) );
		$source  = untrailingslashit( $source );

		if ( trailingslashit( $source ) === trailingslashit( $desired ) ) {
			return trailingslashit( $source );
		}

		if ( $wp_filesystem->is_dir( $desired ) ) {
			$wp_filesystem->delete( $desired, true );
		}

		$moved = $wp_filesystem->move( $source, $desired );
		if ( ! $moved ) {
			return new WP_Error(
				'ztp_rename_failed',
				__( 'Could not rename the GitHub update package folder.', 'zouetech-portfolio' )
			);
		}

		return trailingslashit( $desired );
	}

	/**
	 * Fetch and cache the latest GitHub release.
	 *
	 * @since 1.0.1
	 * @param bool $force Bypass cache.
	 * @return array<string, string>|false
	 */
	private function get_latest_release( $force = false ) {
		$cache_key = 'ztp_github_release_' . md5( $this->repository );

		if ( ! $force ) {
			$cached = get_transient( $cache_key );
			if ( false !== $cached ) {
				return ( is_array( $cached ) && ! empty( $cached['version'] ) ) ? $cached : false;
			}
		} else {
			delete_transient( $cache_key );
		}

		$response = wp_remote_get(
			'https://api.github.com/repos/' . $this->repository . '/releases/latest',
			array(
				'timeout' => 20,
				'headers' => array(
					'Accept'     => 'application/vnd.github+json',
					'User-Agent' => 'Zouetech-Portfolio-Updater',
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			set_transient( $cache_key, array(), 15 * MINUTE_IN_SECONDS );
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) || empty( $body['tag_name'] ) ) {
			set_transient( $cache_key, array(), 15 * MINUTE_IN_SECONDS );
			return false;
		}

		$version = ltrim( (string) $body['tag_name'], 'vV' );
		$package = '';

		if ( ! empty( $body['assets'] ) && is_array( $body['assets'] ) ) {
			foreach ( $body['assets'] as $asset ) {
				if ( empty( $asset['browser_download_url'] ) ) {
					continue;
				}
				$name = isset( $asset['name'] ) ? (string) $asset['name'] : '';
				if ( preg_match( '/\.zip$/i', $name ) ) {
					$package = (string) $asset['browser_download_url'];
					break;
				}
			}
		}

		if ( ! $package && ! empty( $body['zipball_url'] ) ) {
			$package = (string) $body['zipball_url'];
		}

		if ( ! $package ) {
			set_transient( $cache_key, array(), 15 * MINUTE_IN_SECONDS );
			return false;
		}

		$release = array(
			'version' => $version,
			'package' => $package,
			'notes'   => isset( $body['body'] ) ? (string) $body['body'] : '',
		);

		// Short cache so new Releases are picked up quickly.
		set_transient( $cache_key, $release, HOUR_IN_SECONDS );

		return $release;
	}
}
