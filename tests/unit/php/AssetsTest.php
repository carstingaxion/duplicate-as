<?php
/**
 * Unit tests for Duplicate_As_Assets.
 *
 * Tests editor asset enqueueing behavior.
 *
 * @package DuplicateAs\Tests\Unit
 * @since   0.3.0
 */

class AssetsTest extends WP_UnitTestCase {

	/**
	 * Instance under test.
	 *
	 * @var Duplicate_As_Assets
	 */
	private Duplicate_As_Assets $assets;

	/**
	 * Set up each test.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();
		$this->assets = Duplicate_As_Assets::get_instance( DUPLICATE_AS_PLUGIN_FILE );
	}

	/**
	 * Test singleton pattern returns same instance.
	 *
	 * @covers Duplicate_As_Assets::get_instance
	 * @return void
	 */
	public function test_singleton_returns_same_instance(): void {
		$instance1 = Duplicate_As_Assets::get_instance();
		$instance2 = Duplicate_As_Assets::get_instance();
		$this->assertSame( $instance1, $instance2 );
	}

	/**
	 * Test that enqueue_block_editor_assets hook is registered.
	 *
	 * @covers Duplicate_As_Assets::__construct
	 * @return void
	 */
	public function test_editor_assets_hook_registered(): void {
		$this->assertIsInt(
			has_action( 'enqueue_block_editor_assets', array( $this->assets, 'enqueue_editor_assets' ) )
		);
	}

	/**
	 * Test that enqueue_editor_assets enqueues the script when asset file exists.
	 *
	 * @covers Duplicate_As_Assets::enqueue_editor_assets
	 * @return void
	 */
	public function test_enqueue_editor_assets_registers_script(): void {
		// Only run if build assets exist.
		$asset_path = plugin_dir_path( DUPLICATE_AS_PLUGIN_FILE ) . 'build/index.asset.php';
		if ( ! file_exists( $asset_path ) ) {
			$this->markTestSkipped( 'Build assets not found. Run npm run build first.' );
		}

		$this->assets->enqueue_editor_assets();

		$this->assertTrue( wp_script_is( 'duplicate-as-editor', 'enqueued' ) );
	}

	/**
	 * Test that enqueue_editor_assets enqueues the style when asset file exists.
	 *
	 * @covers Duplicate_As_Assets::enqueue_editor_assets
	 * @return void
	 */
	public function test_enqueue_editor_assets_registers_style(): void {
		$asset_path = plugin_dir_path( DUPLICATE_AS_PLUGIN_FILE ) . 'build/index.asset.php';
		if ( ! file_exists( $asset_path ) ) {
			$this->markTestSkipped( 'Build assets not found. Run npm run build first.' );
		}

		$this->assets->enqueue_editor_assets();

		$this->assertTrue( wp_style_is( 'duplicate-as-editor', 'enqueued' ) );
	}
}
