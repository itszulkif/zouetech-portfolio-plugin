<?php
/**
 * Client Testimonial meta section.
 *
 * @package Zouetech_Portfolio
 * @var WP_Post $post
 */

defined( 'ABSPATH' ) || exit;

$ztp_name    = Zouetech_Portfolio_Helpers::get_meta( $post->ID, '_ztp_testimonial_name', '' );
$ztp_company = Zouetech_Portfolio_Helpers::get_meta( $post->ID, '_ztp_testimonial_company', '' );
$ztp_role    = Zouetech_Portfolio_Helpers::get_meta( $post->ID, '_ztp_testimonial_designation', '' );
$ztp_photo   = absint( Zouetech_Portfolio_Helpers::get_meta( $post->ID, '_ztp_testimonial_photo', 0 ) );
$ztp_review  = Zouetech_Portfolio_Helpers::get_meta( $post->ID, '_ztp_testimonial_review', '' );
$ztp_rating  = absint( Zouetech_Portfolio_Helpers::get_meta( $post->ID, '_ztp_testimonial_rating', 0 ) );
$ztp_url     = $ztp_photo ? wp_get_attachment_image_url( $ztp_photo, 'thumbnail' ) : '';
?>
<div class="ztp-field-grid">
	<div class="ztp-field ztp-field--full">
		<label class="ztp-field__label"><?php esc_html_e( 'Photo', 'zouetech-portfolio' ); ?></label>
		<div class="ztp-image-field" data-ztp-image-field>
			<input type="hidden" id="_ztp_testimonial_photo" name="_ztp_testimonial_photo" value="<?php echo esc_attr( (string) $ztp_photo ); ?>" class="ztp-image-field__id" />
			<div class="ztp-image-field__preview">
				<?php if ( $ztp_url ) : ?>
					<img src="<?php echo esc_url( $ztp_url ); ?>" alt="" />
				<?php else : ?>
					<span class="dashicons dashicons-admin-users"></span>
				<?php endif; ?>
			</div>
			<p class="ztp-field__actions">
				<button type="button" class="button ztp-image-field__pick"><?php esc_html_e( 'Select Photo', 'zouetech-portfolio' ); ?></button>
				<button type="button" class="button-link-delete ztp-image-field__clear"><?php esc_html_e( 'Remove', 'zouetech-portfolio' ); ?></button>
			</p>
		</div>
	</div>

	<div class="ztp-field">
		<label class="ztp-field__label" for="_ztp_testimonial_name"><?php esc_html_e( 'Client Name', 'zouetech-portfolio' ); ?></label>
		<input class="ztp-field__input" type="text" id="_ztp_testimonial_name" name="_ztp_testimonial_name" value="<?php echo esc_attr( $ztp_name ); ?>" />
	</div>
	<div class="ztp-field">
		<label class="ztp-field__label" for="_ztp_testimonial_company"><?php esc_html_e( 'Company', 'zouetech-portfolio' ); ?></label>
		<input class="ztp-field__input" type="text" id="_ztp_testimonial_company" name="_ztp_testimonial_company" value="<?php echo esc_attr( $ztp_company ); ?>" />
	</div>
	<div class="ztp-field">
		<label class="ztp-field__label" for="_ztp_testimonial_designation"><?php esc_html_e( 'Designation', 'zouetech-portfolio' ); ?></label>
		<input class="ztp-field__input" type="text" id="_ztp_testimonial_designation" name="_ztp_testimonial_designation" value="<?php echo esc_attr( $ztp_role ); ?>" />
	</div>
	<div class="ztp-field">
		<label class="ztp-field__label" for="_ztp_testimonial_rating"><?php esc_html_e( 'Rating', 'zouetech-portfolio' ); ?></label>
		<select class="ztp-field__input" id="_ztp_testimonial_rating" name="_ztp_testimonial_rating">
			<?php for ( $ztp_i = 0; $ztp_i <= 5; $ztp_i++ ) : ?>
				<option value="<?php echo esc_attr( (string) $ztp_i ); ?>" <?php selected( $ztp_rating, $ztp_i ); ?>>
					<?php
					echo esc_html(
						0 === $ztp_i
							? __( 'None', 'zouetech-portfolio' )
							: sprintf(
								/* translators: %d: star count */
								_n( '%d star', '%d stars', $ztp_i, 'zouetech-portfolio' ),
								$ztp_i
							)
					);
					?>
				</option>
			<?php endfor; ?>
		</select>
	</div>
	<div class="ztp-field ztp-field--full">
		<label class="ztp-field__label" for="_ztp_testimonial_review"><?php esc_html_e( 'Review', 'zouetech-portfolio' ); ?></label>
		<textarea class="ztp-field__input" id="_ztp_testimonial_review" name="_ztp_testimonial_review" rows="5"><?php echo esc_textarea( $ztp_review ); ?></textarea>
	</div>
</div>
