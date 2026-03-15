<?php
/**
 * Unit tests for Duplicate_As_Admin_Actions.
 *
 * Tests the admin action hooks and handler registration.
 *
 * @package DuplicateAs\Tests\Unit
 * @since   0.3.0
 */

class AdminActionsTest extends WP_UnitTestCase {

	/**
	 * Instance under test.
	 *
	 * @var Duplicate_As_Admin_Actions
	 */
	private Duplicate_As_Admin_Actions $admin_actions;

	/**
	 * Set up each test.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();
		$this->admin_actions = Duplicate_As_Admin_Actions::get_instance();
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
	 * Test singleton pattern returns same instance.
	 *
	 * @covers Duplicate_As_Admin_Actions::get_instance
	 * @return void
	 */
	public function test_singleton_returns_same_instance(): void {
		$instance1 = Duplicate_As_Admin_Actions::get_instance();
		$instance2 = Duplicate_As_Admin_Actions::get_instance();
		$this->assertSame( $instance1, $instance2 );
	}

	/**
	 * Test that duplicate admin action hook is registered.
	 *
	 * @covers Duplicate_As_Admin_Actions::__construct
	 * @return void
	 */
	public function test_duplicate_action_hook_registered(): void {
		$this->assertIsInt(
			has_action(
				'admin_action_duplicate_as_duplicate',
				array( $this->admin_actions, 'handle_admin_duplicate' )
			)
		);
	}

	/**
	 * Test that transform admin action hook is registered.
	 *
	 * @covers Duplicate_As_Admin_Actions::__construct
	 * @return void
	 */
	public function test_transform_action_hook_registered(): void {
		$this->assertIsInt(
			has_action(
				'admin_action_duplicate_as_transform',
				array( $this->admin_actions, 'handle_admin_transform' )
			)
		);
	}

	/**
	 * Test handle_admin_duplicate dies when no post specified.
	 *
	 * @covers Duplicate_As_Admin_Actions::handle_admin_duplicate
	 * @return void
	 */
	public function test_handle_admin_duplicate_no_post(): void {
		unset( $_GET['post'] );
		$this->expectException( WPDieException::class );
		$this->admin_actions->handle_admin_duplicate();
	}

	/**
	 * Test handle_admin_duplicate dies with invalid nonce.
	 *
	 * @covers Duplicate_As_Admin_Actions::handle_admin_duplicate
	 * @return void
	 */
	public function test_handle_admin_duplicate_invalid_nonce(): void {
		$post_id          = self::factory()->post->create();
		$_GET['post']     = $post_id;
		$_GET['_wpnonce'] = 'invalid_nonce';

		$this->expectException( WPDieException::class );
		$this->admin_actions->handle_admin_duplicate();

		unset( $_GET['post'], $_GET['_wpnonce'] );
	}

	/**
	 * Test handle_admin_transform dies when no post specified.
	 *
	 * @covers Duplicate_As_Admin_Actions::handle_admin_transform
	 * @return void
	 */
	public function test_handle_admin_transform_no_post(): void {
		unset( $_GET['post'], $_GET['target_type'] );
		$this->expectException( WPDieException::class );
		$this->admin_actions->handle_admin_transform();
	}

	/**
	 * Test handle_admin_transform dies when no target type specified.
	 *
	 * @covers Duplicate_As_Admin_Actions::handle_admin_transform
	 * @return void
	 */
	public function test_handle_admin_transform_no_target_type(): void {
		$post_id      = self::factory()->post->create();
		$_GET['post'] = $post_id;
		unset( $_GET['target_type'] );

		$this->expectException( WPDieException::class );
		$this->admin_actions->handle_admin_transform();

		unset( $_GET['post'] );
	}

	/**
	 * Test handle_admin_transform dies with invalid nonce.
	 *
	 * @covers Duplicate_As_Admin_Actions::handle_admin_transform
	 * @return void
	 */
	public function test_handle_admin_transform_invalid_nonce(): void {
		$post_id              = self::factory()->post->create();
		$_GET['post']         = $post_id;
		$_GET['target_type']  = 'page';
		$_GET['_wpnonce']     = 'invalid_nonce';

		$this->expectException( WPDieException::class );
		$this->admin_actions->handle_admin_transform();

		unset( $_GET['post'], $_GET['target_type'], $_GET['_wpnonce'] );
	}
}
