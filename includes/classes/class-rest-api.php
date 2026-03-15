<?php
/**
 * REST API Controller
 *
 * Registers and handles the REST API endpoint for post duplication
 * and transformation.
 *
 * @package DuplicateAs
 * @since   0.3.0
 */

if ( ! class_exists( 'Duplicate_As_Rest_Api' ) ) {
	/**
	 * Manages the REST API endpoint for post duplication.
	 *
	 * Registers the /duplicate/{id} endpoint and handles incoming requests
	 * by delegating to the Permissions and Duplicator classes.
	 *
	 * Endpoint: POST /wp-json/duplicate-as/v1/duplicate/{id}
	 *
	 * @since 0.3.0
	 */
	class Duplicate_As_Rest_Api {

		/**
		 * Single instance of the class.
		 *
		 * @since 0.3.0
		 * @var Duplicate_As_Rest_Api|null
		 */
		private static ?Duplicate_As_Rest_Api $instance = null;

		/**
		 * REST API namespace.
		 *
		 * @since 0.1.0
		 * @var string
		 */
		const REST_NAMESPACE = 'duplicate-as/v1';

		/**
		 * Post type support instance.
		 *
		 * @since 0.3.0
		 * @var Duplicate_As_Post_Type_Support
		 */
		private Duplicate_As_Post_Type_Support $post_type_support;

		/**
		 * Permissions instance.
		 *
		 * @since 0.3.0
		 * @var Duplicate_As_Permissions
		 */
		private Duplicate_As_Permissions $permissions;

		/**
		 * Duplicator instance.
		 *
		 * @since 0.3.0
		 * @var Duplicate_As_Duplicator
		 */
		private Duplicate_As_Duplicator $duplicator;

		/**
		 * Private constructor to prevent direct instantiation.
		 *
		 * @since 0.3.0
		 */
		private function __construct() {
			$this->post_type_support = Duplicate_As_Post_Type_Support::get_instance();
			$this->permissions       = Duplicate_As_Permissions::get_instance();
			$this->duplicator        = Duplicate_As_Duplicator::get_instance();
			$this->init_hooks();
		}

		/**
		 * Get the singleton instance.
		 *
		 * @since 0.3.0
		 * @return Duplicate_As_Rest_Api The singleton instance.
		 */
		public static function get_instance(): Duplicate_As_Rest_Api {
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
			add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		}

		/**
		 * Register REST API routes.
		 *
		 * Registers the /duplicate/{id} endpoint for post duplication.
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
					'callback'            => array( $this, 'handle_duplicate' ),
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
		 * Check if user has permission to duplicate posts.
		 *
		 * Verifies:
		 * - Post exists
		 * - Post type allows duplication
		 * - User can edit the source post
		 * - User can create posts in the target post type (if transforming)
		 * - Target post type is in allowed targets list
		 *
		 * @since 0.1.0
		 * @param WP_REST_Request<array{id:int, target_post_type:string|null}> $request Request object.
		 * @return bool True if user has permission, false otherwise.
		 */
		public function check_permission( WP_REST_Request $request ): bool {
			$post_id          = is_int( $request->get_param( 'id' ) ) ? $request->get_param( 'id' ) : 0;
			$target_post_type = is_string( $request->get_param( 'target_post_type' ) ) ? $request->get_param( 'target_post_type' ) : null;
			$post             = get_post( $post_id );

			if ( ! $post || ! $this->post_type_support->is_post_type_allowed( $post->post_type ) ) {
				return false;
			}

			if ( ! $this->permissions->can_edit_source_post( $post, $post_id ) ) {
				return false;
			}

			if ( $target_post_type ) {
				return $this->permissions->can_transform_to_target( $post->post_type, $target_post_type );
			}

			return $this->permissions->can_create_posts( $post->post_type );
		}

		/**
		 * Handle the duplicate REST API request.
		 *
		 * Orchestrates the duplication process via the Duplicator class and
		 * returns the REST response.
		 *
		 * @since 0.1.0
		 * @param WP_REST_Request<array{id:int, target_post_type:string|null}> $request Request object.
		 * @return WP_REST_Response|WP_Error Response with new post data or error.
		 *
		 * @example Success Response:
		 * {
		 *   "success": true,
		 *   "new_post_id": 456,
		 *   "edit_url": "https://example.com/wp-admin/post.php?post=456&action=edit",
		 *   "is_transform": true
		 * }
		 */
		public function handle_duplicate( WP_REST_Request $request ): WP_REST_Response|WP_Error {
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

			if ( ! $this->post_type_support->is_post_type_allowed( $post->post_type ) ) {
				return new WP_Error(
					'post_type_not_allowed',
					__( 'This post type cannot be duplicated.', 'duplicate-as' ),
					array( 'status' => 403 )
				);
			}

			// Verify target post type is allowed.
			if ( $target_post_type ) {
				$allowed_targets = $this->post_type_support->get_transform_targets( $post->post_type );
				if ( ! in_array( $target_post_type, $allowed_targets, true ) ) {
					return new WP_Error(
						'invalid_target_post_type',
						__( 'Target post type is not allowed for this post type.', 'duplicate-as' ),
						array( 'status' => 403 )
					);
				}
			}

			$new_post_id = $this->duplicator->duplicate( $post, $target_post_type );

			if ( is_wp_error( $new_post_id ) ) {
				return $new_post_id;
			}

			return rest_ensure_response(
				array(
					'success'      => true,
					'new_post_id'  => $new_post_id,
					'edit_url'     => get_edit_post_link( $new_post_id, 'raw' ),
					'is_transform' => ! empty( $target_post_type ) && $target_post_type !== $post->post_type,
				)
			);
		}
	}
}
