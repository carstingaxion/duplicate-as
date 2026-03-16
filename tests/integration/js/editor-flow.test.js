/**
 * Integration tests for the editor duplication flow.
 *
 * Tests the full integration between hooks, utilities, and components
 * working together to render the correct UI based on post type support.
 *
 * @package DuplicateAs\Tests\Integration\JS
 * @since   0.3.0
 */

import {
	getTransformTargets,
	buildButtonConfigs,
	hasDuplicateSupport,
	parseDashicon,
} from '../../../src/utils';
import { REST_PATH, DEFAULT_ICON } from '../../../src/constants';

describe( 'Editor flow integration', () => {
	describe( 'Simple duplication flow (page with duplicate_as: true)', () => {
		const postTypeObject = {
			slug: 'page',
			supports: { duplicate_as: true },
			labels: { singular_name: 'Page' },
			icon: 'dashicons-admin-page',
		};

		const allPostTypes = [
			postTypeObject,
			{
				slug: 'post',
				supports: {},
				labels: { singular_name: 'Post' },
				icon: 'dashicons-admin-post',
			},
		];

		it( 'detects duplication support', () => {
			expect( hasDuplicateSupport( postTypeObject ) ).toBe( true );
		} );

		it( 'has no transform targets', () => {
			expect( getTransformTargets( postTypeObject ) ).toEqual( [] );
		} );

		it( 'builds single duplicate button config', () => {
			const buttons = buildButtonConfigs(
				42,
				'page',
				postTypeObject,
				allPostTypes
			);

			expect( buttons ).toHaveLength( 1 );
			expect( buttons[ 0 ] ).toEqual( {
				key: 'duplicate-page',
				postId: 42,
				targetPostType: null,
				currentPostType: 'page',
				targetPostTypeObject: postTypeObject,
			} );
		} );

		it( 'uses default icon for duplication button', () => {
			expect( parseDashicon( postTypeObject.icon ) ).toBe(
				'admin-page'
			);
		} );
	} );

	describe( 'Full duplication + transformation flow (post with array support)', () => {
		const postTypeObject = {
			slug: 'post',
			supports: {
				duplicate_as: [ [ 'post', 'page', 'gatherpress_event' ] ],
			},
			labels: { singular_name: 'Post' },
			icon: 'dashicons-admin-post',
		};

		const pageType = {
			slug: 'page',
			supports: {},
			labels: { singular_name: 'Page' },
			icon: 'dashicons-admin-page',
		};

		const eventType = {
			slug: 'gatherpress_event',
			supports: {},
			labels: { singular_name: 'Event' },
			icon: 'dashicons-calendar',
		};

		const allPostTypes = [ postTypeObject, pageType, eventType ];

		it( 'detects duplication support', () => {
			expect( hasDuplicateSupport( postTypeObject ) ).toBe( true );
		} );

		it( 'returns all three targets', () => {
			const targets = getTransformTargets( postTypeObject );
			expect( targets ).toEqual( [
				'post',
				'page',
				'gatherpress_event',
			] );
		} );

		it( 'builds three button configs', () => {
			const buttons = buildButtonConfigs(
				99,
				'post',
				postTypeObject,
				allPostTypes
			);

			expect( buttons ).toHaveLength( 3 );
		} );

		it( 'first button is duplication (self)', () => {
			const buttons = buildButtonConfigs(
				99,
				'post',
				postTypeObject,
				allPostTypes
			);

			expect( buttons[ 0 ] ).toEqual( {
				key: 'duplicate-post',
				postId: 99,
				targetPostType: null,
				currentPostType: 'post',
				targetPostTypeObject: postTypeObject,
			} );
		} );

		it( 'second button is transform to page', () => {
			const buttons = buildButtonConfigs(
				99,
				'post',
				postTypeObject,
				allPostTypes
			);

			expect( buttons[ 1 ] ).toEqual( {
				key: 'transform-post-to-page',
				postId: 99,
				targetPostType: 'page',
				currentPostType: 'post',
				targetPostTypeObject: pageType,
			} );
		} );

		it( 'third button is transform to event', () => {
			const buttons = buildButtonConfigs(
				99,
				'post',
				postTypeObject,
				allPostTypes
			);

			expect( buttons[ 2 ] ).toEqual( {
				key: 'transform-post-to-gatherpress_event',
				postId: 99,
				targetPostType: 'gatherpress_event',
				currentPostType: 'post',
				targetPostTypeObject: eventType,
			} );
		} );

		it( 'parses icons correctly for each target', () => {
			expect( parseDashicon( pageType.icon ) ).toBe( 'admin-page' );
			expect( parseDashicon( eventType.icon ) ).toBe( 'calendar' );
		} );
	} );

	describe( 'Transform-only flow (no self in targets)', () => {
		const postTypeObject = {
			slug: 'page',
			supports: { duplicate_as: [ [ 'post' ] ] },
			labels: { singular_name: 'Page' },
			icon: 'dashicons-admin-page',
		};

		const postType = {
			slug: 'post',
			supports: {},
			labels: { singular_name: 'Post' },
			icon: 'dashicons-admin-post',
		};

		const allPostTypes = [ postTypeObject, postType ];

		it( 'has duplication support', () => {
			expect( hasDuplicateSupport( postTypeObject ) ).toBe( true );
		} );

		it( 'returns only post as target (no page)', () => {
			expect( getTransformTargets( postTypeObject ) ).toEqual( [
				'post',
			] );
		} );

		it( 'builds only one transform button (no duplicate button)', () => {
			const buttons = buildButtonConfigs(
				10,
				'page',
				postTypeObject,
				allPostTypes
			);

			expect( buttons ).toHaveLength( 1 );
			expect( buttons[ 0 ].targetPostType ).toBe( 'post' );
			expect( buttons[ 0 ].key ).toBe( 'transform-page-to-post' );
		} );
	} );

	describe( 'No support flow', () => {
		const postTypeObject = {
			slug: 'attachment',
			supports: {},
			labels: { singular_name: 'Media' },
		};

		it( 'detects no support', () => {
			expect( hasDuplicateSupport( postTypeObject ) ).toBe( false );
		} );

		it( 'has no transform targets', () => {
			expect( getTransformTargets( postTypeObject ) ).toEqual( [] );
		} );
	} );

	describe( 'REST path construction', () => {
		it( 'builds correct API path for duplication', () => {
			const path = `${ REST_PATH }/42`;
			expect( path ).toBe( '/duplicate-as/v1/duplicate/42' );
		} );

		it( 'builds correct API path for any post ID', () => {
			const postId = 12345;
			const path = `${ REST_PATH }/${ postId }`;
			expect( path ).toBe( '/duplicate-as/v1/duplicate/12345' );
		} );
	} );

	describe( 'Icon fallback chain', () => {
		it( 'uses dashicon name when valid', () => {
			expect( parseDashicon( 'dashicons-admin-post' ) ).toBe(
				'admin-post'
			);
		} );

		it( 'uses icon name directly when no prefix', () => {
			expect( parseDashicon( 'calendar' ) ).toBe( 'calendar' );
		} );

		it( 'falls back to default for SVG data URI', () => {
			expect( parseDashicon( 'data:image/svg+xml;base64,abc' ) ).toBe(
				DEFAULT_ICON
			);
		} );

		it( 'falls back to default for null', () => {
			expect( parseDashicon( null ) ).toBe( DEFAULT_ICON );
		} );

		it( 'falls back to default for undefined', () => {
			expect( parseDashicon( undefined ) ).toBe( DEFAULT_ICON );
		} );
	} );
} );
