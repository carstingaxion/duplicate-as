/**
 * Unit tests for src/components/duplicate-post-status-info.js
 *
 * Tests the container component that reads editor state and renders buttons.
 *
 * @package DuplicateAs\Tests\Unit\JS
 * @since   0.3.0
 */

import { render, screen } from '@testing-library/react';
import { DuplicatePostStatusInfo } from '../../../../src/components/duplicate-post-status-info';

// Mock usePostData hook
let mockPostData = {};
jest.mock( '../../../../src/hooks/use-post-data', () => ( {
	usePostData: () => mockPostData,
} ) );

// Mock DuplicateButton component
jest.mock( '../../../../src/components/duplicate-button', () => ( {
	DuplicateButton: ( { postId, targetPostType, currentPostType } ) => (
		<button data-testid={ `btn-${ targetPostType || 'duplicate' }` }>
			{ targetPostType
				? `Transform to ${ targetPostType }`
				: 'Duplicate' }
		</button>
	),
} ) );
/* 
// Mock PluginPostStatusInfo
jest.mock( '@wordpress/editor', () => ( {
	PluginPostStatusInfo: ( { children, className } ) => (
		<div data-testid="plugin-post-status-info" className={ className }>
			{ children }
		</div>
	),
} ) );

// Mock i18n
jest.mock( '@wordpress/i18n', () => ( {
	__: ( str ) => str,
	sprintf: ( format, ...args ) => {
		let result = format;
		args.forEach( ( arg ) => {
			result = result.replace( '%s', arg );
		} );
		return result;
	},
} ) );
 */
describe( 'DuplicatePostStatusInfo', () => {
	beforeEach( () => {
		jest.clearAllMocks();
	} );

	it( 'returns null when postId is missing', () => {
		mockPostData = {
			postId: 0,
			postType: 'post',
			postTypeObject: { supports: { duplicate_as: true } },
			allPostTypes: [],
		};

		const { container } = render( <DuplicatePostStatusInfo /> );
		expect( container.innerHTML ).toBe( '' );
	} );

	it( 'returns null when postTypeObject is null', () => {
		mockPostData = {
			postId: 42,
			postType: 'post',
			postTypeObject: null,
			allPostTypes: [],
		};

		const { container } = render( <DuplicatePostStatusInfo /> );
		expect( container.innerHTML ).toBe( '' );
	} );

	it( 'returns null when duplicate_as support is false', () => {
		mockPostData = {
			postId: 42,
			postType: 'post',
			postTypeObject: { supports: { duplicate_as: false } },
			allPostTypes: [],
		};

		const { container } = render( <DuplicatePostStatusInfo /> );
		expect( container.innerHTML ).toBe( '' );
	} );

	it( 'returns null when supports is empty', () => {
		mockPostData = {
			postId: 42,
			postType: 'post',
			postTypeObject: { supports: {} },
			allPostTypes: [],
		};

		const { container } = render( <DuplicatePostStatusInfo /> );
		expect( container.innerHTML ).toBe( '' );
	} );

	it( 'renders PluginPostStatusInfo when supported', () => {
		mockPostData = {
			postId: 42,
			postType: 'page',
			postTypeObject: {
				slug: 'page',
				supports: { duplicate_as: true },
				labels: { singular_name: 'Page' },
			},
			allPostTypes: [
				{ slug: 'page', supports: { duplicate_as: true } },
			],
		};

		render( <DuplicatePostStatusInfo /> );
		expect(
			screen.getByTestId( 'plugin-post-status-info' )
		).toBeInTheDocument();
	} );

	it( 'renders a single duplicate button for simple support', () => {
		mockPostData = {
			postId: 42,
			postType: 'page',
			postTypeObject: {
				slug: 'page',
				supports: { duplicate_as: true },
				labels: { singular_name: 'Page' },
			},
			allPostTypes: [
				{ slug: 'page', supports: { duplicate_as: true } },
			],
		};

		render( <DuplicatePostStatusInfo /> );
		expect( screen.getByTestId( 'btn-duplicate' ) ).toBeInTheDocument();
	} );

	it( 'renders multiple buttons for array support', () => {
		mockPostData = {
			postId: 42,
			postType: 'page',
			postTypeObject: {
				slug: 'page',
				supports: { duplicate_as: [ [ 'page', 'post' ] ] },
				labels: { singular_name: 'Page' },
			},
			allPostTypes: [
				{
					slug: 'page',
					supports: { duplicate_as: [ [ 'page', 'post' ] ] },
				},
				{ slug: 'post', supports: {} },
			],
		};

		render( <DuplicatePostStatusInfo /> );
		expect( screen.getByTestId( 'btn-duplicate' ) ).toBeInTheDocument();
		expect( screen.getByTestId( 'btn-post' ) ).toBeInTheDocument();
	} );

	it( 'has the duplicate-as-status-info class', () => {
		mockPostData = {
			postId: 42,
			postType: 'page',
			postTypeObject: {
				slug: 'page',
				supports: { duplicate_as: true },
				labels: { singular_name: 'Page' },
			},
			allPostTypes: [
				{ slug: 'page', supports: { duplicate_as: true } },
			],
		};

		render( <DuplicatePostStatusInfo /> );
		expect(
			screen.getByTestId( 'plugin-post-status-info' )
		).toHaveClass( 'duplicate-as-status-info' );
	} );
} );
