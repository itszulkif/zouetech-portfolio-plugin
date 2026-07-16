<?php
/**
 * Portfolio Tags taxonomy.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Tag
 */
class Zouetech_Portfolio_Tag {

	/**
	 * Taxonomy slug.
	 *
	 * @var string
	 */
	const TAXONOMY = 'portfolio-tag';

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
	 * Register the taxonomy.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register() {
		$labels = array(
			'name'                       => _x( 'Portfolio Tags', 'Taxonomy general name', 'zouetech-portfolio' ),
			'singular_name'              => _x( 'Portfolio Tag', 'Taxonomy singular name', 'zouetech-portfolio' ),
			'search_items'               => __( 'Search Tags', 'zouetech-portfolio' ),
			'popular_items'              => __( 'Popular Tags', 'zouetech-portfolio' ),
			'all_items'                  => __( 'All Tags', 'zouetech-portfolio' ),
			'edit_item'                  => __( 'Edit Tag', 'zouetech-portfolio' ),
			'update_item'                => __( 'Update Tag', 'zouetech-portfolio' ),
			'add_new_item'               => __( 'Add New Tag', 'zouetech-portfolio' ),
			'new_item_name'              => __( 'New Tag Name', 'zouetech-portfolio' ),
			'separate_items_with_commas' => __( 'Separate tags with commas', 'zouetech-portfolio' ),
			'add_or_remove_items'        => __( 'Add or remove tags', 'zouetech-portfolio' ),
			'choose_from_most_used'      => __( 'Choose from the most used tags', 'zouetech-portfolio' ),
			'not_found'                  => __( 'No tags found.', 'zouetech-portfolio' ),
			'menu_name'                  => __( 'Tags', 'zouetech-portfolio' ),
			'back_to_items'              => __( '← Back to Tags', 'zouetech-portfolio' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
			'show_in_rest'      => true,
			'rest_base'         => 'portfolio-tag',
			'rewrite'           => array(
				'slug'       => 'portfolio-tag',
				'with_front' => false,
			),
		);

		register_taxonomy( self::TAXONOMY, array( Zouetech_Portfolio_CPT::POST_TYPE ), $args );
	}
}
