<?php
/**
 * Project Overview meta section.
 *
 * @package Zouetech_Portfolio
 * @var WP_Post $post
 */

defined( 'ABSPATH' ) || exit;

$ztp_overview = Zouetech_Portfolio_Helpers::get_meta( $post->ID, '_ztp_overview', '' );

wp_editor(
	$ztp_overview,
	'ztp_overview_editor',
	array(
		'textarea_name' => '_ztp_overview',
		'textarea_rows' => 10,
		'media_buttons' => true,
		'teeny'         => false,
		'quicktags'     => true,
	)
);
?>
<p class="ztp-field__help"><?php esc_html_e( 'Used by Elementor Dynamic Tags as Project Overview (separate from the main editor).', 'zouetech-portfolio' ); ?></p>
