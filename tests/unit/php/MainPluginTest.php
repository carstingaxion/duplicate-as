<?php
/**
 * Unit tests for the main plugin file and bootstrapping.
 *
 * Tests plugin constants, autoloader, text domain loading,
 * and plugin initialization.
 *
 * @package DuplicateAs\Tests\Unit
 * @since   0.3.0
 */

class MainPluginTest extends WP_UnitTestCase {

	/**
	 * Test DUPLICATE_AS_VERSION constant is defined.
	 *
	 * @return void
	 */
	public function test_version_constant_defined(): void {
		$this->assertTrue( defined( 'DUPLICATE_AS_VERSION' ) );
		$this->assertIsString( DUPLICATE_AS_VERSION );
	}

	/**
	 * Test DUPLICATE_AS_PLUGIN_FILE constant is defined.
	 *
	 * @return void
	 */
	public function test_plugin_file_constant_defined(): void {
		$this->assertTrue( defined( 'DUPLICATE_AS_PLUGIN_FILE' ) );
		$this->assertFileExists( DUPLICATE_AS_PLUGIN_FILE );
	}

	/**
	 * Test autoloader is registered.
	 *
	 * @return void
	 */
	public function test_autoloader_registered(): void {
		$autoloaders = spl_autoload_functions();
		$found       = false;

		foreach ( $autoloaders as $autoloader ) {
			if ( is_string( $autoloader ) && 'duplicate_as_autoloader' === $autoloader ) {
				$found = true;
				break;
			}
		}

		$this->assertTrue( $found, 'Autoloader should be registered.' );
	}

	/**
	 * Test autoloader loads known classes.
	 *
	 * @return void
	 */
	public function test_autoloader_loads_classes(): void {
		$this->assertTrue( class_exists( 'Duplicate_As_Post_Type_Support' ) );
		$this->assertTrue( class_exists( 'Duplicate_As_Permissions' ) );
		$this->assertTrue( class_exists( 'Duplicate_As_Duplicator' ) );
		$this->assertTrue( class_exists( 'Duplicate_As_Rest_Api' ) );
		$this->assertTrue( class_exists( 'Duplicate_As_Admin_Actions' ) );
		$this->assertTrue( class_exists( 'Duplicate_As_Row_Actions' ) );
		$this->assertTrue( class_exists( 'Duplicate_As_Assets' ) );
	}

	/**
	 * Test autoloader ignores non-plugin classes.
	 *
	 * @return void
	 */
	public function test_autoloader_ignores_other_classes(): void {
		// This should not cause any errors.
		$this->assertFalse( class_exists( 'Some_Other_Class_Not_Ours' ) );
	}

	/**
	 * Test text domain loading hook is registered.
	 *
	 * @return void
	 */
	public function test_textdomain_hook_registered(): void {
		$this->assertIsInt( has_action( 'init', 'duplicate_as_load_textdomain' ) );
	}

	/**
	 * Test plugin initialization hook is registered.
	 *
	 * @return void
	 */
	public function test_init_hook_registered(): void {
		$this->assertIsInt( has_action( 'plugins_loaded', 'duplicate_as_init' ) );
	}
}
