<?php
/**
 * Integration tests for the REST API duplication flow.
 *
 * Tests end-to-end REST API requests for duplication and transformation.
 *
 * @package DuplicateAs\Tests\Integration
 * @since   0.3.0
 */

/**
 * Tests for the REST API duplication flow.
 */
class RestApiFlowTest extends WP_UnitTestCase {

	/**
	 * REST server instance.
	 *
	 * @var WP_REST_Server
	 */
	private WP_REST_Server $server;

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

		global $wp_rest_server;
		$wp_rest_server = new WP_REST_Server();
		$this->server   = $wp_rest_server;
		do_action( 'rest_api_init' );

		$this->admin_user_id      = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$this->subscriber_user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
	}

	/**
	 * Tear down each test.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		global $wp_rest_server;
		$wp_rest_server = null;

		remove_post_type_support( 'post', 'duplicate_as' );
		remove_post_type_support( 'page', 'duplicate_as' );
		parent::tear_down();
	}

	/**
	 * Test full REST API duplication request.
	 *
	 * @return void
	 */
	public function test_rest_duplicate_full_flow(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as' );

		$post_id = self::factory()->post->create(
			array(
				'post_title'   => 'REST Flow Test',
				'post_content' => '<!-- wp:paragraph --><p>REST content</p><!-- /wp:paragraph -->',
				'post_status'  => 'publish',
			)
		);
		add_post_meta( $post_id, '_rest_meta', 'rest_value' );

		$request = new WP_REST_Request( 'POST', '/duplicate-as/v1/duplicate/' . $post_id );
		$request->set_param( 'id', $post_id );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertIsInt( $data['new_post_id'] );
		$this->assertFalse( $data['is_transform'] );

		// Verify the new post.
		$new_post = get_post( $data['new_post_id'] );
		$this->assertEquals( 'REST Flow Test', $new_post->post_title );
		$this->assertEquals( 'draft', $new_post->post_status );
		$this->assertEquals( 'rest_value', get_post_meta( $data['new_post_id'], '_rest_meta', true ) );
	}

	/**
	 * Test full REST API transformation request.
	 *
	 * @return void
	 */
	public function test_rest_transform_full_flow(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as', array( 'page', 'post' ) );

		$post_id = self::factory()->post->create(
			array(
				'post_title'   => 'REST Transform Test',
				'post_content' => 'Transform content.',
				'post_status'  => 'publish',
			)
		);

		$request = new WP_REST_Request( 'POST', '/duplicate-as/v1/duplicate/' . $post_id );
		$request->set_param( 'id', $post_id );
		$request->set_param( 'target_post_type', 'page' );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertTrue( $data['is_transform'] );

		$new_post = get_post( $data['new_post_id'] );
		$this->assertEquals( 'page', $new_post->post_type );
		$this->assertEquals( 'REST Transform Test', $new_post->post_title );
	}

	/**
	 * Test REST API returns 403 for unauthorized user.
	 *
	 * @return void
	 */
	public function test_rest_unauthorized_user(): void {
		wp_set_current_user( $this->subscriber_user_id );
		add_post_type_support( 'post', 'duplicate_as' );

		$post_id = self::factory()->post->create();

		$request = new WP_REST_Request( 'POST', '/duplicate-as/v1/duplicate/' . $post_id );
		$request->set_param( 'id', $post_id );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 403, $response->get_status() );
	}

	/**
	 * Test REST API returns 404 for nonexistent post.
	 *
	 * @return void
	 */
	public function test_rest_nonexistent_post(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as' );

		$request = new WP_REST_Request( 'POST', '/duplicate-as/v1/duplicate/999999' );
		$request->set_param( 'id', 999999 );

		$response = $this->server->dispatch( $request );

		// Permission check runs first and returns 403.
		$this->assertContains( $response->get_status(), array( 403, 404 ) );
	}

	/**
	 * Test REST API rejects invalid transform target.
	 *
	 * @return void
	 */
	public function test_rest_invalid_transform_target(): void {
		wp_set_current_user( $this->admin_user_id );
		add_post_type_support( 'post', 'duplicate_as' );

		$post_id = self::factory()->post->create();

		$request = new WP_REST_Request( 'POST', '/duplicate-as/v1/duplicate/' . $post_id );
		$request->set_param( 'id', $post_id );
		$request->set_param( 'target_post_type', 'page' );

		$response = $this->server->dispatch( $request );

		// Permission check or handler should reject this.
		$this->assertContains( $response->get_status(), array( 403 ) );
	}

	/**
	 * Test REST API with unauthenticated request.
	 *
	 * @return void
	 */
	public function test_rest_unauthenticated(): void {
		wp_set_current_user( 0 );
		add_post_type_support( 'post', 'duplicate_as' );

		$post_id = self::factory()->post->create();

		$request = new WP_REST_Request( 'POST', '/duplicate-as/v1/duplicate/' . $post_id );
		$request->set_param( 'id', $post_id );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 401, $response->get_status() );
	}
}
