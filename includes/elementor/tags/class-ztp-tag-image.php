<?php
/**
 * Elementor image Dynamic Tag for portfolio fields.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Tag_Image
 */
class Zouetech_Portfolio_Tag_Image extends \Elementor\Core\DynamicTags\Data_Tag {

	use Zouetech_Portfolio_Tag_Helpers;

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_name() {
		return 'ztp-portfolio-image';
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_title() {
		return __( 'Portfolio Image', 'zouetech-portfolio' );
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
		return array( \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY );
	}

	/**
	 * @since 1.0.0
	 * @return void
	 */
	protected function register_controls() {
		$this->add_control(
			'ztp_field',
			array(
				'label'   => __( 'Field', 'zouetech-portfolio' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $this->ztp_field_options( array( 'image' ) ),
				'default' => '_ztp_testimonial_photo',
			)
		);
	}

	/**
	 * @since 1.0.0
	 * @param array<string, mixed> $options Options.
	 * @return array<string, mixed>
	 */
	public function get_value( array $options = array() ) {
		$post_id = $this->ztp_get_post_id();
		if ( ! $this->ztp_is_portfolio( $post_id ) ) {
			return array(
				'id'  => 0,
				'url' => '',
			);
		}

		$key = $this->get_settings( 'ztp_field' );
		$id  = absint( Zouetech_Portfolio_Helpers::get_meta( $post_id, $key, 0 ) );
		$url = $id ? wp_get_attachment_image_url( $id, 'full' ) : '';

		return array(
			'id'  => $id,
			'url' => $url ? $url : '',
		);
	}
}
