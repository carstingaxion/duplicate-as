<?php
/**
 * GatherPress Compatibility
 *
 * Repairs the GatherPress event date row after a duplication, which the
 * generic copy cannot produce on its own.
 *
 * @package DuplicateAs
 * @since   0.5.0
 */

if ( ! class_exists( 'Duplicate_As_Gatherpress' ) ) {
	/**
	 * Keeps duplicated GatherPress events visible to GatherPress' own queries.
	 *
	 * GatherPress stores event dates twice: as the `gatherpress_datetime` post
	 * meta, and as a row in its custom `gatherpress_events` table, which is what
	 * `GatherPress\Core\Event\Query` reads. The table row is written by
	 * `Event\Setup::set_datetimes()` on `wp_after_insert_post`.
	 *
	 * During a duplication that hook fires from `wp_insert_post()` before the
	 * meta has been copied, so `set_datetimes()` finds no `gatherpress_datetime`
	 * value and returns early. The duplicate ends up with correct meta and no
	 * table row.
	 *
	 * The window is narrow, and worth stating precisely rather than overselling:
	 * every later save repairs it, including publishing, since
	 * `wp_publish_post()` fires `wp_after_insert_post()` too and the meta is in
	 * place by then. So only an untouched duplicated draft is affected. While it
	 * lasts, the editor looks correct, because GatherPress renders from the
	 * derived `gatherpress_datetime_*` meta that the duplication copies, and the
	 * admin list still shows the event, because the query joins the table with a
	 * LEFT JOIN. What is off is the ordering, which reads a NULL datetime.
	 *
	 * This closes that window rather than leaving the duplicate in a state its
	 * own plugin would never produce.
	 *
	 * A general duplicator cannot be expected to know about that table, so the
	 * repair lives here, on the seam the plugin already provides, rather than in
	 * the copy routine itself.
	 *
	 * @since 0.5.0
	 */
	class Duplicate_As_Gatherpress {

		/**
		 * Single instance of the class.
		 *
		 * @since 0.5.0
		 * @var Duplicate_As_Gatherpress|null
		 */
		private static ?Duplicate_As_Gatherpress $instance = null;

		/**
		 * Private constructor to prevent direct instantiation.
		 *
		 * @since 0.5.0
		 */
		private function __construct() {
			$this->init_hooks();
		}

		/**
		 * Get the singleton instance.
		 *
		 * @since 0.5.0
		 * @return Duplicate_As_Gatherpress The singleton instance.
		 */
		public static function get_instance(): Duplicate_As_Gatherpress {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Initialize WordPress hooks.
		 *
		 * @since 0.5.0
		 * @return void
		 */
		private function init_hooks(): void {
			add_action( 'duplicate_as_after_duplicate', array( $this, 'save_event_datetimes' ), 10, 1 );
		}

		/**
		 * Write the GatherPress event date row for a freshly duplicated event.
		 *
		 * Silently does nothing when GatherPress is not active or when the
		 * duplicate is not an event, so this is safe to run unconditionally.
		 * Re-reads the meta that the duplication has already copied, so a
		 * transformation into a post type that carries no event date leaves no
		 * row behind either.
		 *
		 * @since 0.5.0
		 * @param int $new_post_id The ID of the newly created duplicate post.
		 * @return void
		 *
		 * @example
		 * // To opt out, for example when another integration owns the row:
		 * remove_action(
		 *     'duplicate_as_after_duplicate',
		 *     array( Duplicate_As_Gatherpress::get_instance(), 'save_event_datetimes' )
		 * );
		 */
		public function save_event_datetimes( int $new_post_id ): void {
			if ( ! $this->can_save_event_datetimes( $new_post_id ) ) {
				return;
			}

			// GatherPress is an optional companion, not a dependency, so its
			// classes are unknown to static analysis. The call is reached only
			// once can_save_event_datetimes() has confirmed the class exists.

			// @phpstan-ignore-next-line
			\GatherPress\Core\Event\Setup::get_instance()->set_datetimes( $new_post_id );
		}

		/**
		 * Whether the event date row can and should be written for a post.
		 *
		 * True only when GatherPress is active and the post is of a type that
		 * carries an event date. Public so the decision can be asserted on its
		 * own, and so an integration can ask the same question.
		 *
		 * @since 0.5.0
		 * @param int $post_id Post ID to check.
		 * @return bool True when the row should be written, false otherwise.
		 */
		public function can_save_event_datetimes( int $post_id ): bool {
			if ( ! class_exists( '\GatherPress\Core\Event\Setup' ) ) {
				return false;
			}

			return post_type_supports( (string) get_post_type( $post_id ), 'gatherpress-event-date' );
		}
	}
}
