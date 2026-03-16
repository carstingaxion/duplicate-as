<?php
/**
 * Integration tests for filter hooks.
 *
 * Tests that all filter hooks fire correctly and can be used
 * to customize the duplication behavior.
 *
 * @package DuplicateAs\Tests\Integration
 * @since   0.3.0
 */

class FilterHooksTest extends WP_UnitTestCase {

	/**
	 * Admin user ID.
	 *
	 * @var int
	 */
	private int $admin_user_id;

	/**
	 * Duplicator instance.
	 *
	 * @var Duplicate_As_Duplicator
	 */
	private Duplicate_As_Duplicator $duplicator;

	/**
	 * Set up each test.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();
		$this->admin_user_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->admin_user_id );
		$this->duplicator = Duplicate_As_Duplicator::get_instance();
	}

	/**
	 * Tear down each test.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		remove_all_filters( 'duplicate_as_post_data' );
		remove_all_filters( 'duplicate_as_taxonomies' );
		remove_all_filters( 'duplicate_as_taxonomy_terms' );
		remove_all_filters( 'duplicate_as_excluded_meta_keys' );
		remove_all_filters( 'duplicate_as_meta_value' );
		remove_all_filters( 'duplicate_as_featured_image' );
		remove_all_actions( 'duplicate_as_after_duplicate' );
		parent::tear_down();
	}

	/**
	 * Test duplicate_as_post_data filter receives correct parameters.
	 *
	 * @return void
	 */
	public function test_post_data_filter_params(): void {
		$received_params = array();

		add_filter(
			'duplicate_as_post_data',
			function ( $data, $post, $target ) use ( &$received_params ) {
				$received_params = array(
					'data'   => $data,
					'post'   => $post,
					'target' => $target,
				);
				return $data;
			},
			10,
			3
		);

		$post_id = self::factory()->post->create( array( 'post_title' => 'Filter Params Test' ) );
		$post    = get_post( $post_id );
		$this->duplicator->duplicate( $post, 'page' );

		$this->assertIsArray( $received_params['data'] );
		$this->assertInstanceOf( WP_Post::class, $received_params['post'] );
		$this->assertEquals( 'page', $received_params['target'] );
		$this->assertEquals( 'Filter Params Test', $received_params['data']['post_title'] );
	}

	/**
	 * Test duplicate_as_post_data filter can modify all fields.
	 *
	 * @return void
	 */
	public function test_post_data_filter_modifies_all_fields(): void {
		add_filter(
			'duplicate_as_post_data',
			function ( $data ) {
				$data['post_title']   = 'Modified Title';
				$data['post_excerpt'] = 'Modified Excerpt';
				return $data;
			}
		);

		$post_id = self::factory()->post->create(
			array(
				'post_title'   => 'Original',
				'post_excerpt' => 'Original Excerpt',
			)
		);
		$post    = get_post( $post_id );
		$new_id  = $this->duplicator->duplicate( $post );

		$new_post = get_post( $new_id );
		$this->assertEquals( 'Modified Title', $new_post->post_title );
		$this->assertEquals( 'Modified Excerpt', $new_post->post_excerpt );
	}

	/**
	 * Test duplicate_as_taxonomies filter receives correct parameters.
	 *
	 * @return void
	 */
	public function test_taxonomies_filter_params(): void {
		$received_params = array();

		add_filter(
			'duplicate_as_taxonomies',
			function ( $taxonomies, $from_id, $to_id, $source, $target ) use ( &$received_params ) {
				$received_params = compact( 'taxonomies', 'from_id', 'to_id', 'source', 'target' );
				return $taxonomies;
			},
			10,
			5
		);

		$post_id = self::factory()->post->create();
		$post    = get_post( $post_id );
		$this->duplicator->duplicate( $post );

		$this->assertIsArray( $received_params['taxonomies'] );
		$this->assertIsInt( $received_params['from_id'] );
		$this->assertIsInt( $received_params['to_id'] );
		$this->assertEquals( 'post', $received_params['source'] );
		$this->assertEquals( 'post', $received_params['target'] );
	}

	/**
	 * Test duplicate_as_taxonomy_terms filter receives correct parameters.
	 *
	 * @return void
	 */
	public function test_taxonomy_terms_filter_params(): void {
		$received_params = array();

		add_filter(
			'duplicate_as_taxonomy_terms',
			function ( $terms, $taxonomy, $from_id, $to_id ) use ( &$received_params ) {
				$received_params = compact( 'terms', 'taxonomy', 'from_id', 'to_id' );
				return $terms;
			},
			10,
			4
		);

		$cat_id  = self::factory()->category->create();
		$post_id = self::factory()->post->create();
		wp_set_post_categories( $post_id, array( $cat_id ) );
		$post = get_post( $post_id );
		$this->duplicator->duplicate( $post );

		$this->assertIsArray( $received_params['terms'] );
		$this->assertIsString( $received_params['taxonomy'] );
		$this->assertIsInt( $received_params['from_id'] );
		$this->assertIsInt( $received_params['to_id'] );
	}

	/**
	 * Test duplicate_as_excluded_meta_keys filter receives correct parameters.
	 *
	 * @return void
	 */
	public function test_excluded_meta_keys_filter_params(): void {
		$received_params = array();

		add_filter(
			'duplicate_as_excluded_meta_keys',
			function ( $keys, $from_id, $to_id ) use ( &$received_params ) {
				$received_params = compact( 'keys', 'from_id', 'to_id' );
				return $keys;
			},
			10,
			3
		);

		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, '_some_meta', 'value' );
		$post = get_post( $post_id );
		$this->duplicator->duplicate( $post );

		$this->assertIsArray( $received_params['keys'] );
		$this->assertContains( '_edit_lock', $received_params['keys'] );
		$this->assertContains( '_edit_last', $received_params['keys'] );
		$this->assertContains( '_thumbnail_id', $received_params['keys'] );
	}

	/**
	 * Test duplicate_as_meta_value filter receives correct parameters.
	 *
	 * @return void
	 */
	public function test_meta_value_filter_params(): void {
		$received_params = array();

		add_filter(
			'duplicate_as_meta_value',
			function ( $value, $key, $from_id, $to_id ) use ( &$received_params ) {
				$received_params[] = compact( 'value', 'key', 'from_id', 'to_id' );
				return $value;
			},
			10,
			4
		);

		$post_id = self::factory()->post->create();
		add_post_meta( $post_id, '_filter_test', 'test_value' );
		$post = get_post( $post_id );
		$this->duplicator->duplicate( $post );

		$found = false;
		foreach ( $received_params as $params ) {
			if ( '_filter_test' === $params['key'] ) {
				$found = true;
				$this->assertEquals( 'test_value', $params['value'] );
				break;
			}
		}
		$this->assertTrue( $found, 'Meta value filter should have been called for _filter_test key.' );
	}

	/**
	 * Test duplicate_as_featured_image filter receives correct parameters.
	 *
	 * @return void
	 */
	public function test_featured_image_filter_params(): void {
		$received_params = array();
		$file            = DIR_TESTDATA . '/images/test-image.jpg';

		add_filter(
			'duplicate_as_featured_image',
			function ( $thumbnail_id, $from_id, $to_id ) use ( &$received_params ) {
				$received_params = compact( 'thumbnail_id', 'from_id', 'to_id' );
				return $thumbnail_id;
			},
			10,
			3
		);

		$post_id      = self::factory()->post->create();
		$thumbnail_id = self::factory()->attachment->create_upload_object( $file, $post_id );

		set_post_thumbnail( $post_id, $thumbnail_id );
		$post = get_post( $post_id );
		$this->duplicator->duplicate( $post );

		$this->assertEquals( $thumbnail_id, $received_params['thumbnail_id'] );
		$this->assertIsInt( $received_params['from_id'] );
		$this->assertIsInt( $received_params['to_id'] );
	}

	/**
	 * Test duplicate_as_after_duplicate action fires with correct parameters.
	 *
	 * @return void
	 */
	public function test_after_duplicate_action_fires(): void {
		$action_fired    = false;
		$received_params = array();

		add_action(
			'duplicate_as_after_duplicate',
			function ( $new_id, $original_id ) use ( &$action_fired, &$received_params ) {
				$action_fired    = true;
				$received_params = compact( 'new_id', 'original_id' );
			},
			10,
			2
		);

		$post_id = self::factory()->post->create();
		$post    = get_post( $post_id );
		$new_id  = $this->duplicator->duplicate( $post );

		$this->assertTrue( $action_fired );
		$this->assertEquals( $new_id, $received_params['new_id'] );
		$this->assertEquals( $post_id, $received_params['original_id'] );
	}

	/**
	 * Test chaining multiple filters together.
	 *
	 * @return void
	 */
	public function test_filter_chaining(): void {
		// Filter 1: Add prefix to title.
		add_filter(
			'duplicate_as_post_data',
			function ( $data ) {
				$data['post_title'] = 'Copy: ' . $data['post_title'];
				return $data;
			}
		);

		// Filter 2: Exclude custom meta.
		add_filter(
			'duplicate_as_excluded_meta_keys',
			function ( $keys ) {
				$keys[] = '_skip_this';
				return $keys;
			}
		);

		// Filter 3: Reset counter meta.
		add_filter(
			'duplicate_as_meta_value',
			function ( $value, $key ) {
				if ( '_view_count' === $key ) {
					return '0';
				}
				return $value;
			},
			10,
			2
		);

		$post_id = self::factory()->post->create( array( 'post_title' => 'Chain Test' ) );
		add_post_meta( $post_id, '_skip_this', 'should_not_copy' );
		add_post_meta( $post_id, '_view_count', '999' );
		add_post_meta( $post_id, '_keep_this', 'should_copy' );

		$post   = get_post( $post_id );
		$new_id = $this->duplicator->duplicate( $post );

		$new_post = get_post( $new_id );
		$this->assertEquals( 'Copy: Chain Test', $new_post->post_title );
		$this->assertEmpty( get_post_meta( $new_id, '_skip_this', true ) );
		$this->assertEquals( '0', get_post_meta( $new_id, '_view_count', true ) );
		$this->assertEquals( 'should_copy', get_post_meta( $new_id, '_keep_this', true ) );
	}
}
