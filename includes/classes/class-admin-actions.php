<?php
/**
 * Admin Actions Handler
 *
 * Handles admin action requests for duplicating and transforming posts
 * from the WordPress admin list tables.
 *
 * @package DuplicateAs
 * @since   0.3.0
 */

if ( ! class_exists( 'Duplicate_As_Admin_Actions' ) ) {
	/**
	 * Handles admin action requests for post duplication and transformation.
	 *
	 * Processes the duplicate and transform admin actions triggered from
	 * the post list table row action links. Validates permissions and nonces,
	 * delegates to the Duplicator class, and redirects to the new post.
	 *
	 * @since 0.3.0
	 */
	class Duplicate_As_Admin_Actions {

		/**
		 * Single instance of the class.
		 *
		 * @since 0.3.0
		 * @var Duplicate_As_Admin_Actions|null
		 */
		private static ?Duplicate_As_Admin_Actions $instance = null;

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
			$this->permissions = Duplicate_As_Permissions::get_instance();
			$this->duplicator  = Duplicate_As_Duplicator::get_instance();
			$this->init_hooks();
		}

		/**
		 * Get the singleton instance.
		 *
		 * @since 0.3.0
		 * @return Duplicate_As_Admin_Actions The singleton instance.
		 */
		public static function get_instance(): Duplicate_As_Admin_Actions {
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
			add_action( 'admin_action_duplicate_as_duplicate', array( $this, 'handle_admin_duplicate' ) );
			add_action( 'admin_action_duplicate_as_transform', array( $this, 'handle_admin_transform' ) );
		}

		/**
		 * Handle duplicate action from admin list table.
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

			if ( ! $this->permissions->verify_nonce( 'duplicate_as_duplicate_' . $post_id, $nonce ) ) {
				wp_die( esc_html__( 'Security check failed.', 'duplicate-as' ) );
			}

			$post = $this->permissions->validate_post_and_permissions( $post_id );
			$this->process_duplication_and_redirect( $post );
		}

		/**
		 * Handle transform action from admin list table.
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

			if ( ! $this->permissions->verify_nonce( 'duplicate_as_transform_' . $post_id . '_' . $target_type, $nonce ) ) {
				wp_die( esc_html__( 'Security check failed.', 'duplicate-as' ) );
			}

			$post = $this->permissions->validate_post_and_permissions( $post_id, $target_type );
			$this->process_duplication_and_redirect( $post, $target_type );
		}

		/**
		 * Process duplication and redirect.
		 *
		 * Common logic for performing duplication/transformation and redirecting to edit screen.
		 *
		 * @since 0.1.0
		 * @param WP_Post     $post        Post to duplicate.
		 * @param string|null $target_type Target post type (null for duplication).
		 * @return void Dies after redirect.
		 */
		private function process_duplication_and_redirect( WP_Post $post, ?string $target_type = null ): void {
			$new_post_id = $this->duplicator->duplicate( $post, $target_type );

			if ( is_wp_error( $new_post_id ) ) {
				wp_die( esc_html( $new_post_id->get_error_message() ) );
			}

			wp_safe_redirect( (string) get_edit_post_link( $new_post_id, 'raw' ) );
			exit;
		}
	}
}
