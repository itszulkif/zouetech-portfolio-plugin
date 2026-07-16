<?php
/**
 * Basic & Links meta section.
 *
 * @package Zouetech_Portfolio
 * @var WP_Post $post
 */

defined( 'ABSPATH' ) || exit;

$ztp_video  = Zouetech_Portfolio_Helpers::get_meta( $post->ID, '_ztp_video_url', '' );
$ztp_live   = Zouetech_Portfolio_Helpers::get_meta( $post->ID, '_ztp_live_url', '' );
$ztp_github = Zouetech_Portfolio_Helpers::get_meta( $post->ID, '_ztp_github_url', '' );
$ztp_gal    = Zouetech_Portfolio_Helpers::get_meta( $post->ID, '_ztp_gallery', '' );
$ztp_ids    = Zouetech_Portfolio_Helpers::get_gallery_ids( $post->ID );
?>
<div class="ztp-field-grid">
	<div class="ztp-field ztp-field--full">
		<label class="ztp-field__label" for="_ztp_gallery"><?php esc_html_e( 'Project Gallery', 'zouetech-portfolio' ); ?></label>
		<input type="hidden" id="_ztp_gallery" name="_ztp_gallery" value="<?php echo esc_attr( $ztp_gal ); ?>" class="ztp-gallery-ids" />
		<div class="ztp-gallery-preview" data-ztp-gallery-preview>
			<?php foreach ( $ztp_ids as $ztp_att_id ) : ?>
				<?php $ztp_thumb = wp_get_attachment_image_url( $ztp_att_id, 'thumbnail' ); ?>
				<?php if ( $ztp_thumb ) : ?>
					<div class="ztp-gallery-item" data-id="<?php echo esc_attr( (string) $ztp_att_id ); ?>">
						<img src="<?php echo esc_url( $ztp_thumb ); ?>" alt="" />
						<button type="button" class="ztp-gallery-item__remove" aria-label="<?php esc_attr_e( 'Remove image', 'zouetech-portfolio' ); ?>">&times;</button>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<p class="ztp-field__actions">
			<button type="button" class="button ztp-gallery-add"><?php esc_html_e( 'Add Images', 'zouetech-portfolio' ); ?></button>
			<button type="button" class="button-link-delete ztp-gallery-clear"><?php esc_html_e( 'Clear Gallery', 'zouetech-portfolio' ); ?></button>
		</p>
	</div>

	<div class="ztp-field">
		<label class="ztp-field__label" for="_ztp_live_url"><?php esc_html_e( 'Live Preview URL', 'zouetech-portfolio' ); ?></label>
		<input class="ztp-field__input" type="url" id="_ztp_live_url" name="_ztp_live_url" value="<?php echo esc_attr( $ztp_live ); ?>" placeholder="https://" />
	</div>
	<div class="ztp-field">
		<label class="ztp-field__label" for="_ztp_github_url"><?php esc_html_e( 'GitHub URL', 'zouetech-portfolio' ); ?></label>
		<input class="ztp-field__input" type="url" id="_ztp_github_url" name="_ztp_github_url" value="<?php echo esc_attr( $ztp_github ); ?>" placeholder="https://github.com/" />
	</div>
	<div class="ztp-field ztp-field--full">
		<label class="ztp-field__label" for="_ztp_video_url"><?php esc_html_e( 'Project Video URL', 'zouetech-portfolio' ); ?></label>
		<input class="ztp-field__input" type="url" id="_ztp_video_url" name="_ztp_video_url" value="<?php echo esc_attr( $ztp_video ); ?>" placeholder="https://" />
		<p class="ztp-field__help"><?php esc_html_e( 'Optional. YouTube, Vimeo, or direct video URL.', 'zouetech-portfolio' ); ?></p>
	</div>
</div>
<p class="ztp-field__help ztp-field__help--note">
	<?php esc_html_e( 'Title, short description, long description, and featured image use the standard WordPress fields above.', 'zouetech-portfolio' ); ?>
</p>
