/**
 * Mock for @wordpress/components
 *
 * Provides minimal mock implementations of commonly used WP components.
 * Each renders a simple HTML element with forwarded props.
 *
 * @package
 * @since   0.4.0
 */
const React = require( 'react' );

/**
 * Resolve an icon prop to a name that tests can assert on.
 *
 * String icons are Dashicon names. Object icons are `@wordpress/icons`
 * components, which the shared icons mock tags with a `name` property.
 *
 * @param {string|Object|undefined} icon - Icon prop value.
 * @return {string|undefined} Icon name, or undefined when no icon is set.
 */
const iconName = ( icon ) => {
	if ( ! icon ) {
		return undefined;
	}
	if ( typeof icon === 'string' ) {
		return icon;
	}
	return icon.name || 'icon-object';
};

const Button = ( {
	children,
	onClick,
	disabled,
	isBusy,
	icon,
	className,
	variant,
	isPressed,
	...rest
} ) =>
	React.createElement(
		'button',
		{
			onClick,
			disabled,
			className,
			'data-is-busy': isBusy ? 'true' : undefined,
			'data-icon': iconName( icon ),
			'data-icon-is-object':
				icon && typeof icon === 'object' ? 'true' : undefined,
			'data-variant': variant,
			...rest,
		},
		children
	);

const TextControl = ( props ) =>
	React.createElement( 'input', { type: 'text', ...props } );
const SelectControl = ( props ) => React.createElement( 'select', props );
const Spinner = () =>
	React.createElement( 'span', { className: 'components-spinner' } );
const Notice = ( { children } ) =>
	React.createElement( 'div', { className: 'components-notice' }, children );
const Panel = ( { children } ) => React.createElement( 'div', null, children );
const PanelBody = ( { children } ) =>
	React.createElement( 'div', null, children );
const PanelRow = ( { children } ) =>
	React.createElement( 'div', null, children );

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
