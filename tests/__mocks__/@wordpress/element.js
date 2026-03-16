/**
 * Mock for @wordpress/element
 *
 * Re-exports React primitives so components using @wordpress/element work in tests.
 *
 * @package DuplicateAs\Tests
 * @since   0.4.0
 */
const React = require( 'react' );

module.exports = {
	...React,
	useState: React.useState,
	useEffect: React.useEffect,
	useCallback: React.useCallback,
	useMemo: React.useMemo,
	useRef: React.useRef,
	useContext: React.useContext,
	useReducer: React.useReducer,
	createElement: React.createElement,
	Fragment: React.Fragment,
	createPortal: require( 'react-dom' ).createPortal,
	render: require( 'react-dom' ).render,
	createRoot: require( 'react-dom/client' ).createRoot,
	RawHTML: ( { children } ) => React.createElement( 'div', { dangerouslySetInnerHTML: { __html: children } } ),
};
