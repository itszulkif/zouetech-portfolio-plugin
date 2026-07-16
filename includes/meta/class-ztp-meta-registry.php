<?php
/**
 * Registers portfolio post meta for REST / Elementor / Rank Math.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Meta_Registry
 */
class Zouetech_Portfolio_Meta_Registry {

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'register' ), 20 );
	}

	/**
	 * Register all post meta fields.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register() {
		foreach ( Zouetech_Portfolio_Meta_Fields::get_fields() as $meta_key => $field ) {
			$args = array(
				'type'              => isset( $field['data_type'] ) ? $field['data_type'] : 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => isset( $field['default'] ) ? $field['default'] : '',
				'auth_callback'     => array( 'Zouetech_Portfolio_Meta_Sanitizer', 'auth_callback' ),
				'sanitize_callback' => static function ( $value ) use ( $meta_key ) {
					return Zouetech_Portfolio_Meta_Sanitizer::sanitize_by_key( $value, $meta_key );
				},
			);

			// REST schema descriptions help Elementor / consumers.
			$args['show_in_rest'] = array(
				'schema' => array(
					'type'        => $args['type'],
					'description' => isset( $field['label'] ) ? $field['label'] : $meta_key,
					'context'     => array( 'view', 'edit' ),
				),
			);

			if ( 'integer' === $args['type'] ) {
				$args['show_in_rest']['schema']['type'] = 'integer';
			}

			register_post_meta( Zouetech_Portfolio_CPT::POST_TYPE, $meta_key, $args );
		}
	}
}
