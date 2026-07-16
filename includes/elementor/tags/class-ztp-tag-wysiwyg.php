<?php
/**
 * Elementor WYSIWYG Dynamic Tag for project overview.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Tag_Wysiwyg
 */
class Zouetech_Portfolio_Tag_Wysiwyg extends \Elementor\Core\DynamicTags\Tag {

	use Zouetech_Portfolio_Tag_Helpers;

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_name() {
		return 'ztp-portfolio-wysiwyg';
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_title() {
		return __( 'Portfolio Overview', 'zouetech-portfolio' );
	}

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
		if ( class_exists( '\Elementor\Modules\DynamicTags\Module' ) ) {
			$ref = new \ReflectionClass( '\Elementor\Modules\DynamicTags\Module' );
			if ( $ref->hasConstant( 'WYSIWYG_CATEGORY' ) ) {
				return array( \Elementor\Modules\DynamicTags\Module::WYSIWYG_CATEGORY );
			}
		}
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
	}

	/**
	 * @since 1.0.0
	 * @return void
	 */
	public function render() {
		$post_id = $this->ztp_get_post_id();
		if ( ! $this->ztp_is_portfolio( $post_id ) ) {
			return;
		}

		$content = Zouetech_Portfolio_Helpers::get_meta( $post_id, '_ztp_overview', '' );
		echo wp_kses_post( $content );
	}
}
