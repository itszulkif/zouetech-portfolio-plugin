<?php
/**
 * Technologies repeater meta section.
 *
 * @package Zouetech_Portfolio
 * @var WP_Post $post
 */

defined( 'ABSPATH' ) || exit;

$ztp_techs = Zouetech_Portfolio_Helpers::get_repeater( $post->ID, '_ztp_technologies' );
if ( empty( $ztp_techs ) ) {
	$ztp_techs = array(
		array(
			'name' => '',
			'icon' => 0,
		),
	);
}
?>
<div class="ztp-repeater" data-ztp-repeater="technologies">
	<div class="ztp-repeater__rows" data-ztp-repeater-rows>
		<?php foreach ( $ztp_techs as $ztp_i => $ztp_row ) : ?>
			<?php
			$ztp_icon_id  = isset( $ztp_row['icon'] ) ? absint( $ztp_row['icon'] ) : 0;
			$ztp_icon_url = $ztp_icon_id ? wp_get_attachment_image_url( $ztp_icon_id, 'thumbnail' ) : '';
			?>
			<div class="ztp-repeater__row ztp-repeater__row--tech" data-ztp-row>
				<span class="ztp-repeater__handle dashicons dashicons-menu" aria-hidden="true"></span>
				<div class="ztp-tech-icon" data-ztp-tech-icon>
					<input type="hidden" name="_ztp_technologies_rows[<?php echo esc_attr( (string) $ztp_i ); ?>][icon]" value="<?php echo esc_attr( (string) $ztp_icon_id ); ?>" class="ztp-tech-icon__id" />
					<div class="ztp-tech-icon__preview">
						<?php if ( $ztp_icon_url ) : ?>
							<img src="<?php echo esc_url( $ztp_icon_url ); ?>" alt="" />
						<?php else : ?>
							<span class="dashicons dashicons-format-image"></span>
						<?php endif; ?>
					</div>
					<button type="button" class="button-link ztp-tech-icon__pick"><?php esc_html_e( 'Icon', 'zouetech-portfolio' ); ?></button>
					<button type="button" class="button-link-delete ztp-tech-icon__clear"><?php esc_html_e( 'Clear', 'zouetech-portfolio' ); ?></button>
				</div>
				<input
					type="text"
					class="ztp-field__input"
					name="_ztp_technologies_rows[<?php echo esc_attr( (string) $ztp_i ); ?>][name]"
					value="<?php echo esc_attr( isset( $ztp_row['name'] ) ? $ztp_row['name'] : '' ); ?>"
					placeholder="<?php esc_attr_e( 'Technology name (e.g. WordPress)', 'zouetech-portfolio' ); ?>"
				/>
				<button type="button" class="button-link-delete ztp-repeater__remove" aria-label="<?php esc_attr_e( 'Remove technology', 'zouetech-portfolio' ); ?>">&times;</button>
			</div>
		<?php endforeach; ?>
	</div>
	<button type="button" class="button ztp-repeater__add" data-ztp-repeater-add><?php esc_html_e( 'Add Technology', 'zouetech-portfolio' ); ?></button>
	<template data-ztp-repeater-template>
		<div class="ztp-repeater__row ztp-repeater__row--tech" data-ztp-row>
			<span class="ztp-repeater__handle dashicons dashicons-menu" aria-hidden="true"></span>
			<div class="ztp-tech-icon" data-ztp-tech-icon>
				<input type="hidden" name="_ztp_technologies_rows[__INDEX__][icon]" value="0" class="ztp-tech-icon__id" />
				<div class="ztp-tech-icon__preview"><span class="dashicons dashicons-format-image"></span></div>
				<button type="button" class="button-link ztp-tech-icon__pick"><?php esc_html_e( 'Icon', 'zouetech-portfolio' ); ?></button>
				<button type="button" class="button-link-delete ztp-tech-icon__clear"><?php esc_html_e( 'Clear', 'zouetech-portfolio' ); ?></button>
			</div>
			<input type="text" class="ztp-field__input" name="_ztp_technologies_rows[__INDEX__][name]" value="" placeholder="<?php esc_attr_e( 'Technology name', 'zouetech-portfolio' ); ?>" />
			<button type="button" class="button-link-delete ztp-repeater__remove" aria-label="<?php esc_attr_e( 'Remove technology', 'zouetech-portfolio' ); ?>">&times;</button>
		</div>
	</template>
</div>
