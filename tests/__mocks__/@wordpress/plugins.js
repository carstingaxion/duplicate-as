/**
 * Mock for @wordpress/plugins
 *
 * Provides a no-op registerPlugin function.
 *
 * @package DuplicateAs\Tests
 * @since   0.4.0
 */
const registerPlugin = jest.fn();
const unregisterPlugin = jest.fn();

module.exports = {
	registerPlugin,
	unregisterPlugin,
};
