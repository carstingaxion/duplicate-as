<?php
/**
 * Permissions Manager
 *
 * Centralizes all permission and validation checks for post duplication
 * and transformation operations.
 *
 * @package DuplicateAs
 * @since   0.3.0
 */

if ( ! class_exists( 'Duplicate_As_Permissions' ) ) {
	/**
	 * Handles permission checks and validation for duplication operations.
	 *
	 * Provides methods to verify user capabilities, validate post types,
	 * and check transformation permissions. Used by both the REST API
	 * and admin action handlers.
	 *
	 * @since 0.3.0
	 */
	class Duplicate_As_Permissions {

		/**
		 * Single instance of the class.
		 *
		 * @since 0.3.0
		 * @var Duplicate_As_Permissions|null
		 */
		private static ?Duplicate_As_Permissions $instance = null;

		/**
		 * Post type support instance.
		 *
		 * @since 0.3.0
		 * @var Duplicate_As_Post_Type_Support
		 */
		private Duplicate_As_Post_Type_Support $post_type_support;

		/**
		 * Private constructor to prevent direct instantiation.
		 *
		 * @since 0.3.0
		 */
		private function __construct() {
			$this->post_type_support = Duplicate_As_Post_Type_Support::get_instance();
		}

		/**
		 * Get the singleton instance.
		 *
		 * @since 0.3.0
		 * @return Duplicate_As_Permissions The singleton instance.
		 */
		public static function get_instance(): Duplicate_As_Permissions {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Verify nonce for admin actions.
		 *
		 * Validates the nonce for duplicate or transform actions.
		 *
		 * @since 0.1.0
		 * @param string      $action      Nonce action name.
		 * @param string|null $nonce_value Nonce value to verify.
		 * @return bool True if nonce is valid.
		 */
		public function verify_nonce( string $action, ?string $nonce_value ): bool {
			if ( ! $nonce_value ) {
				return false;
			}
			return (bool) wp_verify_nonce( $nonce_value, $action );
		}

		/**
		 * Check if user can edit the source post.
		 *
		 * Validates that the source post type object exists with proper capabilities
		 * and the current user can edit the specific post.
		 *
		 * @since 0.2.0
		 * @param WP_Post $post    Post object to check.
		 * @param int     $post_id Post ID for capability check.
		 * @return bool True if user can edit, false otherwise.
		 */
		public function can_edit_source_post( WP_Post $post, int $post_id ): bool {
			$source_post_type_obj = get_post_type_object( $post->post_type );
			if ( ! $source_post_type_obj || ! is_string( $source_post_type_obj->cap->edit_post ) || ! is_string( $source_post_type_obj->cap->create_posts ) ) {
				return false;
			}

			return current_user_can( $source_post_type_obj->cap->edit_post, $post_id );
		}

		/**
		 * Check if user can create posts of a given post type.
		 *
		 * @since 0.2.0
		 * @param string $post_type Post type slug.
		 * @return bool True if user can create posts, false otherwise.
		 */
		public function can_create_posts( string $post_type ): bool {
			$post_type_obj = get_post_type_object( $post_type );
			if ( ! $post_type_obj || ! is_string( $post_type_obj->cap->create_posts ) ) {
				return false;
			}

			return current_user_can( $post_type_obj->cap->create_posts );
		}

		/**
		 * Check if user can transform to target post type.
		 *
		 * Validates that the target post type is in the allowed targets list,
		 * the target post type object exists, and the user has permission to create
		 * posts of the target type.
		 *
		 * @since 0.2.0
		 * @param string $source_post_type Source post type slug.
		 * @param string $target_post_type Target post type slug.
		 * @return bool True if transformation is allowed, false otherwise.
		 */
		public function can_transform_to_target( string $source_post_type, string $target_post_type ): bool {
			$allowed_targets = $this->post_type_support->get_transform_targets( $source_post_type );
			if ( ! in_array( $target_post_type, $allowed_targets, true ) ) {
				return false;
			}

			$target_post_type_obj = get_post_type_object( $target_post_type );
			if ( ! $target_post_type_obj || ! is_string( $target_post_type_obj->cap->create_posts ) ) {
				return false;
			}

			return current_user_can( $target_post_type_obj->cap->create_posts );
		}

		/**
		 * Validate source post type and capabilities.
		 *
		 * Checks if the source post type exists and has required capabilities.
		 *
		 * @since 0.1.0
		 * @param WP_Post $post Post object to validate.
		 * @return void Dies with error message if validation fails.
		 */
		public function validate_source_post_type( WP_Post $post ): void {
			$post_type_obj = get_post_type_object( $post->post_type );
			if ( ! $post_type_obj || ! is_string( $post_type_obj->cap->edit_post ) || ! is_string( $post_type_obj->cap->create_posts ) ) {
				wp_die( esc_html__( 'Post type not found for duplication.', 'duplicate-as' ) );
			}
		}

		/**
		 * Validate duplication support for post type.
		 *
		 * Checks if the post type supports the duplicate_as feature.
		 *
		 * @since 0.1.0
		 * @param WP_Post $post Post object to validate.
		 * @return void Dies with error message if validation fails.
		 */
		public function validate_duplication_support( WP_Post $post ): void {
			if ( ! $this->post_type_support->is_post_type_allowed( $post->post_type ) ) {
				wp_die( esc_html__( 'This post type cannot be duplicated.', 'duplicate-as' ) );
			}
		}

		/**
		 * Validate user permissions for source post.
		 *
		 * Checks if user can edit the source post and create new posts.
		 *
		 * @since 0.1.0
		 * @param WP_Post $post Post object to validate.
		 * @return void Dies with error message if validation fails.
		 */
		public function validate_source_permissions( WP_Post $post ): void {
			$post_type_obj = get_post_type_object( $post->post_type );
			if ( ! $post_type_obj || ! is_string( $post_type_obj->cap->edit_post ) || ! is_string( $post_type_obj->cap->create_posts ) ) {
				return;
			}

			if ( ! current_user_can( $post_type_obj->cap->edit_post, $post->ID ) ) {
				wp_die( esc_html__( 'You do not have permission to duplicate this post.', 'duplicate-as' ) );
			}

			if ( ! current_user_can( $post_type_obj->cap->create_posts ) ) {
				wp_die( esc_html__( 'You do not have permission to duplicate this post.', 'duplicate-as' ) );
			}
		}

		/**
		 * Validate target post type and transformation permissions.
		 *
		 * Checks if transformation to target post type is allowed and user has permissions.
		 *
		 * @since 0.1.0
		 * @param WP_Post $post        Post object to validate.
		 * @param string  $target_type Target post type slug.
		 * @return void Dies with error message if validation fails.
		 */
		public function validate_transformation( WP_Post $post, string $target_type ): void {
			$target_post_type_obj = get_post_type_object( $target_type );
			if ( ! $target_post_type_obj || ! is_string( $target_post_type_obj->cap->create_posts ) ) {
				wp_die( esc_html__( 'Target post type does not exist.', 'duplicate-as' ) );
			}

			if ( ! current_user_can( $target_post_type_obj->cap->create_posts ) ) {
				wp_die( esc_html__( 'You do not have permission to create posts of the target type.', 'duplicate-as' ) );
			}

			$allowed_targets = $this->post_type_support->get_transform_targets( $post->post_type );
			if ( ! in_array( $target_type, $allowed_targets, true ) ) {
				wp_die( esc_html__( 'This transformation is not allowed.', 'duplicate-as' ) );
			}
		}

		/**
		 * Validate post and permissions for duplication.
		 *
		 * Common validation logic used by both duplicate and transform handlers.
		 * Checks post existence, post type support, and user permissions.
		 *
		 * @since 0.1.0
		 * @param int         $post_id     Post ID to validate.
		 * @param string|null $target_type Optional target post type for transformation.
		 * @return WP_Post Post object if valid.
		 */
		public function validate_post_and_permissions( int $post_id, ?string $target_type = null ): WP_Post {
			$post = get_post( $post_id );

			if ( ! $post ) {
				wp_die( esc_html__( 'Post not found.', 'duplicate-as' ) );
			}

			$this->validate_source_post_type( $post );
			$this->validate_duplication_support( $post );
			$this->validate_source_permissions( $post );

			if ( $target_type ) {
				$this->validate_transformation( $post, $target_type );
			}

			return $post;
		}
	}
}
