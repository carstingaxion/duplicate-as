<?php
/**
 * Figuren_Theater duplicate_as.
 *
 * @package figuren-theater/duplicate-as
 */

namespace Figuren_Theater\duplicate_as;

use Altis;

/**
 * Register module.
 *
 * @return void
 */
function register() :void {

	$default_settings = [
		'enabled' => true, // Needs to be set.
	];
	$options = [
		'defaults' => $default_settings,
	];

	Altis\register_module(
		'duplicate-as',
		DIRECTORY,
		'duplicate_as',
		$options,
		__NAMESPACE__ . '\\bootstrap'
	);
}

/**
 * Bootstrap module, when enabled.
 *
 * @return void
 */
function bootstrap() :void {

	/**
	 * Automatically load Plugins.
	 *
	 * @example NameSpace\bootstrap();
	 */

	/**
	 * Load 'Best practices'.
	 *
	 * @example NameSpace\bootstrap();
	 */
}
