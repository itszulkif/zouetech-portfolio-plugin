<?php
/**
 * Elementor Dynamic Tags registration (no widgets / no templates).
 *
 * @package Zouetech_Portfolio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Zouetech_Portfolio_Elementor_Dynamic_Tags
 */
class Zouetech_Portfolio_Elementor_Dynamic_Tags {

	/**
	 * Dynamic tag group slug.
	 *
	 * @var string
	 */
	const GROUP = 'zouetech-portfolio';

	/**
	 * Whether tags were already registered this request.
	 *
	 * @var bool
	 */
	private static $registered = false;

	/**
	 * Register hooks when Elementor is available.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'elementor/dynamic_tags/register', array( $this, 'register_tags' ) );
		// Older Elementor / Pro compatibility.
		add_action( 'elementor/dynamic_tags/register_tags', array( $this, 'register_tags' ) );
	}

	/**
	 * Register Dynamic Tags group and tags.
	 *
	 * @since 1.0.0
	 * @param mixed $dynamic_tags_manager Elementor dynamic tags manager.
	 * @return void
	 */
	public function register_tags( $dynamic_tags_manager ) {
		if ( self::$registered ) {
			return;
		}

		if ( ! class_exists( '\Elementor\Core\DynamicTags\Tag' ) ) {
			return;
		}

		self::$registered = true;

		require_once ZTP_PLUGIN_DIR . 'includes/elementor/tags/class-ztp-tag-base.php';
		require_once ZTP_PLUGIN_DIR . 'includes/elementor/tags/class-ztp-tag-text.php';
		require_once ZTP_PLUGIN_DIR . 'includes/elementor/tags/class-ztp-tag-wysiwyg.php';

		if ( method_exists( $dynamic_tags_manager, 'register_group' ) ) {
			$dynamic_tags_manager->register_group(
				self::GROUP,
				array(
					'title' => __( 'Zouetech Portfolio', 'zouetech-portfolio' ),
				)
			);
		}

		$tags = array(
			'Zouetech_Portfolio_Tag_Text',
			'Zouetech_Portfolio_Tag_Wysiwyg',
		);

		if ( class_exists( '\Elementor\Core\DynamicTags\Data_Tag' ) ) {
			require_once ZTP_PLUGIN_DIR . 'includes/elementor/tags/class-ztp-tag-url.php';
			require_once ZTP_PLUGIN_DIR . 'includes/elementor/tags/class-ztp-tag-image.php';
			require_once ZTP_PLUGIN_DIR . 'includes/elementor/tags/class-ztp-tag-gallery.php';

			// Dedicated Button Link tags (appear under Link → Dynamic Tags).
			$tags[] = 'Zouetech_Portfolio_Tag_Live_URL';
			$tags[] = 'Zouetech_Portfolio_Tag_Github_URL';
			$tags[] = 'Zouetech_Portfolio_Tag_Video_URL';
			$tags[] = 'Zouetech_Portfolio_Tag_URL';
			$tags[] = 'Zouetech_Portfolio_Tag_Image';
			$tags[] = 'Zouetech_Portfolio_Tag_Gallery';
		}

		foreach ( $tags as $tag_class ) {
			if ( ! class_exists( $tag_class ) ) {
				continue;
			}

			try {
				if ( method_exists( $dynamic_tags_manager, 'register' ) ) {
					$dynamic_tags_manager->register( new $tag_class() );
				} elseif ( method_exists( $dynamic_tags_manager, 'register_tag' ) ) {
					$dynamic_tags_manager->register_tag( $tag_class );
				}
			} catch ( \Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
				// Skip broken tag; do not break remaining registration.
			}
		}
	}
}
