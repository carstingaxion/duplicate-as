/**
 * Mock for @wordpress/data
 *
 * Provides mock implementations of useSelect, useDispatch, select, and dispatch.
 * Individual tests can override these via jest.mock() if needed.
 *
 * @package DuplicateAs\Tests
 * @since   0.4.0
 */
const useSelect = jest.fn( ( selector ) => selector( jest.fn() ) );
const useDispatch = jest.fn( () => ( {} ) );
const select = jest.fn( () => ( {} ) );
const dispatch = jest.fn( () => ( {
	createNotice: jest.fn(),
} ) );
const createReduxStore = jest.fn();
const register = jest.fn();

module.exports = {
	useSelect,
	useDispatch,
	select,
	dispatch,
	createReduxStore,
	register,
};
