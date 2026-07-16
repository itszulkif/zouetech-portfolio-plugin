<?php
/**
 * Portfolio Projects custom post type.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_CPT
 */
class Zouetech_Portfolio_CPT {

	/**
	 * Post type slug.
	 *
	 * @var string
	 */
	const POST_TYPE = 'portfolio';

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'register' ), 0 );
	}

	/**
	 * Register the custom post type.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register() {
		$labels = array(
			'name'                  => _x( 'Portfolio Projects', 'Post type general name', 'zouetech-portfolio' ),
			'singular_name'         => _x( 'Portfolio Project', 'Post type singular name', 'zouetech-portfolio' ),
			'menu_name'             => _x( 'Portfolio', 'Admin Menu text', 'zouetech-portfolio' ),
			'name_admin_bar'        => _x( 'Portfolio Project', 'Add New on Toolbar', 'zouetech-portfolio' ),
			'add_new'               => __( 'Add New', 'zouetech-portfolio' ),
			'add_new_item'          => __( 'Add New Project', 'zouetech-portfolio' ),
			'new_item'              => __( 'New Project', 'zouetech-portfolio' ),
			'edit_item'             => __( 'Edit Project', 'zouetech-portfolio' ),
			'view_item'             => __( 'View Project', 'zouetech-portfolio' ),
			'all_items'             => __( 'All Projects', 'zouetech-portfolio' ),
			'search_items'          => __( 'Search Projects', 'zouetech-portfolio' ),
			'parent_item_colon'     => __( 'Parent Projects:', 'zouetech-portfolio' ),
			'not_found'             => __( 'No projects found.', 'zouetech-portfolio' ),
			'not_found_in_trash'    => __( 'No projects found in Trash.', 'zouetech-portfolio' ),
			'featured_image'        => _x( 'Project Featured Image', 'Overrides the “Featured Image” phrase', 'zouetech-portfolio' ),
			'set_featured_image'    => _x( 'Set featured image', 'Overrides the “Set featured image” phrase', 'zouetech-portfolio' ),
			'remove_featured_image' => _x( 'Remove featured image', 'Overrides the “Remove featured image” phrase', 'zouetech-portfolio' ),
			'use_featured_image'    => _x( 'Use as featured image', 'Overrides the “Use as featured image” phrase', 'zouetech-portfolio' ),
			'archives'              => _x( 'Project archives', 'The post type archive label', 'zouetech-portfolio' ),
			'insert_into_item'      => _x( 'Insert into project', 'Overrides the “Insert into post” phrase', 'zouetech-portfolio' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this project', 'Overrides the “Uploaded to this post” phrase', 'zouetech-portfolio' ),
			'filter_items_list'     => _x( 'Filter projects list', 'Screen reader text', 'zouetech-portfolio' ),
			'items_list_navigation' => _x( 'Projects list navigation', 'Screen reader text', 'zouetech-portfolio' ),
			'items_list'            => _x( 'Projects list', 'Screen reader text', 'zouetech-portfolio' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => true,
			'rest_base'          => 'portfolio',
			'query_var'          => true,
			'rewrite'            => array(
				'slug'       => 'portfolio',
				'with_front' => false,
			),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 20,
			'menu_icon'          => 'dashicons-portfolio',
			'supports'           => array(
				'title',
				'editor',
				'excerpt',
				'thumbnail',
				'revisions',
				'author',
				'custom-fields',
			),
			'delete_with_user'   => false,
		);

		register_post_type( self::POST_TYPE, $args );
	}
}
