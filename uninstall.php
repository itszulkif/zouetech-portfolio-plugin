<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Leaves portfolio posts and taxonomies intact.
 * Removes plugin options and all `_ztp_*` post meta.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Delete plugin options.
 */
delete_option( 'ztp_version' );
delete_option( 'ztp_activated_at' );

/**
 * Delete all post meta keys that start with _ztp_ for portfolio posts.
 *
 * Uses WP APIs; no custom tables.
 */
$ztp_portfolio_ids = get_posts(
	array(
		'post_type'      => 'portfolio',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	)
);

if ( ! empty( $ztp_portfolio_ids ) ) {
	global $wpdb;

	$meta_keys = array(
		'_ztp_video_url',
		'_ztp_live_url',
		'_ztp_github_url',
		'_ztp_gallery',
		'_ztp_client_name',
		'_ztp_industry',
		'_ztp_project_type',
		'_ztp_services',
		'_ztp_platform',
		'_ztp_duration',
		'_ztp_country',
		'_ztp_location',
		'_ztp_completion_date',
		'_ztp_year',
		'_ztp_project_status',
		'_ztp_overview',
		'_ztp_features',
		'_ztp_technologies',
		'_ztp_testimonial_name',
		'_ztp_testimonial_company',
		'_ztp_testimonial_designation',
		'_ztp_testimonial_photo',
		'_ztp_testimonial_review',
		'_ztp_testimonial_rating',
	);

	foreach ( $ztp_portfolio_ids as $ztp_post_id ) {
		foreach ( $meta_keys as $ztp_meta_key ) {
			delete_post_meta( $ztp_post_id, $ztp_meta_key );
		}
	}

	unset( $wpdb, $meta_keys, $ztp_post_id, $ztp_meta_key );
}

unset( $ztp_portfolio_ids );
