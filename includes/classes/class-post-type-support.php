<?php
/**
 * Post Type Support Manager
 *
 * Handles registration and querying of post type support for the duplicate_as feature.
 * This is the source of truth for which post types can be duplicated and/or transformed.
 *
 * @package DuplicateAs
 * @since   0.3.0
 */

if ( ! class_exists( 'Duplicate_As_Post_Type_Support' ) ) {
	/**
	 * Manages post type support for the duplicate_as feature.
	 *
	 * Registers default post type supports and provides methods to query
	 * which post types support duplication and which transformation targets
	 * are available for each post type.
	 *
	 * @since 0.3.0
	 */
	class Duplicate_As_Post_Type_Support {

		/**
		 * Single instance of the class.
		 *
		 * @since 0.3.0
		 * @var Duplicate_As_Post_Type_Support|null
		 */
		private static ?Duplicate_As_Post_Type_Support $instance = null;

		/**
		 * Private constructor to prevent direct instantiation.
		 *
		 * @since 0.3.0
		 */
		private function __construct() {
			$this->init_hooks();
		}

		/**
		 * Get the singleton instance.
		 *
		 * @since 0.3.0
		 * @return Duplicate_As_Post_Type_Support The singleton instance.
		 */
		public static function get_instance(): Duplicate_As_Post_Type_Support {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Initialize WordPress hooks.
		 *
		 * @since 0.3.0
		 * @return void
		 */
		private function init_hooks(): void {
			add_action( 'init', array( $this, 'add_default_support' ) );
		}

		/**
		 * Add duplicate_as support to default post types.
		 *
		 * Enables duplication for pages and transformation for posts.
		 * The third parameter on posts enables transformation to other types.
		 *
		 * @since 0.1.0
		 * @return void
		 *
		 * @example
		 * // Enable simple duplication for a custom post type:
		 * add_post_type_support( 'book', 'duplicate_as' );
		 *
		 * @example
		 * // Enable transformation from 'book' to 'article':
		 * add_post_type_support( 'book', 'duplicate_as', 'article' );
		 *
		 * @example
		 * // Enable both duplication and transformation:
		 * add_post_type_support( 'page', 'duplicate_as', array('page', 'post', 'gatherpress_event') );
		 */
		public function add_default_support(): void {
			add_post_type_support( 'page', 'duplicate_as' );
			add_post_type_support( 'post', 'duplicate_as', array( 'page', 'post', 'gatherpress_event' ) );
		}

		/**
		 * Check if a post type is allowed for duplication.
		 *
		 * Verifies that the post type has 'duplicate_as' support enabled.
		 *
		 * @since 0.1.0
		 * @param string $post_type Post type slug to check.
		 * @return bool True if duplication is supported, false otherwise.
		 */
		public function is_post_type_allowed( string $post_type ): bool {
			return post_type_supports( $post_type, 'duplicate_as' );
		}

		/**
		 * Get all available transformation targets for a post type.
		 *
		 * Returns an array of post types that the source post type can be transformed into.
		 * If the array includes the source post type itself, duplication is also supported.
		 *
		 * @since 0.1.0
		 * @param string $post_type Current post type slug.
		 * @return array<string> Array of target post types (empty if only simple duplication).
		 *
		 * @example
		 * // For: add_post_type_support( 'page', 'duplicate_as', array('page', 'post') )
		 * $targets = $support->get_transform_targets( 'page' );
		 * // Returns: ['page', 'post']
		 *
		 * @example
		 * // For: add_post_type_support( 'post', 'duplicate_as' )
		 * $targets = $support->get_transform_targets( 'post' );
		 * // Returns: []
		 */
		public function get_transform_targets( string $post_type ): array {
			$support = get_all_post_type_supports( $post_type );

			if ( ! isset( $support['duplicate_as'] ) ) {
				return array();
			}
			$duplicate_support = $support['duplicate_as'];

			// If it's an array, return valid post types.
			if ( is_array( $duplicate_support ) && is_array( $duplicate_support[0] ) ) {
				$valid_targets = array();
				foreach ( $duplicate_support[0] as $target ) {
					if ( is_string( $target ) && post_type_exists( $target ) ) {
						$valid_targets[] = $target;
					}
				}
				return $valid_targets;
			}

			// If it's a single string, return it as an array.
			if ( is_string( $duplicate_support ) && post_type_exists( $duplicate_support ) ) {
				return array( $duplicate_support );
			}

			// Simple duplication (true) - no transformation targets.
			return array();
		}
	}
}
