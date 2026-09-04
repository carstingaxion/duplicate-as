/**
 * Unit tests for src/hooks/use-duplicate.js
 *
 * Tests the useDuplicate hook's loading state management,
 * API call behavior, error handling, and redirect logic.
 *
 * @package
 * @since   0.3.0
 */

import { renderHook, act } from '@testing-library/react';
import { useDuplicate } from '../../../../src/hooks/use-duplicate';

// Mock @wordpress/api-fetch
const mockApiFetch = jest.fn();
jest.mock( '@wordpress/api-fetch', () => ( {
	__esModule: true,
	default: ( ...args ) => mockApiFetch( ...args ),
} ) );

// Mock @wordpress/data dispatch
const mockCreateNotice = jest.fn();
jest.mock( '@wordpress/data', () => ( {
	dispatch: () => ( {
		createNotice: mockCreateNotice,
	} ),
} ) );

// Mock window.location
const originalLocation = window.location;

beforeAll( () => {
	delete window.location;
	window.location = { href: '' };
} );

afterAll( () => {
	window.location = originalLocation;
} );

beforeEach( () => {
	jest.clearAllMocks();
	window.location.href = '';
} );

describe( 'useDuplicate', () => {
	it( 'returns isLoading as false initially', () => {
		const { result } = renderHook( () => useDuplicate() );

		expect( result.current.isLoading ).toBe( false );
	} );

	it( 'returns handleDuplicate as a function', () => {
		const { result } = renderHook( () => useDuplicate() );

		expect( typeof result.current.handleDuplicate ).toBe( 'function' );
	} );

	it( 'does nothing when postId is falsy', async () => {
		const { result } = renderHook( () => useDuplicate() );

		await act( async () => {
			await result.current.handleDuplicate( 0, null );
		} );

		expect( mockApiFetch ).not.toHaveBeenCalled();
	} );

	it( 'calls apiFetch with correct path for duplication', async () => {
		mockApiFetch.mockResolvedValue( {
			success: true,
			edit_url:
				'http://example.com/wp-admin/post.php?post=456&action=edit',
		} );

		const { result } = renderHook( () => useDuplicate() );

		await act( async () => {
			await result.current.handleDuplicate( 123, null );
		} );

		expect( mockApiFetch ).toHaveBeenCalledWith( {
			path: '/duplicate-as/v1/duplicate/123',
			method: 'POST',
			data: {},
		} );
	} );

	it( 'calls apiFetch with target_post_type for transformation', async () => {
		mockApiFetch.mockResolvedValue( {
			success: true,
			edit_url:
				'http://example.com/wp-admin/post.php?post=789&action=edit',
		} );

		const { result } = renderHook( () => useDuplicate() );

		await act( async () => {
			await result.current.handleDuplicate( 123, 'page' );
		} );

		expect( mockApiFetch ).toHaveBeenCalledWith( {
			path: '/duplicate-as/v1/duplicate/123',
			method: 'POST',
			data: { target_post_type: 'page' },
		} );
	} );

	it( 'redirects to edit_url on success', async () => {
		const editUrl =
			'http://example.com/wp-admin/post.php?post=456&action=edit';
		mockApiFetch.mockResolvedValue( {
			success: true,
			edit_url: editUrl,
		} );

		const { result } = renderHook( () => useDuplicate() );

		await act( async () => {
			await result.current.handleDuplicate( 123, null );
		} );

		expect( window.location.href ).toBe( editUrl );
	} );

	it( 'shows error notice on duplication failure', async () => {
		mockApiFetch.mockRejectedValue( new Error( 'API Error' ) );

		const { result } = renderHook( () => useDuplicate() );

		await act( async () => {
			await result.current.handleDuplicate( 123, null );
		} );

		expect( mockCreateNotice ).toHaveBeenCalledWith(
			'error',
			'Failed to duplicate post. Please try again.',
			{ isDismissible: true }
		);
	} );

	it( 'shows transform error notice on transformation failure', async () => {
		mockApiFetch.mockRejectedValue( new Error( 'API Error' ) );

		const { result } = renderHook( () => useDuplicate() );

		await act( async () => {
			await result.current.handleDuplicate( 123, 'page' );
		} );

		expect( mockCreateNotice ).toHaveBeenCalledWith(
			'error',
			'Failed to transform post. Please try again.',
			{ isDismissible: true }
		);
	} );

	it( 'resets isLoading after error', async () => {
		mockApiFetch.mockRejectedValue( new Error( 'API Error' ) );

		const { result } = renderHook( () => useDuplicate() );

		await act( async () => {
			await result.current.handleDuplicate( 123, null );
		} );

		expect( result.current.isLoading ).toBe( false );
	} );

	it( 'does not redirect when response has no edit_url', async () => {
		mockApiFetch.mockResolvedValue( {
			success: true,
			edit_url: null,
		} );

		const { result } = renderHook( () => useDuplicate() );

		await act( async () => {
			await result.current.handleDuplicate( 123, null );
		} );

		expect( window.location.href ).toBe( '' );
	} );

	it( 'does not redirect when success is false', async () => {
		mockApiFetch.mockResolvedValue( {
			success: false,
			edit_url: 'http://example.com/test',
		} );

		const { result } = renderHook( () => useDuplicate() );

		await act( async () => {
			await result.current.handleDuplicate( 123, null );
		} );

		expect( window.location.href ).toBe( '' );
	} );
} );
