<?php
/**
 * Unit tests for Duplicate_As_Rest_Api.
 *
 * Tests the REST API endpoint registration, permission checks,
 * and request handling for post duplication.
 *
 * @package DuplicateAs\Tests\Unit
 * @since   0.3.0
 */

class RestApiTest extends WP_UnitTestCase {

	/**
	 * Instance under test.
	 *
	 * @var Duplicate_As_Rest_Api
	 */
	private Duplicate_As_Rest_Api $rest_api;

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
		$this->rest_api           = Duplicate_As_Rest_Api::get_instance();
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
	 * Test REST route is registered.
	 *
	 * @covers Duplicate_As_Rest_Api::register_rest_routes
	 * @return void
	 */
	public function test_rest_route_registered(): void {
		// Initialize the REST server which triggers rest_api_init,
		// where our routes are registered via the hook.
		global $wp_rest_server;
		$wp_rest_server = new WP_REST_Server();
		do_action( 'rest_api_init' );

		$routes = $wp_rest_server->get_routes();
		$this->assertArrayHasKey( '/duplicate-as/v1/duplicate/(?P<id>\\d+)', $routes );

		$wp_rest_server = null;
	}

	/**
	 * Test check_permission returns true for admin with supported type.
	 *
	 * @covers Duplicate_As_Rest_Api::check_permission
	 * @return void
	 */
	public function test_check_permission_admin_allowed(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as' );
		$post_id = self::factory()->post->create();

		$request = new WP_REST_Request( 'POST', '/duplicate-as/v1/duplicate/' . $post_id );
		$request->set_param( 'id', $post_id );

		$this->assertTrue( $this->rest_api->check_permission( $request ) );
	}

	/**
	 * Test check_permission returns false for subscriber.
	 *
	 * @covers Duplicate_As_Rest_Api::check_permission
	 * @return void
	 */
	public function test_check_permission_subscriber_denied(): void {
		wp_set_current_user( $this->subscriber_user_id );
		add_post_type_support( 'post', 'duplicate_as' );
		$post_id = self::factory()->post->create();

		$request = new WP_REST_Request( 'POST', '/duplicate-as/v1/duplicate/' . $post_id );
		$request->set_param( 'id', $post_id );

		$this->assertFalse( $this->rest_api->check_permission( $request ) );
	}

	/**
	 * Test check_permission returns false for unsupported type.
	 *
	 * @covers Duplicate_As_Rest_Api::check_permission
	 * @return void
	 */
	public function test_check_permission_unsupported_type(): void {
		wp_set_current_user( $this->admin_user_id );
		remove_post_type_support( 'post', 'duplicate_as' );
		$post_id = self::factory()->post->create();

		$request = new WP_REST_Request( 'POST', '/duplicate-as/v1/duplicate/' . $post_id );
		$request->set_param( 'id', $post_id );

		$this->assertFalse( $this->rest_api->check_permission( $request ) );
	}

	/**
	 * Test check_permission returns false for nonexistent post.
	 *
	 * @covers Duplicate_As_Rest_Api::check_permission
	 * @return void
	 */
	public function test_check_permission_nonexistent_post(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as' );

		$request = new WP_REST_Request( 'POST', '/duplicate-as/v1/duplicate/999999' );
		$request->set_param( 'id', 999999 );

		$this->assertFalse( $this->rest_api->check_permission( $request ) );
	}

	/**
	 * Test check_permission with valid transform target.
	 *
	 * @covers Duplicate_As_Rest_Api::check_permission
	 * @return void
	 */
	public function test_check_permission_valid_transform(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as', array( 'page', 'post' ) );
		$post_id = self::factory()->post->create();

		$request = new WP_REST_Request( 'POST', '/duplicate-as/v1/duplicate/' . $post_id );
		$request->set_param( 'id', $post_id );
		$request->set_param( 'target_post_type', 'page' );

		$this->assertTrue( $this->rest_api->check_permission( $request ) );
	}

	/**
	 * Test check_permission with invalid transform target.
	 *
	 * @covers Duplicate_As_Rest_Api::check_permission
	 * @return void
	 */
	public function test_check_permission_invalid_transform(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as' );
		$post_id = self::factory()->post->create();

		$request = new WP_REST_Request( 'POST', '/duplicate-as/v1/duplicate/' . $post_id );
		$request->set_param( 'id', $post_id );
		$request->set_param( 'target_post_type', 'page' );

		$this->assertFalse( $this->rest_api->check_permission( $request ) );
	}

	/**
	 * Test handle_duplicate returns success response.
	 *
	 * @covers Duplicate_As_Rest_Api::handle_duplicate
	 * @return void
	 */
	public function test_handle_duplicate_success(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as' );
		$post_id = self::factory()->post->create(
			array(
				'post_title'   => 'REST Duplicate Test',
				'post_content' => 'Test content for REST.',
			)
		);

		$request = new WP_REST_Request( 'POST', '/duplicate-as/v1/duplicate/' . $post_id );
		$request->set_param( 'id', $post_id );

		$response = $this->rest_api->handle_duplicate( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertIsInt( $data['new_post_id'] );
		$this->assertNotEmpty( $data['edit_url'] );
		$this->assertFalse( $data['is_transform'] );
	}

	/**
	 * Test handle_duplicate returns error for nonexistent post.
	 *
	 * @covers Duplicate_As_Rest_Api::handle_duplicate
	 * @return void
	 */
	public function test_handle_duplicate_post_not_found(): void {
		wp_set_current_user( $this->admin_user_id );

		$request = new WP_REST_Request( 'POST', '/duplicate-as/v1/duplicate/999999' );
		$request->set_param( 'id', 999999 );

		$response = $this->rest_api->handle_duplicate( $request );

		$this->assertInstanceOf( WP_Error::class, $response );
		$this->assertEquals( 'post_not_found', $response->get_error_code() );
	}

	/**
	 * Test handle_duplicate returns error for unsupported type.
	 *
	 * @covers Duplicate_As_Rest_Api::handle_duplicate
	 * @return void
	 */
	public function test_handle_duplicate_type_not_allowed(): void {
		wp_set_current_user( $this->admin_user_id );
		remove_post_type_support( 'post', 'duplicate_as' );
		$post_id = self::factory()->post->create();

		$request = new WP_REST_Request( 'POST', '/duplicate-as/v1/duplicate/' . $post_id );
		$request->set_param( 'id', $post_id );

		$response = $this->rest_api->handle_duplicate( $request );

		$this->assertInstanceOf( WP_Error::class, $response );
		$this->assertEquals( 'post_type_not_allowed', $response->get_error_code() );
	}

	/**
	 * Test handle_duplicate with transform returns correct is_transform flag.
	 *
	 * @covers Duplicate_As_Rest_Api::handle_duplicate
	 * @return void
	 */
	public function test_handle_duplicate_transform_flag(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as', array( 'page', 'post' ) );
		$post_id = self::factory()->post->create();

		$request = new WP_REST_Request( 'POST', '/duplicate-as/v1/duplicate/' . $post_id );
		$request->set_param( 'id', $post_id );
		$request->set_param( 'target_post_type', 'page' );

		$response = $this->rest_api->handle_duplicate( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$data = $response->get_data();
		$this->assertTrue( $data['is_transform'] );
	}

	/**
	 * Test handle_duplicate returns error for invalid transform target.
	 *
	 * @covers Duplicate_As_Rest_Api::handle_duplicate
	 * @return void
	 */
	public function test_handle_duplicate_invalid_target(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as' );
		$post_id = self::factory()->post->create();

		$request = new WP_REST_Request( 'POST', '/duplicate-as/v1/duplicate/' . $post_id );
		$request->set_param( 'id', $post_id );
		$request->set_param( 'target_post_type', 'page' );

		$response = $this->rest_api->handle_duplicate( $request );

		$this->assertInstanceOf( WP_Error::class, $response );
		$this->assertEquals( 'invalid_target_post_type', $response->get_error_code() );
	}

	/**
	 * Test singleton pattern returns same instance.
	 *
	 * @covers Duplicate_As_Rest_Api::get_instance
	 * @return void
	 */
	public function test_singleton_returns_same_instance(): void {
		$instance1 = Duplicate_As_Rest_Api::get_instance();
		$instance2 = Duplicate_As_Rest_Api::get_instance();
		$this->assertSame( $instance1, $instance2 );
	}
}
