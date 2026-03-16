/**
 * Unit tests for src/constants.js
 *
 * @package
 * @since   0.3.0
 */

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
		it( 'should be a non-empty string', () => {
			expect( typeof DEFAULT_ICON ).toBe( 'string' );
			expect( DEFAULT_ICON.length ).toBeGreaterThan( 0 );
		} );

		it( 'should be a valid dashicon name without prefix', () => {
			expect( DEFAULT_ICON ).not.toContain( 'dashicons-' );
		} );

		it( 'should match expected value', () => {
			expect( DEFAULT_ICON ).toBe( 'admin-page' );
		} );
	} );
} );
