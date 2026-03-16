<?php
/**
 * Integration tests for the complete duplication flow.
 *
 * Tests end-to-end duplication and transformation workflows
 * including post creation, content copying, and metadata handling.
 *
 * @package DuplicateAs\Tests\Integration
 * @since   0.3.0
 */

class DuplicationFlowTest extends WP_UnitTestCase {

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
	 * Test complete post duplication flow.
	 *
	 * Creates a post with title, content, excerpt, categories, tags,
	 * custom meta, and featured image, then duplicates it and verifies
	 * all content was properly copied.
	 *
	 * @return void
	 */
	public function test_complete_duplication_flow(): void {
		$file = DIR_TESTDATA . '/images/test-image.jpg';

		// Set up source post with all metadata.
		$cat_id  = self::factory()->category->create( array( 'name' => 'Integration Cat' ) );
		$post_id = self::factory()->post->create(
			array(
				'post_title'     => 'Integration Test Post',
				'post_content'   => '<!-- wp:paragraph --><p>Rich content.</p><!-- /wp:paragraph -->',
				'post_excerpt'   => 'Short excerpt for integration test.',
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'menu_order'     => 3,
			)
		);
		

		wp_set_post_categories( $post_id, array( $cat_id ) );
		wp_set_post_tags( $post_id, array( 'int-tag-1', 'int-tag-2' ) );
		add_post_meta( $post_id, '_custom_field_a', 'value_a' );
		add_post_meta( $post_id, '_custom_field_b', 'value_b' );

		$thumbnail_id = self::factory()->attachment->create_upload_object( $file, $post_id );
		set_post_thumbnail( $post_id, $thumbnail_id );

		// Perform duplication.
		$post       = get_post( $post_id );
		$duplicator = Duplicate_As_Duplicator::get_instance();
		$new_id     = $duplicator->duplicate( $post );

		$this->assertIsInt( $new_id );
		$new_post = get_post( $new_id );

		// Verify post data.
		$this->assertEquals( 'Integration Test Post', $new_post->post_title );
		$this->assertStringContainsString( 'Rich content.', $new_post->post_content );
		$this->assertEquals( 'Short excerpt for integration test.', $new_post->post_excerpt );
		$this->assertEquals( 'draft', $new_post->post_status );
		$this->assertEquals( 'closed', $new_post->comment_status );
		$this->assertEquals( 'closed', $new_post->ping_status );
		$this->assertEquals( 3, $new_post->menu_order );
		$this->assertEquals( 'post', $new_post->post_type );

		// Verify taxonomies.
		$new_cats = wp_get_post_categories( $new_id );
		$this->assertContains( $cat_id, $new_cats );

		$new_tags = wp_get_post_tags( $new_id, array( 'fields' => 'names' ) );
		$this->assertContains( 'int-tag-1', $new_tags );
		$this->assertContains( 'int-tag-2', $new_tags );

		// Verify meta.
		$this->assertEquals( 'value_a', get_post_meta( $new_id, '_custom_field_a', true ) );
		$this->assertEquals( 'value_b', get_post_meta( $new_id, '_custom_field_b', true ) );

		// Verify excluded meta.
		$this->assertEmpty( get_post_meta( $new_id, '_edit_lock', true ) );
		$this->assertEmpty( get_post_meta( $new_id, '_edit_last', true ) );

		// Verify featured image.
		$this->assertEquals( $thumbnail_id, get_post_thumbnail_id( $new_id ) );
	}

	/**
	 * Test complete transformation flow from post to page.
	 *
	 * @return void
	 */
	public function test_complete_transformation_flow(): void {
		$cat_id  = self::factory()->category->create( array( 'name' => 'Transform Cat' ) );
		$post_id = self::factory()->post->create(
			array(
				'post_title'   => 'Transform Test Post',
				'post_content' => '<!-- wp:heading --><h2>Heading</h2><!-- /wp:heading -->',
				'post_status'  => 'publish',
			)
		);

		wp_set_post_categories( $post_id, array( $cat_id ) );
		wp_set_post_tags( $post_id, array( 'transform-tag' ) );
		add_post_meta( $post_id, '_transform_meta', 'meta_value' );

		$post       = get_post( $post_id );
		$duplicator = Duplicate_As_Duplicator::get_instance();
		$new_id     = $duplicator->duplicate( $post, 'page' );

		$this->assertIsInt( $new_id );
		$new_post = get_post( $new_id );

		// Verify post type changed.
		$this->assertEquals( 'page', $new_post->post_type );
		$this->assertEquals( 'draft', $new_post->post_status );
		$this->assertEquals( 'Transform Test Post', $new_post->post_title );
		$this->assertStringContainsString( 'Heading', $new_post->post_content );

		// Verify meta was copied.
		$this->assertEquals( 'meta_value', get_post_meta( $new_id, '_transform_meta', true ) );

		// Verify tags were NOT copied (page doesn't support post_tag).
		$new_tags = wp_get_object_terms( $new_id, 'post_tag', array( 'fields' => 'names' ) );
		if ( ! is_wp_error( $new_tags ) ) {
			$this->assertEmpty( $new_tags );
		}
	}

	/**
	 * Test multiple duplications of the same post.
	 *
	 * Ensures each duplication creates a unique post.
	 *
	 * @return void
	 */
	public function test_multiple_duplications(): void {
		$post_id = self::factory()->post->create(
			array(
				'post_title' => 'Multiple Copies',
			)
		);
		$post    = get_post( $post_id );

		$duplicator = Duplicate_As_Duplicator::get_instance();
		$ids        = array();

		for ( $i = 0; $i < 3; $i++ ) {
			$new_id = $duplicator->duplicate( $post );
			$this->assertIsInt( $new_id );
			$ids[] = $new_id;
		}

		// All IDs should be unique.
		$this->assertCount( 3, array_unique( $ids ) );

		// All should be drafts with same title.
		foreach ( $ids as $id ) {
			$dup = get_post( $id );
			$this->assertEquals( 'Multiple Copies', $dup->post_title );
			$this->assertEquals( 'draft', $dup->post_status );
		}
	}

	/**
	 * Test page duplication preserves parent.
	 *
	 * @return void
	 */
	public function test_page_duplication_preserves_parent(): void {
		$parent_id = self::factory()->post->create(
			array(
				'post_type'  => 'page',
				'post_title' => 'Parent Page',
			)
		);
		$child_id  = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Child Page',
				'post_parent' => $parent_id,
			)
		);

		$post       = get_post( $child_id );
		$duplicator = Duplicate_As_Duplicator::get_instance();
		$new_id     = $duplicator->duplicate( $post );

		$new_post = get_post( $new_id );
		$this->assertEquals( $parent_id, $new_post->post_parent );
	}

	/**
	 * Test duplication with serialized meta data.
	 *
	 * @return void
	 */
	public function test_duplication_with_serialized_meta(): void {
		$post_id       = self::factory()->post->create();
		$complex_value = array(
			'key1' => 'value1',
			'key2' => array( 'nested' => true ),
		);
		add_post_meta( $post_id, '_complex_meta', $complex_value );

		$post       = get_post( $post_id );
		$duplicator = Duplicate_As_Duplicator::get_instance();
		$new_id     = $duplicator->duplicate( $post );

		$new_meta = get_post_meta( $new_id, '_complex_meta', true );
		$this->assertEquals( $complex_value, $new_meta );
	}

	/**
	 * Test duplication with post password.
	 *
	 * @return void
	 */
	public function test_duplication_preserves_password(): void {
		$post_id = self::factory()->post->create(
			array(
				'post_password' => 'secret123',
			)
		);

		$post       = get_post( $post_id );
		$duplicator = Duplicate_As_Duplicator::get_instance();
		$new_id     = $duplicator->duplicate( $post );

		$new_post = get_post( $new_id );
		$this->assertEquals( 'secret123', $new_post->post_password );
	}

	/**
	 * Test duplication with multiple meta values for same key.
	 *
	 * @return void
	 */
	public function test_duplication_multiple_meta_values(): void {
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, '_multi_meta', 'value_1' );
		add_post_meta( $post_id, '_multi_meta', 'value_2' );
		add_post_meta( $post_id, '_multi_meta', 'value_3' );

		$post       = get_post( $post_id );
		$duplicator = Duplicate_As_Duplicator::get_instance();
		$new_id     = $duplicator->duplicate( $post );

		$new_meta = get_post_meta( $new_id, '_multi_meta' );
		$this->assertCount( 3, $new_meta );
		$this->assertContains( 'value_1', $new_meta );
		$this->assertContains( 'value_2', $new_meta );
		$this->assertContains( 'value_3', $new_meta );
	}
}
