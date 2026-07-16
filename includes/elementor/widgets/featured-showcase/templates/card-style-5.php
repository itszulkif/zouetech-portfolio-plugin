<?php
/**
 * Featured Portfolio Showcase — Card Style 5.
 *
 * Placeholder layout with a dark feature shell and compact cards.
 *
 * @package Zouetech_Portfolio
 *
 * @var array  $projects
 * @var array  $show
 * @var array  $labels
 * @var array  $featured
 * @var array  $cards
 * @var int    $cards_count
 * @var int    $duration
 * @var string $uid
 * @var string $style
 */

defined( 'ABSPATH' ) || exit;

$json = wp_json_encode( $projects );
if ( ! $json ) {
	$json = '[]';
}
?>
<div
	id="<?php echo esc_attr( $uid ); ?>"
	class="ztp-fs ztp-fs--<?php echo esc_attr( $style ); ?>"
	data-ztp-fs
	data-ztp-style="<?php echo esc_attr( $style ); ?>"
	data-ztp-duration="<?php echo esc_attr( (string) $duration ); ?>"
	data-ztp-cards="<?php echo esc_attr( (string) $cards_count ); ?>"
	data-ztp-projects="<?php echo esc_attr( $json ); ?>"
	role="region"
	aria-roledescription="<?php esc_attr_e( 'portfolio showcase', 'zouetech-portfolio' ); ?>"
	aria-label="<?php esc_attr_e( 'Featured portfolio showcase', 'zouetech-portfolio' ); ?>"
	tabindex="0"
>
	<div class="ztp-fs__featured ztp-fs__featured--dark">
		<div class="ztp-fs__featured-content ztp-fs__featured-content--dark">
			<div class="ztp-fs__copy" data-ztp-copy>
				<?php if ( ! empty( $show['category'] ) ) : ?>
					<p class="ztp-fs__category" data-ztp-category><?php echo esc_html( $featured['category'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $show['title'] ) ) : ?>
					<h2 class="ztp-fs__title" data-ztp-title aria-live="polite"><a href="<?php echo esc_url( $featured['url'] ); ?>"><?php echo esc_html( $featured['title'] ); ?></a></h2>
				<?php endif; ?>
				<?php if ( ! empty( $show['excerpt'] ) ) : ?>
					<p class="ztp-fs__excerpt" data-ztp-excerpt><?php echo esc_html( $featured['excerpt'] ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $show['nav'] ) ) : ?>
				<div class="ztp-fs__nav">
					<button type="button" class="ztp-fs__nav-btn ztp-fs__nav-btn--prev" data-ztp-prev aria-label="<?php echo esc_attr( $labels['back'] ); ?>"><?php echo esc_html( $labels['back'] ); ?></button>
					<button type="button" class="ztp-fs__nav-btn ztp-fs__nav-btn--next" data-ztp-next aria-label="<?php echo esc_attr( $labels['next'] ); ?>"><?php echo esc_html( $labels['next'] ); ?></button>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $show['image'] ) ) : ?>
			<div class="ztp-fs__featured-media ztp-fs__featured-media--dark">
				<img class="ztp-fs__featured-img" src="<?php echo esc_url( $featured['image']['url'] ); ?>" alt="<?php echo esc_attr( $featured['title'] ); ?>" data-ztp-featured-img decoding="async" />
			</div>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $show['gallery'] ) ) : ?>
		<?php $ztp_has_gallery = ! empty( $featured['gallery'] ); ?>
		<div class="ztp-fs__gallery<?php echo $ztp_has_gallery ? '' : ' is-empty'; ?>" data-ztp-gallery <?php echo $ztp_has_gallery ? '' : 'hidden'; ?>>
			<button type="button" class="ztp-fs__gallery-arrow ztp-fs__gallery-arrow--prev" data-ztp-gallery-prev aria-label="<?php esc_attr_e( 'Scroll gallery left', 'zouetech-portfolio' ); ?>" hidden><span aria-hidden="true">‹</span></button>
			<div class="ztp-fs__gallery-viewport">
				<div class="ztp-fs__gallery-track" data-ztp-gallery-track>
					<?php foreach ( $featured['gallery'] as $g_i => $g_item ) : ?>
						<button type="button" class="ztp-fs__thumb<?php echo ! empty( $g_item['is_featured'] ) ? ' ztp-fs__thumb--featured' : ''; ?><?php echo 0 === (int) $g_i ? ' is-active' : ''; ?>" data-ztp-thumb data-index="<?php echo esc_attr( (string) $g_i ); ?>" data-url="<?php echo esc_url( $g_item['url'] ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Gallery image %d', 'zouetech-portfolio' ), $g_i + 1 ) ); ?>" <?php echo 0 === (int) $g_i ? 'aria-current="true"' : ''; ?>>
							<img src="<?php echo esc_url( $g_item['thumb'] ); ?>" alt="" loading="<?php echo ( $g_i < 2 ) ? 'eager' : 'lazy'; ?>" decoding="async" />
						</button>
					<?php endforeach; ?>
				</div>
			</div>
			<button type="button" class="ztp-fs__gallery-arrow ztp-fs__gallery-arrow--next" data-ztp-gallery-next aria-label="<?php esc_attr_e( 'Scroll gallery right', 'zouetech-portfolio' ); ?>" hidden><span aria-hidden="true">›</span></button>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $show['cards'] ) && ! empty( $cards ) ) : ?>
		<div class="ztp-fs__cards ztp-fs__cards--compact" data-ztp-cards>
			<?php foreach ( $cards as $card ) : ?>
				<article class="ztp-fs__card" data-ztp-card data-id="<?php echo esc_attr( (string) $card['id'] ); ?>">
					<a class="ztp-fs__card-media" href="<?php echo esc_url( $card['url'] ); ?>">
						<img src="<?php echo esc_url( $card['image']['url'] ); ?>" alt="<?php echo esc_attr( $card['title'] ); ?>" loading="lazy" decoding="async" data-ztp-card-img />
					</a>
					<div class="ztp-fs__card-body">
						<a class="ztp-fs__card-title" href="<?php echo esc_url( $card['url'] ); ?>"><?php echo esc_html( $card['title'] ); ?></a>
						<?php if ( ! empty( $card['category'] ) ) : ?>
							<?php if ( ! empty( $card['category_url'] ) ) : ?>
								<a class="ztp-fs__card-category" href="<?php echo esc_url( $card['category_url'] ); ?>"><?php echo esc_html( $card['category'] ); ?></a>
							<?php else : ?>
								<span class="ztp-fs__card-category"><?php echo esc_html( $card['category'] ); ?></span>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
