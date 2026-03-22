/**
 * Mock for @wordpress/api-fetch
 *
 * Default export is a jest.fn() that can be configured per test.
 *
 * @package
 * @since   0.4.0
 */
const apiFetch = jest.fn();
module.exports = apiFetch;
module.exports.__esModule = true;
module.exports.default = apiFetch;
