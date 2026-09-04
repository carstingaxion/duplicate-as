/**
 * Mock for @wordpress/i18n
 *
 * Provides passthrough implementations of i18n functions.
 *
 * @param str
 * @package
 * @since   0.4.0
 */
const __ = ( str ) => str;
const _x = ( str ) => str;
const _n = ( single, plural, number ) => ( number === 1 ? single : plural );
const _nx = ( single, plural, number ) => ( number === 1 ? single : plural );
const sprintf = ( format, ...args ) => {
	let i = 0;
	return format.replace( /%s/g, () =>
		args[ i ] !== undefined ? args[ i++ ] : ''
	);
};

module.exports = { __, _x, _n, _nx, sprintf };
