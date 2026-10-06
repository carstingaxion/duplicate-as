/**
 * Mock for @wordpress/icons
 *
 * Provides lightweight stand-ins for the icon components imported by
 * src/utils.js and src/constants.js. Each icon is a plain object with a
 * `name` property, so tests can assert which icon was selected without
 * rendering the real SVG markup.
 *
 * @package
 * @since   0.6.0
 */

/**
 * Build a mock icon component for a given slug.
 *
 * @param {string} name - Icon slug.
 * @return {Object} Mock icon object.
 */
const makeIcon = ( name ) => ( {
	__mockIcon: true,
	name,
} );

const icons = [
	'archive',
	'audio',
	'brush',
	'calendar',
	'category',
	'chevronLeft',
	'comment',
	'copy',
	'dashboard',
	'file',
	'grid',
	'home',
	'image',
	'link',
	'list',
	'media',
	'menu',
	'page',
	'pencil',
	'people',
	'pin',
	'plugins',
	'plus',
	'post',
	'search',
	'settings',
	'sites',
	'tag',
	'tool',
	'update',
	'video',
	'wordpress',
];

module.exports = icons.reduce( ( acc, name ) => {
	acc[ name ] = makeIcon( name );
	return acc;
}, {} );
