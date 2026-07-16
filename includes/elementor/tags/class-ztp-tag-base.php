<?php
/**
 * Shared helpers for Zouetech Portfolio Elementor tags.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Trait Zouetech_Portfolio_Tag_Helpers
 */
trait Zouetech_Portfolio_Tag_Helpers {

	/**
	 * Current portfolio post ID in Elementor context.
	 *
	 * Respects Theme Builder / preview queried object when available.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	protected function ztp_get_post_id() {
		$post_id = 0;

		// Theme Builder / archive preview often uses the queried object.
		$queried = get_queried_object_id();
		if ( $queried && Zouetech_Portfolio_CPT::POST_TYPE === get_post_type( $queried ) ) {
			$post_id = $queried;
		}

		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		// Elementor preview of a specific post.
		if ( ( ! $post_id || Zouetech_Portfolio_CPT::POST_TYPE !== get_post_type( $post_id ) )
			&& isset( $_GET['preview_id'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) {
			$preview_id = absint( wp_unslash( $_GET['preview_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( $preview_id && Zouetech_Portfolio_CPT::POST_TYPE === get_post_type( $preview_id ) ) {
				$post_id = $preview_id;
			}
		}

		return $post_id ? absint( $post_id ) : 0;
	}

	/**
	 * Whether current post is a portfolio project.
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	protected function ztp_is_portfolio( $post_id ) {
		return $post_id && Zouetech_Portfolio_CPT::POST_TYPE === get_post_type( $post_id );
	}

	/**
	 * Build unique select options for meta keys of given Elementor kinds.
	 *
	 * Skips raw repeater JSON keys (features/technologies) — those use list virtual keys.
	 * Uses elementor_label when set so labels never collide.
	 *
	 * @since 1.0.0
	 * @param array<int, string> $kinds Elementor kinds from field schema.
	 * @return array<string, string>
	 */
	protected function ztp_field_options( array $kinds ) {
		$options = array();
		$seen    = array();

		foreach ( Zouetech_Portfolio_Meta_Fields::get_fields() as $key => $field ) {
			$kind = isset( $field['elementor'] ) ? $field['elementor'] : 'text';

			// Raw JSON repeaters are not usable as plain text — skip always.
			if ( in_array( $kind, array( 'features', 'technologies' ), true ) ) {
				continue;
			}

			if ( ! in_array( $kind, $kinds, true ) ) {
				continue;
			}

			$label = ! empty( $field['elementor_label'] )
				? $field['elementor_label']
				: ( isset( $field['label'] ) ? $field['label'] : $key );

			// Guarantee uniqueness if two labels somehow match.
			if ( isset( $seen[ $label ] ) ) {
				$label = $label . ' (' . $key . ')';
			}
			$seen[ $label ]  = true;
			$options[ $key ] = $label;
		}

		// Virtual text-only helpers (once each).
		if ( in_array( 'text', $kinds, true ) ) {
			$virtual = array(
				'_ztp_features_list'     => __( 'Key Features (List)', 'zouetech-portfolio' ),
				'_ztp_technologies_list' => __( 'Technologies (List)', 'zouetech-portfolio' ),
			);
			foreach ( $virtual as $vkey => $vlabel ) {
				if ( ! isset( $options[ $vkey ] ) ) {
					$options[ $vkey ] = $vlabel;
				}
			}
		}

		return $options;
	}

	/**
	 * Resolve a text-like meta value for Dynamic Tags.
	 *
	 * @since 1.0.0
	 * @param int    $post_id Post ID.
	 * @param string $key     Field key.
	 * @return string
	 */
	protected function ztp_resolve_text( $post_id, $key ) {
		if ( '_ztp_features_list' === $key ) {
			return Zouetech_Portfolio_Helpers::format_features_text( $post_id );
		}
		if ( '_ztp_technologies_list' === $key ) {
			return Zouetech_Portfolio_Helpers::format_technologies_text( $post_id );
		}
		// Always return human-readable status for the status field.
		if ( '_ztp_project_status' === $key || '_ztp_project_status_label' === $key ) {
			return Zouetech_Portfolio_Helpers::get_status_label( $post_id );
		}

		$value = Zouetech_Portfolio_Helpers::get_meta( $post_id, $key, '' );

		if ( '_ztp_testimonial_rating' === $key && '' !== $value && null !== $value ) {
			$rating = absint( $value );
			if ( $rating > 0 ) {
				return (string) $rating;
			}
			return '';
		}

		return is_scalar( $value ) ? (string) $value : '';
	}
}
