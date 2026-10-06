/**
 * Unit tests for src/constants.js
 *
 * @package
 * @since   0.3.0
 */

import { page } from '@wordpress/icons';
import { REST_PATH, DEFAULT_ICON } from '../../../src/constants';

describe( 'Constants', () => {
	describe( 'REST_PATH', () => {
		it( 'should be a non-empty string', () => {
			expect( typeof REST_PATH ).toBe( 'string' );
			expect( REST_PATH.length ).toBeGreaterThan( 0 );
		} );

		it( 'should start with a forward slash', () => {
			expect( REST_PATH.startsWith( '/' ) ).toBe( true );
		} );

		it( 'should contain the plugin namespace', () => {
			expect( REST_PATH ).toContain( 'duplicate-as' );
		} );

		it( 'should contain version prefix', () => {
			expect( REST_PATH ).toContain( '/v1/' );
		} );

		it( 'should match expected value', () => {
			expect( REST_PATH ).toBe( '/duplicate-as/v1/duplicate' );
		} );
	} );

	describe( 'DEFAULT_ICON', () => {
		it( 'should be an @wordpress/icons component, not a Dashicon name', () => {
			expect( typeof DEFAULT_ICON ).not.toBe( 'string' );
			expect( DEFAULT_ICON ).not.toBeNull();
			expect( typeof DEFAULT_ICON ).toBe( 'object' );
		} );

		it( 'should be the page icon from @wordpress/icons', () => {
			expect( DEFAULT_ICON ).toEqual(
				expect.objectContaining( { name: 'page' } )
			);
		} );

		it( 'should match the page icon export', () => {
			expect( DEFAULT_ICON ).toBe( page );
		} );
	} );
} );
