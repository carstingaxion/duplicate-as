/**
 * Mock for @wordpress/components
 *
 * Provides minimal mock implementations of commonly used WP components.
 * Each renders a simple HTML element with forwarded props.
 *
 * @package DuplicateAs\Tests
 * @since   0.4.0
 */
const React = require( 'react' );

const Button = ( { children, onClick, disabled, isBusy, icon, className, variant, isPressed, ...rest } ) =>
	React.createElement(
		'button',
		{
			onClick,
			disabled,
			className,
			'data-is-busy': isBusy ? 'true' : undefined,
			'data-icon': icon,
			'data-variant': variant,
			...rest,
		},
		children
	);

const TextControl = ( props ) => React.createElement( 'input', { type: 'text', ...props } );
const SelectControl = ( props ) => React.createElement( 'select', props );
const Spinner = () => React.createElement( 'span', { className: 'components-spinner' } );
const Notice = ( { children } ) => React.createElement( 'div', { className: 'components-notice' }, children );
const Panel = ( { children } ) => React.createElement( 'div', null, children );
const PanelBody = ( { children } ) => React.createElement( 'div', null, children );
const PanelRow = ( { children } ) => React.createElement( 'div', null, children );

module.exports = {
	Button,
	TextControl,
	SelectControl,
	Spinner,
	Notice,
	Panel,
	PanelBody,
	PanelRow,
};
