<?php
/**
 * Plugin Name:       Duplicate as
 * Description:       Duplicate or Duplicate as different post type, directly from the Editor Sidebar or the Admin List Tables.
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            cb + WordPress Telex
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       duplicate-as
 *
 * @package DuplicateAs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Duplicate_As' ) ) {
	/**
	 * Main plugin class using singleton pattern
	 *
	 * This class manages the duplicate post functionality, including:
	 * - REST API endpoint registration
	 * - Post duplication logic
	 * - Permission checks
	 * - Post type support management
	 * - Asset enqueueing
	 * - Admin list table action links
	 *
	 * @since 0.1.0
	 */
	class Duplicate_As {
		/**
		 * Single instance of the class
		 *
		 * @since 0.1.0
		 * @var Duplicate_As|null
		 */
		private static $instance = null;

		/**
		 * Plugin version
		 *
		 * @since 0.1.0
		 * @var string
		 */
		const VERSION = '0.1.0';

		/**
		 * REST API namespace
		 *
		 * @since 0.1.0
		 * @var string
		 */
		const REST_NAMESPACE = 'duplicate-as/v1';

		/**
		 * Private constructor to prevent direct instantiation
		 *
		 * Ensures singleton pattern by making constructor private.
		 * Use get_instance() to retrieve the single instance.
		 *
		 * @since 0.1.0
		 */
		private function __construct() {
			$this->init_hooks();
		}

		/**
		 * Get the singleton instance
		 *
		 * Creates a new instance if one doesn't exist, or returns
		 * the existing instance.
		 *
		 * @since 0.1.0
		 * @return Duplicate_As The singleton instance
		 */
		public static function get_instance(): Duplicate_As {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Initialize WordPress hooks
		 *
		 * Registers all action and filter hooks used by the plugin.
		 *
		 * @since 0.1.0
		 * @return void
		 */
		private function init_hooks(): void {
			add_action( 'init', array( $this, 'add_post_type_support' ) );
			add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
			add_filter( 'post_row_actions', array( $this, 'add_row_actions' ), 10, 2 );
			add_filter( 'page_row_actions', array( $this, 'add_row_actions' ), 10, 2 );
			add_action( 'admin_action_duplicate_as_duplicate', array( $this, 'handle_admin_duplicate' ) );
			add_action( 'admin_action_duplicate_as_transform', array( $this, 'handle_admin_transform' ) );
		}

		/**
		 * Add duplicate_as support to default post types
		 *
		 * Enables duplication for posts and transformation for pages.
		 * The third parameter on pages enables transformation to 'post' type.
		 *
		 * @since 0.1.0
		 * @return void
		 *
		 * @example
		 * // Enable simple duplication for a custom post type
		 * add_post_type_support( 'book', 'duplicate_as' );
		 *
		 * @example
		 * // Enable transformation from 'book' to 'article'
		 * add_post_type_support( 'book', 'duplicate_as', 'article' );
		 *
		 * @example
		 * // Enable both duplication and transformation
		 * add_post_type_support( 'page', 'duplicate_as', array('page', 'post', 'gatherpress_event') );
		 */
		public function add_post_type_support(): void {
			add_post_type_support( 'page', 'duplicate_as' );
			add_post_type_support( 'post', 'duplicate_as', array( 'page', 'post', 'gatherpress_event' ) );
		}

		/**
		 * Check if a post type is allowed for duplication
		 *
		 * Verifies that the post type has 'duplicate_as' support enabled.
		 *
		 * @since 0.1.0
		 * @param string $post_type Post type to check.
		 * @return bool True if duplication is supported, false otherwise.
		 */
		public function is_post_type_allowed( string $post_type ): bool {
			return post_type_supports( $post_type, 'duplicate_as' );
		}

		/**
		 * Get all available transformation targets for a post type
		 *
		 * Returns an array of post types that the source post type can be transformed into.
		 * If the array includes the source post type itself, duplication is also supported.
		 *
		 * @since 0.1.0
		 * @param string $post_type Current post type.
		 * @return array<string> Array of target post types (empty if only simple duplication).
		 *
		 * @example
		 * // For: add_post_type_support( 'page', 'duplicate_as', array('page', 'post') )
		 * $targets = $this->get_transform_targets( 'page' );
		 * // Returns: ['page', 'post']
		 *
		 * @example
		 * // For: add_post_type_support( 'post', 'duplicate_as' )
		 * $targets = $this->get_transform_targets( 'post' );
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

		/**
		 * Verify nonce for admin actions
		 *
		 * Validates the nonce for duplicate or transform actions.
		 *
		 * @since 0.1.0
		 * @param string      $action      Nonce action name.
		 * @param string|null $nonce_value Nonce value to verify.
		 * @return bool True if nonce is valid.
		 */
		private function verify_nonce( string $action, ?string $nonce_value ): bool {
			if ( ! $nonce_value ) {
				return false;
			}
			return (bool) wp_verify_nonce( $nonce_value, $action );
		}

		/**
		 * Validate source post type and capabilities
		 *
		 * Checks if the source post type exists and has required capabilities.
		 *
		 * @since 0.1.0
		 * @param WP_Post $post Post object to validate.
		 * @return void Dies with error message if validation fails.
		 */
		private function validate_source_post_type( WP_Post $post ): void {
			$post_type_obj = get_post_type_object( $post->post_type );
			if ( ! $post_type_obj || ! is_string( $post_type_obj->cap->edit_post ) || ! is_string( $post_type_obj->cap->create_posts ) ) {
				wp_die( esc_html__( 'Post type not found for duplication.', 'duplicate-as' ) );
			}
		}

		/**
		 * Validate duplication support for post type
		 *
		 * Checks if the post type supports the duplicate_as feature.
		 *
		 * @since 0.1.0
		 * @param WP_Post $post Post object to validate.
		 * @return void Dies with error message if validation fails.
		 */
		private function validate_duplication_support( WP_Post $post ): void {
			if ( ! $this->is_post_type_allowed( $post->post_type ) ) {
				wp_die( esc_html__( 'This post type cannot be duplicated.', 'duplicate-as' ) );
			}
		}

		/**
		 * Validate user permissions for source post
		 *
		 * Checks if user can edit the source post and create new posts.
		 *
		 * @since 0.1.0
		 * @param WP_Post $post Post object to validate.
		 * @return void Dies with error message if validation fails.
		 */
		private function validate_source_permissions( WP_Post $post ): void {
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
		 * Validate target post type and transformation permissions
		 *
		 * Checks if transformation to target post type is allowed and user has permissions.
		 *
		 * @since 0.1.0
		 * @param WP_Post $post        Post object to validate.
		 * @param string  $target_type Target post type slug.
		 * @return void Dies with error message if validation fails.
		 */
		private function validate_transformation( WP_Post $post, string $target_type ): void {
			$target_post_type_obj = get_post_type_object( $target_type );
			if ( ! $target_post_type_obj || ! is_string( $target_post_type_obj->cap->create_posts ) ) {
				wp_die( esc_html__( 'Target post type does not exist.', 'duplicate-as' ) );
			}
			
			if ( ! current_user_can( $target_post_type_obj->cap->create_posts ) ) {
				wp_die( esc_html__( 'You do not have permission to create posts of the target type.', 'duplicate-as' ) );
			}

			$allowed_targets = $this->get_transform_targets( $post->post_type );
			if ( ! in_array( $target_type, $allowed_targets, true ) ) {
				wp_die( esc_html__( 'This transformation is not allowed.', 'duplicate-as' ) );
			}
		}

		/**
		 * Validate post and permissions for duplication
		 *
		 * Common validation logic used by both duplicate and transform handlers.
		 * Checks post existence, post type support, and user permissions.
		 *
		 * @since 0.1.0
		 * @param int         $post_id     Post ID to validate.
		 * @param string|null $target_type Optional target post type for transformation.
		 * @return WP_Post Post object if valid.
		 * @throws Exception Dies with error message if validation fails.
		 */
		private function validate_post_and_permissions( int $post_id, ?string $target_type = null ): WP_Post {
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

		/**
		 * Process duplication and redirect
		 *
		 * Common logic for performing duplication/transformation and redirecting to edit screen.
		 *
		 * @since 0.1.0
		 * @param WP_Post     $post        Post to duplicate.
		 * @param string|null $target_type Target post type (null for duplication).
		 * @return void Dies after redirect.
		 */
		private function process_duplication_and_redirect( WP_Post $post, ?string $target_type = null ): void {
			$new_post_id = $this->create_duplicate( $post, $target_type );

			if ( is_wp_error( $new_post_id ) ) {
				wp_die( esc_html( $new_post_id->get_error_message() ) );
			}

			$source_post_type = $post->post_type;
			$final_post_type  = $target_type ? $target_type : $post->post_type;
			
			$this->copy_taxonomies( $post->ID, $new_post_id, $source_post_type, $final_post_type );
			$this->copy_post_meta( $post->ID, $new_post_id );
			$this->copy_featured_image( $post->ID, $new_post_id );

			/**
			 * Fires after a post has been duplicated
			 *
			 * @since 0.1.0
			 *
			 * @param int $new_post_id The ID of the newly created duplicate post.
			 * @param int $post->ID    The ID of the original post.
			 */
			do_action( 'duplicate_as_after_duplicate', $new_post_id, $post->ID );

			wp_safe_redirect( (string) get_edit_post_link( $new_post_id, 'raw' ) );
			exit;
		}

		/**
		 * Check if row actions should be added for post
		 *
		 * Validates post type and user permissions.
		 *
		 * @since 0.1.0
		 * @param WP_Post $post Current post object.
		 * @return bool True if actions should be added.
		 */
		private function should_add_row_actions( WP_Post $post ): bool {
			$post_type_obj = get_post_type_object( $post->post_type );
			if ( ! $post_type_obj || ! is_string( $post_type_obj->cap->edit_post ) ) {
				return false;
			}

			if ( ! $this->is_post_type_allowed( $post->post_type ) ) {
				return false;
			}

			if ( ! current_user_can( $post_type_obj->cap->edit_post, $post->ID ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Add duplicate action link to row actions
		 *
		 * Creates the duplicate link when appropriate.
		 *
		 * @since 0.1.0
		 * @param array<string, string> $actions Current action links.
		 * @param WP_Post               $post    Current post object.
		 * @param array<string>         $targets Transform targets.
		 * @return array<string, string> Modified action links.
		 */
		private function add_duplicate_action( array $actions, WP_Post $post, array $targets ): array {
			if ( empty( $targets ) || in_array( $post->post_type, $targets, true ) ) {
				$duplicate_url = wp_nonce_url(
					admin_url( 'admin.php?action=duplicate_as_duplicate&post=' . $post->ID ),
					'duplicate_as_duplicate_' . $post->ID
				);

				$actions['duplicate_as_duplicate'] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					esc_url( $duplicate_url ),
					esc_attr(
						sprintf( 
							/* translators: Duplicate {post_title} */
							__( 'Duplicate "%s"', 'duplicate-as' ),
							$post->post_title
						)
					),
					esc_html__( 'Duplicate', 'duplicate-as' )
				);
			}
			return $actions;
		}

		/**
		 * Add transform action links to row actions
		 *
		 * Creates transform links for each valid target post type.
		 *
		 * @since 0.1.0
		 * @param array<string, string> $actions Current action links.
		 * @param WP_Post               $post    Current post object.
		 * @param array<string>         $targets Transform targets.
		 * @return array<string, string> Modified action links.
		 */
		private function add_transform_actions( array $actions, WP_Post $post, array $targets ): array {
			if ( empty( $targets ) ) {
				return $actions;
			}

			foreach ( $targets as $target ) {
				if ( $target === $post->post_type ) {
					continue;
				}

				$target_post_type_obj = get_post_type_object( $target );
				if ( ! $target_post_type_obj || ! is_string( $target_post_type_obj->cap->create_posts ) || ! is_string( $target_post_type_obj->labels->singular_name ) ) {
					continue;
				}
				
				if ( ! current_user_can( $target_post_type_obj->cap->create_posts ) ) {
					continue;
				}

				$transform_url = wp_nonce_url(
					admin_url(
						sprintf(
							'admin.php?action=duplicate_as_transform&post=%d&target_type=%s',
							$post->ID,
							rawurlencode( $target )
						) 
					),
					'duplicate_as_transform_' . $post->ID . '_' . $target
				);

				$target_label = $target_post_type_obj->labels->singular_name;
				$action_key   = 'duplicate_as_transform_' . $target;

				$actions[ $action_key ] = sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
					esc_url( $transform_url ),
					esc_attr(
						sprintf(
							/* translators: Duplicate {post_title} into {target post_type singular label}*/
							__( 'Duplicate "%1$s" into %2$s', 'duplicate-as' ),
							$post->post_title,
							$target_label
						)
					),
					esc_html(
						sprintf(
							/* translators: Duplicate as {target post_type singular label} */
							__( 'Duplicate as %s', 'duplicate-as' ),
							$target_label
						)
					)
				);
			}

			return $actions;
		}

		/**
		 * Add action links to post list table
		 *
		 * Adds "Duplicate" and "Duplicate as {Type}" links to the post row actions.
		 * Only visible for post types that support duplicate_as.
		 *
		 * @since 0.1.0
		 * @param array<string, string> $actions Array of action links.
		 * @param WP_Post               $post    Current post object.
		 * @return array<string, string> Modified array of action links.
		 *
		 * @example Original actions:
		 * [
		 *   'edit' => '<a href="...">Edit</a>',
		 *   'trash' => '<a href="...">Trash</a>'
		 * ]
		 *
		 * @example Modified actions (with duplicate only):
		 * [
		 *   'edit' => '<a href="...">Edit</a>',
		 *   'duplicate_as_duplicate' => '<a href="...">Duplicate</a>',
		 *   'trash' => '<a href="...">Trash</a>'
		 * ]
		 *
		 * @example Modified actions (with transform):
		 * [
		 *   'edit' => '<a href="...">Edit</a>',
		 *   'duplicate_as_duplicate' => '<a href="...">Duplicate</a>',
		 *   'duplicate_as_transform_post' => '<a href="...">Duplicate as Post</a>',
		 *   'trash' => '<a href="...">Trash</a>'
		 * ]
		 */
		public function add_row_actions( array $actions, WP_Post $post ): array {
			if ( ! $this->should_add_row_actions( $post ) ) {
				return $actions;
			}

			$targets = $this->get_transform_targets( $post->post_type );
			$actions = $this->add_duplicate_action( $actions, $post, $targets );
			$actions = $this->add_transform_actions( $actions, $post, $targets );

			return $actions;
		}

		/**
		 * Handle duplicate action from admin list table
		 *
		 * Processes the duplicate request from post list table action links.
		 * Creates a duplicate and redirects to edit screen.
		 *
		 * @since 0.1.0
		 * @return void
		 */
		public function handle_admin_duplicate(): void {
			if ( ! isset( $_GET['post'] ) || ! is_numeric( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				wp_die( esc_html__( 'No post specified.', 'duplicate-as' ) );
			}

			$post_id = absint( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$nonce   = isset( $_GET['_wpnonce'] ) && is_string( $_GET['_wpnonce'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				: null;

			if ( ! $this->verify_nonce( 'duplicate_as_duplicate_' . $post_id, $nonce ) ) {
				wp_die( esc_html__( 'Security check failed.', 'duplicate-as' ) );
			}

			$post = $this->validate_post_and_permissions( $post_id );
			$this->process_duplication_and_redirect( $post );
		}

		/**
		 * Handle transform action from admin list table
		 *
		 * Processes the transform request from post list table action links.
		 * Creates a transformed post and redirects to edit screen.
		 *
		 * @since 0.1.0
		 * @return void
		 */
		public function handle_admin_transform(): void {
			if ( ! isset( $_GET['post'] ) || ! is_numeric( $_GET['post'] ) || ! isset( $_GET['target_type'] ) || ! is_string( $_GET['target_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				wp_die( esc_html__( 'Missing required parameters.', 'duplicate-as' ) );
			}

			$post_id     = absint( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$target_type = sanitize_key( $_GET['target_type'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$nonce       = isset( $_GET['_wpnonce'] ) && is_string( $_GET['_wpnonce'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				: null;

			if ( ! $this->verify_nonce( 'duplicate_as_transform_' . $post_id . '_' . $target_type, $nonce ) ) {
				wp_die( esc_html__( 'Security check failed.', 'duplicate-as' ) );
			}

			$post = $this->validate_post_and_permissions( $post_id, $target_type );
			$this->process_duplication_and_redirect( $post, $target_type );
		}

		/**
		 * Register REST API routes
		 *
		 * Registers the /duplicate/{id} endpoint for post duplication.
		 *
		 * Endpoint: POST /wp-json/duplicate-as/v1/duplicate/{id}
		 *
		 * @since 0.1.0
		 * @return void
		 *
		 * @example Request:
		 * POST /wp-json/duplicate-as/v1/duplicate/123
		 * Body: { "target_post_type": "post" }
		 *
		 * @example Response (Success):
		 * {
		 *   "success": true,
		 *   "new_post_id": 456,
		 *   "edit_url": "https://example.com/wp-admin/post.php?post=456&action=edit",
		 *   "is_transform": false
		 * }
		 *
		 * @example Response (Error):
		 * {
		 *   "code": "post_not_found",
		 *   "message": "Post not found.",
		 *   "data": {"status": 404}
		 * }
		 */
		public function register_rest_routes(): void {
			register_rest_route(
				self::REST_NAMESPACE,
				'/duplicate/(?P<id>\d+)',
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'duplicate_as_endpoint' ),
					'permission_callback' => array( $this, 'check_permission' ),
					'args'                => array(
						'id'               => array(
							'description'       => __( 'The post ID to duplicate.', 'duplicate-as' ),
							'type'              => 'integer',
							'required'          => true,
							'validate_callback' => function ( $param ): bool {
								return is_numeric( $param );
							},
						),
						'target_post_type' => array(
							'description'       => __( 'The target post type for transformation.', 'duplicate-as' ),
							'type'              => 'string',
							'required'          => false,
							'validate_callback' => function ( $param ): bool {
								return ( empty( $param ) || ! is_string( $param ) ) || post_type_exists( $param );
							},
						),
					),
				)
			);
		}

		/**
		 * Check if user has permission to duplicate posts
		 *
		 * Verifies:
		 * - Post exists
		 * - Post type allows duplication
		 * - User can edit the source post
		 * - User can create posts in the target post type (if transforming)
		 * - Target post type is in allowed targets list
		 *
		 * @since 0.1.0
		 * @param WP_REST_Request<array{id:int, target_post_type:string|null}> $request Request object containing post ID and optional target post type.
		 * @return bool True if user has permission, false otherwise.
		 *
		 * @example
		 * // Request object structure:
		 * $request->get_param( 'id' ); // Post ID (integer)
		 * $request->get_param( 'target_post_type' ); // Target post type (string, optional)
		 */
		public function check_permission( WP_REST_Request $request ): bool {
			$post_id          = is_int( $request->get_param( 'id' ) ) ? $request->get_param( 'id' ) : 0;
			$target_post_type = is_string( $request->get_param( 'target_post_type' ) ) ? $request->get_param( 'target_post_type' ) : null;
			$post             = get_post( $post_id );

			if ( ! $post ) {
				return false;
			}

			if ( ! $this->is_post_type_allowed( $post->post_type ) ) {
				return false;
			}

			// Check if post type exists.
			$source_post_type_obj = get_post_type_object( $post->post_type );
			if ( ! $source_post_type_obj || ! is_string( $source_post_type_obj->cap->edit_post ) || ! is_string( $source_post_type_obj->cap->create_posts ) ) {
				return false;
			}
			// Check if user can edit the source post.
			if ( ! current_user_can( $source_post_type_obj->cap->edit_post, $post_id ) ) {
				return false;
			}
			
			// If target post type is specified, verify it's allowed.
			if ( $target_post_type ) {
				$allowed_targets = $this->get_transform_targets( $post->post_type );
				
				// Check if target is in allowed list.
				if ( ! in_array( $target_post_type, $allowed_targets, true ) ) {
					return false;
				}
				
				// Check if post type exists.
				$target_post_type_obj = get_post_type_object( $target_post_type );
				if ( ! $target_post_type_obj || ! is_string( $target_post_type_obj->cap->create_posts ) ) {
					return false;
				}

				// Check permission for target post type.
				if ( ! current_user_can( $target_post_type_obj->cap->create_posts ) ) {
					return false;
				}
			} elseif ( ! current_user_can( $source_post_type_obj->cap->create_posts ) ) {
				// No target specified - check if user can create posts of source type.
				return false;
			}

			return true;
		}

		/**
		 * Duplicate a post with all its metadata
		 *
		 * Orchestrates the complete duplication process:
		 * 1. Creates duplicate post
		 * 2. Copies taxonomies
		 * 3. Copies post meta
		 * 4. Copies featured image
		 *
		 * @since 0.1.0
		 * @param WP_REST_Request<array{id:int, target_post_type:string|null}> $request Request object containing post ID and optional target post type.
		 * @return WP_REST_Response|WP_Error Response with new post data or error.
		 *
		 * @example Success Response:
		 * {
		 *   "success": true,
		 *   "new_post_id": 456,
		 *   "edit_url": "https://example.com/wp-admin/post.php?post=456&action=edit",
		 *   "is_transform": true
		 * }
		 *
		 * @example Error Response:
		 * {
		 *   "code": "duplication_failed",
		 *   "message": "Failed to duplicate post.",
		 *   "data": {"status": 500}
		 * }
		 */
		public function duplicate_as_endpoint( WP_REST_Request $request ): WP_REST_Response|WP_Error {
			$post_id          = is_int( $request->get_param( 'id' ) ) ? $request->get_param( 'id' ) : 0;
			$target_post_type = is_string( $request->get_param( 'target_post_type' ) ) ? $request->get_param( 'target_post_type' ) : null;
			$post             = get_post( $post_id );

			if ( ! $post ) {
				return new WP_Error(
					'post_not_found',
					__( 'Post not found.', 'duplicate-as' ),
					array( 'status' => 404 )
				);
			}

			if ( ! $this->is_post_type_allowed( $post->post_type ) ) {
				return new WP_Error(
					'post_type_not_allowed',
					__( 'This post type cannot be duplicated.', 'duplicate-as' ),
					array( 'status' => 403 )
				);
			}

			// Verify target post type is allowed.
			if ( $target_post_type ) {
				$allowed_targets = $this->get_transform_targets( $post->post_type );
				if ( ! in_array( $target_post_type, $allowed_targets, true ) ) {
					return new WP_Error(
						'invalid_target_post_type',
						__( 'Target post type is not allowed for this post type.', 'duplicate-as' ),
						array( 'status' => 403 )
					);
				}
			}

			$new_post_id = $this->create_duplicate( $post, $target_post_type );

			if ( is_wp_error( $new_post_id ) ) {
				return $new_post_id;
			}

			$source_post_type = $post->post_type;
			$final_post_type  = $target_post_type ? $target_post_type : $post->post_type;
			$this->copy_taxonomies( $post_id, $new_post_id, $source_post_type, $final_post_type );
			$this->copy_post_meta( $post_id, $new_post_id );
			$this->copy_featured_image( $post_id, $new_post_id );

			/**
			 * Fires after a post has been duplicated
			 *
			 * @since 0.1.0
			 *
			 * @param int $new_post_id The ID of the newly created duplicate post.
			 * @param int $post_id     The ID of the original post.
			 */
			do_action( 'duplicate_as_after_duplicate', $new_post_id, $post_id );

			return rest_ensure_response(
				array(
					'success'      => true,
					'new_post_id'  => $new_post_id,
					'edit_url'     => get_edit_post_link( $new_post_id, 'raw' ),
					'is_transform' => ! empty( $target_post_type ) && $target_post_type !== $post->post_type,
				)
			);
		}


		/**
		 * Convert post type template to block format.
		 *
		 * WordPress post type templates are arrays where each element is:
		 * [
		 *   0 => string,                 // Block name (e.g. 'core/paragraph')
		 *   1 => array<string, mixed>,   // Block attributes (optional)
		 *   2 => array<int, array>       // Inner blocks in template format (optional, recursive)
		 * ]
		 *
		 * This converts them to the structure expected by serialize_blocks():
		 * [
		 *   'blockName'    => string|null,
		 *   'attrs'        => array<string, mixed>,
		 *   'innerBlocks'  => array<int, array>, // Same structure recursively
		 *   'innerHTML'    => string,
		 *   'innerContent' => array<int, string>
		 * ]
		 *
		 * @since 0.1.0
		 *
		 * @param array<int, array{
		 *     0?: string,
		 *     1?: array<string, mixed>,
		 *     2?: array<int, array>
		 * }> $template_blocks Post type template blocks (recursive).
		 *
		 * @return array<int, array{
		 *     blockName: string|null,
		 *     attrs: array<string, mixed>,
		 *     innerBlocks: array<int, array>,
		 *     innerHTML: string,
		 *     innerContent: array<int, string>
		 * }> Blocks formatted for serialize_blocks().
		 *
		 * @example Input (post type template):
		 * [
		 *   ['core/heading', ['level' => 2]],
		 *   ['core/paragraph', ['placeholder' => 'Add text...']]
		 * ]
		 *
		 * @example Output (block format):
		 * [
		 *   [
		 *     'blockName' => 'core/heading',
		 *     'attrs' => ['level' => 2],
		 *     'innerBlocks' => [],
		 *     'innerHTML' => '',
		 *     'innerContent' => []
		 *   ],
		 *   [
		 *     'blockName' => 'core/paragraph',
		 *     'attrs' => ['placeholder' => 'Add text...'],
		 *     'innerBlocks' => [],
		 *     'innerHTML' => '',
		 *     'innerContent' => []
		 *   ]
		 * ]
		 */
		private function convert_template_to_blocks( array $template_blocks ): array {
			$blocks = array();
			
			foreach ( $template_blocks as $template_block ) {
				if ( ! is_array( $template_block ) ) {
					continue;
				}
				
				// Template format: [ 'block/name', { attrs }, [ innerBlocks ] ].
				$block_name   = isset( $template_block[0] ) && is_string( $template_block[0] ) ? $template_block[0] : '';
				$block_attrs  = isset( $template_block[1] ) && is_array( $template_block[1] ) ? $template_block[1] : array();
				$inner_blocks = isset( $template_block[2] ) && is_array( $template_block[2] ) ? $template_block[2] : array();
				
				if ( empty( $block_name ) ) {
					continue;
				}
				
				// Convert to block parser format.
				$block = array(
					'blockName'    => $block_name,
					'attrs'        => $block_attrs,
					'innerBlocks'  => ! empty( $inner_blocks ) ? $this->convert_template_to_blocks( $inner_blocks ) : array(),
					'innerHTML'    => '',
					'innerContent' => array(),
				);
				
				$blocks[] = $block;
			}
			
			return $blocks;
		}

		/**
		 * Create a duplicate post
		 *
		 * Creates a new post with the same content as the original.
		 * The new post is always created as a draft.
		 * When transforming to a different post type, prepends blocks from the
		 * target post type's template before the duplicated content.
		 *
		 * @since 0.1.0
		 * @param WP_Post     $post             Original post object.
		 * @param string|null $target_post_type Target post type for transformation.
		 * @return int|WP_Error New post ID on success, WP_Error on failure.
		 *
		 * @example Post data structure:
		 * [
		 *   'post_title'     => 'My Post Title',
		 *   'post_content'   => '<!-- wp:paragraph -->...<!-- /wp:paragraph -->',
		 *   'post_excerpt'   => 'Excerpt...',
		 *   'post_type'      => 'post',
		 *   'post_status'    => 'draft',
		 *   'comment_status' => 'open',
		 *   'ping_status'    => 'open',
		 *   'post_parent'    => 0,
		 *   'menu_order'     => 0,
		 *   'post_password'  => ''
		 * ]
		 *
		 * @example Template conversion:
		 * // Template: [['core/heading', {...}], ['core/paragraph', {...}]]
		 * // Converted: serialize_blocks() format
		 * // Original: '<!-- wp:image -->...<!-- /wp:image -->'
		 * // Result: '<!-- wp:heading -->...<!-- /wp:heading --><!-- wp:paragraph -->...<!-- /wp:paragraph --><!-- wp:image -->...<!-- /wp:image -->'
		 */
		private function create_duplicate( WP_Post $post, ?string $target_post_type = null ) {
			$new_post_type = $target_post_type ? $target_post_type : $post->post_type;
			$post_content  = $post->post_content;
			
			// If transforming to a different post type, prepend template blocks.
			if ( $target_post_type && $target_post_type !== $post->post_type ) {
				$target_post_type_obj = get_post_type_object( $target_post_type );
				
				if ( $target_post_type_obj && ! empty( $target_post_type_obj->template ) && is_array( $target_post_type_obj->template ) ) {
					// Convert template format to block parser format.
					$formatted_blocks = $this->convert_template_to_blocks( $target_post_type_obj->template );
					
					if ( ! empty( $formatted_blocks ) ) {
						// Serialize the formatted blocks to block markup.
						$template_content = serialize_blocks( $formatted_blocks );
						
						if ( ! empty( $template_content ) ) {
							// Prepend template content before the duplicated content.
							$post_content = $template_content . $post_content;
						}
					}
				}
			}

			$new_post_data = array(
				'post_title'     => $post->post_title,
				'post_content'   => $post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_type'      => $new_post_type,
				'post_status'    => 'draft',
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_parent'    => $post->post_parent,
				'menu_order'     => $post->menu_order,
				'post_password'  => $post->post_password,
			);

			/**
			 * Filters the post data before creating a duplicate
			 *
			 * Allows modification of post data before the duplicate is created.
			 *
			 * @since 0.1.0
			 *
			 * @param array       $new_post_data     The post data for the duplicate.
			 * @param WP_Post     $post              The original post object.
			 * @param string|null $target_post_type  Target post type if transforming.
			 *
			 * @example
			 * ```php
			 * add_filter( 'duplicate_as_post_data', function( $post_data, $original_post, $target_post_type ) {
			 *     // Add prefix to title
			 *     $post_data['post_title'] = 'Copy of ' . $post_data['post_title'];
			 *     return $post_data;
			 * }, 10, 3 );
			 * ```
			 */
			$new_post_data = apply_filters( 'duplicate_as_post_data', $new_post_data, $post, $target_post_type );
			/** Assume this is still valid, after filtering ;)
			 *
			 * @phpstan-ignore-next-line */
			$new_post_id = wp_insert_post( $new_post_data, true );

			if ( is_wp_error( $new_post_id ) ) {
				return new WP_Error(
					'duplication_failed',
					__( 'Failed to duplicate post.', 'duplicate-as' ),
					array( 'status' => 500 )
				);
			}

			return $new_post_id;
		}

		/**
		 * Copy taxonomies from original post to duplicate
		 *
		 * Only copies taxonomies that are registered for both source
		 * and target post types.
		 *
		 * @since 0.1.0
		 *
		 * @param int    $from_post_id     Original post ID.
		 * @param int    $to_post_id       New post ID.
		 * @param string $source_post_type Source post type.
		 * @param string $target_post_type Target post type.
		 * @return void
		 *
		 * @example Taxonomy structure:
		 * // For a post with categories and tags:
		 * Source taxonomies: ['category', 'post_tag', 'custom_tax']
		 * Target taxonomies: ['category', 'post_tag']
		 * Copied taxonomies: ['category', 'post_tag'] (intersection)
		 */
		private function copy_taxonomies( int $from_post_id, int $to_post_id, string $source_post_type, string $target_post_type ): void {
			// Get taxonomies for source post type.
			$source_taxonomies = get_object_taxonomies( $source_post_type );
			// Get taxonomies for target post type.
			$target_taxonomies = get_object_taxonomies( $target_post_type );
			
			// Only copy taxonomies that exist in both post types.
			$taxonomies = array_intersect( $source_taxonomies, $target_taxonomies );
			
			/**
			 * Filters the taxonomies to copy during duplication
			 *
			 * @since 0.1.0
			 *
			 * @param array  $taxonomies       Array of taxonomy names to copy.
			 * @param int    $from_post_id     The original post ID.
			 * @param int    $to_post_id       The duplicate post ID.
			 * @param string $source_post_type The source post type.
			 * @param string $target_post_type The target post type.
			 *
			 * @example
			 * ```php
			 * add_filter( 'duplicate_as_taxonomies', function( $taxonomies, $from_id, $to_id, $source, $target ) {
			 *     // Remove category from duplication
			 *     return array_diff( $taxonomies, ['category'] );
			 * }, 10, 5 );
			 * ```
			 */
			$taxonomies = apply_filters( 'duplicate_as_taxonomies', $taxonomies, $from_post_id, $to_post_id, $source_post_type, $target_post_type );
			
			foreach ( $taxonomies as $taxonomy ) {
				// Double-check the taxonomy is registered for the target post type.
				if ( ! is_string( $taxonomy ) || ! is_object_in_taxonomy( $target_post_type, $taxonomy ) ) {
					continue;
				}
				
				$terms = wp_get_object_terms( $from_post_id, $taxonomy, array( 'fields' => 'ids' ) );
				
				if ( is_wp_error( $terms ) || empty( $terms ) ) {
					continue;
				}
				
				/**
				 * Filters the terms to copy for a specific taxonomy
				 *
				 * @since 0.1.0
				 *
				 * @param array  $terms        Array of term IDs to copy.
				 * @param string $taxonomy     The taxonomy name.
				 * @param int    $from_post_id The original post ID.
				 * @param int    $to_post_id   The duplicate post ID.
				 *
				 * @example
				 * ```php
				 * add_filter( 'duplicate_as_taxonomy_terms', function( $terms, $taxonomy, $from_id, $to_id ) {
				 *     if ( $taxonomy === 'category' ) {
				 *         // Only copy the first category
				 *         return array_slice( $terms, 0, 1 );
				 *     }
				 *     return $terms;
				 * }, 10, 4 );
				 * ```
				 */
				$terms = apply_filters( 'duplicate_as_taxonomy_terms', $terms, $taxonomy, $from_post_id, $to_post_id );
				
				if ( ! empty( $terms ) ) {
					wp_set_object_terms( $to_post_id, $terms, $taxonomy );
				}
			}
		}

		/**
		 * Copy post meta from original post to duplicate
		 *
		 * Copies all post meta except excluded keys like edit locks.
		 * Handles serialized data properly.
		 *
		 * @since 0.1.0
		 * @param int $from_post_id Original post ID.
		 * @param int $to_post_id   New post ID.
		 * @return void
		 *
		 * @example Meta data structure:
		 * [
		 *   '_custom_field' => ['value1'],
		 *   '_another_field' => ['serialized:data:here'],
		 *   '_edit_lock' => ['timestamp'] // Excluded by default
		 * ]
		 */
		private function copy_post_meta( int $from_post_id, int $to_post_id ): void {
			$post_meta     = (array) get_post_meta( $from_post_id );
			$excluded_keys = array(
				'_edit_last',
				'_edit_lock',
				'_thumbnail_id',
			);
			
			/**
			 * Filters the list of meta keys to exclude from duplication
			 *
			 * @since 0.1.0
			 *
			 * @param array $excluded_keys Array of meta keys to exclude.
			 * @param int   $from_post_id  The original post ID.
			 * @param int   $to_post_id    The duplicate post ID.
			 *
			 * @example
			 * ```php
			 * add_filter( 'duplicate_as_excluded_meta_keys', function( $excluded_keys, $from_id, $to_id ) {
			 *     // Exclude view count from duplication
			 *     $excluded_keys[] = '_view_count';
			 *     return $excluded_keys;
			 * }, 10, 3 );
			 * ```
			 */
			$excluded_keys = apply_filters( 'duplicate_as_excluded_meta_keys', $excluded_keys, $from_post_id, $to_post_id );

			foreach ( $post_meta as $meta_key => $meta_values ) {
				if ( ! is_string( $meta_key ) || in_array( $meta_key, $excluded_keys, true ) || ! is_array( $meta_values ) ) {
					continue;
				}
	
				foreach ( $meta_values as $meta_value ) {
					if ( ! is_string( $meta_value ) ) {
						continue;
					}
					$meta_value = maybe_unserialize( $meta_value );
					
					/**
					 * Filters the meta value before adding it to the duplicate post
					 *
					 * @since 0.1.0
					 *
					 * @param mixed  $meta_value   The meta value to copy.
					 * @param string $meta_key     The meta key.
					 * @param int    $from_post_id The original post ID.
					 * @param int    $to_post_id   The duplicate post ID.
					 *
					 * @example
					 * ```php
					 * add_filter( 'duplicate_as_meta_value', function( $value, $key, $from_id, $to_id ) {
					 *     if ( $key === '_custom_counter' ) {
					 *         // Reset counter to 0 for duplicate
					 *         return 0;
					 *     }
					 *     return $value;
					 * }, 10, 4 );
					 * ```
					 */
					$meta_value = apply_filters( 'duplicate_as_meta_value', $meta_value, $meta_key, $from_post_id, $to_post_id );
					
					add_post_meta( $to_post_id, $meta_key, $meta_value );
				}
			}
		}

		/**
		 * Copy featured image from original post to duplicate
		 *
		 * Sets the same featured image (thumbnail) on the duplicate post.
		 *
		 * @since 0.1.0
		 * @param int $from_post_id Original post ID.
		 * @param int $to_post_id   New post ID.
		 * @return void
		 *
		 * @example
		 * // Original post has thumbnail ID 789
		 * // Duplicate will also have thumbnail ID 789
		 */
		private function copy_featured_image( int $from_post_id, int $to_post_id ): void {
			$thumbnail_id = get_post_thumbnail_id( $from_post_id );
			
			/**
			 * Filters the featured image ID to copy
			 *
			 * @since 0.1.0
			 *
			 * @param int|false $thumbnail_id The thumbnail ID, or false if none exists.
			 * @param int       $from_post_id The original post ID.
			 * @param int       $to_post_id   The duplicate post ID.
			 *
			 * @example
			 * ```php
			 * add_filter( 'duplicate_as_featured_image', function( $thumbnail_id, $from_id, $to_id ) {
			 *     // Don't copy featured image
			 *     return false;
			 * }, 10, 3 );
			 * ```
			 */
			$thumbnail_id = apply_filters( 'duplicate_as_featured_image', $thumbnail_id, $from_post_id, $to_post_id );
			
			if ( $thumbnail_id ) {
				set_post_thumbnail( $to_post_id, $thumbnail_id );
			}
		}

		/**
		 * Enqueue editor scripts and styles
		 *
		 * Loads JavaScript and CSS for the block editor UI.
		 *
		 * @since 0.1.0
		 * @return void
		 */
		public function enqueue_editor_assets(): void {
			/** 
			 * Safe types coming from the wp-scripts package
			 * 
			 * @var array{version:string, dependencies:array<string>} $asset_file
			 */
			$asset_file = include plugin_dir_path( __FILE__ ) . 'build/index.asset.php';

			wp_enqueue_script(
				'duplicate-as-editor',
				plugins_url( 'build/index.js', __FILE__ ),
				$asset_file['dependencies'],
				$asset_file['version'],
				true
			);

			wp_enqueue_style(
				'duplicate-as-editor',
				plugins_url( 'build/index.css', __FILE__ ),
				array( 'wp-components' ),
				$asset_file['version']
			);
		}
	}
}

if ( ! function_exists( 'duplicate_as_init' ) ) {
	/**
	 * Initialize the plugin
	 *
	 * Returns the singleton instance of the plugin class.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	function duplicate_as_init(): void {
		Duplicate_As::get_instance();
	}
	add_action( 'plugins_loaded', 'duplicate_as_init' );
}
