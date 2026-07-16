<?php
/**
 * Portfolio Categories taxonomy.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Category
 */
class Zouetech_Portfolio_Category {

	/**
	 * Taxonomy slug.
	 *
	 * @var string
	 */
	const TAXONOMY = 'portfolio-category';

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
			'name'                       => _x( 'Portfolio Categories', 'Taxonomy general name', 'zouetech-portfolio' ),
			'singular_name'              => _x( 'Portfolio Category', 'Taxonomy singular name', 'zouetech-portfolio' ),
			'search_items'               => __( 'Search Categories', 'zouetech-portfolio' ),
			'all_items'                  => __( 'All Categories', 'zouetech-portfolio' ),
			'parent_item'                => __( 'Parent Category', 'zouetech-portfolio' ),
			'parent_item_colon'          => __( 'Parent Category:', 'zouetech-portfolio' ),
			'edit_item'                  => __( 'Edit Category', 'zouetech-portfolio' ),
			'update_item'                => __( 'Update Category', 'zouetech-portfolio' ),
			'add_new_item'               => __( 'Add New Category', 'zouetech-portfolio' ),
			'new_item_name'              => __( 'New Category Name', 'zouetech-portfolio' ),
			'menu_name'                  => __( 'Categories', 'zouetech-portfolio' ),
			'popular_items'              => null,
			'separate_items_with_commas' => null,
			'add_or_remove_items'        => null,
			'choose_from_most_used'      => null,
			'not_found'                  => __( 'No categories found.', 'zouetech-portfolio' ),
			'back_to_items'              => __( '← Back to Categories', 'zouetech-portfolio' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
			'rest_base'         => 'portfolio-category',
			'rewrite'           => array(
				'slug'         => 'portfolio-category',
				'with_front'   => false,
				'hierarchical' => true,
			),
		);

		register_taxonomy( self::TAXONOMY, array( Zouetech_Portfolio_CPT::POST_TYPE ), $args );
	}
}
