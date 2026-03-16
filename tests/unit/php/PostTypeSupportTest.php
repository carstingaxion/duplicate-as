<?php
/**
 * Unit tests for Duplicate_As_Post_Type_Support.
 *
 * Tests the post type support registration, querying, and
 * transform target resolution logic.
 *
 * @package DuplicateAs\Tests\Unit
 * @since   0.3.0
 */

/**
 * Tests for Duplicate_As_Post_Type_Support
 */
class PostTypeSupportTest extends WP_UnitTestCase {

	/**
	 * Instance under test.
	 *
	 * @var Duplicate_As_Post_Type_Support
	 */
	private Duplicate_As_Post_Type_Support $instance;

	/**
	 * Set up each test.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();
		$this->instance = Duplicate_As_Post_Type_Support::get_instance();
	}

	/**
	 * Tear down each test.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		// Clean up custom post type support added during tests.
		remove_post_type_support( 'post', 'duplicate_as' );
		remove_post_type_support( 'page', 'duplicate_as' );
		parent::tear_down();
	}

	/**
	 * Test that default post type support is added for pages.
	 *
	 * @covers Duplicate_As_Post_Type_Support::add_default_support
	 * @return void
	 */
	public function test_default_support_for_page(): void {
		$this->instance->add_default_support();
		$this->assertTrue( post_type_supports( 'page', 'duplicate_as' ) );
	}

	/**
	 * Test that default post type support is added for posts.
	 *
	 * @covers Duplicate_As_Post_Type_Support::add_default_support
	 * @return void
	 */
	public function test_default_support_for_post(): void {
		$this->instance->add_default_support();
		$this->assertTrue( post_type_supports( 'post', 'duplicate_as' ) );
	}

	/**
	 * Test is_post_type_allowed returns true for supported type.
	 *
	 * @covers Duplicate_As_Post_Type_Support::is_post_type_allowed
	 * @return void
	 */
	public function test_is_post_type_allowed_returns_true(): void {
		add_post_type_support( 'post', 'duplicate_as' );
		$this->assertTrue( $this->instance->is_post_type_allowed( 'post' ) );
	}

	/**
	 * Test is_post_type_allowed returns false for unsupported type.
	 *
	 * @covers Duplicate_As_Post_Type_Support::is_post_type_allowed
	 * @return void
	 */
	public function test_is_post_type_allowed_returns_false(): void {
		remove_post_type_support( 'post', 'duplicate_as' );
		$this->assertFalse( $this->instance->is_post_type_allowed( 'post' ) );
	}

	/**
	 * Test is_post_type_allowed returns false for nonexistent type.
	 *
	 * @covers Duplicate_As_Post_Type_Support::is_post_type_allowed
	 * @return void
	 */
	public function test_is_post_type_allowed_nonexistent_type(): void {
		$this->assertFalse( $this->instance->is_post_type_allowed( 'nonexistent_type' ) );
	}

	/**
	 * Test get_transform_targets returns empty array for simple support.
	 *
	 * @covers Duplicate_As_Post_Type_Support::get_transform_targets
	 * @return void
	 */
	public function test_get_transform_targets_simple_support(): void {
		add_post_type_support( 'page', 'duplicate_as' );
		$targets = $this->instance->get_transform_targets( 'page' );
		$this->assertIsArray( $targets );
		$this->assertEmpty( $targets );
	}

	/**
	 * Test get_transform_targets returns targets for array support.
	 *
	 * @covers Duplicate_As_Post_Type_Support::get_transform_targets
	 * @return void
	 */
	public function test_get_transform_targets_with_array(): void {
		add_post_type_support( 'post', 'duplicate_as', array( 'page', 'post' ) );
		$targets = $this->instance->get_transform_targets( 'post' );
		$this->assertContains( 'page', $targets );
		$this->assertContains( 'post', $targets );
	}

	/**
	 * Test get_transform_targets filters out non-existent post types.
	 *
	 * @covers Duplicate_As_Post_Type_Support::get_transform_targets
	 * @return void
	 */
	public function test_get_transform_targets_filters_nonexistent(): void {
		add_post_type_support( 'post', 'duplicate_as', array( 'page', 'nonexistent_type_xyz' ) );
		$targets = $this->instance->get_transform_targets( 'post' );
		$this->assertContains( 'page', $targets );
		$this->assertNotContains( 'nonexistent_type_xyz', $targets );
	}

	/**
	 * Test get_transform_targets returns empty for unsupported type.
	 *
	 * @covers Duplicate_As_Post_Type_Support::get_transform_targets
	 * @return void
	 */
	public function test_get_transform_targets_unsupported_type(): void {
		remove_post_type_support( 'post', 'duplicate_as' );
		$targets = $this->instance->get_transform_targets( 'post' );
		$this->assertEmpty( $targets );
	}

	/**
	 * Test singleton pattern returns same instance.
	 *
	 * @covers Duplicate_As_Post_Type_Support::get_instance
	 * @return void
	 */
	public function test_singleton_returns_same_instance(): void {
		$instance1 = Duplicate_As_Post_Type_Support::get_instance();
		$instance2 = Duplicate_As_Post_Type_Support::get_instance();
		$this->assertSame( $instance1, $instance2 );
	}
}
