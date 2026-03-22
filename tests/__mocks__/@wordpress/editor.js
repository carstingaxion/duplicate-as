/**
 * Mock for @wordpress/editor
 *
 * Provides mock implementations of editor components and store reference.
 *
 * @package
 * @since   0.4.0
 */
const React = require( 'react' );

const PluginPostStatusInfo = ( { children, className } ) =>
	React.createElement(
		'div',
		{ 'data-testid': 'plugin-post-status-info', className },
		children
	);

const store = 'core/editor';

module.exports = {
	PluginPostStatusInfo,
	store,
};
