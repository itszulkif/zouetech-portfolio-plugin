<?php
/**
 * Style 2 — single card item (shared by grid + AJAX).
 *
 * @package Zouetech_Portfolio
 *
 * @var array $project
 * @var array $show
 * @var array $labels
 */

defined( 'ABSPATH' ) || exit;

$view_label   = ! empty( $labels['view_details'] ) ? $labels['view_details'] : __( 'View Details', 'zouetech-portfolio' );
$has_image    = ! empty( $show['image'] ) && ! empty( $project['image']['url'] );
$has_category = ! empty( $show['category'] ) && ! empty( $project['category'] );
?>
<article class="ztp-fs__card ztp-fs__card--s2" data-ztp-s2-card data-id="<?php echo esc_attr( (string) $project['id'] ); ?>">
	<?php if ( $has_image ) : ?>
		<div class="ztp-fs__card-media ztp-fs__card-media--s2">
			<a class="ztp-fs__card-media-link" href="<?php echo esc_url( $project['url'] ); ?>" aria-label="<?php echo esc_attr( $project['title'] ); ?>">
				<img
					src="<?php echo esc_url( $project['image']['url'] ); ?>"
					alt="<?php echo esc_attr( $project['title'] ); ?>"
					loading="lazy"
					decoding="async"
				/>
			</a>

			<?php if ( $has_category ) : ?>
				<?php if ( ! empty( $project['category_url'] ) ) : ?>
					<a class="ztp-fs__card-badge" href="<?php echo esc_url( $project['category_url'] ); ?>">
						<?php echo esc_html( $project['category'] ); ?>
					</a>
				<?php else : ?>
					<span class="ztp-fs__card-badge"><?php echo esc_html( $project['category'] ); ?></span>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	<?php elseif ( $has_category ) : ?>
		<?php if ( ! empty( $project['category_url'] ) ) : ?>
			<a class="ztp-fs__card-badge ztp-fs__card-badge--inline" href="<?php echo esc_url( $project['category_url'] ); ?>">
				<?php echo esc_html( $project['category'] ); ?>
			</a>
		<?php else : ?>
			<span class="ztp-fs__card-badge ztp-fs__card-badge--inline"><?php echo esc_html( $project['category'] ); ?></span>
		<?php endif; ?>
	<?php endif; ?>

	<div class="ztp-fs__card-body ztp-fs__card-body--s2">
		<?php if ( ! empty( $show['title'] ) ) : ?>
			<h3 class="ztp-fs__card-title">
				<a href="<?php echo esc_url( $project['url'] ); ?>"><?php echo esc_html( $project['title'] ); ?></a>
			</h3>
		<?php endif; ?>

		<?php if ( ! empty( $show['excerpt'] ) && ! empty( $project['excerpt'] ) ) : ?>
			<p class="ztp-fs__excerpt ztp-fs__card-excerpt"><?php echo esc_html( $project['excerpt'] ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $show['view_details'] ) ) : ?>
			<a class="ztp-fs__card-btn" href="<?php echo esc_url( $project['url'] ); ?>">
				<?php echo esc_html( $view_label ); ?>
			</a>
		<?php endif; ?>
	</div>
</article>
