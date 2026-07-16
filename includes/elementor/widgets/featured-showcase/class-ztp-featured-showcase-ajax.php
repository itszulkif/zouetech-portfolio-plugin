<?php
/**
 * Featured Showcase AJAX (Style 2 pagination / load more).
 *
 * @package Zouetech_Portfolio
 * @since   1.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Featured_Showcase_Ajax
 */
class Zouetech_Portfolio_Featured_Showcase_Ajax {

	/**
	 * Register AJAX hooks.
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public static function register() {
		add_action( 'wp_ajax_ztp_fs_s2_load', array( __CLASS__, 'handle' ) );
		add_action( 'wp_ajax_nopriv_ztp_fs_s2_load', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle Style 2 page / load-more request.
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public static function handle() {
		check_ajax_referer( 'ztp_fs_s2', 'nonce' );

		$raw = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( is_string( $raw ) ) {
			$settings = json_decode( $raw, true );
		} else {
			$settings = array();
		}

		if ( ! is_array( $settings ) ) {
			wp_send_json_error( array( 'message' => 'Invalid settings.' ), 400 );
		}

		$settings = self::sanitize_settings( $settings );
		$page     = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		if ( $page < 1 ) {
			$page = 1;
		}

		$settings['paged'] = $page;

		$queryer = new Zouetech_Portfolio_Featured_Showcase_Query();
		$query   = $queryer->query( $settings );
		$projects = $queryer->normalize( $query, $settings );

		$show = array(
			'image'        => ( ! isset( $settings['show_featured_image'] ) || 'yes' === $settings['show_featured_image'] ),
			'category'     => ( ! isset( $settings['show_category'] ) || 'yes' === $settings['show_category'] ),
			'title'        => ( ! isset( $settings['show_title'] ) || 'yes' === $settings['show_title'] ),
			'excerpt'      => ( ! isset( $settings['show_excerpt'] ) || 'yes' === $settings['show_excerpt'] ),
			'view_details' => ( ! isset( $settings['show_view_details'] ) || 'yes' === $settings['show_view_details'] ),
		);

		$labels = array(
			'view_details' => ! empty( $settings['view_details_label'] )
				? sanitize_text_field( $settings['view_details_label'] )
				: __( 'View Details', 'zouetech-portfolio' ),
		);

		ob_start();
		foreach ( $projects as $project ) {
			$item_file = ZTP_PLUGIN_DIR . 'includes/elementor/widgets/featured-showcase/templates/card-style-2-item.php';
			if ( is_readable( $item_file ) ) {
				include $item_file;
			}
		}
		$html = ob_get_clean();

		wp_send_json_success(
			array(
				'html'          => $html,
				'page'          => $page,
				'max_num_pages' => (int) $query->max_num_pages,
				'found_posts'   => (int) $query->found_posts,
			)
		);
	}

	/**
	 * Sanitize widget settings used for querying.
	 *
	 * @since 1.0.1
	 * @param array<string, mixed> $settings Raw settings.
	 * @return array<string, mixed>
	 */
	private static function sanitize_settings( array $settings ) {
		$out = array(
			'source_post_type'           => isset( $settings['source_post_type'] ) ? sanitize_key( $settings['source_post_type'] ) : Zouetech_Portfolio_CPT::POST_TYPE,
			'source_taxonomy'            => isset( $settings['source_taxonomy'] ) ? sanitize_key( $settings['source_taxonomy'] ) : '',
			'source_term'                => isset( $settings['source_term'] ) ? sanitize_text_field( (string) $settings['source_term'] ) : '0',
			'source_term_id'             => isset( $settings['source_term_id'] ) ? absint( $settings['source_term_id'] ) : 0,
			'orderby'                    => isset( $settings['orderby'] ) ? sanitize_key( $settings['orderby'] ) : 'date',
			'order'                      => isset( $settings['order'] ) ? sanitize_text_field( (string) $settings['order'] ) : 'DESC',
			'posts_per_page'             => isset( $settings['posts_per_page'] ) ? absint( $settings['posts_per_page'] ) : 8,
			'exclude_current'            => isset( $settings['exclude_current'] ) && 'yes' === $settings['exclude_current'] ? 'yes' : '',
			'image_size'                 => isset( $settings['image_size'] ) ? sanitize_key( $settings['image_size'] ) : 'large',
			'gallery_count'              => isset( $settings['gallery_count'] ) ? absint( $settings['gallery_count'] ) : 0,
			'showcase_style'             => 'card-style-2',
			's2_excerpt_length'          => isset( $settings['s2_excerpt_length'] ) ? absint( $settings['s2_excerpt_length'] ) : 20,
			's2_apply_excerpt_to_custom' => isset( $settings['s2_apply_excerpt_to_custom'] ) && 'yes' === $settings['s2_apply_excerpt_to_custom'] ? 'yes' : '',
			'show_featured_image'        => isset( $settings['show_featured_image'] ) ? sanitize_text_field( (string) $settings['show_featured_image'] ) : 'yes',
			'show_category'              => isset( $settings['show_category'] ) ? sanitize_text_field( (string) $settings['show_category'] ) : 'yes',
			'show_title'                 => isset( $settings['show_title'] ) ? sanitize_text_field( (string) $settings['show_title'] ) : 'yes',
			'show_excerpt'               => isset( $settings['show_excerpt'] ) ? sanitize_text_field( (string) $settings['show_excerpt'] ) : 'yes',
			'show_view_details'          => isset( $settings['show_view_details'] ) ? sanitize_text_field( (string) $settings['show_view_details'] ) : 'yes',
			'view_details_label'         => isset( $settings['view_details_label'] ) ? sanitize_text_field( (string) $settings['view_details_label'] ) : '',
		);

		return $out;
	}
}
