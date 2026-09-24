/**
 * Unit tests for src/utils.js
 *
 * Tests all pure utility functions: getTransformTargets, parseDashicon,
 * buildButtonConfigs, and hasDuplicateSupport.
 *
 * @package
 * @since   0.3.0
 */

import {
	getTransformTargets,
	parseDashicon,
	buildButtonConfigs,
	hasDuplicateSupport,
} from '../../../src/utils';

describe( 'getTransformTargets', () => {
	it( 'returns empty array for null input', () => {
		expect( getTransformTargets( null ) ).toEqual( [] );
	} );

	it( 'returns empty array for undefined input', () => {
		expect( getTransformTargets( undefined ) ).toEqual( [] );
	} );

	it( 'returns empty array when supports is missing', () => {
		expect( getTransformTargets( {} ) ).toEqual( [] );
	} );

	it( 'returns empty array when duplicate_as is missing', () => {
		expect( getTransformTargets( { supports: {} } ) ).toEqual( [] );
	} );

	it( 'returns empty array when duplicate_as is false', () => {
		expect(
			getTransformTargets( { supports: { duplicate_as: false } } )
		).toEqual( [] );
	} );

	it( 'returns empty array when duplicate_as is true (simple duplication)', () => {
		expect(
			getTransformTargets( { supports: { duplicate_as: true } } )
		).toEqual( [] );
	} );

	it( 'returns single target when duplicate_as is a string', () => {
		expect(
			getTransformTargets( { supports: { duplicate_as: 'post' } } )
		).toEqual( [ 'post' ] );
	} );

	it( 'returns empty array when duplicate_as is an empty string', () => {
		expect(
			getTransformTargets( { supports: { duplicate_as: '' } } )
		).toEqual( [] );
	} );

	it( 'returns targets from nested array format', () => {
		const obj = {
			supports: {
				duplicate_as: [ [ 'page', 'post', 'event' ] ],
			},
		};
		expect( getTransformTargets( obj ) ).toEqual( [
			'page',
			'post',
			'event',
		] );
	} );

	it( 'filters out non-string values from array', () => {
		const obj = {
			supports: {
				duplicate_as: [ [ 'page', 123, null, 'post', undefined ] ],
			},
		};
		expect( getTransformTargets( obj ) ).toEqual( [ 'page', 'post' ] );
	} );

	it( 'filters out empty strings from array', () => {
		const obj = {
			supports: {
				duplicate_as: [ [ 'page', '', 'post' ] ],
			},
		};
		expect( getTransformTargets( obj ) ).toEqual( [ 'page', 'post' ] );
	} );

	it( 'returns empty array for nested empty array', () => {
		const obj = {
			supports: {
				duplicate_as: [ [] ],
			},
		};
		expect( getTransformTargets( obj ) ).toEqual( [] );
	} );
} );

describe( 'parseDashicon', () => {
	it( 'returns default icon for null input', () => {
		expect( parseDashicon( null ) ).toBe( 'admin-page' );
	} );

	it( 'returns default icon for undefined input', () => {
		expect( parseDashicon( undefined ) ).toBe( 'admin-page' );
	} );

	it( 'returns default icon for empty string', () => {
		expect( parseDashicon( '' ) ).toBe( 'admin-page' );
	} );

	it( 'returns default icon for non-string input', () => {
		expect( parseDashicon( 42 ) ).toBe( 'admin-page' );
	} );

	it( 'returns default icon for data URI (SVG)', () => {
		expect( parseDashicon( 'data:image/svg+xml;base64,abc' ) ).toBe(
			'admin-page'
		);
	} );

	it( 'strips dashicons- prefix', () => {
		expect( parseDashicon( 'dashicons-admin-post' ) ).toBe( 'admin-post' );
	} );

	it( 'returns icon name as-is when no prefix', () => {
		expect( parseDashicon( 'admin-post' ) ).toBe( 'admin-post' );
	} );

	it( 'handles calendar icon correctly', () => {
		expect( parseDashicon( 'dashicons-calendar' ) ).toBe( 'calendar' );
	} );

	it( 'handles icon without dashicons prefix', () => {
		expect( parseDashicon( 'heart' ) ).toBe( 'heart' );
	} );
} );

describe( 'hasDuplicateSupport', () => {
	it( 'returns false for null', () => {
		expect( hasDuplicateSupport( null ) ).toBe( false );
	} );

	it( 'returns false for undefined', () => {
		expect( hasDuplicateSupport( undefined ) ).toBe( false );
	} );

	it( 'returns false when supports is missing', () => {
		expect( hasDuplicateSupport( {} ) ).toBe( false );
	} );

	it( 'returns false when duplicate_as is missing', () => {
		expect( hasDuplicateSupport( { supports: {} } ) ).toBe( false );
	} );

	it( 'returns false when duplicate_as is false', () => {
		expect(
			hasDuplicateSupport( { supports: { duplicate_as: false } } )
		).toBe( false );
	} );

	it( 'returns true when duplicate_as is true', () => {
		expect(
			hasDuplicateSupport( { supports: { duplicate_as: true } } )
		).toBe( true );
	} );

	it( 'returns true when duplicate_as is a string', () => {
		expect(
			hasDuplicateSupport( { supports: { duplicate_as: 'post' } } )
		).toBe( true );
	} );

	it( 'returns true when duplicate_as is an array', () => {
		expect(
			hasDuplicateSupport( {
				supports: { duplicate_as: [ [ 'page', 'post' ] ] },
			} )
		).toBe( true );
	} );
} );

describe( 'buildButtonConfigs', () => {
	const makePostType = ( slug, overrides = {} ) => ( {
		slug,
		supports: { duplicate_as: true },
		labels: {
			singular_name: slug.charAt( 0 ).toUpperCase() + slug.slice( 1 ),
		},
		icon: 'dashicons-admin-' + slug,
		...overrides,
	} );

	const pageType = makePostType( 'page' );
	const postType = makePostType( 'post' );
	const eventType = makePostType( 'event' );
	const allTypes = [ pageType, postType, eventType ];

	it( 'returns single duplicate button when no targets', () => {
		const simpleType = makePostType( 'page', {
			supports: { duplicate_as: true },
		} );
		const buttons = buildButtonConfigs( 1, 'page', simpleType, allTypes );

		expect( buttons ).toHaveLength( 1 );
		expect( buttons[ 0 ].key ).toBe( 'duplicate-page' );
		expect( buttons[ 0 ].postId ).toBe( 1 );
		expect( buttons[ 0 ].targetPostType ).toBeNull();
		expect( buttons[ 0 ].currentPostType ).toBe( 'page' );
	} );

	it( 'returns duplicate button when self is in targets', () => {
		const withSelf = makePostType( 'page', {
			supports: { duplicate_as: [ [ 'page', 'post' ] ] },
		} );
		const buttons = buildButtonConfigs( 42, 'page', withSelf, allTypes );

		const dupButton = buttons.find( ( b ) => b.key === 'duplicate-page' );
		expect( dupButton ).toBeDefined();
		expect( dupButton.targetPostType ).toBeNull();
	} );

	it( 'returns transform buttons for other targets', () => {
		const withTargets = makePostType( 'page', {
			supports: { duplicate_as: [ [ 'page', 'post', 'event' ] ] },
		} );
		const buttons = buildButtonConfigs( 42, 'page', withTargets, allTypes );

		expect( buttons ).toHaveLength( 3 );

		const transformPost = buttons.find(
			( b ) => b.key === 'transform-page-to-post'
		);
		expect( transformPost ).toBeDefined();
		expect( transformPost.targetPostType ).toBe( 'post' );
		expect( transformPost.currentPostType ).toBe( 'page' );

		const transformEvent = buttons.find(
			( b ) => b.key === 'transform-page-to-event'
		);
		expect( transformEvent ).toBeDefined();
		expect( transformEvent.targetPostType ).toBe( 'event' );
	} );

	it( 'returns only transform buttons when self is not in targets', () => {
		const transformOnly = makePostType( 'page', {
			supports: { duplicate_as: [ [ 'post' ] ] },
		} );
		const buttons = buildButtonConfigs(
			10,
			'page',
			transformOnly,
			allTypes
		);

		expect( buttons ).toHaveLength( 1 );
		expect( buttons[ 0 ].key ).toBe( 'transform-page-to-post' );
		expect( buttons[ 0 ].targetPostType ).toBe( 'post' );
	} );

	it( 'sets targetPostTypeObject from allPostTypes', () => {
		const withTargets = makePostType( 'page', {
			supports: { duplicate_as: [ [ 'post' ] ] },
		} );
		const buttons = buildButtonConfigs( 1, 'page', withTargets, allTypes );

		expect( buttons[ 0 ].targetPostTypeObject ).toBe( postType );
	} );

	it( 'sets targetPostTypeObject to undefined for unknown types', () => {
		const withUnknown = makePostType( 'page', {
			supports: { duplicate_as: [ [ 'unknown_type' ] ] },
		} );
		const buttons = buildButtonConfigs( 1, 'page', withUnknown, allTypes );

		expect( buttons[ 0 ].targetPostTypeObject ).toBeUndefined();
	} );

	it( 'passes correct postId to all buttons', () => {
		const withTargets = makePostType( 'page', {
			supports: { duplicate_as: [ [ 'page', 'post' ] ] },
		} );
		const buttons = buildButtonConfigs( 99, 'page', withTargets, allTypes );

		buttons.forEach( ( button ) => {
			expect( button.postId ).toBe( 99 );
		} );
	} );

	it( 'generates unique keys for all buttons', () => {
		const withTargets = makePostType( 'page', {
			supports: { duplicate_as: [ [ 'page', 'post', 'event' ] ] },
		} );
		const buttons = buildButtonConfigs( 1, 'page', withTargets, allTypes );
		const keys = buttons.map( ( b ) => b.key );

		expect( new Set( keys ).size ).toBe( keys.length );
	} );
} );
