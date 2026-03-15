<?php
/**
 * Row Actions
 *
 * Adds duplicate and transform action links to the post list table
 * row actions (quick edit area).
 *
 * @package DuplicateAs
 * @since   0.3.0
 */

if ( ! class_exists( 'Duplicate_As_Row_Actions' ) ) {
	/**
	 * Manages post list table row action links for duplication and transformation.
	 *
	 * Adds "Duplicate" and "Duplicate as {Type}" links to the quick action
	 * area below each post in the admin list tables.
	 *
	 * @since 0.3.0
	 */
	class Duplicate_As_Row_Actions {

		/**
		 * Single instance of the class.
		 *
		 * @since 0.3.0
		 * @var Duplicate_As_Row_Actions|null
		 */
		private static ?Duplicate_As_Row_Actions $instance = null;

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
			$this->init_hooks();
		}

		/**
		 * Get the singleton instance.
		 *
		 * @since 0.3.0
		 * @return Duplicate_As_Row_Actions The singleton instance.
		 */
		public static function get_instance(): Duplicate_As_Row_Actions {
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
			add_filter( 'post_row_actions', array( $this, 'add_row_actions' ), 10, 2 );
			add_filter( 'page_row_actions', array( $this, 'add_row_actions' ), 10, 2 );
		}

		/**
		 * Add action links to post list table.
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
		 * @example Modified actions (with duplicate and transform):
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

			$targets = $this->post_type_support->get_transform_targets( $post->post_type );
			$actions = $this->add_duplicate_action( $actions, $post, $targets );
			$actions = $this->add_transform_actions( $actions, $post, $targets );

			return $actions;
		}

		/**
		 * Check if row actions should be added for post.
		 *
		 * Validates post type support and user permissions.
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

			if ( ! $this->post_type_support->is_post_type_allowed( $post->post_type ) ) {
				return false;
			}

			if ( ! current_user_can( $post_type_obj->cap->edit_post, $post->ID ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Add duplicate action link to row actions.
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
		 * Add transform action links to row actions.
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
							/* translators: Duplicate {post_title} into {target post_type singular label} */
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
	}
}
