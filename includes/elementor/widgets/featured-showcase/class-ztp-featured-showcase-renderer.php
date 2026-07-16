<?php
/**
 * Featured Showcase HTML renderer.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Featured_Showcase_Renderer
 */
class Zouetech_Portfolio_Featured_Showcase_Renderer {

	/**
	 * Whether an Elementor SWITCHER setting is enabled.
	 *
	 * Elementor stores: on = 'yes', off = '' (empty).
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings Settings.
	 * @param string               $key      Control key.
	 * @return bool
	 */
	private function is_switch_on( array $settings, $key ) {
		return isset( $settings[ $key ] ) && 'yes' === $settings[ $key ];
	}

	/**
	 * Render selected showcase style.
	 *
	 * @since 1.0.0
	 * @param array<int, array<string, mixed>> $projects Projects.
	 * @param array<string, mixed>             $settings Settings.
	 * @param WP_Query|null                    $query    Optional query (for pagination).
	 * @return void
	 */
	public function render( array $projects, array $settings, $query = null ) {
		$style = Zouetech_Portfolio_Featured_Showcase_Styles::sanitize(
			isset( $settings['showcase_style'] ) ? $settings['showcase_style'] : Zouetech_Portfolio_Featured_Showcase_Styles::DEFAULT
		);

		if ( empty( $projects ) ) {
			echo '<div class="ztp-fs ztp-fs--empty ztp-fs--' . esc_attr( $style ) . '"><p>' . esc_html__( 'No portfolio projects found.', 'zouetech-portfolio' ) . '</p></div>';
			return;
		}

		$template = Zouetech_Portfolio_Featured_Showcase_Styles::get_template_path( $style );
		if ( ! is_readable( $template ) ) {
			echo '<div class="ztp-fs ztp-fs--empty"><p>' . esc_html__( 'Selected showcase style is not available.', 'zouetech-portfolio' ) . '</p></div>';
			return;
		}

		$cards_count = 3;
		if ( 'card-style-1' === $style && isset( $settings['s1_columns_desktop'] ) ) {
			$cards_count = absint( $settings['s1_columns_desktop'] );
		} elseif ( isset( $settings['columns_desktop'] ) ) {
			$cards_count = absint( $settings['columns_desktop'] );
		}
		if ( $cards_count < 1 ) {
			$cards_count = 3;
		}

		$duration = 500;
		if ( 'card-style-1' === $style ) {
			if ( isset( $settings['s1_animation_duration']['size'] ) ) {
				$duration = absint( $settings['s1_animation_duration']['size'] );
			} elseif ( isset( $settings['s1_animation_duration'] ) ) {
				$duration = absint( $settings['s1_animation_duration'] );
			}
		} elseif ( isset( $settings['animation_duration']['size'] ) ) {
			$duration = absint( $settings['animation_duration']['size'] );
		} elseif ( isset( $settings['animation_duration'] ) ) {
			$duration = absint( $settings['animation_duration'] );
		}
		if ( $duration < 100 ) {
			$duration = 500;
		}

		$show = array(
			'image'        => $this->is_switch_on( $settings, 'show_featured_image' ),
			'gallery'      => $this->is_switch_on( $settings, 'show_gallery' ),
			'category'     => $this->is_switch_on( $settings, 'show_category' ),
			'title'        => $this->is_switch_on( $settings, 'show_title' ),
			'excerpt'      => $this->is_switch_on( $settings, 'show_excerpt' ),
			'view_details' => $this->is_switch_on( $settings, 'show_view_details' ),
			'align_button' => $this->is_switch_on( $settings, 's2_align_button' ),
			'nav'          => $this->is_switch_on( $settings, 'show_nav' ),
			'cards'        => $this->is_switch_on( $settings, 'show_bottom_cards' ),
		);

		$labels = array(
			'back'         => ! empty( $settings['back_label'] ) ? $settings['back_label'] : __( '← Back', 'zouetech-portfolio' ),
			'next'         => ! empty( $settings['next_label'] ) ? $settings['next_label'] : __( 'Next →', 'zouetech-portfolio' ),
			'view_details' => ! empty( $settings['view_details_label'] ) ? $settings['view_details_label'] : __( 'View Details', 'zouetech-portfolio' ),
		);

		$pagination = array(
			'type'            => 'none',
			'page'            => 1,
			'max'             => 1,
			'load_more_label' => __( 'Load More', 'zouetech-portfolio' ),
		);

		if ( 'card-style-2' === $style ) {
			$pagination['type'] = isset( $settings['s2_pagination_type'] ) ? sanitize_key( $settings['s2_pagination_type'] ) : 'none';
			$pagination['page'] = isset( $settings['paged'] ) ? absint( $settings['paged'] ) : 1;
			$pagination['max']  = ( $query instanceof WP_Query ) ? max( 1, (int) $query->max_num_pages ) : 1;
			if ( ! empty( $settings['s2_load_more_label'] ) ) {
				$pagination['load_more_label'] = $settings['s2_load_more_label'];
			}
		}

		$head     = 0;
		$featured = $projects[ $head ];
		$cards    = array();
		$total    = count( $projects );
		for ( $i = 0; $i < $cards_count; $i++ ) {
			$idx = ( $head + 1 + $i ) % $total;
			if ( $total > 1 && $idx === $head ) {
				continue;
			}
			if ( $total <= 1 ) {
				break;
			}
			$cards[] = $projects[ $idx ];
			if ( count( $cards ) >= min( $cards_count, $total - 1 ) ) {
				break;
			}
		}

		$uid = 'ztp-fs-' . wp_unique_id();

		include $template;
	}
}
