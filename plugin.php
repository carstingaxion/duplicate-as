<?php
/**
 * Plugin Name:       Duplicate as
 * Description:       Duplicate or Duplicate as different post type, directly from the Editor Sidebar or the Admin List Tables.
 * Version:           0.2.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Carsten Bach
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       duplicate-as
 * Domain Path:       /languages
 *
 * @package DuplicateAs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version constant.
 *
 * @since 0.3.0
 * @var string
 */
if ( ! defined( 'DUPLICATE_AS_VERSION' ) ) {
	define( 'DUPLICATE_AS_VERSION', '0.3.0' );
}

/**
 * Plugin file path constant.
 *
 * @since 0.3.0
 * @var string
 */
if ( ! defined( 'DUPLICATE_AS_PLUGIN_FILE' ) ) {
	define( 'DUPLICATE_AS_PLUGIN_FILE', __FILE__ );
}

if ( ! function_exists( 'duplicate_as_autoloader' ) ) {
	/**
	 * Autoload class files from includes/classes directory.
	 *
	 * Maps class names to file paths using WordPress naming conventions:
	 * - Class: Duplicate_As_Post_Type_Support
	 * - File:  includes/classes/class-post-type-support.php
	 *
	 * @since 0.3.0
	 * @param string $class_name Fully qualified class name to autoload.
	 * @return void
	 */
	function duplicate_as_autoloader( string $class_name ): void {
		// Only autoload classes with our prefix.
		if ( 0 !== strpos( $class_name, 'Duplicate_As_' ) ) {
			return;
		}

		// Convert class name to file name.
		// Example: Duplicate_As_Post_Type_Support -> post-type-support.
		$file_name = str_replace( 'Duplicate_As_', '', $class_name );
		$file_name = strtolower( str_replace( '_', '-', $file_name ) );
		$file_path = plugin_dir_path( DUPLICATE_AS_PLUGIN_FILE ) . 'includes/classes/class-' . $file_name . '.php';

		if ( file_exists( $file_path ) ) {
			require_once $file_path;
		}
	}
	spl_autoload_register( 'duplicate_as_autoloader' );
}

if ( ! function_exists( 'duplicate_as_load_textdomain' ) ) {
	/**
	 * Load the plugin text domain for translations.
	 *
	 * Loads translation files from the languages/ directory within the plugin.
	 * WordPress will also check WP_LANG_DIR/plugins/ for override translations.
	 *
	 * @since 0.3.0
	 * @return void
	 */
	function duplicate_as_load_textdomain(): void {
		load_plugin_textdomain(
			'duplicate-as',
			false,
			dirname( plugin_basename( DUPLICATE_AS_PLUGIN_FILE ) ) . '/languages'
		);
	}
	add_action( 'init', 'duplicate_as_load_textdomain' );
}

if ( ! function_exists( 'duplicate_as_init' ) ) {
	/**
	 * Initialize the plugin.
	 *
	 * Bootstraps all singleton class instances in the correct order.
	 * Each class registers its own WordPress hooks internally.
	 *
	 * Load order:
	 * 1. Post_Type_Support - Registers post type supports (must be first)
	 * 2. Permissions       - Permission checks (depends on Post_Type_Support)
	 * 3. Duplicator        - Core duplication logic (standalone)
	 * 4. Rest_Api          - REST endpoint (depends on all above)
	 * 5. Admin_Actions     - Admin action handlers (depends on Permissions, Duplicator)
	 * 6. Row_Actions       - List table links (depends on Post_Type_Support)
	 * 7. Assets            - Editor scripts/styles (standalone)
	 *
	 * @since 0.1.0
	 * @return void
	 */
	function duplicate_as_init(): void {
		Duplicate_As_Post_Type_Support::get_instance();
		Duplicate_As_Permissions::get_instance();
		Duplicate_As_Duplicator::get_instance();
		Duplicate_As_Rest_Api::get_instance();
		Duplicate_As_Admin_Actions::get_instance();
		Duplicate_As_Row_Actions::get_instance();
		Duplicate_As_Assets::get_instance( DUPLICATE_AS_PLUGIN_FILE );
	}
	add_action( 'plugins_loaded', 'duplicate_as_init' );
}
