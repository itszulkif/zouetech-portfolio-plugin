<?php
/**
 * Key Features repeater meta section.
 *
 * @package Zouetech_Portfolio
 * @var WP_Post $post
 */

defined( 'ABSPATH' ) || exit;

$ztp_features = Zouetech_Portfolio_Helpers::get_repeater( $post->ID, '_ztp_features' );
if ( empty( $ztp_features ) ) {
	$ztp_features = array( array( 'title' => '' ) );
}
?>
<div class="ztp-repeater" data-ztp-repeater="features">
	<div class="ztp-repeater__rows" data-ztp-repeater-rows>
		<?php foreach ( $ztp_features as $ztp_i => $ztp_row ) : ?>
			<div class="ztp-repeater__row" data-ztp-row>
				<span class="ztp-repeater__handle dashicons dashicons-menu" aria-hidden="true"></span>
				<input
					type="text"
					class="ztp-field__input"
					name="_ztp_features_rows[<?php echo esc_attr( (string) $ztp_i ); ?>][title]"
					value="<?php echo esc_attr( isset( $ztp_row['title'] ) ? $ztp_row['title'] : '' ); ?>"
					placeholder="<?php esc_attr_e( 'Feature title (e.g. Fast Performance)', 'zouetech-portfolio' ); ?>"
				/>
				<button type="button" class="button-link-delete ztp-repeater__remove" aria-label="<?php esc_attr_e( 'Remove feature', 'zouetech-portfolio' ); ?>">&times;</button>
			</div>
		<?php endforeach; ?>
	</div>
	<button type="button" class="button ztp-repeater__add" data-ztp-repeater-add><?php esc_html_e( 'Add Feature', 'zouetech-portfolio' ); ?></button>
	<template data-ztp-repeater-template>
		<div class="ztp-repeater__row" data-ztp-row>
			<span class="ztp-repeater__handle dashicons dashicons-menu" aria-hidden="true"></span>
			<input type="text" class="ztp-field__input" name="_ztp_features_rows[__INDEX__][title]" value="" placeholder="<?php esc_attr_e( 'Feature title', 'zouetech-portfolio' ); ?>" />
			<button type="button" class="button-link-delete ztp-repeater__remove" aria-label="<?php esc_attr_e( 'Remove feature', 'zouetech-portfolio' ); ?>">&times;</button>
		</div>
	</template>
</div>
