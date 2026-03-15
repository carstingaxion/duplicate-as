<?php
/**
 * Integration tests for post type support configuration.
 *
 * Tests various configurations of add_post_type_support and their
 * effects on the duplication and transformation system.
 *
 * @package DuplicateAs\Tests\Integration
 * @since   0.3.0
 */

class PostTypeSupportFlowTest extends WP_UnitTestCase {

	/**
	 * Post type support instance.
	 *
	 * @var Duplicate_As_Post_Type_Support
	 */
	private Duplicate_As_Post_Type_Support $support;

	/**
	 * Admin user ID.
	 *
	 * @var int
	 */
	private int $admin_user_id;

	/**
	 * Set up each test.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();
		$this->support       = Duplicate_As_Post_Type_Support::get_instance();
		$this->admin_user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->admin_user_id );
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
	 * Test simple duplication-only support.
	 *
	 * add_post_type_support( 'page', 'duplicate_as' )
	 *
	 * @return void
	 */
	public function test_simple_duplication_only(): void {
		add_post_type_support( 'page', 'duplicate_as' );

		$this->assertTrue( $this->support->is_post_type_allowed( 'page' ) );
		$this->assertEmpty( $this->support->get_transform_targets( 'page' ) );
	}

	/**
	 * Test duplication and transformation support with array.
	 *
	 * add_post_type_support( 'page', 'duplicate_as', array('page', 'post') )
	 *
	 * @return void
	 */
	public function test_duplication_and_transformation(): void {
		add_post_type_support( 'page', 'duplicate_as', array( 'page', 'post' ) );

		$this->assertTrue( $this->support->is_post_type_allowed( 'page' ) );
		$targets = $this->support->get_transform_targets( 'page' );
		$this->assertContains( 'page', $targets );
		$this->assertContains( 'post', $targets );
	}

	/**
	 * Test transformation-only support (no self in array).
	 *
	 * add_post_type_support( 'page', 'duplicate_as', array('post') )
	 *
	 * @return void
	 */
	public function test_transformation_only(): void {
		add_post_type_support( 'page', 'duplicate_as', array( 'post' ) );

		$this->assertTrue( $this->support->is_post_type_allowed( 'page' ) );
		$targets = $this->support->get_transform_targets( 'page' );
		$this->assertContains( 'post', $targets );
		$this->assertNotContains( 'page', $targets );
	}

	/**
	 * Test support with multiple target types.
	 *
	 * @return void
	 */
	public function test_multiple_target_types(): void {
		add_post_type_support( 'post', 'duplicate_as', array( 'page', 'post' ) );

		$targets = $this->support->get_transform_targets( 'post' );
		$this->assertCount( 2, $targets );
		$this->assertContains( 'page', $targets );
		$this->assertContains( 'post', $targets );
	}

	/**
	 * Test that remove_post_type_support disables duplication.
	 *
	 * @return void
	 */
	public function test_remove_support(): void {
		add_post_type_support( 'post', 'duplicate_as' );
		$this->assertTrue( $this->support->is_post_type_allowed( 'post' ) );

		remove_post_type_support( 'post', 'duplicate_as' );
		$this->assertFalse( $this->support->is_post_type_allowed( 'post' ) );
	}

	/**
	 * Test support check with custom post type.
	 *
	 * @return void
	 */
	public function test_custom_post_type_support(): void {
		register_post_type(
			'test_cpt',
			array(
				'public' => true,
				'label'  => 'Test CPT',
			)
		);

		$this->assertFalse( $this->support->is_post_type_allowed( 'test_cpt' ) );

		add_post_type_support( 'test_cpt', 'duplicate_as' );
		$this->assertTrue( $this->support->is_post_type_allowed( 'test_cpt' ) );

		unregister_post_type( 'test_cpt' );
	}

	/**
	 * Test support with registered post type via supports array.
	 *
	 * @return void
	 */
	public function test_register_post_type_with_supports(): void {
		register_post_type(
			'supported_cpt',
			array(
				'public'   => true,
				'label'    => 'Supported CPT',
				'supports' => array( 'title', 'editor', 'duplicate_as' ),
			)
		);

		$this->assertTrue( $this->support->is_post_type_allowed( 'supported_cpt' ) );

		unregister_post_type( 'supported_cpt' );
	}

	/**
	 * Test that permissions are checked during REST API flow.
	 *
	 * @return void
	 */
	public function test_permissions_integration_with_rest(): void {
		add_post_type_support( 'post', 'duplicate_as', array( 'page', 'post' ) );

		$post_id     = self::factory()->post->create();
		$rest_api    = Duplicate_As_Rest_Api::get_instance();
		$permissions = Duplicate_As_Permissions::get_instance();

		// Admin should be allowed.
		wp_set_current_user( $this->admin_user_id );
		$this->assertTrue( $permissions->can_transform_to_target( 'post', 'page' ) );

		// Subscriber should be denied.
		$subscriber_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $subscriber_id );
		$this->assertFalse( $permissions->can_transform_to_target( 'post', 'page' ) );
	}

	/**
	 * Test row actions reflect post type support configuration.
	 *
	 * @return void
	 */
	public function test_row_actions_reflect_support(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as', array( 'page', 'post' ) );

		$post_id     = self::factory()->post->create();
		$post        = get_post( $post_id );
		$row_actions = Duplicate_As_Row_Actions::get_instance();

		$actions = $row_actions->add_row_actions( array(), $post );

		// Should have both duplicate and transform.
		$this->assertArrayHasKey( 'duplicate_as_duplicate', $actions );
		$this->assertArrayHasKey( 'duplicate_as_transform_page', $actions );
	}
}
