<?php
/**
 * Meta sanitization helpers.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Meta_Sanitizer
 */
class Zouetech_Portfolio_Meta_Sanitizer {

	/**
	 * Sanitize a meta value by field key using the field schema.
	 *
	 * @since 1.0.0
	 * @param mixed  $value     Raw value.
	 * @param string $meta_key  Meta key.
	 * @return mixed
	 */
	public static function sanitize_by_key( $value, $meta_key ) {
		$fields = Zouetech_Portfolio_Meta_Fields::get_fields();

		if ( ! isset( $fields[ $meta_key ] ) ) {
			return sanitize_text_field( is_scalar( $value ) ? (string) $value : '' );
		}

		$type = isset( $fields[ $meta_key ]['type'] ) ? $fields[ $meta_key ]['type'] : 'text';

		switch ( $type ) {
			case 'url':
				return self::sanitize_url( $value );
			case 'date':
				return self::sanitize_date( $value );
			case 'select':
				$choices = isset( $fields[ $meta_key ]['choices'] ) ? array_keys( $fields[ $meta_key ]['choices'] ) : array();
				return self::sanitize_choice( $value, $choices, $fields[ $meta_key ]['default'] );
			case 'wysiwyg':
				return self::sanitize_wysiwyg( $value );
			case 'textarea':
				return sanitize_textarea_field( is_scalar( $value ) ? (string) $value : '' );
			case 'gallery':
				return self::sanitize_gallery( $value );
			case 'image':
				return self::sanitize_attachment_id( $value );
			case 'rating':
				return self::sanitize_rating( $value );
			case 'repeater_features':
				return self::sanitize_features( $value );
			case 'repeater_technologies':
				return self::sanitize_technologies( $value );
			case 'text':
			default:
				return sanitize_text_field( is_scalar( $value ) ? (string) $value : '' );
		}
	}

	/**
	 * Sanitize URL.
	 *
	 * @since 1.0.0
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_url( $value ) {
		$value = is_scalar( $value ) ? trim( (string) $value ) : '';
		return $value ? esc_url_raw( $value ) : '';
	}

	/**
	 * Sanitize date (Y-m-d).
	 *
	 * @since 1.0.0
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_date( $value ) {
		$value = is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';

		if ( '' === $value ) {
			return '';
		}

		$dt = DateTime::createFromFormat( 'Y-m-d', $value );
		return ( $dt && $dt->format( 'Y-m-d' ) === $value ) ? $value : '';
	}

	/**
	 * Sanitize a choice against an allowed list.
	 *
	 * @since 1.0.0
	 * @param mixed                $value   Raw value.
	 * @param array<int, string>   $allowed Allowed values.
	 * @param string               $default Default.
	 * @return string
	 */
	public static function sanitize_choice( $value, array $allowed, $default = '' ) {
		$value = is_scalar( $value ) ? sanitize_key( (string) $value ) : '';
		return in_array( $value, $allowed, true ) ? $value : $default;
	}

	/**
	 * Sanitize rich text.
	 *
	 * @since 1.0.0
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_wysiwyg( $value ) {
		return wp_kses_post( is_scalar( $value ) ? (string) $value : '' );
	}

	/**
	 * Sanitize gallery as comma-separated attachment IDs.
	 *
	 * @since 1.0.0
	 * @param mixed $value Raw value (CSV string, array of IDs, or JSON).
	 * @return string
	 */
	public static function sanitize_gallery( $value ) {
		$ids = array();

		if ( is_array( $value ) ) {
			$ids = $value;
		} elseif ( is_string( $value ) ) {
			$trimmed = trim( $value );
			if ( '' === $trimmed ) {
				return '';
			}
			if ( '[' === $trimmed[0] ) {
				$decoded = json_decode( $trimmed, true );
				$ids     = is_array( $decoded ) ? $decoded : array();
			} else {
				$ids = preg_split( '/\s*,\s*/', $trimmed );
			}
		}

		$clean = array();
		foreach ( (array) $ids as $id ) {
			$id = absint( $id );
			if ( $id && self::is_image_attachment( $id ) ) {
				$clean[] = $id;
			}
		}

		$clean = array_values( array_unique( $clean ) );
		return implode( ',', $clean );
	}

	/**
	 * Sanitize attachment ID (images only).
	 *
	 * @since 1.0.0
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_attachment_id( $value ) {
		$id = absint( $value );
		return ( $id && self::is_image_attachment( $id ) ) ? $id : 0;
	}

	/**
	 * Sanitize rating 0–5.
	 *
	 * @since 1.0.0
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_rating( $value ) {
		$rating = absint( $value );
		if ( $rating > 5 ) {
			$rating = 5;
		}
		return $rating;
	}

	/**
	 * Sanitize features repeater to JSON string.
	 *
	 * @since 1.0.0
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_features( $value ) {
		$rows = self::decode_repeater( $value );
		$out  = array();

		foreach ( $rows as $row ) {
			$title = isset( $row['title'] ) ? sanitize_text_field( (string) $row['title'] ) : '';
			if ( '' !== $title ) {
				$out[] = array( 'title' => $title );
			}
		}

		return wp_json_encode( $out );
	}

	/**
	 * Sanitize technologies repeater to JSON string.
	 *
	 * @since 1.0.0
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_technologies( $value ) {
		$rows = self::decode_repeater( $value );
		$out  = array();

		foreach ( $rows as $row ) {
			$name = isset( $row['name'] ) ? sanitize_text_field( (string) $row['name'] ) : '';
			$icon = isset( $row['icon'] ) ? self::sanitize_attachment_id( $row['icon'] ) : 0;

			if ( '' !== $name ) {
				$out[] = array(
					'name' => $name,
					'icon' => $icon,
				);
			}
		}

		return wp_json_encode( $out );
	}

	/**
	 * Decode repeater input into array of rows.
	 *
	 * @since 1.0.0
	 * @param mixed $value Raw value.
	 * @return array<int, array<string, mixed>>
	 */
	private static function decode_repeater( $value ) {
		if ( is_array( $value ) ) {
			return $value;
		}

		if ( is_string( $value ) ) {
			$decoded = json_decode( $value, true );
			return is_array( $decoded ) ? $decoded : array();
		}

		return array();
	}

	/**
	 * Whether an attachment is an image.
	 *
	 * @since 1.0.0
	 * @param int $attachment_id Attachment ID.
	 * @return bool
	 */
	private static function is_image_attachment( $attachment_id ) {
		$mime = get_post_mime_type( $attachment_id );
		return ( is_string( $mime ) && 0 === strpos( $mime, 'image/' ) );
	}

	/**
	 * Auth callback for registered meta.
	 *
	 * @since 1.0.0
	 * @param bool   $allowed  Whether allowed.
	 * @param string $meta_key Meta key.
	 * @param int    $post_id  Post ID.
	 * @return bool
	 */
	public static function auth_callback( $allowed, $meta_key, $post_id ) {
		return current_user_can( 'edit_post', $post_id );
	}
}
