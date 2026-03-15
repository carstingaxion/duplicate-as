<?php
/**
 * Unit tests for Duplicate_As_Row_Actions.
 *
 * Tests the row action links added to the post list table.
 *
 * @package DuplicateAs\Tests\Unit
 * @since   0.3.0
 */

class RowActionsTest extends WP_UnitTestCase {

	/**
	 * Instance under test.
	 *
	 * @var Duplicate_As_Row_Actions
	 */
	private Duplicate_As_Row_Actions $row_actions;

	/**
	 * Admin user ID.
	 *
	 * @var int
	 */
	private int $admin_user_id;

	/**
	 * Subscriber user ID.
	 *
	 * @var int
	 */
	private int $subscriber_user_id;

	/**
	 * Set up each test.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();
		$this->row_actions        = Duplicate_As_Row_Actions::get_instance();
		$this->admin_user_id      = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->subscriber_user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
	}

	/**
	 * Tear down each test.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		remove_post_type_support( 'post', 'duplicate_as' );
		remove_post_type_support( 'page', 'duplicate_as' );
		parent::tear_down();
	}

	/**
	 * Test add_row_actions adds duplicate link for supported type.
	 *
	 * @covers Duplicate_As_Row_Actions::add_row_actions
	 * @return void
	 */
	public function test_add_row_actions_adds_duplicate_link(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as' );

		$post_id = self::factory()->post->create();
		$post    = get_post( $post_id );

		$actions = $this->row_actions->add_row_actions( array(), $post );

		$this->assertArrayHasKey( 'duplicate_as_duplicate', $actions );
		$this->assertStringContainsString( 'Duplicate', $actions['duplicate_as_duplicate'] );
	}

	/**
	 * Test add_row_actions does not add link for unsupported type.
	 *
	 * @covers Duplicate_As_Row_Actions::add_row_actions
	 * @return void
	 */
	public function test_add_row_actions_skips_unsupported_type(): void {
		wp_set_current_user( $this->admin_user_id );
		remove_post_type_support( 'post', 'duplicate_as' );

		$post_id = self::factory()->post->create();
		$post    = get_post( $post_id );

		$actions = $this->row_actions->add_row_actions( array(), $post );

		$this->assertArrayNotHasKey( 'duplicate_as_duplicate', $actions );
	}

	/**
	 * Test add_row_actions does not add link for subscriber.
	 *
	 * @covers Duplicate_As_Row_Actions::add_row_actions
	 * @return void
	 */
	public function test_add_row_actions_skips_for_subscriber(): void {
		wp_set_current_user( $this->subscriber_user_id );
		add_post_type_support( 'post', 'duplicate_as' );

		$post_id = self::factory()->post->create();
		$post    = get_post( $post_id );

		$actions = $this->row_actions->add_row_actions( array(), $post );

		$this->assertArrayNotHasKey( 'duplicate_as_duplicate', $actions );
	}

	/**
	 * Test add_row_actions adds transform links for array targets.
	 *
	 * @covers Duplicate_As_Row_Actions::add_row_actions
	 * @return void
	 */
	public function test_add_row_actions_adds_transform_links(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as', array( 'page', 'post' ) );

		$post_id = self::factory()->post->create();
		$post    = get_post( $post_id );

		$actions = $this->row_actions->add_row_actions( array(), $post );

		// Should have duplicate (post → post) and transform (post → page).
		$this->assertArrayHasKey( 'duplicate_as_duplicate', $actions );
		$this->assertArrayHasKey( 'duplicate_as_transform_page', $actions );
	}

	/**
	 * Test add_row_actions preserves existing actions.
	 *
	 * @covers Duplicate_As_Row_Actions::add_row_actions
	 * @return void
	 */
	public function test_add_row_actions_preserves_existing(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as' );

		$post_id    = self::factory()->post->create();
		$post       = get_post( $post_id );
		$existing   = array( 'edit' => '<a href="#">Edit</a>' );

		$actions = $this->row_actions->add_row_actions( $existing, $post );

		$this->assertArrayHasKey( 'edit', $actions );
		$this->assertArrayHasKey( 'duplicate_as_duplicate', $actions );
	}

	/**
	 * Test transform links contain proper nonce URL.
	 *
	 * @covers Duplicate_As_Row_Actions::add_row_actions
	 * @return void
	 */
	public function test_transform_link_contains_nonce(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as', array( 'page', 'post' ) );

		$post_id = self::factory()->post->create();
		$post    = get_post( $post_id );

		$actions = $this->row_actions->add_row_actions( array(), $post );

		$this->assertStringContainsString( '_wpnonce', $actions['duplicate_as_transform_page'] );
		$this->assertStringContainsString( 'target_type=page', $actions['duplicate_as_transform_page'] );
	}

	/**
	 * Test duplicate link contains proper aria-label.
	 *
	 * @covers Duplicate_As_Row_Actions::add_row_actions
	 * @return void
	 */
	public function test_duplicate_link_has_aria_label(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as' );

		$post_id = self::factory()->post->create( array( 'post_title' => 'Test Title' ) );
		$post    = get_post( $post_id );

		$actions = $this->row_actions->add_row_actions( array(), $post );

		$this->assertStringContainsString( 'aria-label', $actions['duplicate_as_duplicate'] );
		$this->assertStringContainsString( 'Test Title', $actions['duplicate_as_duplicate'] );
	}

	/**
	 * Test add_row_actions works for pages.
	 *
	 * @covers Duplicate_As_Row_Actions::add_row_actions
	 * @return void
	 */
	public function test_add_row_actions_works_for_pages(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'page', 'duplicate_as' );

		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$page    = get_post( $page_id );

		$actions = $this->row_actions->add_row_actions( array(), $page );

		$this->assertArrayHasKey( 'duplicate_as_duplicate', $actions );
	}

	/**
	 * Test singleton pattern returns same instance.
	 *
	 * @covers Duplicate_As_Row_Actions::get_instance
	 * @return void
	 */
	public function test_singleton_returns_same_instance(): void {
		$instance1 = Duplicate_As_Row_Actions::get_instance();
		$instance2 = Duplicate_As_Row_Actions::get_instance();
		$this->assertSame( $instance1, $instance2 );
	}
}
