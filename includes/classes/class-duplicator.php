<?php
/**
 * Duplicator
 *
 * Core duplication logic: creates duplicate posts, copies taxonomies,
 * post meta, featured images, and handles template block conversion.
 *
 * @package DuplicateAs
 * @since   0.3.0
 */

if ( ! class_exists( 'Duplicate_As_Duplicator' ) ) {
	/**
	 * Handles the actual post duplication and content copying operations.
	 *
	 * Responsible for:
	 * - Creating duplicate posts with optional post type transformation
	 * - Copying taxonomies (only those registered for both source and target types)
	 * - Copying post meta (with exclusion list support)
	 * - Copying featured images
	 * - Converting post type templates to block format for prepending
	 *
	 * All copy operations fire filter hooks to allow customization by other plugins.
	 *
	 * @since 0.3.0
	 */
	class Duplicate_As_Duplicator {

		/**
		 * Single instance of the class.
		 *
		 * @since 0.3.0
		 * @var Duplicate_As_Duplicator|null
		 */
		private static ?Duplicate_As_Duplicator $instance = null;

		/**
		 * Private constructor to prevent direct instantiation.
		 *
		 * @since 0.3.0
		 */
		private function __construct() {}

		/**
		 * Get the singleton instance.
		 *
		 * @since 0.3.0
		 * @return Duplicate_As_Duplicator The singleton instance.
		 */
		public static function get_instance(): Duplicate_As_Duplicator {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Perform a full duplication of a post.
		 *
		 * Orchestrates the complete duplication process:
		 * 1. Creates duplicate post (with optional post type transformation)
		 * 2. Copies taxonomies (only those shared between source and target)
		 * 3. Copies post meta (excluding internal WordPress fields)
		 * 4. Copies featured image
		 * 5. Fires the `duplicate_as_after_duplicate` action
		 *
		 * @since 0.3.0
		 * @param WP_Post     $post             Original post object.
		 * @param string|null $target_post_type Target post type for transformation (null for same type).
		 * @return int|WP_Error New post ID on success, WP_Error on failure.
		 *
		 * @example
		 * $duplicator = Duplicate_As_Duplicator::get_instance();
		 * $new_id = $duplicator->duplicate( $post );
		 * // Returns: 456 (new draft post ID)
		 *
		 * @example
		 * $new_id = $duplicator->duplicate( $post, 'post' );
		 * // Returns: 457 (new draft post of type 'post')
		 */
		public function duplicate( WP_Post $post, ?string $target_post_type = null ) {
			$new_post_id = $this->create_duplicate( $post, $target_post_type );

			if ( is_wp_error( $new_post_id ) ) {
				return $new_post_id;
			}

			$source_post_type = $post->post_type;
			$final_post_type  = $target_post_type ? $target_post_type : $post->post_type;

			$this->copy_taxonomies( $post->ID, $new_post_id, $source_post_type, $final_post_type );
			$this->copy_post_meta( $post->ID, $new_post_id );
			$this->copy_featured_image( $post->ID, $new_post_id );

			/**
			 * Fires after a post has been duplicated.
			 *
			 * @since 0.1.0
			 *
			 * @param int $new_post_id The ID of the newly created duplicate post.
			 * @param int $post_id     The ID of the original post.
			 */
			do_action( 'duplicate_as_after_duplicate', $new_post_id, $post->ID );

			return $new_post_id;
		}

		/**
		 * Create a duplicate post.
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
		 */
		private function create_duplicate( WP_Post $post, ?string $target_post_type = null ) {
			$new_post_type = $target_post_type ? $target_post_type : $post->post_type;
			$post_content  = $post->post_content;

			// If transforming to a different post type, prepend template blocks.
			if ( $target_post_type && $target_post_type !== $post->post_type ) {
				$post_content = $this->prepend_target_template( $target_post_type, $post_content );
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
			 * Filters the post data before creating a duplicate.
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
		 * Prepend target post type template blocks to content.
		 *
		 * If the target post type has a registered template, converts those
		 * template blocks to serialized block markup and prepends them to
		 * the duplicated content.
		 *
		 * @since 0.3.0
		 * @param string $target_post_type Target post type slug.
		 * @param string $post_content     Original post content.
		 * @return string Modified post content with template blocks prepended.
		 */
		private function prepend_target_template( string $target_post_type, string $post_content ): string {
			$target_post_type_obj = get_post_type_object( $target_post_type );

			// @phpstan-ignore-next-line
			if ( ! $target_post_type_obj || empty( $target_post_type_obj->template ) || ! is_array( $target_post_type_obj->template ) ) {
				return $post_content;
			}

			/**
			 * This should be the shape coming from WP core.
			 *
			 * @var array<int, array{0?: string, 1?: array<string, mixed>, 2?: array<int, array<mixed>>}> $raw_template
			 */
			$raw_template     = $target_post_type_obj->template;
			$formatted_blocks = $this->convert_template_to_blocks( $raw_template );

			if ( empty( $formatted_blocks ) ) {
				return $post_content;
			}

			$template_content = serialize_blocks( $formatted_blocks );

			if ( empty( $template_content ) ) {
				return $post_content;
			}

			return $template_content . $post_content;
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
		 *   'innerBlocks'  => array<int, array>,
		 *   'innerHTML'    => string,
		 *   'innerContent' => array<int, string>
		 * ]
		 *
		 * @since 0.1.0
		 *
		 * @phpstan-param array<int, array{0?: string, 1?: array<string, mixed>, 2?: array<int, array<mixed>>}> $template_blocks
		 *
		 * @param array<int, array<mixed>> $template_blocks Post type template blocks (recursive).
		 *
		 * @phpstan-return array<int, array{blockName: string, attrs: array<string, mixed>, innerBlocks: array<int, array<mixed>>, innerHTML: string, innerContent: array<int, string>}>
		 *
		 * @return array<int, array<string, mixed>> Blocks formatted for serialize_blocks().
		 */
		private function convert_template_to_blocks( array $template_blocks ): array {
			$blocks = array();

			foreach ( $template_blocks as $template_block ) {
				$block = $this->convert_single_template_block( $template_block );
				if ( null !== $block ) {
					$blocks[] = $block;
				}
			}

			return $blocks;
		}

		/**
		 * Convert a single template block entry to block parser format.
		 *
		 * Parses one entry from a WordPress post type template array
		 * and converts it to the format expected by serialize_blocks().
		 * Recursively converts inner blocks if present.
		 *
		 * @since 0.2.0
		 *
		 * @param mixed $template_block A single template block entry.
		 *                              Expected format: array{0: string, 1?: array<string, mixed>, 2?: array<int, array>}.
		 *
		 * @phpstan-return array{blockName: string, attrs: array<string, mixed>, innerBlocks: array<int, array<mixed>>, innerHTML: string, innerContent: array<int, string>}|null
		 *
		 * @return array<string, mixed>|null Block in parser format, or null if the entry is invalid.
		 */
		private function convert_single_template_block( $template_block ): ?array {
			if ( ! is_array( $template_block ) ) {
				return null;
			}

			$block_name = isset( $template_block[0] ) && is_string( $template_block[0] ) ? $template_block[0] : '';
			if ( empty( $block_name ) ) {
				return null;
			}

			/**
			 * This should be the shape coming from WP core.
			 *
			 * @var array<string, mixed> $block_attrs
			 */
			$block_attrs = isset( $template_block[1] ) && is_array( $template_block[1] ) ? $template_block[1] : array();

			/**
			 * This should be the shape coming from WP core.
			 *
			 * @var array<int, array{0?: string, 1?: array<string, mixed>, 2?: array<int, array<mixed>>}> $inner_blocks
			 */
			$inner_blocks = isset( $template_block[2] ) && is_array( $template_block[2] ) ? $template_block[2] : array();

			return array(
				'blockName'    => $block_name,
				'attrs'        => $block_attrs,
				'innerBlocks'  => ! empty( $inner_blocks ) ? $this->convert_template_to_blocks( $inner_blocks ) : array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			);
		}

		/**
		 * Copy taxonomies from original post to duplicate.
		 *
		 * Only copies taxonomies that are registered for both source
		 * and target post types.
		 *
		 * @since 0.1.0
		 *
		 * @param int    $from_post_id     Original post ID.
		 * @param int    $to_post_id       New post ID.
		 * @param string $source_post_type Source post type slug.
		 * @param string $target_post_type Target post type slug.
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
			 * Filters the taxonomies to copy during duplication.
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
				 * Filters the terms to copy for a specific taxonomy.
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
		 * Copy post meta from original post to duplicate.
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
			 * Filters the list of meta keys to exclude from duplication.
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
					 * Filters the meta value before adding it to the duplicate post.
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
		 * Copy featured image from original post to duplicate.
		 *
		 * Sets the same featured image (thumbnail) on the duplicate post.
		 *
		 * @since 0.1.0
		 * @param int $from_post_id Original post ID.
		 * @param int $to_post_id   New post ID.
		 * @return void
		 */
		private function copy_featured_image( int $from_post_id, int $to_post_id ): void {
			$thumbnail_id = get_post_thumbnail_id( $from_post_id );

			/**
			 * Filters the featured image ID to copy.
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
	}
}
