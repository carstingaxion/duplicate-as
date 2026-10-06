/* global afterEach */
/**
 * Mock for @testing-library/react
 *
 * Provides lightweight implementations of render, screen, fireEvent,
 * renderHook, and act that work without installing @testing-library/react.
 *
 * Uses React 18 createRoot API to avoid ReactDOM.render deprecation warnings.
 *
 * @package
 * @since   0.4.0
 */
const React = require( 'react' );
const ReactDOMClient = require( 'react-dom/client' );

/**
 * Wrapper for React.act() — the recommended API for React 18+.
 * Falls back to a simple callback executor if unavailable.
 *
 * @param {Function} callback - Callback to run inside act.
 * @return {*} Return value of the callback.
 */
function act( callback ) {
	// React 18 exports act from 'react'
	if ( typeof React.act === 'function' ) {
		return React.act( callback );
	}
	// Fallback for older React versions
	const result = callback();
	if ( result && typeof result.then === 'function' ) {
		return result;
	}
	return result;
}

/**
 * Containers mounted during the current test.
 *
 * The queries below look at the whole document body, so containers left
 * behind by an earlier test would be matched before the current one.
 * `cleanup()` runs after every test to prevent that.
 *
 * @type {Array<{root: Object, container: HTMLElement}>}
 */
const mountedRoots = [];

/**
 * Unmount every container mounted since the last cleanup and remove it
 * from the document.
 */
function cleanup() {
	for ( const { root, container } of mountedRoots ) {
		act( () => {
			root.unmount();
		} );
		container.remove();
	}
	mountedRoots.length = 0;
}

/**
 * Minimal render function that mounts a component into a container
 * using the React 18 createRoot API.
 *
 * @param {React.ReactElement} ui      - React element to render.
 * @param {Object}             options - Render options.
 * @return {Object} Object with container, unmount, and rerender.
 */
function render( ui, options = {} ) {
	const container = options.container || document.createElement( 'div' );
	document.body.appendChild( container );

	const root = ReactDOMClient.createRoot( container );
	mountedRoots.push( { root, container } );

	act( () => {
		root.render( ui );
	} );

	return {
		container,
		unmount: () => {
			act( () => {
				root.unmount();
			} );
			container.remove();
			const index = mountedRoots.findIndex(
				( entry ) => entry.root === root
			);
			if ( index !== -1 ) {
				mountedRoots.splice( index, 1 );
			}
		},
		rerender: ( newUi ) => {
			act( () => {
				root.render( newUi );
			} );
		},
	};
}

/**
 * Minimal screen object that queries the document body.
 */
const screen = {
	getByText: ( text ) => {
		const walk = ( node ) => {
			for ( const child of node.childNodes ) {
				if (
					child.nodeType === 3 &&
					child.textContent.includes( text )
				) {
					return child.parentElement;
				}
				const found = walk( child );
				if ( found ) {
					return found;
				}
			}
			return null;
		};
		const el = walk( document.body );
		if ( ! el ) {
			throw new Error(
				`Unable to find an element with the text: ${ text }`
			);
		}
		return el;
	},
	getByRole: ( role ) => {
		const el =
			document.body.querySelector(
				`[${ role === 'button' ? 'button' : `role="${ role }"` }]`
			) ||
			( role === 'button'
				? document.body.querySelector( 'button' )
				: null );
		if ( ! el ) {
			throw new Error(
				`Unable to find an element with the role: ${ role }`
			);
		}
		return el;
	},
	getByTestId: ( testId ) => {
		const el = document.body.querySelector( `[data-testid="${ testId }"]` );
		if ( ! el ) {
			throw new Error(
				`Unable to find an element with data-testid: ${ testId }`
			);
		}
		return el;
	},
	queryByText: ( text ) => {
		try {
			return screen.getByText( text );
		} catch ( e ) {
			return null;
		}
	},
	queryByTestId: ( testId ) => {
		return document.body.querySelector( `[data-testid="${ testId }"]` );
	},
};

/**
 * Minimal fireEvent helper.
 */
const fireEvent = {
	click: ( el ) => {
		const event = new window.MouseEvent( 'click', {
			bubbles: true,
			cancelable: true,
		} );
		act( () => {
			el.dispatchEvent( event );
		} );
	},
	change: ( el, options ) => {
		const event = new window.Event( 'change', { bubbles: true } );
		Object.defineProperty( event, 'target', { value: options.target } );
		act( () => {
			el.dispatchEvent( event );
		} );
	},
};

/**
 * Minimal renderHook implementation using React 18 createRoot API.
 *
 * @param {Function} hookFn - Hook function to test.
 * @return {Object} Object with result ref, unmount, and rerender.
 */
function renderHook( hookFn ) {
	const result = { current: null };

	function TestComponent() {
		result.current = hookFn();
		return null;
	}

	const container = document.createElement( 'div' );
	document.body.appendChild( container );
	const root = ReactDOMClient.createRoot( container );
	mountedRoots.push( { root, container } );

	act( () => {
		root.render( React.createElement( TestComponent ) );
	} );

	return {
		result,
		unmount: () => {
			act( () => {
				root.unmount();
			} );
			container.remove();
			const index = mountedRoots.findIndex(
				( entry ) => entry.root === root
			);
			if ( index !== -1 ) {
				mountedRoots.splice( index, 1 );
			}
		},
		rerender: () => {
			act( () => {
				root.render( React.createElement( TestComponent ) );
			} );
		},
	};
}

// Custom matchers - add to expect
if ( typeof expect !== 'undefined' && expect.extend ) {
	expect.extend( {
		toBeInTheDocument( received ) {
			const pass =
				received !== null && document.body.contains( received );
			return {
				pass,
				message: () =>
					pass
						? `expected element not to be in the document`
						: `expected element to be in the document`,
			};
		},
		toBeDisabled( received ) {
			const pass = received && received.disabled === true;
			return {
				pass,
				message: () =>
					pass
						? `expected element not to be disabled`
						: `expected element to be disabled`,
			};
		},
		toHaveClass( received, className ) {
			const pass =
				received &&
				received.classList &&
				received.classList.contains( className );
			return {
				pass,
				message: () =>
					pass
						? `expected element not to have class "${ className }"`
						: `expected element to have class "${ className }"`,
			};
		},
	} );
}

// Unmount rendered trees after every test so queries in the next test
// do not match leftovers from the previous one.
if ( typeof afterEach === 'function' ) {
	afterEach( cleanup );
}

module.exports = {
	render,
	screen,
	fireEvent,
	renderHook,
	act,
	cleanup,
};
