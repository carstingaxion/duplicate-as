/**
 * Mock for CSS/SCSS imports in Jest tests.
 *
 * When JS files import stylesheets, Jest cannot parse them.
 * This mock returns an empty object so imports resolve cleanly.
 *
 * @package
 * @since   0.4.0
 */
module.exports = {};
