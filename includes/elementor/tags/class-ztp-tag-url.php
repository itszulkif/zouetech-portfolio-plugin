<?php
/**
 * Elementor URL Dynamic Tags for portfolio link fields (Button Link, etc.).
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\Elementor\Core\DynamicTags\Data_Tag' ) ) {
	return;
}

/**
 * Base URL Data Tag.
 */
abstract class Zouetech_Portfolio_Tag_URL_Base extends \Elementor\Core\DynamicTags\Data_Tag {

	use Zouetech_Portfolio_Tag_Helpers;

	/**
	 * Meta key returned by child class.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	abstract protected function ztp_meta_key();

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_group() {
		return Zouetech_Portfolio_Elementor_Dynamic_Tags::GROUP;
	}

	/**
	 * @since 1.0.0
	 * @return array<int, string>
	 */
	public function get_categories() {
		return array( \Elementor\Modules\DynamicTags\Module::URL_CATEGORY );
	}

	/**
	 * @since 1.0.0
	 * @param array<string, mixed> $options Options.
	 * @return string
	 */
	public function get_value( array $options = array() ) {
		unset( $options );

		$post_id = $this->ztp_get_post_id();
		if ( ! $this->ztp_is_portfolio( $post_id ) ) {
			return '';
		}

		$url = Zouetech_Portfolio_Helpers::get_meta( $post_id, $this->ztp_meta_key(), '' );
		return $url ? esc_url( $url ) : '';
	}
}

/**
 * Live Preview URL — for Button / Link controls.
 */
class Zouetech_Portfolio_Tag_Live_URL extends Zouetech_Portfolio_Tag_URL_Base {

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_name() {
		return 'ztp-live-preview-url';
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_title() {
		return __( 'Live Preview URL', 'zouetech-portfolio' );
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	protected function ztp_meta_key() {
		return '_ztp_live_url';
	}
}

/**
 * GitHub URL — for Button / Link controls.
 */
class Zouetech_Portfolio_Tag_Github_URL extends Zouetech_Portfolio_Tag_URL_Base {

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_name() {
		return 'ztp-github-url';
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_title() {
		return __( 'GitHub URL', 'zouetech-portfolio' );
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	protected function ztp_meta_key() {
		return '_ztp_github_url';
	}
}

/**
 * Project Video URL — for Button / Link controls.
 */
class Zouetech_Portfolio_Tag_Video_URL extends Zouetech_Portfolio_Tag_URL_Base {

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_name() {
		return 'ztp-video-url';
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_title() {
		return __( 'Project Video URL', 'zouetech-portfolio' );
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	protected function ztp_meta_key() {
		return '_ztp_video_url';
	}
}

/**
 * Generic Portfolio URL picker (all URL fields).
 */
class Zouetech_Portfolio_Tag_URL extends Zouetech_Portfolio_Tag_URL_Base {

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_name() {
		return 'ztp-portfolio-url';
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_title() {
		return __( 'Portfolio URL (Any)', 'zouetech-portfolio' );
	}

	/**
	 * Unused — key comes from control.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function ztp_meta_key() {
		return '_ztp_live_url';
	}

	/**
	 * @since 1.0.0
	 * @return void
	 */
	protected function register_controls() {
		$this->add_control(
			'ztp_field',
			array(
				'label'   => __( 'URL Field', 'zouetech-portfolio' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'_ztp_live_url'  => __( 'Live Preview URL', 'zouetech-portfolio' ),
					'_ztp_github_url' => __( 'GitHub URL', 'zouetech-portfolio' ),
					'_ztp_video_url' => __( 'Project Video URL', 'zouetech-portfolio' ),
				),
				'default' => '_ztp_live_url',
			)
		);
	}

	/**
	 * @since 1.0.0
	 * @param array<string, mixed> $options Options.
	 * @return string
	 */
	public function get_value( array $options = array() ) {
		unset( $options );

		$post_id = $this->ztp_get_post_id();
		if ( ! $this->ztp_is_portfolio( $post_id ) ) {
			return '';
		}

		$key = $this->get_settings( 'ztp_field' );
		if ( ! $key ) {
			$key = '_ztp_live_url';
		}

		$url = Zouetech_Portfolio_Helpers::get_meta( $post_id, $key, '' );
		return $url ? esc_url( $url ) : '';
	}
}
