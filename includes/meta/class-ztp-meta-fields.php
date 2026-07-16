<?php
/**
 * Portfolio meta field definitions (single source of truth).
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Meta_Fields
 */
class Zouetech_Portfolio_Meta_Fields {

	/**
	 * Get project status choices.
	 *
	 * @since 1.0.0
	 * @return array<string, string>
	 */
	public static function get_status_choices() {
		return array(
			'completed'   => __( 'Completed', 'zouetech-portfolio' ),
			'in-progress' => __( 'In Progress', 'zouetech-portfolio' ),
			'on-hold'     => __( 'On Hold', 'zouetech-portfolio' ),
			'archived'    => __( 'Archived', 'zouetech-portfolio' ),
		);
	}

	/**
	 * Get all registerable meta field schemas.
	 *
	 * @since 1.0.0
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_fields() {
		return array(
			'_ztp_video_url'                => array(
				'label'       => __( 'Project Video URL', 'zouetech-portfolio' ),
				'group'       => 'basic',
				'type'        => 'url',
				'data_type'   => 'string',
				'default'     => '',
				'elementor'   => 'url',
				'description' => __( 'Optional video URL (YouTube, Vimeo, etc.).', 'zouetech-portfolio' ),
			),
			'_ztp_live_url'                 => array(
				'label'     => __( 'Live Preview URL', 'zouetech-portfolio' ),
				'group'     => 'basic',
				'type'      => 'url',
				'data_type' => 'string',
				'default'   => '',
				'elementor' => 'url',
			),
			'_ztp_github_url'               => array(
				'label'     => __( 'GitHub URL', 'zouetech-portfolio' ),
				'group'     => 'basic',
				'type'      => 'url',
				'data_type' => 'string',
				'default'   => '',
				'elementor' => 'url',
			),
			'_ztp_gallery'                  => array(
				'label'     => __( 'Project Gallery', 'zouetech-portfolio' ),
				'group'     => 'basic',
				'type'      => 'gallery',
				'data_type' => 'string',
				'default'   => '',
				'elementor' => 'gallery',
			),
			'_ztp_client_name'              => array(
				'label'           => __( 'Client Name', 'zouetech-portfolio' ),
				'elementor_label' => __( 'Project Client Name', 'zouetech-portfolio' ),
				'group'           => 'project_info',
				'type'            => 'text',
				'data_type'       => 'string',
				'default'         => '',
				'elementor'       => 'text',
			),
			'_ztp_industry'                 => array(
				'label'     => __( 'Industry', 'zouetech-portfolio' ),
				'group'     => 'project_info',
				'type'      => 'text',
				'data_type' => 'string',
				'default'   => '',
				'elementor' => 'text',
			),
			'_ztp_project_type'             => array(
				'label'     => __( 'Project Type', 'zouetech-portfolio' ),
				'group'     => 'project_info',
				'type'      => 'text',
				'data_type' => 'string',
				'default'   => '',
				'elementor' => 'text',
			),
			'_ztp_services'                 => array(
				'label'     => __( 'Services', 'zouetech-portfolio' ),
				'group'     => 'project_info',
				'type'      => 'text',
				'data_type' => 'string',
				'default'   => '',
				'elementor' => 'text',
			),
			'_ztp_platform'                 => array(
				'label'     => __( 'Platform', 'zouetech-portfolio' ),
				'group'     => 'project_info',
				'type'      => 'text',
				'data_type' => 'string',
				'default'   => '',
				'elementor' => 'text',
			),
			'_ztp_duration'                 => array(
				'label'     => __( 'Duration', 'zouetech-portfolio' ),
				'group'     => 'project_info',
				'type'      => 'text',
				'data_type' => 'string',
				'default'   => '',
				'elementor' => 'text',
			),
			'_ztp_country'                  => array(
				'label'     => __( 'Country', 'zouetech-portfolio' ),
				'group'     => 'project_info',
				'type'      => 'text',
				'data_type' => 'string',
				'default'   => '',
				'elementor' => 'text',
			),
			'_ztp_location'                 => array(
				'label'     => __( 'Location', 'zouetech-portfolio' ),
				'group'     => 'project_info',
				'type'      => 'text',
				'data_type' => 'string',
				'default'   => '',
				'elementor' => 'text',
			),
			'_ztp_completion_date'          => array(
				'label'     => __( 'Completion Date', 'zouetech-portfolio' ),
				'group'     => 'project_info',
				'type'      => 'date',
				'data_type' => 'string',
				'default'   => '',
				'elementor' => 'text',
			),
			'_ztp_year'                     => array(
				'label'     => __( 'Year', 'zouetech-portfolio' ),
				'group'     => 'project_info',
				'type'      => 'text',
				'data_type' => 'string',
				'default'   => '',
				'elementor' => 'text',
			),
			'_ztp_project_status'           => array(
				'label'     => __( 'Project Status', 'zouetech-portfolio' ),
				'group'     => 'project_info',
				'type'      => 'select',
				'choices'   => self::get_status_choices(),
				'data_type' => 'string',
				'default'   => 'completed',
				'elementor' => 'text',
			),
			'_ztp_overview'                 => array(
				'label'     => __( 'Project Overview', 'zouetech-portfolio' ),
				'group'     => 'overview',
				'type'      => 'wysiwyg',
				'data_type' => 'string',
				'default'   => '',
				'elementor' => 'wysiwyg',
			),
			'_ztp_features'                 => array(
				'label'     => __( 'Key Features', 'zouetech-portfolio' ),
				'group'     => 'features',
				'type'      => 'repeater_features',
				'data_type' => 'string',
				'default'   => '[]',
				'elementor' => 'features',
			),
			'_ztp_technologies'             => array(
				'label'     => __( 'Technologies', 'zouetech-portfolio' ),
				'group'     => 'technologies',
				'type'      => 'repeater_technologies',
				'data_type' => 'string',
				'default'   => '[]',
				'elementor' => 'technologies',
			),
			'_ztp_testimonial_name'         => array(
				'label'           => __( 'Client Name', 'zouetech-portfolio' ),
				'elementor_label' => __( 'Testimonial Client Name', 'zouetech-portfolio' ),
				'group'           => 'testimonial',
				'type'            => 'text',
				'data_type'       => 'string',
				'default'         => '',
				'elementor'       => 'text',
			),
			'_ztp_testimonial_company'      => array(
				'label'           => __( 'Company', 'zouetech-portfolio' ),
				'elementor_label' => __( 'Testimonial Company', 'zouetech-portfolio' ),
				'group'           => 'testimonial',
				'type'            => 'text',
				'data_type'       => 'string',
				'default'         => '',
				'elementor'       => 'text',
			),
			'_ztp_testimonial_designation'  => array(
				'label'           => __( 'Designation', 'zouetech-portfolio' ),
				'elementor_label' => __( 'Testimonial Designation', 'zouetech-portfolio' ),
				'group'           => 'testimonial',
				'type'            => 'text',
				'data_type'       => 'string',
				'default'         => '',
				'elementor'       => 'text',
			),
			'_ztp_testimonial_photo'        => array(
				'label'           => __( 'Photo', 'zouetech-portfolio' ),
				'elementor_label' => __( 'Testimonial Photo', 'zouetech-portfolio' ),
				'group'           => 'testimonial',
				'type'            => 'image',
				'data_type'       => 'integer',
				'default'         => 0,
				'elementor'       => 'image',
			),
			'_ztp_testimonial_review'       => array(
				'label'           => __( 'Review', 'zouetech-portfolio' ),
				'elementor_label' => __( 'Testimonial Review', 'zouetech-portfolio' ),
				'group'           => 'testimonial',
				'type'            => 'textarea',
				'data_type'       => 'string',
				'default'         => '',
				'elementor'       => 'text',
			),
			'_ztp_testimonial_rating'       => array(
				'label'           => __( 'Rating', 'zouetech-portfolio' ),
				'elementor_label' => __( 'Testimonial Rating', 'zouetech-portfolio' ),
				'group'           => 'testimonial',
				'type'            => 'rating',
				'data_type'       => 'integer',
				'default'         => 0,
				'elementor'       => 'text',
			),
		);
	}

	/**
	 * Get admin UI groups (section cards).
	 *
	 * @since 1.0.0
	 * @return array<string, array<string, string>>
	 */
	public static function get_groups() {
		return array(
			'basic'         => array(
				'title' => __( 'Basic & Links', 'zouetech-portfolio' ),
				'icon'  => 'dashicons-admin-links',
				'desc'  => __( 'Gallery and project URLs.', 'zouetech-portfolio' ),
			),
			'project_info'  => array(
				'title' => __( 'Project Information', 'zouetech-portfolio' ),
				'icon'  => 'dashicons-info',
				'desc'  => __( 'Client, industry, timeline, and status.', 'zouetech-portfolio' ),
			),
			'overview'      => array(
				'title' => __( 'Project Overview', 'zouetech-portfolio' ),
				'icon'  => 'dashicons-media-document',
				'desc'  => __( 'Long-form project overview for Elementor Dynamic Tags.', 'zouetech-portfolio' ),
			),
			'features'      => array(
				'title' => __( 'Key Features', 'zouetech-portfolio' ),
				'icon'  => 'dashicons-star-filled',
				'desc'  => __( 'Unlimited feature titles.', 'zouetech-portfolio' ),
			),
			'technologies'  => array(
				'title' => __( 'Technologies', 'zouetech-portfolio' ),
				'icon'  => 'dashicons-hammer',
				'desc'  => __( 'Tools and stacks used on this project.', 'zouetech-portfolio' ),
			),
			'testimonial'   => array(
				'title' => __( 'Client Testimonial', 'zouetech-portfolio' ),
				'icon'  => 'dashicons-format-quote',
				'desc'  => __( 'Optional client feedback.', 'zouetech-portfolio' ),
			),
		);
	}

	/**
	 * Get fields filtered by group.
	 *
	 * @since 1.0.0
	 * @param string $group Group key.
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_fields_by_group( $group ) {
		$fields = array();

		foreach ( self::get_fields() as $key => $field ) {
			if ( isset( $field['group'] ) && $group === $field['group'] ) {
				$fields[ $key ] = $field;
			}
		}

		return $fields;
	}
}
