<?php
/**
 * Shared helper utilities.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Helpers
 */
class Zouetech_Portfolio_Helpers {

	/**
	 * Get a single portfolio meta value with a default.
	 *
	 * @since 1.0.0
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @param mixed  $default Default value if empty.
	 * @return mixed
	 */
	public static function get_meta( $post_id, $key, $default = '' ) {
		$post_id = absint( $post_id );

		if ( ! $post_id ) {
			return $default;
		}

		$value = get_post_meta( $post_id, $key, true );

		if ( '' === $value || null === $value ) {
			return $default;
		}

		return $value;
	}

	/**
	 * Decode JSON repeater meta into array.
	 *
	 * @since 1.0.0
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_repeater( $post_id, $key ) {
		$raw = self::get_meta( $post_id, $key, '[]' );

		if ( is_array( $raw ) ) {
			return $raw;
		}

		$decoded = json_decode( (string) $raw, true );
		return is_array( $decoded ) ? $decoded : array();
	}

	/**
	 * Get gallery attachment IDs.
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 * @return array<int, int>
	 */
	public static function get_gallery_ids( $post_id ) {
		$raw = self::get_meta( $post_id, '_ztp_gallery', '' );

		if ( '' === $raw ) {
			return array();
		}

		$parts = preg_split( '/\s*,\s*/', (string) $raw );
		$ids   = array();

		foreach ( (array) $parts as $part ) {
			$id = absint( $part );
			if ( $id ) {
				$ids[] = $id;
			}
		}

		return $ids;
	}

	/**
	 * Whether Elementor is active and available.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public static function is_elementor_active() {
		return did_action( 'elementor/loaded' ) || class_exists( '\Elementor\Plugin' );
	}

	/**
	 * Format features as plain list text for Dynamic Tags.
	 *
	 * @since 1.0.0
	 * @param int    $post_id Post ID.
	 * @param string $sep     Separator.
	 * @return string
	 */
	public static function format_features_text( $post_id, $sep = ', ' ) {
		$titles = array();
		foreach ( self::get_repeater( $post_id, '_ztp_features' ) as $row ) {
			if ( ! empty( $row['title'] ) ) {
				$titles[] = $row['title'];
			}
		}
		return implode( $sep, $titles );
	}

	/**
	 * Format technologies as plain list text for Dynamic Tags.
	 *
	 * @since 1.0.0
	 * @param int    $post_id Post ID.
	 * @param string $sep     Separator.
	 * @return string
	 */
	public static function format_technologies_text( $post_id, $sep = ', ' ) {
		$names = array();
		foreach ( self::get_repeater( $post_id, '_ztp_technologies' ) as $row ) {
			if ( ! empty( $row['name'] ) ) {
				$names[] = $row['name'];
			}
		}
		return implode( $sep, $names );
	}

	/**
	 * Get human-readable project status label.
	 *
	 * @since 1.0.0
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function get_status_label( $post_id ) {
		$status  = self::get_meta( $post_id, '_ztp_project_status', 'completed' );
		$choices = Zouetech_Portfolio_Meta_Fields::get_status_choices();
		return isset( $choices[ $status ] ) ? $choices[ $status ] : $status;
	}
}
