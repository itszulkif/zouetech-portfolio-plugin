<?php
/**
 * GitHub branch auto-updater.
 *
 * Watches the `main` branch. Any new push (new commit SHA) can auto-install.
 * No GitHub Release required. Manual "update now" is not needed.
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
	 * Cron hook.
	 *
	 * @var string
	 */
	const CRON_HOOK = 'ztp_check_github_updates';

	/**
	 * Option: last installed commit SHA.
	 *
	 * @var string
	 */
	const SHA_OPTION = 'ztp_github_installed_sha';

	/**
	 * Main plugin file.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * owner/repo.
	 *
	 * @var string
	 */
	private $repository;

	/**
	 * Branch name.
	 *
	 * @var string
	 */
	private $branch = 'main';

	/**
	 * @param string $plugin_file Plugin main file.
	 * @param string $repository  GitHub repo slug.
	 */
	public function __construct( $plugin_file, $repository ) {
		$this->plugin_file = $plugin_file;
		$this->repository  = $repository;

		add_filter( 'cron_schedules', array( $this, 'add_cron_schedule' ) );
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_information' ), 10, 3 );
		add_filter( 'upgrader_source_selection', array( $this, 'fix_source_folder' ), 10, 4 );
		add_filter( 'auto_update_plugin', array( $this, 'force_auto_update' ), 10, 2 );
		add_filter( 'plugin_auto_update_setting_html', array( $this, 'auto_update_label' ), 10, 2 );
		add_action( 'upgrader_process_complete', array( $this, 'after_upgrade' ), 10, 2 );
		add_action( self::CRON_HOOK, array( $this, 'cron_auto_update' ) );
		add_action( 'init', array( $this, 'ensure_cron_scheduled' ), 20 );

		// Faster check when visiting wp-admin.
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'maybe_admin_check' ), 30 );
		}
	}

	/**
	 * Custom 15-minute schedule.
	 *
	 * @param array<string, array<string, mixed>> $schedules Schedules.
	 * @return array<string, array<string, mixed>>
	 */
	public function add_cron_schedule( $schedules ) {
		$schedules['ztp_fifteen_minutes'] = array(
			'interval' => 15 * MINUTE_IN_SECONDS,
			'display'  => __( 'Every 15 Minutes (Zouetech Portfolio)', 'zouetech-portfolio' ),
		);
		return $schedules;
	}

	/**
	 * Ensure cron is scheduled.
	 *
	 * @return void
	 */
	public function ensure_cron_scheduled() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + 120, 'ztp_fifteen_minutes', self::CRON_HOOK );
		}
	}

	/**
	 * Clear cron.
	 *
	 * @return void
	 */
	public static function clear_cron() {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	/**
	 * Force auto-update for this plugin.
	 *
	 * @param bool|null $update Update decision.
	 * @param object    $item   Item.
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
	 * Locked auto-update label.
	 *
	 * @param string $html        HTML.
	 * @param string $plugin_file File.
	 * @return string
	 */
	public function auto_update_label( $html, $plugin_file ) {
		if ( plugin_basename( $this->plugin_file ) !== $plugin_file ) {
			return $html;
		}
		return esc_html__( 'Auto-updates from GitHub (main branch)', 'zouetech-portfolio' );
	}

	/**
	 * Occasional admin-side refresh (max once / 10 min).
	 *
	 * @return void
	 */
	public function maybe_admin_check() {
		if ( get_transient( 'ztp_admin_update_checked' ) ) {
			return;
		}
		set_transient( 'ztp_admin_update_checked', 1, 10 * MINUTE_IN_SECONDS );
		$this->cron_auto_update();
	}

	/**
	 * Cron worker: detect new GitHub commit and upgrade.
	 *
	 * @return void
	 */
	public function cron_auto_update() {
		$remote = $this->get_remote_info( true );
		if ( ! $remote ) {
			return;
		}

		if ( ! $this->is_update_available( $remote ) ) {
			return;
		}

		delete_site_transient( 'update_plugins' );
		wp_update_plugins();

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$basename = plugin_basename( $this->plugin_file );
		$skin     = new Automatic_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->upgrade( $basename );

		if ( ! is_wp_error( $result ) && $result ) {
			update_option( self::SHA_OPTION, $remote['sha'], false );
		}
	}

	/**
	 * Inject update into WP transient.
	 *
	 * @param object $transient Transient.
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
		$remote   = $this->get_remote_info();

		if ( ! $remote || empty( $remote['package'] ) ) {
			return $transient;
		}

		if ( $this->is_update_available( $remote ) ) {
			$transient->response[ $basename ] = (object) array(
				'id'          => $basename,
				'slug'        => dirname( $basename ),
				'plugin'      => $basename,
				'new_version' => $remote['new_version'],
				'url'         => 'https://github.com/' . $this->repository,
				'package'     => $remote['package'],
				'tested'      => get_bloginfo( 'version' ),
				'requires'    => '6.0',
				'auto_update' => true,
			);
		} else {
			$transient->no_update[ $basename ] = (object) array(
				'slug'        => dirname( $basename ),
				'plugin'      => $basename,
				'new_version' => ZTP_VERSION,
				'url'         => 'https://github.com/' . $this->repository,
				'package'     => $remote['package'],
			);
		}

		return $transient;
	}

	/**
	 * Plugin info modal.
	 *
	 * @param mixed  $result Result.
	 * @param string $action Action.
	 * @param object $args   Args.
	 * @return mixed
	 */
	public function plugin_information( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) ) {
			return $result;
		}
		if ( $args->slug !== dirname( plugin_basename( $this->plugin_file ) ) ) {
			return $result;
		}

		$remote = $this->get_remote_info();
		if ( ! $remote ) {
			return $result;
		}

		return (object) array(
			'name'          => 'Zouetech Portfolio',
			'slug'          => dirname( plugin_basename( $this->plugin_file ) ),
			'version'       => $remote['new_version'],
			'author'        => '<a href="https://zouetech.com/">Zouetech</a>',
			'homepage'      => 'https://github.com/' . $this->repository,
			'download_link' => $remote['package'],
			'sections'      => array(
				'description' => __( 'Auto-updates from the GitHub main branch when you push.', 'zouetech-portfolio' ),
			),
		);
	}

	/**
	 * Rename extracted GitHub folder to plugin folder name.
	 *
	 * @param string      $source        Source.
	 * @param string      $remote_source Remote.
	 * @param WP_Upgrader $upgrader      Upgrader.
	 * @param array       $hook_extra    Extra.
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

		// Sometimes GitHub zip nests one extra directory.
		$files = array_values( array_filter( (array) scandir( $source ), static function ( $f ) {
			return '.' !== $f && '..' !== $f;
		} ) );

		$actual = $source;
		if ( 1 === count( $files ) && is_dir( $source . '/' . $files[0] ) ) {
			$maybe = $source . '/' . $files[0];
			if ( file_exists( $maybe . '/zouetech-portfolio.php' ) || file_exists( $maybe . '/' . basename( $this->plugin_file ) ) ) {
				$actual = $maybe;
			} elseif ( ! file_exists( $source . '/zouetech-portfolio.php' ) ) {
				$actual = $maybe;
			}
		}

		if ( $wp_filesystem->is_dir( $desired ) ) {
			$wp_filesystem->delete( $desired, true );
		}

		if ( ! $wp_filesystem->move( $actual, $desired ) ) {
			return new WP_Error( 'ztp_rename_failed', __( 'Could not rename GitHub package folder.', 'zouetech-portfolio' ) );
		}

		return trailingslashit( $desired );
	}

	/**
	 * Remember installed SHA after upgrade.
	 *
	 * @param WP_Upgrader $upgrader Upgrader.
	 * @param array       $options  Options.
	 * @return void
	 */
	public function after_upgrade( $upgrader, $options ) {
		if ( empty( $options['action'] ) || 'update' !== $options['action'] || empty( $options['type'] ) || 'plugin' !== $options['type'] ) {
			return;
		}

		$basename = plugin_basename( $this->plugin_file );
		$plugins  = array();
		if ( ! empty( $options['plugins'] ) && is_array( $options['plugins'] ) ) {
			$plugins = $options['plugins'];
		} elseif ( ! empty( $options['plugin'] ) ) {
			$plugins = array( $options['plugin'] );
		}

		if ( ! in_array( $basename, $plugins, true ) ) {
			return;
		}

		$remote = $this->get_remote_info( true );
		if ( $remote && ! empty( $remote['sha'] ) ) {
			update_option( self::SHA_OPTION, $remote['sha'], false );
		}
	}

	/**
	 * Whether remote commit should be installed.
	 *
	 * @param array<string, string> $remote Remote info.
	 * @return bool
	 */
	private function is_update_available( array $remote ) {
		if ( empty( $remote['sha'] ) || empty( $remote['package'] ) ) {
			return false;
		}

		$installed_sha = (string) get_option( self::SHA_OPTION, '' );

		// New commit on main → update.
		if ( $installed_sha && hash_equals( $installed_sha, $remote['sha'] ) ) {
			return false;
		}

		// First run after installing this updater: if SHA unknown but version newer, update.
		if ( ! $installed_sha && version_compare( ZTP_VERSION, $remote['version'], '>=' ) ) {
			// Same version, never recorded SHA — record current and skip once.
			update_option( self::SHA_OPTION, $remote['sha'], false );
			return false;
		}

		if ( ! $installed_sha && version_compare( ZTP_VERSION, $remote['version'], '<' ) ) {
			return true;
		}

		// Different SHA than last installed → update.
		return ( ! $installed_sha || ! hash_equals( $installed_sha, $remote['sha'] ) );
	}

	/**
	 * Fetch remote version + commit from GitHub main.
	 *
	 * @param bool $force Bypass cache.
	 * @return array<string, string>|false
	 */
	private function get_remote_info( $force = false ) {
		$cache_key = 'ztp_github_branch_' . md5( $this->repository . $this->branch );

		if ( ! $force ) {
			$cached = get_transient( $cache_key );
			if ( is_array( $cached ) && ! empty( $cached['sha'] ) ) {
				return $cached;
			}
		} else {
			delete_transient( $cache_key );
		}

		$headers = array(
			'Accept'     => 'application/vnd.github+json',
			'User-Agent' => 'Zouetech-Portfolio-Updater',
		);

		// Latest commit on main.
		$commit_res = wp_remote_get(
			'https://api.github.com/repos/' . $this->repository . '/commits/' . rawurlencode( $this->branch ),
			array(
				'timeout' => 20,
				'headers' => $headers,
			)
		);

		if ( is_wp_error( $commit_res ) || 200 !== (int) wp_remote_retrieve_response_code( $commit_res ) ) {
			set_transient( $cache_key, array(), 10 * MINUTE_IN_SECONDS );
			return false;
		}

		$commit = json_decode( wp_remote_retrieve_body( $commit_res ), true );
		$sha    = isset( $commit['sha'] ) ? (string) $commit['sha'] : '';
		if ( ! $sha ) {
			set_transient( $cache_key, array(), 10 * MINUTE_IN_SECONDS );
			return false;
		}

		// Remote plugin header Version.
		$raw_res = wp_remote_get(
			'https://raw.githubusercontent.com/' . $this->repository . '/' . rawurlencode( $this->branch ) . '/zouetech-portfolio.php',
			array(
				'timeout' => 20,
				'headers' => array( 'User-Agent' => 'Zouetech-Portfolio-Updater' ),
			)
		);

		$version = ZTP_VERSION;
		if ( ! is_wp_error( $raw_res ) && 200 === (int) wp_remote_retrieve_response_code( $raw_res ) ) {
			$body = (string) wp_remote_retrieve_body( $raw_res );
			if ( preg_match( '/^\s*\*\s*Version:\s*(.+)$/mi', $body, $m ) ) {
				$version = trim( $m[1] );
			}
		}

		$installed_sha = (string) get_option( self::SHA_OPTION, '' );
		$new_version   = $version;

		// If commit changed but Version header did not, still force a newer "new_version".
		if ( $installed_sha && ! hash_equals( $installed_sha, $sha ) && version_compare( $version, ZTP_VERSION, '<=' ) ) {
			$new_version = ZTP_VERSION . '.' . substr( preg_replace( '/[^0-9a-f]/i', '', $sha ), 0, 8 );
		} elseif ( version_compare( $version, ZTP_VERSION, '>' ) ) {
			$new_version = $version;
		} elseif ( $installed_sha && ! hash_equals( $installed_sha, $sha ) ) {
			$new_version = $version . '.' . substr( preg_replace( '/[^0-9a-f]/i', '', $sha ), 0, 8 );
		}

		$info = array(
			'sha'         => $sha,
			'version'     => $version,
			'new_version' => $new_version,
			'package'     => 'https://codeload.github.com/' . $this->repository . '/zip/refs/heads/' . rawurlencode( $this->branch ),
		);

		set_transient( $cache_key, $info, 10 * MINUTE_IN_SECONDS );

		return $info;
	}
}
