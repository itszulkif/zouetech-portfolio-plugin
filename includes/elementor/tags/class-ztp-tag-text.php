<?php
/**
 * Elementor text Dynamic Tag for portfolio fields.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Tag_Text
 */
class Zouetech_Portfolio_Tag_Text extends \Elementor\Core\DynamicTags\Tag {

	use Zouetech_Portfolio_Tag_Helpers;

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_name() {
		return 'ztp-portfolio-text';
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_title() {
		return __( 'Portfolio Field', 'zouetech-portfolio' );
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
		return array( \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY );
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
				'options' => $this->ztp_field_options( array( 'text' ) ),
				'default' => '_ztp_client_name',
			)
		);
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

		$key = $this->get_settings( 'ztp_field' );
		if ( ! $key ) {
			return;
		}

		echo wp_kses_post( $this->ztp_resolve_text( $post_id, $key ) );
	}
}
