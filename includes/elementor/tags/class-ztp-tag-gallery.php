<?php
/**
 * Elementor gallery Dynamic Tag for portfolio gallery.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Tag_Gallery
 */
class Zouetech_Portfolio_Tag_Gallery extends \Elementor\Core\DynamicTags\Data_Tag {

	use Zouetech_Portfolio_Tag_Helpers;

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_name() {
		return 'ztp-portfolio-gallery';
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_title() {
		return __( 'Portfolio Gallery', 'zouetech-portfolio' );
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
			if ( $ref->hasConstant( 'GALLERY_CATEGORY' ) ) {
				return array( \Elementor\Modules\DynamicTags\Module::GALLERY_CATEGORY );
			}
		}
		return array( \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY );
	}

	/**
	 * @since 1.0.0
	 * @param array<string, mixed> $options Options.
	 * @return array<int, array<string, mixed>>
	 */
	public function get_value( array $options = array() ) {
		$post_id = $this->ztp_get_post_id();
		if ( ! $this->ztp_is_portfolio( $post_id ) ) {
			return array();
		}

		$images = array();
		foreach ( Zouetech_Portfolio_Helpers::get_gallery_ids( $post_id ) as $id ) {
			$url = wp_get_attachment_image_url( $id, 'full' );
			if ( $url ) {
				$images[] = array(
					'id'  => $id,
					'url' => $url,
				);
			}
		}

		return $images;
	}
}
