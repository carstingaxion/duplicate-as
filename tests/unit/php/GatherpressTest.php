<?php
/**
 * Unit tests for Duplicate_As_Gatherpress.
 *
 * Tests the GatherPress event date repair that runs after a duplication.
 * GatherPress itself is not installed in this suite, so each test isolates
 * one of the two guards by satisfying the other where it can.
 *
 * @package DuplicateAs\Tests\Unit
 * @since   0.5.0
 */

/**
 * Tests for Duplicate_As_Gatherpress.
 */
class GatherpressTest extends WP_UnitTestCase {

	/**
	 * Instance under test.
	 *
	 * @var Duplicate_As_Gatherpress
	 */
	private Duplicate_As_Gatherpress $gatherpress;

	/**
	 * Set up each test.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();
		$this->gatherpress = Duplicate_As_Gatherpress::get_instance();
	}

	/**
	 * Test singleton pattern returns same instance.
	 *
	 * @covers Duplicate_As_Gatherpress::get_instance
	 * @return void
	 */
	public function test_singleton_returns_same_instance(): void {
		$instance1 = Duplicate_As_Gatherpress::get_instance();
		$instance2 = Duplicate_As_Gatherpress::get_instance();
		$this->assertSame( $instance1, $instance2 );
	}

	/**
	 * Test that the after-duplicate hook is registered.
	 *
	 * @covers Duplicate_As_Gatherpress::__construct
	 * @return void
	 */
	public function test_after_duplicate_hook_registered(): void {
		$this->assertIsInt(
			has_action( 'duplicate_as_after_duplicate', array( $this->gatherpress, 'save_event_datetimes' ) )
		);
	}

	/**
	 * Test that the action passes exactly one argument to the callback.
	 *
	 * `duplicate_as_after_duplicate` fires with two arguments while the
	 * callback declares one. Registering it for two would pass the source post
	 * ID into a parameter that does not exist and fatal on every duplication.
	 *
	 * @covers Duplicate_As_Gatherpress::__construct
	 * @return void
	 */
	public function test_hook_is_registered_for_one_argument(): void {
		global $wp_filter;

		$callback = array( $this->gatherpress, 'save_event_datetimes' );
		$priority = has_action( 'duplicate_as_after_duplicate', $callback );
		$key      = _wp_filter_build_unique_id( 'duplicate_as_after_duplicate', $callback, $priority );

		$this->assertSame(
			1,
			$wp_filter['duplicate_as_after_duplicate']->callbacks[ $priority ][ $key ]['accepted_args']
		);
	}

	/**
	 * Test that an event post type is still refused while GatherPress is absent.
	 *
	 * The post type guard is satisfied here, so a false result isolates the
	 * class_exists guard. Without it the callback would fatal on a site that
	 * registered the support without the plugin, for example after
	 * deactivating GatherPress while duplicate-as stays active.
	 *
	 * @covers Duplicate_As_Gatherpress::can_save_event_datetimes
	 * @return void
	 */
	public function test_cannot_save_event_datetimes_when_gatherpress_is_absent(): void {
		$this->assertFalse(
			class_exists( '\GatherPress\Core\Event\Setup' ),
			'Failed to assert GatherPress is absent, which this test depends on.'
		);

		register_post_type(
			'da_fake_event',
			array(
				'public'   => true,
				'supports' => array( 'title', 'editor', 'gatherpress-event-date' ),
			)
		);

		$post_id = self::factory()->post->create( array( 'post_type' => 'da_fake_event' ) );

		$this->assertTrue(
			post_type_supports( 'da_fake_event', 'gatherpress-event-date' ),
			'Failed to assert the fixture post type carries event date support.'
		);

		$this->assertFalse( $this->gatherpress->can_save_event_datetimes( $post_id ) );
	}

	/**
	 * Test that a post type without event date support is refused.
	 *
	 * Guards against a transformation into a plain post type writing an event
	 * row for something that is not an event.
	 *
	 * @covers Duplicate_As_Gatherpress::can_save_event_datetimes
	 * @return void
	 */
	public function test_cannot_save_event_datetimes_for_a_post_type_without_event_date_support(): void {
		$post_id = self::factory()->post->create( array( 'post_type' => 'page' ) );

		$this->assertFalse(
			post_type_supports( 'page', 'gatherpress-event-date' ),
			'Failed to assert pages carry no event date support.'
		);

		$this->assertFalse( $this->gatherpress->can_save_event_datetimes( $post_id ) );
	}

	/**
	 * Test that the callback returns without touching GatherPress when refused.
	 *
	 * With GatherPress absent, reaching the call would be a fatal error rather
	 * than a failed assertion, so this test passing is the assertion. It also
	 * proves the guard is actually consulted by `save_event_datetimes()` and
	 * not only exposed as a predicate.
	 *
	 * @covers Duplicate_As_Gatherpress::save_event_datetimes
	 * @return void
	 */
	public function test_save_event_datetimes_returns_early_when_refused(): void {
		$post_id = self::factory()->post->create();

		$this->assertFalse( $this->gatherpress->can_save_event_datetimes( $post_id ) );

		$this->gatherpress->save_event_datetimes( $post_id );

		$this->assertSame(
			'post',
			get_post_type( $post_id ),
			'Failed to assert the post survived the call unchanged.'
		);
	}
}
