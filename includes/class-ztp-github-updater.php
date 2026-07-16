<?php
/**
 * GitHub release updater for native WordPress update + auto-update toggles.
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
	}

	/**
	 * Push update data into the plugins update transient.
	 *
	 * @since 1.0.1
	 * @param object $transient Update transient.
	 * @return object
	 */
	public function check_for_update( $transient ) {
		if ( ! is_object( $transient ) || empty( $transient->checked ) ) {
			return $transient;
		}

		$basename = plugin_basename( $this->plugin_file );
		$release  = $this->get_latest_release();

		if ( ! $release || empty( $release['version'] ) || empty( $release['package'] ) ) {
			return $transient;
		}

		if ( version_compare( ZTP_VERSION, $release['version'], '<' ) ) {
			$transient->response[ $basename ] = (object) array(
				'slug'        => dirname( $basename ),
				'plugin'      => $basename,
				'new_version' => $release['version'],
				'url'         => 'https://github.com/' . $this->repository,
				'package'     => $release['package'],
				'tested'      => get_bloginfo( 'version' ),
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
				'changelog'   => ! empty( $release['notes'] ) ? wp_kses_post( $release['notes'] ) : '',
			),
		);
	}

	/**
	 * Fetch and cache the latest GitHub release.
	 *
	 * @since 1.0.1
	 * @return array<string, string>|false
	 */
	private function get_latest_release() {
		$cache_key = 'ztp_github_release_' . md5( $this->repository );
		$cached    = get_transient( $cache_key );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		$response = wp_remote_get(
			'https://api.github.com/repos/' . $this->repository . '/releases/latest',
			array(
				'timeout' => 15,
				'headers' => array(
					'Accept'     => 'application/vnd.github+json',
					'User-Agent' => 'Zouetech-Portfolio-Updater',
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			set_transient( $cache_key, array(), HOUR_IN_SECONDS );
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $body ) || empty( $body['tag_name'] ) ) {
			set_transient( $cache_key, array(), HOUR_IN_SECONDS );
			return false;
		}

		$version = ltrim( (string) $body['tag_name'], 'vV' );
		$package = '';

		if ( ! empty( $body['zipball_url'] ) ) {
			$package = (string) $body['zipball_url'];
		} elseif ( ! empty( $body['assets'][0]['browser_download_url'] ) ) {
			$package = (string) $body['assets'][0]['browser_download_url'];
		}

		if ( ! $package ) {
			set_transient( $cache_key, array(), HOUR_IN_SECONDS );
			return false;
		}

		$release = array(
			'version' => $version,
			'package' => $package,
			'notes'   => isset( $body['body'] ) ? (string) $body['body'] : '',
		);

		set_transient( $cache_key, $release, 12 * HOUR_IN_SECONDS );

		return $release;
	}
}
