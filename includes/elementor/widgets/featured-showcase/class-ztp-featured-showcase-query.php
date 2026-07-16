<?php
/**
 * Featured Showcase query builder (single WP_Query).
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Featured_Showcase_Query
 */
class Zouetech_Portfolio_Featured_Showcase_Query {

	/**
	 * Resolve post type from settings.
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings Settings.
	 * @return string
	 */
	public static function get_post_type( array $settings ) {
		$post_type = isset( $settings['source_post_type'] ) ? sanitize_key( $settings['source_post_type'] ) : Zouetech_Portfolio_CPT::POST_TYPE;
		if ( ! $post_type || ! post_type_exists( $post_type ) ) {
			$post_type = Zouetech_Portfolio_CPT::POST_TYPE;
		}
		return $post_type;
	}

	/**
	 * Resolve taxonomy from settings (explicit or default for post type).
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings Settings.
	 * @param string               $post_type Post type.
	 * @return string Empty if none.
	 */
	public static function get_taxonomy( array $settings, $post_type ) {
		$taxonomy = isset( $settings['source_taxonomy'] ) ? sanitize_key( $settings['source_taxonomy'] ) : '';

		if ( $taxonomy && taxonomy_exists( $taxonomy ) ) {
			return $taxonomy;
		}

		// Sensible defaults.
		if ( Zouetech_Portfolio_CPT::POST_TYPE === $post_type ) {
			return Zouetech_Portfolio_Category::TAXONOMY;
		}
		if ( 'post' === $post_type ) {
			return 'category';
		}

		$taxonomies = get_object_taxonomies( $post_type, 'names' );
		return ! empty( $taxonomies[0] ) ? $taxonomies[0] : '';
	}

	/**
	 * Run a single query from widget settings.
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings Widget settings.
	 * @return WP_Query
	 */
	public function query( array $settings ) {
		$posts_per_page = isset( $settings['posts_per_page'] ) ? absint( $settings['posts_per_page'] ) : 8;
		if ( $posts_per_page < 1 ) {
			$posts_per_page = 8;
		}

		$orderby = isset( $settings['orderby'] ) ? sanitize_key( $settings['orderby'] ) : 'date';
		$order   = isset( $settings['order'] ) ? strtoupper( sanitize_text_field( $settings['order'] ) ) : 'DESC';
		if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
			$order = 'DESC';
		}

		$allowed_orderby = array( 'date', 'title', 'menu_order', 'rand', 'modified' );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'date';
		}

		$post_type = self::get_post_type( $settings );

		$args = array(
			'post_type'              => $post_type,
			'post_status'            => 'publish',
			'posts_per_page'         => $posts_per_page,
			'orderby'                => $orderby,
			'order'                  => $order,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => true,
		);

		// Term filter: source_term_id overrides dropdown; else source_term / legacy.
		$term_id  = 0;
		$taxonomy = self::get_taxonomy( $settings, $post_type );

		if ( ! empty( $settings['source_term_id'] ) ) {
			$term_id = absint( $settings['source_term_id'] );
		} elseif ( ! empty( $settings['source_term'] ) && '0' !== (string) $settings['source_term'] ) {
			$raw = sanitize_text_field( (string) $settings['source_term'] );
			if ( false !== strpos( $raw, ':' ) ) {
				$parts = explode( ':', $raw, 2 );
				if ( ! empty( $parts[0] ) && taxonomy_exists( $parts[0] ) ) {
					$taxonomy = sanitize_key( $parts[0] );
				}
				$term_id = isset( $parts[1] ) ? absint( $parts[1] ) : 0;
			} else {
				$term_id = absint( $raw );
			}
		} elseif ( isset( $settings['portfolio_category'] ) ) {
			$term_id = absint( $settings['portfolio_category'] );
		}

		if ( $term_id > 0 && $taxonomy ) {
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => $taxonomy,
					'field'    => 'term_id',
					'terms'    => array( $term_id ),
				),
			);
		}

		$exclude_current = ! empty( $settings['exclude_current'] ) && 'yes' === $settings['exclude_current'];
		if ( $exclude_current && is_singular( $post_type ) ) {
			$args['post__not_in'] = array( get_the_ID() ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
		}

		return new WP_Query( $args );
	}

	/**
	 * Normalize posts into JS-safe project arrays.
	 *
	 * @since 1.0.0
	 * @param WP_Query             $query    Query.
	 * @param array<string, mixed> $settings Settings.
	 * @return array<int, array<string, mixed>>
	 */
	public function normalize( WP_Query $query, array $settings ) {
		$image_size    = isset( $settings['image_size'] ) ? sanitize_key( $settings['image_size'] ) : 'large';
		$gallery_count = isset( $settings['gallery_count'] ) ? absint( $settings['gallery_count'] ) : 0;
		$post_type     = self::get_post_type( $settings );
		$taxonomy      = self::get_taxonomy( $settings, $post_type );
		$projects      = array();

		if ( ! $query->have_posts() ) {
			return $projects;
		}

		$use_ztp_gallery = ( Zouetech_Portfolio_CPT::POST_TYPE === $post_type );

		foreach ( $query->posts as $post ) {
			$post_id = (int) $post->ID;

			$thumb_id  = (int) get_post_thumbnail_id( $post_id );
			$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, $image_size ) : '';
			$thumb_src = $thumb_id ? wp_get_attachment_image_src( $thumb_id, $image_size ) : null;

			$gallery_ids = array();
			if ( $use_ztp_gallery ) {
				$gallery_ids = Zouetech_Portfolio_Helpers::get_gallery_ids( $post_id );
				if ( $gallery_count > 0 ) {
					$gallery_ids = array_slice( $gallery_ids, 0, $gallery_count );
				}
			}

			$gallery = array();

			if ( $thumb_url ) {
				$gallery[] = array(
					'id'          => $thumb_id,
					'url'         => $thumb_url,
					'thumb'       => wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) ?: $thumb_url,
					'is_featured' => 1,
				);
			}

			foreach ( $gallery_ids as $att_id ) {
				if ( $thumb_id && (int) $att_id === $thumb_id ) {
					continue;
				}
				$url = wp_get_attachment_image_url( $att_id, $image_size );
				$sm  = wp_get_attachment_image_url( $att_id, 'thumbnail' );
				if ( $url ) {
					$gallery[] = array(
						'id'          => (int) $att_id,
						'url'         => $url,
						'thumb'       => $sm ? $sm : $url,
						'is_featured' => 0,
					);
				}
			}

			$cat     = '';
			$cat_url = '';
			if ( $taxonomy ) {
				$terms = get_the_terms( $post_id, $taxonomy );
				if ( is_array( $terms ) && ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$cat       = $terms[0]->name;
					$term_link = get_term_link( $terms[0] );
					$cat_url   = ( ! is_wp_error( $term_link ) ) ? $term_link : '';
				}
			}

			$excerpt = has_excerpt( $post_id )
				? get_the_excerpt( $post_id )
				: wp_trim_words( wp_strip_all_tags( $post->post_content ), 28, '…' );

			$main_url = $thumb_url;
			$main_id  = $thumb_id;
			if ( ! $main_url && ! empty( $gallery[0]['url'] ) ) {
				$main_url = $gallery[0]['url'];
				$main_id  = (int) $gallery[0]['id'];
			}

			$projects[] = array(
				'id'           => $post_id,
				'title'        => get_the_title( $post_id ),
				'url'          => get_permalink( $post_id ),
				'excerpt'      => $excerpt,
				'category'     => $cat,
				'category_url' => $cat_url,
				'image'        => array(
					'id'     => $main_id,
					'url'    => $main_url ? $main_url : '',
					'width'  => $thumb_src ? (int) $thumb_src[1] : 0,
					'height' => $thumb_src ? (int) $thumb_src[2] : 0,
				),
				'gallery'      => $gallery,
			);
		}

		wp_reset_postdata();

		return $projects;
	}
}
