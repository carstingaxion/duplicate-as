<?php
/**
 * Unit tests for Duplicate_As_Permissions.
 *
 * Tests permission verification, nonce validation, and capability checks
 * for duplication and transformation operations.
 *
 * @package DuplicateAs\Tests\Unit
 * @since   0.3.0
 */

class PermissionsTest extends WP_UnitTestCase {

	/**
	 * Instance under test.
	 *
	 * @var Duplicate_As_Permissions
	 */
	private Duplicate_As_Permissions $permissions;

	/**
	 * Admin user ID for capability tests.
	 *
	 * @var int
	 */
	private int $admin_user_id;

	/**
	 * Subscriber user ID for capability tests.
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
		$this->permissions        = Duplicate_As_Permissions::get_instance();
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
	 * Test verify_nonce returns true for valid nonce.
	 *
	 * @covers Duplicate_As_Permissions::verify_nonce
	 * @return void
	 */
	public function test_verify_nonce_valid(): void {
		$action = 'test_action';
		$nonce  = wp_create_nonce( $action );
		$this->assertTrue( $this->permissions->verify_nonce( $action, $nonce ) );
	}

	/**
	 * Test verify_nonce returns false for invalid nonce.
	 *
	 * @covers Duplicate_As_Permissions::verify_nonce
	 * @return void
	 */
	public function test_verify_nonce_invalid(): void {
		$this->assertFalse( $this->permissions->verify_nonce( 'test_action', 'invalid_nonce' ) );
	}

	/**
	 * Test verify_nonce returns false for null nonce.
	 *
	 * @covers Duplicate_As_Permissions::verify_nonce
	 * @return void
	 */
	public function test_verify_nonce_null(): void {
		$this->assertFalse( $this->permissions->verify_nonce( 'test_action', null ) );
	}

	/**
	 * Test can_edit_source_post returns true for admin.
	 *
	 * @covers Duplicate_As_Permissions::can_edit_source_post
	 * @return void
	 */
	public function test_can_edit_source_post_admin(): void {
		wp_set_current_user( $this->admin_user_id );
		$post_id = self::factory()->post->create();
		$post    = get_post( $post_id );
		$this->assertTrue( $this->permissions->can_edit_source_post( $post, $post_id ) );
	}

	/**
	 * Test can_edit_source_post returns false for subscriber.
	 *
	 * @covers Duplicate_As_Permissions::can_edit_source_post
	 * @return void
	 */
	public function test_can_edit_source_post_subscriber(): void {
		wp_set_current_user( $this->subscriber_user_id );
		$post_id = self::factory()->post->create();
		$post    = get_post( $post_id );
		$this->assertFalse( $this->permissions->can_edit_source_post( $post, $post_id ) );
	}

	/**
	 * Test can_create_posts returns true for admin on post type.
	 *
	 * @covers Duplicate_As_Permissions::can_create_posts
	 * @return void
	 */
	public function test_can_create_posts_admin(): void {
		wp_set_current_user( $this->admin_user_id );
		$this->assertTrue( $this->permissions->can_create_posts( 'post' ) );
	}

	/**
	 * Test can_create_posts returns false for subscriber on post type.
	 *
	 * @covers Duplicate_As_Permissions::can_create_posts
	 * @return void
	 */
	public function test_can_create_posts_subscriber(): void {
		wp_set_current_user( $this->subscriber_user_id );
		$this->assertFalse( $this->permissions->can_create_posts( 'post' ) );
	}

	/**
	 * Test can_create_posts returns false for nonexistent post type.
	 *
	 * @covers Duplicate_As_Permissions::can_create_posts
	 * @return void
	 */
	public function test_can_create_posts_nonexistent_type(): void {
		wp_set_current_user( $this->admin_user_id );
		$this->assertFalse( $this->permissions->can_create_posts( 'nonexistent_type_xyz' ) );
	}

	/**
	 * Test can_transform_to_target returns true for allowed target.
	 *
	 * @covers Duplicate_As_Permissions::can_transform_to_target
	 * @return void
	 */
	public function test_can_transform_to_target_allowed(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as', array( 'page', 'post' ) );
		$this->assertTrue( $this->permissions->can_transform_to_target( 'post', 'page' ) );
	}

	/**
	 * Test can_transform_to_target returns false for disallowed target.
	 *
	 * @covers Duplicate_As_Permissions::can_transform_to_target
	 * @return void
	 */
	public function test_can_transform_to_target_disallowed(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as' );
		$this->assertFalse( $this->permissions->can_transform_to_target( 'post', 'page' ) );
	}

	/**
	 * Test can_transform_to_target returns false for subscriber.
	 *
	 * @covers Duplicate_As_Permissions::can_transform_to_target
	 * @return void
	 */
	public function test_can_transform_to_target_subscriber(): void {
		wp_set_current_user( $this->subscriber_user_id );
		add_post_type_support( 'post', 'duplicate_as', array( 'page', 'post' ) );
		$this->assertFalse( $this->permissions->can_transform_to_target( 'post', 'page' ) );
	}

	/**
	 * Test validate_post_and_permissions returns post for valid request.
	 *
	 * @covers Duplicate_As_Permissions::validate_post_and_permissions
	 * @return void
	 */
	public function test_validate_post_and_permissions_valid(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as' );
		$post_id = self::factory()->post->create();
		$result  = $this->permissions->validate_post_and_permissions( $post_id );
		$this->assertInstanceOf( WP_Post::class, $result );
		$this->assertEquals( $post_id, $result->ID );
	}

	/**
	 * Test validate_post_and_permissions dies for nonexistent post.
	 *
	 * @covers Duplicate_As_Permissions::validate_post_and_permissions
	 * @return void
	 */
	public function test_validate_post_and_permissions_nonexistent(): void {
		wp_set_current_user( $this->admin_user_id );
		$this->expectException( WPDieException::class );
		$this->permissions->validate_post_and_permissions( 999999 );
	}

	/**
	 * Test validate_post_and_permissions dies for unsupported post type.
	 *
	 * @covers Duplicate_As_Permissions::validate_post_and_permissions
	 * @return void
	 */
	public function test_validate_post_and_permissions_unsupported_type(): void {
		wp_set_current_user( $this->admin_user_id );
		remove_post_type_support( 'post', 'duplicate_as' );
		$post_id = self::factory()->post->create();
		$this->expectException( WPDieException::class );
		$this->permissions->validate_post_and_permissions( $post_id );
	}

	/**
	 * Test validate_post_and_permissions dies for subscriber.
	 *
	 * @covers Duplicate_As_Permissions::validate_post_and_permissions
	 * @return void
	 */
	public function test_validate_post_and_permissions_no_permission(): void {
		wp_set_current_user( $this->subscriber_user_id );
		add_post_type_support( 'post', 'duplicate_as' );
		$post_id = self::factory()->post->create();
		$this->expectException( WPDieException::class );
		$this->permissions->validate_post_and_permissions( $post_id );
	}

	/**
	 * Test validate_post_and_permissions with valid transform target.
	 *
	 * @covers Duplicate_As_Permissions::validate_post_and_permissions
	 * @return void
	 */
	public function test_validate_post_and_permissions_with_valid_target(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as', array( 'page', 'post' ) );
		$post_id = self::factory()->post->create();
		$result  = $this->permissions->validate_post_and_permissions( $post_id, 'page' );
		$this->assertInstanceOf( WP_Post::class, $result );
	}

	/**
	 * Test validate_post_and_permissions dies for invalid transform target.
	 *
	 * @covers Duplicate_As_Permissions::validate_post_and_permissions
	 * @return void
	 */
	public function test_validate_post_and_permissions_invalid_target(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as' );
		$post_id = self::factory()->post->create();
		$this->expectException( WPDieException::class );
		$this->permissions->validate_post_and_permissions( $post_id, 'page' );
	}

	/**
	 * Test singleton pattern returns same instance.
	 *
	 * @covers Duplicate_As_Permissions::get_instance
	 * @return void
	 */
	public function test_singleton_returns_same_instance(): void {
		$instance1 = Duplicate_As_Permissions::get_instance();
		$instance2 = Duplicate_As_Permissions::get_instance();
		$this->assertSame( $instance1, $instance2 );
	}
}
