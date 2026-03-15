<?php
/**
 * Unit tests for Duplicate_As_Duplicator.
 *
 * Tests the core duplication logic including post creation, taxonomy copying,
 * meta copying, featured image handling, and template block conversion.
 *
 * @package DuplicateAs\Tests\Unit
 * @since   0.3.0
 */

class DuplicatorTest extends WP_UnitTestCase {

	/**
	 * Instance under test.
	 *
	 * @var Duplicate_As_Duplicator
	 */
	private Duplicate_As_Duplicator $duplicator;

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
		$this->duplicator    = Duplicate_As_Duplicator::get_instance();
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
	 * Test basic post duplication.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_duplicate_creates_new_post(): void {
		$post_id = self::factory()->post->create(
			array(
				'post_title'   => 'Original Post',
				'post_content' => '<!-- wp:paragraph --><p>Hello</p><!-- /wp:paragraph -->',
				'post_status'  => 'publish',
			)
		);
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );

		$this->assertIsInt( $new_post_id );
		$this->assertNotEquals( $post_id, $new_post_id );
	}

	/**
	 * Test duplicate preserves post title.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_duplicate_preserves_title(): void {
		$post_id = self::factory()->post->create(
			array(
				'post_title' => 'My Important Title',
			)
		);
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );
		$new_post    = get_post( $new_post_id );

		$this->assertEquals( 'My Important Title', $new_post->post_title );
	}

	/**
	 * Test duplicate preserves post content.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_duplicate_preserves_content(): void {
		$content = '<!-- wp:paragraph --><p>Test content here</p><!-- /wp:paragraph -->';
		$post_id = self::factory()->post->create(
			array(
				'post_content' => $content,
			)
		);
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );
		$new_post    = get_post( $new_post_id );

		$this->assertEquals( $content, $new_post->post_content );
	}

	/**
	 * Test duplicate creates draft status.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_duplicate_creates_draft(): void {
		$post_id = self::factory()->post->create(
			array(
				'post_status' => 'publish',
			)
		);
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );
		$new_post    = get_post( $new_post_id );

		$this->assertEquals( 'draft', $new_post->post_status );
	}

	/**
	 * Test duplicate preserves excerpt.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_duplicate_preserves_excerpt(): void {
		$post_id = self::factory()->post->create(
			array(
				'post_excerpt' => 'This is the excerpt.',
			)
		);
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );
		$new_post    = get_post( $new_post_id );

		$this->assertEquals( 'This is the excerpt.', $new_post->post_excerpt );
	}

	/**
	 * Test duplicate preserves comment status.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_duplicate_preserves_comment_status(): void {
		$post_id = self::factory()->post->create(
			array(
				'comment_status' => 'closed',
			)
		);
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );
		$new_post    = get_post( $new_post_id );

		$this->assertEquals( 'closed', $new_post->comment_status );
	}

	/**
	 * Test duplicate preserves ping status.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_duplicate_preserves_ping_status(): void {
		$post_id = self::factory()->post->create(
			array(
				'ping_status' => 'closed',
			)
		);
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );
		$new_post    = get_post( $new_post_id );

		$this->assertEquals( 'closed', $new_post->ping_status );
	}

	/**
	 * Test duplicate preserves menu order.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_duplicate_preserves_menu_order(): void {
		$post_id = self::factory()->post->create(
			array(
				'menu_order' => 5,
			)
		);
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );
		$new_post    = get_post( $new_post_id );

		$this->assertEquals( 5, $new_post->menu_order );
	}

	/**
	 * Test duplicate copies categories.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_duplicate_copies_categories(): void {
		$cat_id  = self::factory()->category->create( array( 'name' => 'Test Category' ) );
		$post_id = self::factory()->post->create();
		wp_set_post_categories( $post_id, array( $cat_id ) );
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );

		$new_categories = wp_get_post_categories( $new_post_id );
		$this->assertContains( $cat_id, $new_categories );
	}

	/**
	 * Test duplicate copies tags.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_duplicate_copies_tags(): void {
		$post_id = self::factory()->post->create();
		wp_set_post_tags( $post_id, array( 'tag-one', 'tag-two' ) );
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );

		$new_tags     = wp_get_post_tags( $new_post_id, array( 'fields' => 'names' ) );
		$this->assertContains( 'tag-one', $new_tags );
		$this->assertContains( 'tag-two', $new_tags );
	}

	/**
	 * Test duplicate copies post meta.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_duplicate_copies_post_meta(): void {
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, '_custom_field', 'custom_value' );
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );

		$this->assertEquals( 'custom_value', get_post_meta( $new_post_id, '_custom_field', true ) );
	}

	/**
	 * Test duplicate excludes edit lock meta.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_duplicate_excludes_edit_lock(): void {
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, '_edit_lock', time() . ':1' );
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );

		$this->assertEmpty( get_post_meta( $new_post_id, '_edit_lock', true ) );
	}

	/**
	 * Test duplicate excludes edit last meta.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_duplicate_excludes_edit_last(): void {
		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, '_edit_last', '1' );
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );

		$this->assertEmpty( get_post_meta( $new_post_id, '_edit_last', true ) );
	}

	/**
	 * Test duplicate copies featured image.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_duplicate_copies_featured_image(): void {
		$post_id      = self::factory()->post->create();
		$thumbnail_id = self::factory()->attachment->create_upload_object(
			DIR_TESTDATA . '/images/test-image.jpg',
			$post_id
		);

		// If test image doesn't exist, create a simple attachment.
		if ( ! $thumbnail_id ) {
			$thumbnail_id = self::factory()->attachment->create(
				array(
					'post_parent' => $post_id,
					'post_type'   => 'attachment',
				)
			);
		}

		set_post_thumbnail( $post_id, $thumbnail_id );
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );

		$this->assertEquals( $thumbnail_id, get_post_thumbnail_id( $new_post_id ) );
	}

	/**
	 * Test duplicate with transformation changes post type.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_duplicate_transforms_post_type(): void {
		$post_id = self::factory()->post->create(
			array(
				'post_title'   => 'Transform Me',
				'post_content' => 'Some content here.',
			)
		);
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post, 'page' );
		$new_post    = get_post( $new_post_id );

		$this->assertEquals( 'page', $new_post->post_type );
		$this->assertEquals( 'Transform Me', $new_post->post_title );
	}

	/**
	 * Test duplicate fires after_duplicate action.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_duplicate_fires_action(): void {
		$action_fired = false;
		$captured_ids = array();

		add_action(
			'duplicate_as_after_duplicate',
			function ( $new_id, $original_id ) use ( &$action_fired, &$captured_ids ) {
				$action_fired   = true;
				$captured_ids[] = $new_id;
				$captured_ids[] = $original_id;
			},
			10,
			2
		);

		$post_id = self::factory()->post->create();
		$post    = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );

		$this->assertTrue( $action_fired );
		$this->assertEquals( $new_post_id, $captured_ids[0] );
		$this->assertEquals( $post_id, $captured_ids[1] );
	}

	/**
	 * Test duplicate_as_post_data filter is applied.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_post_data_filter_applied(): void {
		add_filter(
			'duplicate_as_post_data',
			function ( $data ) {
				$data['post_title'] = 'Filtered Title';
				return $data;
			}
		);

		$post_id = self::factory()->post->create(
			array(
				'post_title' => 'Original Title',
			)
		);
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );
		$new_post    = get_post( $new_post_id );

		$this->assertEquals( 'Filtered Title', $new_post->post_title );

		remove_all_filters( 'duplicate_as_post_data' );
	}

	/**
	 * Test duplicate_as_excluded_meta_keys filter is applied.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_excluded_meta_keys_filter_applied(): void {
		add_filter(
			'duplicate_as_excluded_meta_keys',
			function ( $keys ) {
				$keys[] = '_my_secret_field';
				return $keys;
			}
		);

		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, '_my_secret_field', 'secret_value' );
		add_post_meta( $post_id, '_my_public_field', 'public_value' );
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );

		$this->assertEmpty( get_post_meta( $new_post_id, '_my_secret_field', true ) );
		$this->assertEquals( 'public_value', get_post_meta( $new_post_id, '_my_public_field', true ) );

		remove_all_filters( 'duplicate_as_excluded_meta_keys' );
	}

	/**
	 * Test duplicate_as_meta_value filter is applied.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_meta_value_filter_applied(): void {
		add_filter(
			'duplicate_as_meta_value',
			function ( $value, $key ) {
				if ( '_counter' === $key ) {
					return 0;
				}
				return $value;
			},
			10,
			2
		);

		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, '_counter', '42' );
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );

		$this->assertEquals( 0, get_post_meta( $new_post_id, '_counter', true ) );

		remove_all_filters( 'duplicate_as_meta_value' );
	}

	/**
	 * Test duplicate_as_featured_image filter is applied.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_featured_image_filter_applied(): void {
		add_filter( 'duplicate_as_featured_image', '__return_false' );

		$post_id      = self::factory()->post->create();
		$thumbnail_id = self::factory()->attachment->create(
			array(
				'post_parent' => $post_id,
				'post_type'   => 'attachment',
			)
		);
		set_post_thumbnail( $post_id, $thumbnail_id );
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );

		$this->assertEmpty( get_post_thumbnail_id( $new_post_id ) );

		remove_all_filters( 'duplicate_as_featured_image' );
	}

	/**
	 * Test duplicate_as_taxonomies filter is applied.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_taxonomies_filter_applied(): void {
		add_filter(
			'duplicate_as_taxonomies',
			function ( $taxonomies ) {
				return array_diff( $taxonomies, array( 'category' ) );
			}
		);

		$cat_id  = self::factory()->category->create( array( 'name' => 'Filtered Cat' ) );
		$post_id = self::factory()->post->create();
		wp_set_post_categories( $post_id, array( $cat_id ) );
		wp_set_post_tags( $post_id, array( 'keep-me' ) );
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );

		$new_cats = wp_get_post_categories( $new_post_id );
		$new_tags = wp_get_post_tags( $new_post_id, array( 'fields' => 'names' ) );

		// Categories should NOT be copied (filtered out).
		$this->assertNotContains( $cat_id, $new_cats );
		// Tags should still be copied.
		$this->assertContains( 'keep-me', $new_tags );

		remove_all_filters( 'duplicate_as_taxonomies' );
	}

	/**
	 * Test duplicate_as_taxonomy_terms filter is applied.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_taxonomy_terms_filter_applied(): void {
		$cat1 = self::factory()->category->create( array( 'name' => 'Keep This' ) );
		$cat2 = self::factory()->category->create( array( 'name' => 'Remove This' ) );

		add_filter(
			'duplicate_as_taxonomy_terms',
			function ( $terms, $taxonomy ) use ( $cat2 ) {
				if ( 'category' === $taxonomy ) {
					return array_diff( $terms, array( $cat2 ) );
				}
				return $terms;
			},
			10,
			2
		);

		$post_id = self::factory()->post->create();
		wp_set_post_categories( $post_id, array( $cat1, $cat2 ) );
		$post = get_post( $post_id );

		$new_post_id = $this->duplicator->duplicate( $post );

		$new_cats = wp_get_post_categories( $new_post_id );
		$this->assertContains( $cat1, $new_cats );
		$this->assertNotContains( $cat2, $new_cats );

		remove_all_filters( 'duplicate_as_taxonomy_terms' );
	}

	/**
	 * Test that taxonomy copy only copies shared taxonomies.
	 *
	 * When transforming from post to page, only taxonomies registered
	 * for both types should be copied.
	 *
	 * @covers Duplicate_As_Duplicator::duplicate
	 * @return void
	 */
	public function test_transform_only_copies_shared_taxonomies(): void {
		$post_id = self::factory()->post->create();
		wp_set_post_tags( $post_id, array( 'test-tag' ) );
		$post = get_post( $post_id );

		// Transform post to page (pages don't have post_tag by default).
		$new_post_id = $this->duplicator->duplicate( $post, 'page' );

		// Tags should not be copied to page since page doesn't support post_tag.
		$new_tags = wp_get_object_terms( $new_post_id, 'post_tag', array( 'fields' => 'names' ) );
		if ( ! is_wp_error( $new_tags ) ) {
			$this->assertEmpty( $new_tags );
		}
	}

	/**
	 * Test singleton pattern returns same instance.
	 *
	 * @covers Duplicate_As_Duplicator::get_instance
	 * @return void
	 */
	public function test_singleton_returns_same_instance(): void {
		$instance1 = Duplicate_As_Duplicator::get_instance();
		$instance2 = Duplicate_As_Duplicator::get_instance();
		$this->assertSame( $instance1, $instance2 );
	}
}
