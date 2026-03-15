<?php
/**
 * PHPUnit bootstrap file for Duplicate As plugin tests.
 *
 * Loads the WordPress test framework and the plugin under test.
 * Supports both wp-env and manual WP_TESTS_DIR configurations.
 *
 * Usage with wp-env:
 *   wp-env run tests-cli --env-cwd='wp-content/plugins/duplicate-as' \
 *     bash -c 'WP_TESTS_DIR=/wordpress-phpunit composer test'
 *
 * @package DuplicateAs\Tests
 */

// Composer autoloader.
$composer_autoload = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( file_exists( $composer_autoload ) ) {
	require_once $composer_autoload;
}

// Determine the WordPress test suite location.
// Priority: WP_TESTS_DIR env var > wp-env default > local fallback.
$wp_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $wp_tests_dir ) {
	// wp-env default location for the test suite.
	$wp_tests_dir = '/wordpress-phpunit';
}

if ( ! file_exists( $wp_tests_dir . '/includes/functions.php' ) ) {
	// Try the system tmp directory as a fallback (common for local installs).
	$wp_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $wp_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find WordPress test suite at: {$wp_tests_dir}" . PHP_EOL;
	echo PHP_EOL;
	echo 'Set the WP_TESTS_DIR environment variable to point to your WordPress test suite.' . PHP_EOL;
	echo 'When using wp-env, run:' . PHP_EOL;
	echo '  npx wp-env run tests-cli --env-cwd="wp-content/plugins/duplicate-as" bash -c "WP_TESTS_DIR=/wordpress-phpunit vendor/bin/phpunit"' . PHP_EOL;
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $wp_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested before tests run.
 *
 * This function is hooked into `muplugins_loaded` so it runs before WordPress
 * finishes loading. This ensures our plugin code is available for all tests.
 */
tests_add_filter(
	'muplugins_loaded',
	function () {
		// Load our plugin.
		require dirname( __DIR__ ) . '/plugin.php';
	}
);

// Start up the WP testing environment.
require $wp_tests_dir . '/includes/bootstrap.php';
