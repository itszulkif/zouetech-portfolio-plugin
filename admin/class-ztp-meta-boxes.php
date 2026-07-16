<?php
/**
 * Portfolio admin meta boxes.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Meta_Boxes
 */
class Zouetech_Portfolio_Meta_Boxes {

	/**
	 * Nonce action.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'ztp_save_portfolio_meta';

	/**
	 * Nonce field name.
	 *
	 * @var string
	 */
	const NONCE_NAME = 'ztp_portfolio_meta_nonce';

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . Zouetech_Portfolio_CPT::POST_TYPE, array( $this, 'save' ), 10, 2 );
	}

	/**
	 * Register meta boxes.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'ztp_portfolio_details',
			__( 'Portfolio Project Details', 'zouetech-portfolio' ),
			array( $this, 'render' ),
			Zouetech_Portfolio_CPT::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render the meta box UI.
	 *
	 * @since 1.0.0
	 * @param WP_Post $post Current post.
	 * @return void
	 */
	public function render( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$groups = Zouetech_Portfolio_Meta_Fields::get_groups();

		echo '<div class="ztp-admin-wrap">';

		foreach ( $groups as $group_key => $group ) {
			$fields = Zouetech_Portfolio_Meta_Fields::get_fields_by_group( $group_key );
			$view   = ZTP_PLUGIN_DIR . 'admin/views/meta-box-' . str_replace( '_', '-', $group_key ) . '.php';

			echo '<section class="ztp-card" data-ztp-group="' . esc_attr( $group_key ) . '">';
			echo '<header class="ztp-card__header">';
			echo '<span class="ztp-card__icon dashicons ' . esc_attr( $group['icon'] ) . '" aria-hidden="true"></span>';
			echo '<div class="ztp-card__titles">';
			echo '<h3 class="ztp-card__title">' . esc_html( $group['title'] ) . '</h3>';
			if ( ! empty( $group['desc'] ) ) {
				echo '<p class="ztp-card__desc">' . esc_html( $group['desc'] ) . '</p>';
			}
			echo '</div></header>';
			echo '<div class="ztp-card__body">';

			if ( is_readable( $view ) ) {
				include $view;
			} else {
				$this->render_default_fields( $post, $fields );
			}

			echo '</div></section>';
		}

		echo '</div>';
	}

	/**
	 * Fallback renderer for simple fields.
	 *
	 * @since 1.0.0
	 * @param WP_Post                          $post   Post.
	 * @param array<string, array<string, mixed>> $fields Fields.
	 * @return void
	 */
	private function render_default_fields( $post, $fields ) {
		foreach ( $fields as $key => $field ) {
			$value = Zouetech_Portfolio_Helpers::get_meta( $post->ID, $key, isset( $field['default'] ) ? $field['default'] : '' );
			$type  = isset( $field['type'] ) ? $field['type'] : 'text';

			echo '<div class="ztp-field">';
			echo '<label class="ztp-field__label" for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label>';

			switch ( $type ) {
				case 'url':
				case 'text':
				case 'date':
					$input_type = ( 'date' === $type ) ? 'date' : ( ( 'url' === $type ) ? 'url' : 'text' );
					printf(
						'<input class="ztp-field__input" type="%1$s" id="%2$s" name="%2$s" value="%3$s" />',
						esc_attr( $input_type ),
						esc_attr( $key ),
						esc_attr( (string) $value )
					);
					break;
				case 'textarea':
					printf(
						'<textarea class="ztp-field__input" id="%1$s" name="%1$s" rows="4">%2$s</textarea>',
						esc_attr( $key ),
						esc_textarea( (string) $value )
					);
					break;
				case 'select':
					echo '<select class="ztp-field__input" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
					$choices = isset( $field['choices'] ) ? $field['choices'] : array();
					foreach ( $choices as $choice_value => $choice_label ) {
						printf(
							'<option value="%1$s" %2$s>%3$s</option>',
							esc_attr( $choice_value ),
							selected( (string) $value, (string) $choice_value, false ),
							esc_html( $choice_label )
						);
					}
					echo '</select>';
					break;
				case 'rating':
					echo '<select class="ztp-field__input" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
					for ( $i = 0; $i <= 5; $i++ ) {
						$label = ( 0 === $i ) ? __( 'None', 'zouetech-portfolio' ) : sprintf(
							/* translators: %d: star rating */
							_n( '%d star', '%d stars', $i, 'zouetech-portfolio' ),
							$i
						);
						printf(
							'<option value="%1$d" %2$s>%3$s</option>',
							(int) $i,
							selected( (int) $value, $i, false ),
							esc_html( $label )
						);
					}
					echo '</select>';
					break;
				default:
					break;
			}

			if ( ! empty( $field['description'] ) ) {
				echo '<p class="ztp-field__help">' . esc_html( $field['description'] ) . '</p>';
			}

			echo '</div>';
		}
	}

	/**
	 * Save meta box data.
	 *
	 * @since 1.0.0
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return void
	 */
	public function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) );
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( Zouetech_Portfolio_CPT::POST_TYPE !== $post->post_type ) {
			return;
		}

		foreach ( Zouetech_Portfolio_Meta_Fields::get_fields() as $meta_key => $field ) {
			$type = isset( $field['type'] ) ? $field['type'] : 'text';

			if ( 'repeater_features' === $type ) {
				$raw = isset( $_POST['_ztp_features_rows'] ) ? wp_unslash( $_POST['_ztp_features_rows'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$sanitized = Zouetech_Portfolio_Meta_Sanitizer::sanitize_features( is_array( $raw ) ? $raw : array() );
				update_post_meta( $post_id, '_ztp_features', $sanitized );
				continue;
			}

			if ( 'repeater_technologies' === $type ) {
				$raw = isset( $_POST['_ztp_technologies_rows'] ) ? wp_unslash( $_POST['_ztp_technologies_rows'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$sanitized = Zouetech_Portfolio_Meta_Sanitizer::sanitize_technologies( is_array( $raw ) ? $raw : array() );
				update_post_meta( $post_id, '_ztp_technologies', $sanitized );
				continue;
			}

			if ( ! isset( $_POST[ $meta_key ] ) ) {
				// Allow clearing checkbox-like / optional emptied fields submitted as empty.
				if ( in_array( $type, array( 'gallery', 'image', 'wysiwyg', 'textarea', 'url', 'text', 'date', 'select', 'rating' ), true ) ) {
					// Some fields may be omitted from POST when using media pickers reset via JS — gallery/image handled below.
					if ( 'gallery' === $type || 'image' === $type ) {
						$empty = ( 'image' === $type ) ? 0 : '';
						$empty = Zouetech_Portfolio_Meta_Sanitizer::sanitize_by_key( $empty, $meta_key );
						update_post_meta( $post_id, $meta_key, $empty );
					}
				}
				continue;
			}

			$raw       = wp_unslash( $_POST[ $meta_key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$sanitized = Zouetech_Portfolio_Meta_Sanitizer::sanitize_by_key( $raw, $meta_key );
			update_post_meta( $post_id, $meta_key, $sanitized );
		}
	}
}
