/**
 * Unit tests for src/hooks/use-post-data.js
 *
 * Tests the usePostData hook that selects post-related data
 * from WordPress core and editor stores.
 *
 * @package
 * @since   0.3.0
 */

import { renderHook } from '@testing-library/react';
import { usePostData } from '../../../../src/hooks/use-post-data';

// Mock @wordpress/data
const mockSelect = jest.fn();
jest.mock( '@wordpress/data', () => ( {
	useSelect: ( selector ) => selector( mockSelect ),
} ) );

// Mock store references
jest.mock( '@wordpress/editor', () => ( {
	store: 'core/editor',
} ) );

jest.mock( '@wordpress/core-data', () => ( {
	store: 'core',
} ) );

describe( 'usePostData', () => {
	let editorSelectors;
	let coreSelectors;

	beforeEach( () => {
		editorSelectors = {
			getCurrentPostType: jest.fn().mockReturnValue( 'post' ),
			getCurrentPostId: jest.fn().mockReturnValue( 42 ),
		};

		coreSelectors = {
			getPostType: jest.fn().mockReturnValue( {
				slug: 'post',
				supports: { duplicate_as: true },
				labels: { singular_name: 'Post' },
			} ),
			getPostTypes: jest.fn().mockReturnValue( [
				{ slug: 'post', supports: { duplicate_as: true } },
				{ slug: 'page', supports: {} },
			] ),
		};

		mockSelect.mockImplementation( ( storeName ) => {
			if ( storeName === 'core/editor' ) {
				return editorSelectors;
			}
			if ( storeName === 'core' ) {
				return coreSelectors;
			}
			return {};
		} );
	} );

	afterEach( () => {
		jest.clearAllMocks();
	} );

	it( 'returns postId from editor store', () => {
		const { result } = renderHook( () => usePostData() );
		expect( result.current.postId ).toBe( 42 );
	} );

	it( 'returns postType from editor store', () => {
		const { result } = renderHook( () => usePostData() );
		expect( result.current.postType ).toBe( 'post' );
	} );

	it( 'returns postTypeObject from core store', () => {
		const { result } = renderHook( () => usePostData() );
		expect( result.current.postTypeObject ).toEqual( {
			slug: 'post',
			supports: { duplicate_as: true },
			labels: { singular_name: 'Post' },
		} );
	} );

	it( 'returns allPostTypes from core store', () => {
		const { result } = renderHook( () => usePostData() );
		expect( result.current.allPostTypes ).toHaveLength( 2 );
	} );

	it( 'calls getPostType with current post type', () => {
		renderHook( () => usePostData() );
		expect( coreSelectors.getPostType ).toHaveBeenCalledWith( 'post' );
	} );

	it( 'calls getPostTypes with per_page -1', () => {
		renderHook( () => usePostData() );
		expect( coreSelectors.getPostTypes ).toHaveBeenCalledWith( {
			per_page: -1,
		} );
	} );

	it( 'returns empty array when getPostTypes returns null', () => {
		coreSelectors.getPostTypes.mockReturnValue( null );
		const { result } = renderHook( () => usePostData() );
		expect( result.current.allPostTypes ).toEqual( [] );
	} );

	it( 'returns undefined postTypeObject when getPostType returns undefined', () => {
		coreSelectors.getPostType.mockReturnValue( undefined );
		const { result } = renderHook( () => usePostData() );
		expect( result.current.postTypeObject ).toBeUndefined();
	} );
} );
