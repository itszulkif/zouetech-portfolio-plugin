<?php
/**
 * Featured Portfolio Showcase — Card Style 2.
 *
 * Grid of portfolio cards: image + category badge, then title & excerpt panel.
 *
 * @package Zouetech_Portfolio
 *
 * @var array  $projects
 * @var array  $show
 * @var array  $labels
 * @var array  $settings
 * @var array  $pagination
 * @var string $uid
 * @var string $style
 */

defined( 'ABSPATH' ) || exit;

$align_btn = ! empty( $show['align_button'] );
$pag_type  = isset( $pagination['type'] ) ? $pagination['type'] : 'none';
$pag_page  = isset( $pagination['page'] ) ? (int) $pagination['page'] : 1;
$pag_max   = isset( $pagination['max'] ) ? (int) $pagination['max'] : 1;
$pag_label = isset( $pagination['load_more_label'] ) ? $pagination['load_more_label'] : __( 'Load More', 'zouetech-portfolio' );

$query_settings = array(
	'source_post_type'           => isset( $settings['source_post_type'] ) ? $settings['source_post_type'] : '',
	'source_taxonomy'            => isset( $settings['source_taxonomy'] ) ? $settings['source_taxonomy'] : '',
	'source_term'                => isset( $settings['source_term'] ) ? $settings['source_term'] : '0',
	'source_term_id'             => isset( $settings['source_term_id'] ) ? $settings['source_term_id'] : 0,
	'orderby'                    => isset( $settings['orderby'] ) ? $settings['orderby'] : 'date',
	'order'                      => isset( $settings['order'] ) ? $settings['order'] : 'DESC',
	'posts_per_page'             => isset( $settings['posts_per_page'] ) ? $settings['posts_per_page'] : 8,
	'exclude_current'            => isset( $settings['exclude_current'] ) ? $settings['exclude_current'] : '',
	'image_size'                 => isset( $settings['image_size'] ) ? $settings['image_size'] : 'large',
	'gallery_count'              => isset( $settings['gallery_count'] ) ? $settings['gallery_count'] : 0,
	'showcase_style'             => 'card-style-2',
	's2_excerpt_length'          => isset( $settings['s2_excerpt_length'] ) ? $settings['s2_excerpt_length'] : 20,
	's2_apply_excerpt_to_custom' => isset( $settings['s2_apply_excerpt_to_custom'] ) ? $settings['s2_apply_excerpt_to_custom'] : '',
	's2_pagination_type'         => $pag_type,
	'show_featured_image'        => ! empty( $show['image'] ) ? 'yes' : '',
	'show_category'              => ! empty( $show['category'] ) ? 'yes' : '',
	'show_title'                 => ! empty( $show['title'] ) ? 'yes' : '',
	'show_excerpt'               => ! empty( $show['excerpt'] ) ? 'yes' : '',
	'show_view_details'          => ! empty( $show['view_details'] ) ? 'yes' : '',
	'view_details_label'         => isset( $labels['view_details'] ) ? $labels['view_details'] : '',
);

$settings_json = wp_json_encode( $query_settings );
if ( ! $settings_json ) {
	$settings_json = '{}';
}

$root_class = 'ztp-fs ztp-fs--' . $style;
if ( $align_btn ) {
	$root_class .= ' is-btn-align';
}
?>
<div
	id="<?php echo esc_attr( $uid ); ?>"
	class="<?php echo esc_attr( $root_class ); ?>"
	data-ztp-s2
	data-ztp-style="<?php echo esc_attr( $style ); ?>"
	data-ztp-page="<?php echo esc_attr( (string) $pag_page ); ?>"
	data-ztp-max="<?php echo esc_attr( (string) $pag_max ); ?>"
	data-ztp-pag-type="<?php echo esc_attr( $pag_type ); ?>"
	data-ztp-settings="<?php echo esc_attr( $settings_json ); ?>"
	role="region"
	aria-label="<?php esc_attr_e( 'Portfolio grid', 'zouetech-portfolio' ); ?>"
>
	<div class="ztp-fs__cards ztp-fs__cards--s2" data-ztp-s2-grid>
		<?php foreach ( $projects as $project ) : ?>
			<?php
			$item_file = ZTP_PLUGIN_DIR . 'includes/elementor/widgets/featured-showcase/templates/card-style-2-item.php';
			if ( is_readable( $item_file ) ) {
				include $item_file;
			}
			?>
		<?php endforeach; ?>
	</div>

	<?php if ( 'numbers' === $pag_type && $pag_max > 1 ) : ?>
		<nav class="ztp-fs__pagination ztp-fs__pagination--numbers" data-ztp-s2-pagination aria-label="<?php esc_attr_e( 'Portfolio pagination', 'zouetech-portfolio' ); ?>">
			<?php for ( $i = 1; $i <= $pag_max; $i++ ) : ?>
				<button
					type="button"
					class="ztp-fs__page-btn<?php echo ( $i === $pag_page ) ? ' is-active' : ''; ?>"
					data-ztp-s2-page="<?php echo esc_attr( (string) $i ); ?>"
					aria-label="<?php echo esc_attr( sprintf( /* translators: %d page number */ __( 'Page %d', 'zouetech-portfolio' ), $i ) ); ?>"
					<?php echo ( $i === $pag_page ) ? 'aria-current="page"' : ''; ?>
				>
					<?php echo esc_html( (string) $i ); ?>
				</button>
			<?php endfor; ?>
		</nav>
	<?php elseif ( in_array( $pag_type, array( 'load_more', 'infinite' ), true ) && $pag_page < $pag_max ) : ?>
		<div class="ztp-fs__pagination ztp-fs__pagination--more" data-ztp-s2-pagination>
			<?php if ( 'load_more' === $pag_type ) : ?>
				<button type="button" class="ztp-fs__load-more" data-ztp-s2-load-more>
					<?php echo esc_html( $pag_label ); ?>
				</button>
			<?php else : ?>
				<div class="ztp-fs__infinite-sentinel" data-ztp-s2-sentinel aria-hidden="true"></div>
				<span class="ztp-fs__loading" data-ztp-s2-loading hidden><?php esc_html_e( 'Loading…', 'zouetech-portfolio' ); ?></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
