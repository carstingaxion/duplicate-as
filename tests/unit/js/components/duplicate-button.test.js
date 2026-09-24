/**
 * Unit tests for src/components/duplicate-button.js
 *
 * Tests button rendering, labels, icons, loading states, and click behavior.
 *
 * @package
 * @since   0.3.0
 */

import { render, screen, fireEvent } from '@testing-library/react';
import { DuplicateButton } from '../../../../src/components/duplicate-button';

// Mock useDuplicate hook
const mockHandleDuplicate = jest.fn();
let mockIsLoading = false;

jest.mock( '../../../../src/hooks/use-duplicate', () => ( {
	useDuplicate: () => ( {
		isLoading: mockIsLoading,
		handleDuplicate: mockHandleDuplicate,
	} ),
} ) );

describe( 'DuplicateButton', () => {
	beforeEach( () => {
		jest.clearAllMocks();
		mockIsLoading = false;
	} );

	const defaultProps = {
		postId: 42,
		targetPostType: null,
		currentPostType: 'page',
		targetPostTypeObject: {
			slug: 'page',
			labels: { singular_name: 'Page' },
			icon: 'dashicons-admin-page',
		},
	};

	describe( 'Duplication mode (no target)', () => {
		it( 'renders "Duplicate" label', () => {
			render( <DuplicateButton { ...defaultProps } /> );
			expect( screen.getByText( 'Duplicate' ) ).toBeInTheDocument();
		} );

		it( 'uses default icon for duplication', () => {
			render( <DuplicateButton { ...defaultProps } /> );
			const button = screen.getByRole( 'button' );
			expect( button.getAttribute( 'data-icon' ) ).toBe( 'admin-page' );
		} );

		it( 'calls handleDuplicate with postId and null on click', () => {
			render( <DuplicateButton { ...defaultProps } /> );
			fireEvent.click( screen.getByRole( 'button' ) );
			expect( mockHandleDuplicate ).toHaveBeenCalledWith( 42, null );
		} );
	} );

	describe( 'Transform mode (different target)', () => {
		const transformProps = {
			...defaultProps,
			targetPostType: 'post',
			targetPostTypeObject: {
				slug: 'post',
				labels: { singular_name: 'Post' },
				icon: 'dashicons-admin-post',
			},
		};

		it( 'renders "Duplicate as {Type}" label', () => {
			render( <DuplicateButton { ...transformProps } /> );
			expect(
				screen.getByText( 'Duplicate as Post' )
			).toBeInTheDocument();
		} );

		it( 'uses target post type icon', () => {
			render( <DuplicateButton { ...transformProps } /> );
			const button = screen.getByRole( 'button' );
			expect( button.getAttribute( 'data-icon' ) ).toBe( 'admin-post' );
		} );

		it( 'calls handleDuplicate with postId and target type on click', () => {
			render( <DuplicateButton { ...transformProps } /> );
			fireEvent.click( screen.getByRole( 'button' ) );
			expect( mockHandleDuplicate ).toHaveBeenCalledWith( 42, 'post' );
		} );

		it( 'uses target label from targetPostTypeObject', () => {
			const customProps = {
				...transformProps,
				targetPostTypeObject: {
					slug: 'event',
					labels: { singular_name: 'Event' },
					icon: 'dashicons-calendar',
				},
				targetPostType: 'event',
			};
			render( <DuplicateButton { ...customProps } /> );
			expect(
				screen.getByText( 'Duplicate as Event' )
			).toBeInTheDocument();
		} );

		it( 'falls back to targetPostType slug when labels missing', () => {
			const noLabelsProps = {
				...transformProps,
				targetPostTypeObject: undefined,
			};
			render( <DuplicateButton { ...noLabelsProps } /> );
			expect(
				screen.getByText( 'Duplicate as post' )
			).toBeInTheDocument();
		} );
	} );

	describe( 'Same type transform (targetPostType === currentPostType)', () => {
		it( 'renders "Duplicate" label when target matches current', () => {
			const sameTypeProps = {
				...defaultProps,
				targetPostType: 'page',
				currentPostType: 'page',
			};
			render( <DuplicateButton { ...sameTypeProps } /> );
			expect( screen.getByText( 'Duplicate' ) ).toBeInTheDocument();
		} );
	} );

	describe( 'Loading state', () => {
		it( 'shows "Processing\u2026" when loading', () => {
			mockIsLoading = true;
			render( <DuplicateButton { ...defaultProps } /> );
			expect(
				screen.getByText( 'Processing\u2026' )
			).toBeInTheDocument();
		} );

		it( 'disables button when loading', () => {
			mockIsLoading = true;
			render( <DuplicateButton { ...defaultProps } /> );
			expect( screen.getByRole( 'button' ) ).toBeDisabled();
		} );

		it( 'sets isBusy when loading', () => {
			mockIsLoading = true;
			render( <DuplicateButton { ...defaultProps } /> );
			expect(
				screen.getByRole( 'button' ).getAttribute( 'data-is-busy' )
			).toBe( 'true' );
		} );

		it( 'is not disabled when not loading', () => {
			render( <DuplicateButton { ...defaultProps } /> );
			expect( screen.getByRole( 'button' ) ).not.toBeDisabled();
		} );
	} );

	describe( 'CSS class', () => {
		it( 'has the duplicate-as-button class', () => {
			render( <DuplicateButton { ...defaultProps } /> );
			expect( screen.getByRole( 'button' ) ).toHaveClass(
				'duplicate-as-button'
			);
		} );
	} );
} );
