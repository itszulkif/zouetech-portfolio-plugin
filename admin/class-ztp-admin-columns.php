<?php
/**
 * Portfolio list table columns.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Admin_Columns
 */
class Zouetech_Portfolio_Admin_Columns {

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_hooks() {
		add_filter( 'manage_' . Zouetech_Portfolio_CPT::POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . Zouetech_Portfolio_CPT::POST_TYPE . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_filter( 'manage_edit-' . Zouetech_Portfolio_CPT::POST_TYPE . '_sortable_columns', array( $this, 'sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'orderby_meta' ) );
	}

	/**
	 * Add custom columns.
	 *
	 * @since 1.0.0
	 * @param array<string, string> $columns Existing columns.
	 * @return array<string, string>
	 */
	public function columns( $columns ) {
		$new = array();

		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['ztp_thumbnail'] = __( 'Image', 'zouetech-portfolio' );
				$new['ztp_client']    = __( 'Client', 'zouetech-portfolio' );
				$new['ztp_status']    = __( 'Status', 'zouetech-portfolio' );
				$new['ztp_year']      = __( 'Year', 'zouetech-portfolio' );
			}
		}

		return $new;
	}

	/**
	 * Render custom column content.
	 *
	 * @since 1.0.0
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_column( $column, $post_id ) {
		switch ( $column ) {
			case 'ztp_thumbnail':
				if ( has_post_thumbnail( $post_id ) ) {
					echo get_the_post_thumbnail( $post_id, array( 48, 48 ) );
				} else {
					echo '&mdash;';
				}
				break;

			case 'ztp_client':
				$client = Zouetech_Portfolio_Helpers::get_meta( $post_id, '_ztp_client_name', '' );
				echo $client ? esc_html( $client ) : '&mdash;';
				break;

			case 'ztp_status':
				echo esc_html( Zouetech_Portfolio_Helpers::get_status_label( $post_id ) );
				break;

			case 'ztp_year':
				$year = Zouetech_Portfolio_Helpers::get_meta( $post_id, '_ztp_year', '' );
				echo $year ? esc_html( $year ) : '&mdash;';
				break;
		}
	}

	/**
	 * Mark columns as sortable.
	 *
	 * @since 1.0.0
	 * @param array<string, string> $columns Sortable columns.
	 * @return array<string, string>
	 */
	public function sortable_columns( $columns ) {
		$columns['ztp_client'] = 'ztp_client';
		$columns['ztp_year']   = 'ztp_year';
		return $columns;
	}

	/**
	 * Handle meta orderby for sortable columns.
	 *
	 * @since 1.0.0
	 * @param WP_Query $query Query.
	 * @return void
	 */
	public function orderby_meta( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		if ( 'ztp_client' === $orderby ) {
			$query->set( 'meta_key', '_ztp_client_name' );
			$query->set( 'orderby', 'meta_value' );
		}

		if ( 'ztp_year' === $orderby ) {
			$query->set( 'meta_key', '_ztp_year' );
			$query->set( 'orderby', 'meta_value' );
		}
	}
}
