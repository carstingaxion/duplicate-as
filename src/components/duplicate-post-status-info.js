/**
 * DuplicatePostStatusInfo component.
 *
 * Container component that reads editor state, builds the list of
 * action buttons, and renders them inside PluginPostStatusInfo.
 *
 * @package DuplicateAs
 * @since   0.4.0
 */

/**
 * WordPress dependencies
 */
import { PluginPostStatusInfo } from '@wordpress/editor';

import { hasDuplicateSupport, buildButtonConfigs } from '../utils';
import { usePostData } from '../hooks/use-post-data';
import { DuplicateButton } from './duplicate-button';

/**
 * Duplicate Post Status Info Component.
 *
 * Renders one or more buttons at the top of the PluginPostStatusInfo panel
 * in the editor sidebar. Supports multiple actions when the post type is
 * configured with an array of targets.
 *
 * Logic:
 * - If targets array is empty or undefined: Show single "Duplicate" button.
 * - If targets array includes current post type: Show "Duplicate" button for current type.
 * - For each target that differs from current type: Show "Duplicate as {Type}" button.
 *
 * Features:
 * - Only visible for supported post types
 * - Shows loading state while duplicating
 * - Redirects to edit screen on success
 * - Handles errors with user-friendly messages
 * - Permission-aware (respects user capabilities)
 * - Prominent placement at top of status panel
 * - Uses target post type icon and label for transform actions
 * - Supports multiple buttons for multiple actions
 *
 * @since 0.1.0
 * @return {JSX.Element|null} PluginPostStatusInfo component or null if not supported.
 *
 * @example
 * // Component renders as (single action):
 * // ┌─ Summary ─────────────────┐
 * // │ [Duplicate Button]        │
 * // │ Visibility: Public        │
 * // │ Publish: Immediately      │
 * // └───────────────────────────┘
 *
 * @example
 * // Component renders as (multiple actions):
 * // For: add_post_type_support('page', 'duplicate_as', ['page', 'post', 'event'])
 * // ┌─ Summary ─────────────────┐
 * // │ [Duplicate]               │  // page → page (duplicate)
 * // │ [Duplicate as Post]       │  // page → post (transform)
 * // │ [Duplicate as Event]      │  // page → event (transform)
 * // │ Visibility: Public        │
 * // └───────────────────────────┘
 */
export function DuplicatePostStatusInfo() {
	const { postId, postType, postTypeObject, allPostTypes } = usePostData();

	// Don't render if post ID is missing or post type doesn't support duplication.
	if ( ! postId || ! hasDuplicateSupport( postTypeObject ) ) {
		return null;
	}

	const buttons = buildButtonConfigs( postId, postType, postTypeObject, allPostTypes );

	return (
		<PluginPostStatusInfo className="duplicate-as-status-info">
			{ buttons.map( ( { key, ...buttonProps } ) => (
				<DuplicateButton key={ key } { ...buttonProps } />
			) ) }
		</PluginPostStatusInfo>
	);
}
