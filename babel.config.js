/**
 * Babel configuration for Jest test transforms.
 *
 * Uses the WordPress default Babel preset which handles JSX,
 * ES modules, and other modern JavaScript features.
 *
 * @package
 */

module.exports = function ( api ) {
	api.cache( true );

	return {
		presets: [ '@wordpress/babel-preset-default' ],
	};
};
