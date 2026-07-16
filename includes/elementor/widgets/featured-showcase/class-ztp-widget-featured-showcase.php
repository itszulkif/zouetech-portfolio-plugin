<?php
/**
 * Elementor widget: Featured Portfolio Showcase.
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Widget_Featured_Showcase
 */
class Zouetech_Portfolio_Widget_Featured_Showcase extends \Elementor\Widget_Base {

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_name() {
		return 'ztp-featured-portfolio-showcase';
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_title() {
		return __( 'Featured Portfolio Showcase', 'zouetech-portfolio' );
	}

	/**
	 * @since 1.0.0
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-gallery-group';
	}

	/**
	 * @since 1.0.0
	 * @return array<int, string>
	 */
	public function get_categories() {
		return array( 'zouetech-portfolio', 'general' );
	}

	/**
	 * @since 1.0.0
	 * @return array<int, string>
	 */
	public function get_keywords() {
		return array( 'portfolio', 'showcase', 'featured', 'zouetech', 'gallery' );
	}

	/**
	 * @since 1.0.0
	 * @return array<int, string>
	 */
	public function get_style_depends() {
		// Load base + all card-style sheets here (not in render) so Elementor
		// preview and frontend print them in <head> before content.
		$deps = array( 'ztp-featured-showcase' );

		foreach ( array_keys( Zouetech_Portfolio_Featured_Showcase_Styles::get_options() ) as $slug ) {
			$handle = Zouetech_Portfolio_Featured_Showcase_Styles::get_style_handle( $slug );
			if ( wp_style_is( $handle, 'registered' ) ) {
				$deps[] = $handle;
			}
		}

		return $deps;
	}

	/**
	 * @since 1.0.0
	 * @return array<int, string>
	 */
	public function get_script_depends() {
		$deps = array( 'ztp-featured-showcase' );

		foreach ( array_keys( Zouetech_Portfolio_Featured_Showcase_Styles::get_options() ) as $slug ) {
			$handle = Zouetech_Portfolio_Featured_Showcase_Styles::get_script_handle( $slug );
			if ( wp_script_is( $handle, 'registered' ) ) {
				$deps[] = $handle;
			}
		}

		return $deps;
	}

	/**
	 * Enqueue optional per-style JS for the selected card style.
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings Widget settings.
	 * @return void
	 */
	private function enqueue_selected_style_assets( array $settings ) {
		$slug = Zouetech_Portfolio_Featured_Showcase_Styles::sanitize(
			isset( $settings['showcase_style'] ) ? $settings['showcase_style'] : Zouetech_Portfolio_Featured_Showcase_Styles::DEFAULT
		);

		$script_handle = Zouetech_Portfolio_Featured_Showcase_Styles::get_script_handle( $slug );
		if ( wp_script_is( $script_handle, 'registered' ) ) {
			wp_enqueue_script( $script_handle );
		}
	}

	/**
	 * Register assets once.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		$css = ZTP_PLUGIN_DIR . 'assets/elementor/featured-showcase/css/ztp-featured-showcase.css';
		$js  = ZTP_PLUGIN_DIR . 'assets/elementor/featured-showcase/js/ztp-featured-showcase.js';

		wp_register_style(
			'ztp-featured-showcase',
			ZTP_PLUGIN_URL . 'assets/elementor/featured-showcase/css/ztp-featured-showcase.css',
			array(),
			file_exists( $css ) ? (string) filemtime( $css ) : ZTP_VERSION
		);

		wp_register_script(
			'ztp-featured-showcase',
			ZTP_PLUGIN_URL . 'assets/elementor/featured-showcase/js/ztp-featured-showcase.js',
			array(),
			file_exists( $js ) ? (string) filemtime( $js ) : ZTP_VERSION,
			true
		);

		Zouetech_Portfolio_Featured_Showcase_Styles::register_assets();
	}

	/**
	 * Public post type choices.
	 *
	 * @since 1.0.0
	 * @return array<string, string>
	 */
	private function get_post_type_options() {
		$options = array();
		$types   = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);

		$skip = array( 'attachment', 'elementor_library', 'e-landing-page' );

		foreach ( $types as $type ) {
			if ( in_array( $type->name, $skip, true ) ) {
				continue;
			}
			$options[ $type->name ] = $type->labels->singular_name ? $type->labels->singular_name : $type->label;
		}

		// Ensure portfolio is first.
		if ( isset( $options[ Zouetech_Portfolio_CPT::POST_TYPE ] ) ) {
			$portfolio = array( Zouetech_Portfolio_CPT::POST_TYPE => $options[ Zouetech_Portfolio_CPT::POST_TYPE ] );
			unset( $options[ Zouetech_Portfolio_CPT::POST_TYPE ] );
			$options = $portfolio + $options;
		}

		return $options;
	}

	/**
	 * Taxonomy choices for source post types.
	 *
	 * @since 1.0.0
	 * @return array<string, string>
	 */
	private function get_taxonomy_options() {
		$options = array(
			'' => __( '— Auto (based on post type) —', 'zouetech-portfolio' ),
		);

		$taxonomies = get_taxonomies(
			array(
				'public'  => true,
				'show_ui' => true,
			),
			'objects'
		);

		foreach ( $taxonomies as $tax ) {
			$options[ $tax->name ] = $tax->labels->singular_name ? $tax->labels->singular_name : $tax->label;
		}

		return $options;
	}

	/**
	 * Term choices across public taxonomies (value: taxonomy:term_id).
	 *
	 * @since 1.0.0
	 * @return array<string, string>
	 */
	private function get_term_options( $taxonomy = '' ) {
		$options = array( '0' => __( 'All Terms', 'zouetech-portfolio' ) );

		$tax_slugs = array();
		if ( $taxonomy && taxonomy_exists( $taxonomy ) ) {
			$tax_slugs[] = $taxonomy;
		} else {
			$tax_slugs = array(
				Zouetech_Portfolio_Category::TAXONOMY,
				'category',
				Zouetech_Portfolio_Tag::TAXONOMY,
				'post_tag',
			);
			$extra = get_taxonomies( array( 'public' => true, 'show_ui' => true ), 'names' );
			foreach ( (array) $extra as $slug ) {
				if ( ! in_array( $slug, $tax_slugs, true ) ) {
					$tax_slugs[] = $slug;
				}
			}
		}

		foreach ( $tax_slugs as $tax_slug ) {
			if ( ! taxonomy_exists( $tax_slug ) ) {
				continue;
			}
			$tax_obj = get_taxonomy( $tax_slug );
			$label   = $tax_obj && ! empty( $tax_obj->labels->singular_name )
				? $tax_obj->labels->singular_name
				: $tax_slug;

			$terms = get_terms(
				array(
					'taxonomy'   => $tax_slug,
					'hide_empty' => false,
					'number'     => 200,
				)
			);

			if ( ! is_array( $terms ) || is_wp_error( $terms ) ) {
				continue;
			}

			foreach ( $terms as $term ) {
				$key             = $tax_slug . ':' . $term->term_id;
				$options[ $key ] = $label . ': ' . $term->name;
			}
		}

		return $options;
	}

	/**
	 * @deprecated Use get_term_options().
	 * @since 1.0.0
	 * @return array<int|string, string>
	 */
	private function get_category_options() {
		return $this->get_term_options( Zouetech_Portfolio_Category::TAXONOMY );
	}

	/**
	 * @since 1.0.0
	 * @return void
	 */
	protected function register_controls() {
		$this->register_content_controls();
		$this->register_style_controls();
	}

	/**
	 * Content tab controls.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function register_content_controls() {
		$this->start_controls_section(
			'section_showcase_style',
			array(
				'label' => __( 'Card Style', 'zouetech-portfolio' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'showcase_style',
			array(
				'label'       => __( 'Select Card Style', 'zouetech-portfolio' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => Zouetech_Portfolio_Featured_Showcase_Styles::DEFAULT,
				'options'     => Zouetech_Portfolio_Featured_Showcase_Styles::get_options(),
				'description' => __( 'Style 1 uses the current featured showcase layout. Styles 2 to 5 are placeholder structures ready for customization.', 'zouetech-portfolio' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_query',
			array(
				'label' => __( 'Query', 'zouetech-portfolio' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'source_post_type',
			array(
				'label'       => __( 'Post Type', 'zouetech-portfolio' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => $this->get_post_type_options(),
				'default'     => Zouetech_Portfolio_CPT::POST_TYPE,
				'description' => __( 'Choose which post type to show in the showcase.', 'zouetech-portfolio' ),
			)
		);

		$this->add_control(
			'source_taxonomy',
			array(
				'label'       => __( 'Taxonomy', 'zouetech-portfolio' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => $this->get_taxonomy_options(),
				'default'     => '',
				'description' => __( 'Leave Auto to use Portfolio Category for portfolio, or Categories for posts.', 'zouetech-portfolio' ),
			)
		);

		$this->add_control(
			'source_term',
			array(
				'label'       => __( 'Term / Category', 'zouetech-portfolio' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => $this->get_term_options(),
				'default'     => '0',
				'description' => __( 'Filter by any term. Format shows Taxonomy: Term name.', 'zouetech-portfolio' ),
			)
		);

		$this->add_control(
			'source_term_id',
			array(
				'label'       => __( 'Term ID (optional)', 'zouetech-portfolio' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'description' => __( 'Overrides Term dropdown when greater than 0. Find ID under Posts → Categories (or Taxonomy) edit URL.', 'zouetech-portfolio' ),
			)
		);

		// Backward compatible control (hidden from UI via condition never true if we keep it migrating).
		$this->add_control(
			'portfolio_category',
			array(
				'type'    => \Elementor\Controls_Manager::HIDDEN,
				'default' => '0',
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'Order By', 'zouetech-portfolio' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'date',
				'options' => array(
					'date'       => __( 'Date', 'zouetech-portfolio' ),
					'title'      => __( 'Title', 'zouetech-portfolio' ),
					'menu_order' => __( 'Menu Order', 'zouetech-portfolio' ),
					'modified'   => __( 'Modified', 'zouetech-portfolio' ),
					'rand'       => __( 'Random', 'zouetech-portfolio' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => __( 'Order', 'zouetech-portfolio' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => array(
					'DESC' => __( 'Descending', 'zouetech-portfolio' ),
					'ASC'  => __( 'Ascending', 'zouetech-portfolio' ),
				),
			)
		);

		$this->add_control(
			'posts_per_page',
			array(
				'label'   => __( 'Total Posts', 'zouetech-portfolio' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 8,
				'min'     => 1,
				'max'     => 50,
			)
		);

		$this->add_control(
			'exclude_current',
			array(
				'label'        => __( 'Exclude Current Post', 'zouetech-portfolio' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_display',
			array(
				'label' => __( 'Display', 'zouetech-portfolio' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$switcher = array(
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'label_on'     => __( 'Yes', 'zouetech-portfolio' ),
			'label_off'    => __( 'No', 'zouetech-portfolio' ),
			'return_value' => 'yes',
			'default'      => 'yes',
		);

		$this->add_control(
			'show_featured_image',
			array_merge(
				$switcher,
				array( 'label' => __( 'Show Featured Image', 'zouetech-portfolio' ) )
			)
		);

		$this->add_control(
			'show_gallery',
			array_merge(
				$switcher,
				array(
					'label'     => __( 'Show Gallery', 'zouetech-portfolio' ),
					'condition' => array( 'showcase_style' => 'card-style-1' ),
				)
			)
		);

		$this->add_control(
			'show_category',
			array_merge(
				$switcher,
				array( 'label' => __( 'Show Category', 'zouetech-portfolio' ) )
			)
		);

		$this->add_control(
			'show_title',
			array_merge(
				$switcher,
				array( 'label' => __( 'Show Title', 'zouetech-portfolio' ) )
			)
		);

		$this->add_control(
			'show_excerpt',
			array_merge(
				$switcher,
				array( 'label' => __( 'Show Short Description', 'zouetech-portfolio' ) )
			)
		);

		$this->add_control(
			's2_excerpt_length',
			array(
				'label'       => __( 'Excerpt Length (words)', 'zouetech-portfolio' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 20,
				'min'         => 5,
				'max'         => 100,
				'condition'   => array(
					'showcase_style' => 'card-style-2',
					'show_excerpt'   => 'yes',
				),
			)
		);

		$this->add_control(
			's2_apply_excerpt_to_custom',
			array_merge(
				$switcher,
				array(
					'label'       => __( 'Apply Length to Custom Excerpt', 'zouetech-portfolio' ),
					'description' => __( 'If Yes, also trim manually written excerpts to the length above.', 'zouetech-portfolio' ),
					'default'     => '',
					'condition'   => array(
						'showcase_style' => 'card-style-2',
						'show_excerpt'   => 'yes',
					),
				)
			)
		);

		$this->add_control(
			'show_view_details',
			array_merge(
				$switcher,
				array(
					'label'     => __( 'Show View Details Button', 'zouetech-portfolio' ),
					'default'   => 'yes',
					'condition' => array( 'showcase_style' => 'card-style-2' ),
				)
			)
		);

		$this->add_control(
			'view_details_label',
			array(
				'label'       => __( 'View Details Label', 'zouetech-portfolio' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => __( 'View Details', 'zouetech-portfolio' ),
				'placeholder' => __( 'View Details', 'zouetech-portfolio' ),
				'condition'   => array(
					'showcase_style'    => 'card-style-2',
					'show_view_details' => 'yes',
				),
			)
		);

		$this->add_control(
			's2_align_button',
			array_merge(
				$switcher,
				array(
					'label'       => __( 'Auto Align Buttons', 'zouetech-portfolio' ),
					'description' => __( 'Keeps View Details buttons aligned at the bottom of every card.', 'zouetech-portfolio' ),
					'default'     => 'yes',
					'condition'   => array(
						'showcase_style'    => 'card-style-2',
						'show_view_details' => 'yes',
					),
				)
			)
		);

		$this->add_control(
			's2_pagination_type',
			array(
				'label'     => __( 'Pagination Type', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => array(
					'none'      => __( 'None', 'zouetech-portfolio' ),
					'numbers'   => __( 'Numbers', 'zouetech-portfolio' ),
					'load_more' => __( 'Load More (Click)', 'zouetech-portfolio' ),
					'infinite'  => __( 'Infinite Scroll', 'zouetech-portfolio' ),
				),
				'condition' => array( 'showcase_style' => 'card-style-2' ),
			)
		);

		$this->add_control(
			's2_load_more_label',
			array(
				'label'     => __( 'Load More Label', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'Load More', 'zouetech-portfolio' ),
				'condition' => array(
					'showcase_style'     => 'card-style-2',
					's2_pagination_type' => 'load_more',
				),
			)
		);

		$this->add_control(
			'show_nav',
			array_merge(
				$switcher,
				array(
					'label'     => __( 'Show Navigation Buttons', 'zouetech-portfolio' ),
					'condition' => array( 'showcase_style' => 'card-style-1' ),
				)
			)
		);

		$this->add_control(
			'show_bottom_cards',
			array_merge(
				$switcher,
				array(
					'label'     => __( 'Show Bottom Cards', 'zouetech-portfolio' ),
					'condition' => array( 'showcase_style' => 'card-style-1' ),
				)
			)
		);

		$this->add_control(
			'gallery_count',
			array(
				'label'       => __( 'Gallery Thumbnail Count', 'zouetech-portfolio' ),
				'description' => __( 'Use 0 to show all gallery images.', 'zouetech-portfolio' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'max'         => 40,
				'condition'   => array(
					'showcase_style' => 'card-style-1',
					'show_gallery'   => 'yes',
				),
			)
		);

		$this->add_control(
			'image_size',
			array(
				'label'   => __( 'Image Size', 'zouetech-portfolio' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'large',
				'options' => array(
					'thumbnail' => __( 'Thumbnail', 'zouetech-portfolio' ),
					'medium'    => __( 'Medium', 'zouetech-portfolio' ),
					'large'     => __( 'Large', 'zouetech-portfolio' ),
					'full'      => __( 'Full', 'zouetech-portfolio' ),
				),
			)
		);

		$this->add_control(
			'back_label',
			array(
				'label'     => __( 'Back Button Label', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( '← Back', 'zouetech-portfolio' ),
				'condition' => array(
					'showcase_style' => 'card-style-1',
					'show_nav'       => 'yes',
				),
			)
		);

		$this->add_control(
			'next_label',
			array(
				'label'     => __( 'Next Button Label', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'Next →', 'zouetech-portfolio' ),
				'condition' => array(
					'showcase_style' => 'card-style-1',
					'show_nav'       => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab controls — isolated per selected card style.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function register_style_controls() {
		$this->register_style1_controls();
		$this->register_style2_controls();
	}

	/**
	 * Style 1 only: layout, typography, colors, navigation.
	 *
	 * @since 1.0.1
	 * @return void
	 */
	private function register_style1_controls() {
		$is_s1 = array( 'showcase_style' => 'card-style-1' );

		$this->start_controls_section(
			'section_s1_layout',
			array(
				'label'     => __( 'Style 1 — Layout', 'zouetech-portfolio' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => $is_s1,
			)
		);

		$this->add_control(
			's1_columns_desktop',
			array(
				'label'     => __( 'Desktop Columns (Cards)', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 3,
				'min'       => 1,
				'max'       => 4,
				'selectors' => array(
					'{{WRAPPER}} .ztp-fs--card-style-1' => '--ztp-fs-cols-d: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			's1_columns_tablet',
			array(
				'label'     => __( 'Tablet Columns', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 2,
				'min'       => 1,
				'max'       => 3,
				'selectors' => array(
					'{{WRAPPER}} .ztp-fs--card-style-1' => '--ztp-fs-cols-t: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			's1_columns_mobile',
			array(
				'label'     => __( 'Mobile Columns', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 1,
				'min'       => 1,
				'max'       => 2,
				'selectors' => array(
					'{{WRAPPER}} .ztp-fs--card-style-1' => '--ztp-fs-cols-m: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			's1_featured_image_height',
			array(
				'label'      => __( 'Featured Image Height', 'zouetech-portfolio' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array( 'min' => 200, 'max' => 800 ),
					'vh' => array( 'min' => 20, 'max' => 80 ),
				),
				'default'    => array( 'unit' => 'px', 'size' => 420 ),
				'selectors'  => array(
					'{{WRAPPER}} .ztp-fs--card-style-1 .ztp-fs__featured-media' => 'height: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			's1_card_image_height',
			array(
				'label'      => __( 'Card Image Height', 'zouetech-portfolio' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 100, 'max' => 400 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 200 ),
				'selectors'  => array(
					'{{WRAPPER}} .ztp-fs--card-style-1 .ztp-fs__card-media' => 'height: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_control(
			's1_border_radius',
			array(
				'label'      => __( 'Border Radius', 'zouetech-portfolio' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 18 ),
				'selectors'  => array(
					'{{WRAPPER}} .ztp-fs--card-style-1'                 => '--ztp-fs-radius: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ztp-fs--card-style-1 .ztp-fs__featured-media' => 'border-radius: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ztp-fs--card-style-1 .ztp-fs__card-media'     => 'border-radius: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ztp-fs--card-style-1 .ztp-fs__thumb'          => 'border-radius: calc({{SIZE}}{{UNIT}} * 0.55);',
				),
			)
		);

		$this->add_control(
			's1_animation_duration',
			array(
				'label'     => __( 'Animation Duration (ms)', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'default'   => 500,
				'min'       => 200,
				'max'       => 1200,
				'selectors' => array(
					'{{WRAPPER}} .ztp-fs--card-style-1' => '--ztp-fs-duration: {{VALUE}}ms;',
				),
			)
		);

		$this->add_responsive_control(
			's1_section_gap',
			array(
				'label'      => __( 'Section Spacing', 'zouetech-portfolio' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 16, 'max' => 80 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 40 ),
				'selectors'  => array(
					'{{WRAPPER}} .ztp-fs--card-style-1' => '--ztp-fs-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_s1_typo',
			array(
				'label'     => __( 'Style 1 — Typography & Colors', 'zouetech-portfolio' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => $is_s1,
			)
		);

		$this->add_control(
			's1_title_color',
			array(
				'label'     => __( 'Title Color', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#111111',
				'selectors' => array(
					'{{WRAPPER}} .ztp-fs--card-style-1 .ztp-fs__title'      => 'color: {{VALUE}};',
					'{{WRAPPER}} .ztp-fs--card-style-1 .ztp-fs__card-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			's1_meta_color',
			array(
				'label'     => __( 'Category Color', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#8a8a8a',
				'selectors' => array(
					'{{WRAPPER}} .ztp-fs--card-style-1 .ztp-fs__category'      => 'color: {{VALUE}};',
					'{{WRAPPER}} .ztp-fs--card-style-1 .ztp-fs__card-category' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			's1_excerpt_color',
			array(
				'label'     => __( 'Description Color', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#4a4a4a',
				'selectors' => array(
					'{{WRAPPER}} .ztp-fs--card-style-1 .ztp-fs__excerpt' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 's1_title_typo',
				'label'    => __( 'Title Typography', 'zouetech-portfolio' ),
				'selector' => '{{WRAPPER}} .ztp-fs--card-style-1 .ztp-fs__title, {{WRAPPER}} .ztp-fs--card-style-1 .ztp-fs__card-title',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 's1_excerpt_typo',
				'label'    => __( 'Description Typography', 'zouetech-portfolio' ),
				'selector' => '{{WRAPPER}} .ztp-fs--card-style-1 .ztp-fs__excerpt',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_s1_nav',
			array(
				'label'     => __( 'Style 1 — Navigation Buttons', 'zouetech-portfolio' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => $is_s1,
			)
		);

		$this->add_control(
			's1_nav_bg',
			array(
				'label'     => __( 'Background', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#111111',
				'selectors' => array(
					'{{WRAPPER}} .ztp-fs--card-style-1 .ztp-fs__nav-btn' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			's1_nav_color',
			array(
				'label'     => __( 'Text Color', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .ztp-fs--card-style-1 .ztp-fs__nav-btn' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			's1_nav_radius',
			array(
				'label'      => __( 'Button Radius', 'zouetech-portfolio' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 999 ),
				'selectors'  => array(
					'{{WRAPPER}} .ztp-fs--card-style-1 .ztp-fs__nav-btn' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 's1_card_shadow',
				'label'    => __( 'Card Hover Shadow', 'zouetech-portfolio' ),
				'selector' => '{{WRAPPER}} .ztp-fs--card-style-1 .ztp-fs__card:hover',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style 2 only: layout, typography, colors, button.
	 *
	 * @since 1.0.1
	 * @return void
	 */
	private function register_style2_controls() {
		$is_s2 = array( 'showcase_style' => 'card-style-2' );

		$this->start_controls_section(
			'section_s2_layout',
			array(
				'label'     => __( 'Style 2 — Layout', 'zouetech-portfolio' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => $is_s2,
			)
		);

		$this->add_control(
			's2_columns_desktop',
			array(
				'label'       => __( 'Cards Per Row (Desktop)', 'zouetech-portfolio' ),
				'description' => __( 'How many cards to show in one row on desktop.', 'zouetech-portfolio' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => '3',
				'options'     => array(
					'3' => __( '3 Cards', 'zouetech-portfolio' ),
					'4' => __( '4 Cards', 'zouetech-portfolio' ),
					'5' => __( '5 Cards', 'zouetech-portfolio' ),
				),
				'selectors'   => array(
					'{{WRAPPER}} .ztp-fs--card-style-2' => '--ztp-fs-cols-d: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			's2_columns_tablet',
			array(
				'label'     => __( 'Cards Per Row (Tablet)', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => '2',
				'options'   => array(
					'1' => __( '1 Card', 'zouetech-portfolio' ),
					'2' => __( '2 Cards', 'zouetech-portfolio' ),
					'3' => __( '3 Cards', 'zouetech-portfolio' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .ztp-fs--card-style-2' => '--ztp-fs-cols-t: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			's2_columns_mobile',
			array(
				'label'     => __( 'Cards Per Row (Mobile)', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => '1',
				'options'   => array(
					'1' => __( '1 Card', 'zouetech-portfolio' ),
					'2' => __( '2 Cards', 'zouetech-portfolio' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .ztp-fs--card-style-2' => '--ztp-fs-cols-m: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			's2_card_image_height',
			array(
				'label'      => __( 'Card Image Height', 'zouetech-portfolio' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 120, 'max' => 480 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 220 ),
				'selectors'  => array(
					'{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-media-link' => 'height: {{SIZE}}{{UNIT}} !important; aspect-ratio: auto;',
				),
			)
		);

		$this->add_control(
			's2_border_radius',
			array(
				'label'      => __( 'Border Radius', 'zouetech-portfolio' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 20 ),
				'selectors'  => array(
					'{{WRAPPER}} .ztp-fs--card-style-2'                    => '--ztp-fs-radius: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-media--s2' => 'border-radius: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-body--s2'  => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			's2_grid_gap',
			array(
				'label'      => __( 'Grid Gap', 'zouetech-portfolio' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 12, 'max' => 60 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 32 ),
				'selectors'  => array(
					'{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__cards--s2' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			's2_card_gap',
			array(
				'label'      => __( 'Image to Content Gap', 'zouetech-portfolio' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 8, 'max' => 40 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 18 ),
				'selectors'  => array(
					'{{WRAPPER}} .ztp-fs--card-style-2' => '--ztp-fs-s2-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_s2_typo',
			array(
				'label'     => __( 'Style 2 — Typography & Colors', 'zouetech-portfolio' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => $is_s2,
			)
		);

		$this->add_control(
			's2_title_color',
			array(
				'label'     => __( 'Title Color', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#111111',
				'selectors' => array(
					'{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-title'   => 'color: {{VALUE}};',
					'{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-title a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			's2_excerpt_color',
			array(
				'label'     => __( 'Description Color', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#666666',
				'selectors' => array(
					'{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-excerpt' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			's2_panel_bg',
			array(
				'label'     => __( 'Content Panel Background', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#f2f2f2',
				'selectors' => array(
					'{{WRAPPER}} .ztp-fs--card-style-2' => '--ztp-fs-s2-body-bg: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			's2_badge_bg',
			array(
				'label'     => __( 'Category Badge Background', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#111111',
				'selectors' => array(
					'{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-badge' => 'background-color: {{VALUE}} !important; background: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			's2_badge_color',
			array(
				'label'     => __( 'Category Badge Text', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-badge'       => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-badge:hover' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 's2_title_typo',
				'label'    => __( 'Title Typography', 'zouetech-portfolio' ),
				'selector' => '{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-title, {{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-title a',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 's2_excerpt_typo',
				'label'    => __( 'Description Typography', 'zouetech-portfolio' ),
				'selector' => '{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-excerpt',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 's2_badge_typo',
				'label'    => __( 'Badge Typography', 'zouetech-portfolio' ),
				'selector' => '{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-badge',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_s2_button',
			array(
				'label'     => __( 'Style 2 — View Details Button', 'zouetech-portfolio' ),
				'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
				'condition' => array(
					'showcase_style'    => 'card-style-2',
					'show_view_details' => 'yes',
				),
			)
		);

		$this->add_control(
			's2_btn_bg',
			array(
				'label'     => __( 'Background', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#111111',
				'selectors' => array(
					'{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-btn' => 'background-color: {{VALUE}} !important; background: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			's2_btn_color',
			array(
				'label'     => __( 'Text Color', 'zouetech-portfolio' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-btn'       => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-btn:hover' => 'color: {{VALUE}} !important;',
					'{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-btn:focus' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			's2_btn_radius',
			array(
				'label'      => __( 'Button Radius', 'zouetech-portfolio' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'default'    => array( 'unit' => 'px', 'size' => 999 ),
				'selectors'  => array(
					'{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-btn' => 'border-radius: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 's2_btn_typo',
				'label'    => __( 'Button Typography', 'zouetech-portfolio' ),
				'selector' => '{{WRAPPER}} .ztp-fs--card-style-2 .ztp-fs__card-btn',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Frontend render.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$this->enqueue_selected_style_assets( $settings );

		// Ensure Style 2 query can calculate max pages when pagination is on.
		if ( empty( $settings['paged'] ) ) {
			$settings['paged'] = 1;
		}

		$queryer  = new Zouetech_Portfolio_Featured_Showcase_Query();
		$query    = $queryer->query( $settings );
		$projects = $queryer->normalize( $query, $settings );

		$renderer = new Zouetech_Portfolio_Featured_Showcase_Renderer();
		$renderer->render( $projects, $settings, $query );
	}
}
