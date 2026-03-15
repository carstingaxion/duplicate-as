/**
 * DuplicateButton component.
 *
 * Renders a single duplicate or transform button with the correct
 * label, icon, and loading state.
 *
 * @package
 * @since   0.4.0
 */

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

import { parseDashicon } from '../utils';
import { DEFAULT_ICON } from '../constants';
import { useDuplicate } from '../hooks/use-duplicate';

/**
 * Duplicate button component props.
 *
 * @typedef {Object} DuplicateButtonProps
 * @property {number}                                      postId               - Current post ID.
 * @property {string|null}                                 targetPostType       - Target post type (null for duplication).
 * @property {string}                                      currentPostType      - Current post type slug.
 * @property {import('../utils').PostTypeObject|undefined} targetPostTypeObject - Target post type object.
 */

/**
 * Duplicate Post Button Component.
 *
 * Renders a single duplicate or transform button.
 * Determines its own label and icon based on whether this is
 * a duplication (same type) or transformation (different type).
 *
 * @since 0.1.0
 * @param {DuplicateButtonProps} props - Component props.
 * @return {JSX.Element} Button component.
 */
export function DuplicateButton( {
	postId,
	targetPostType,
	currentPostType,
	targetPostTypeObject,
} ) {
	const { isLoading, handleDuplicate } = useDuplicate();

	/**
	 * Determine if this is a transformation or duplication.
	 *
	 * @type {boolean}
	 */
	const isTransform = !! targetPostType && targetPostType !== currentPostType;

	/**
	 * Button label: "Duplicate" or "Duplicate as {PostType}".
	 * Uses the target post type's singular label for better UX.
	 *
	 * @type {string}
	 */
	const buttonLabel = isTransform
		? sprintf(
				/* translators: %s: target post type singular name */
				__( 'Duplicate as %s', 'duplicate-as' ),
				targetPostTypeObject?.labels?.singular_name || targetPostType
		  )
		: __( 'Duplicate', 'duplicate-as' );

	/**
	 * Icon for the button.
	 * Uses target post type icon for transform, or default duplicate icon.
	 * Handles Dashicons format (e.g., 'dashicons-admin-post' → 'admin-post').
	 *
	 * @type {string}
	 */
	const icon = isTransform
		? parseDashicon( targetPostTypeObject?.icon )
		: DEFAULT_ICON;

	return (
		<Button
			variant="secondary"
			isPressed={ false }
			isBusy={ isLoading }
			onClick={ () => handleDuplicate( postId, targetPostType ) }
			disabled={ isLoading }
			icon={ icon }
			className="duplicate-as-button"
		>
			{ isLoading ? __( 'Processing…', 'duplicate-as' ) : buttonLabel }
		</Button>
	);
}
