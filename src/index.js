/**
 * Duplicate as - Main Entry Point
 *
 * This file registers a WordPress plugin that adds duplicate/transform buttons
 * to the PluginPostStatusInfo panel in the editor sidebar. The buttons allow users to:
 * - Duplicate posts/pages with all content and metadata
 * - Transform posts to different post types when configured
 * - Support multiple actions (duplicate + transform) when configured with an array
 *
 * @package DuplicateAs
 * @since 0.1.0
 */

/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import { PluginPostStatusInfo } from '@wordpress/editor';
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { store as coreStore } from '@wordpress/core-data';
import { Button } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './editor.scss';

/**
 * Post type object interface
 * @typedef {Object} PostTypeObject
 * @property {Object} supports - Post type supports object
 * @property {boolean|Array<string>|string} supports.duplicate_as - Duplicate support config
 * @property {Object} labels - Post type labels
 * @property {string} labels.singular_name - Singular name for the post type
 * @property {string} [icon] - Post type icon (Dashicon or SVG)
 * @property {string} slug - Post type slug
 */

/**
 * Get all transform targets from post type supports
 *
 * Extracts all target post types from the duplicate_as support configuration.
 * Returns an array of valid post type slugs that can be used for transformation.
 * If the array includes the source post type itself, duplication is also supported.
 *
 * @since 0.1.0
 * @param {PostTypeObject|null|undefined} postTypeObject - The post type object from WordPress core.
 * @return {Array<string>} Array of target post type slugs.
 *
 * @example
 * // For a page with both duplication and transformation:
 * const postTypeObj = {
 *   supports: {
 *     duplicate_as: ['page', 'post', 'event']
 *   }
 * };
 * getTransformTargets(postTypeObj); // Returns: ['page', 'post', 'event']
 *
 * @example
 * // For a post with simple duplication (no transformation):
 * const postTypeObj = {
 *   supports: {
 *     duplicate_as: true
 *   }
 * };
 * getTransformTargets(postTypeObj); // Returns: []
 *
 * @example
 * // For a page with single transformation target:
 * const postTypeObj = {
 *   supports: {
 *     duplicate_as: 'post'
 *   }
 * };
 * getTransformTargets(postTypeObj); // Returns: ['post']
 */
function getTransformTargets(postTypeObject) {
	if (!postTypeObject?.supports?.duplicate_as) {
		return [];
	}

	const duplicateSupport = postTypeObject.supports.duplicate_as;
	
	// If it's an array, filter valid post types
	if (Array.isArray(duplicateSupport)) {
		return duplicateSupport[0].filter(target => typeof target === 'string' && target.length > 0);
	}
	
	// If it's a string (single target), return as array
	if (typeof duplicateSupport === 'string' && duplicateSupport.length > 0) {
		return [duplicateSupport];
	}

	// Simple duplication (true) - no transformation targets
	return [];
}

/**
 * Parse Dashicon from post type icon
 *
 * WordPress post type icons can be:
 * - Dashicons class names (e.g., 'dashicons-admin-post')
 * - Just the icon name (e.g., 'admin-post')
 * - SVG data URIs
 * - null/undefined
 *
 * This function extracts the Dashicon name for use with WordPress components.
 *
 * @since 0.1.0
 * @param {string|null|undefined} icon - Post type icon value.
 * @return {string} Dashicon name without 'dashicons-' prefix, or default icon.
 *
 * @example
 * parseDashicon('dashicons-admin-post'); // Returns: 'admin-post'
 * parseDashicon('admin-post'); // Returns: 'admin-post'
 * parseDashicon(null); // Returns: 'admin-page'
 */
function parseDashicon(icon) {
	if (!icon || typeof icon !== 'string') {
		return 'admin-page';
	}

	// If it's a data URI (SVG), return default icon
	if (icon.startsWith('data:')) {
		return 'admin-page';
	}

	// Remove 'dashicons-' prefix if present
	if (icon.startsWith('dashicons-')) {
		return icon.replace('dashicons-', '');
	}

	return icon;
}

/**
 * Duplicate button component props
 * @typedef {Object} DuplicateButtonProps
 * @property {number} postId - Current post ID
 * @property {string|null} targetPostType - Target post type (null for duplication)
 * @property {string} currentPostType - Current post type
 * @property {PostTypeObject|undefined} targetPostTypeObject - Target post type object
 */

/**
 * Duplicate Post Button Component
 *
 * Renders a single duplicate or transform button.
 *
 * @since 0.1.0
 * @param {DuplicateButtonProps} props - Component props.
 * @return {JSX.Element} Button component.
 */
function DuplicateButton({ postId, targetPostType, currentPostType, targetPostTypeObject }) {
	const [isLoading, setIsLoading] = useState(false);
	
	/**
	 * Determine if this is a transformation or duplication
	 * @type {boolean}
	 */
	const isTransform = targetPostType && targetPostType !== currentPostType;
	
	/**
	 * Button label: "Duplicate" or "Duplicate as {PostType}"
	 * Uses the target post type's singular label for better UX
	 * @type {string}
	 */
	const buttonLabel = isTransform
		? sprintf(
				/* translators: %s: target post type singular name */
				__('Duplicate as %s', 'duplicate-as'),
				targetPostTypeObject?.labels?.singular_name || targetPostType
			)
		: __('Duplicate', 'duplicate-as');

	/**
	 * Icon for the button
	 * Uses target post type icon for transform, or default duplicate icon.
	 * Handles Dashicons format (e.g., 'dashicons-admin-post' -> 'admin-post').
	 * @type {string}
	 */
	const icon = isTransform
		? parseDashicon(targetPostTypeObject?.icon)
		: 'admin-page';

	/**
	 * Handle duplicate/transform button click
	 *
	 * Sends a POST request to the REST API to duplicate the current post.
	 * On success, redirects to the edit screen of the new post.
	 * On error, displays an alert message.
	 *
	 * @since 0.1.0
	 * @async
	 * @return {Promise<void>}
	 *
	 * @example API Request (Duplication):
	 * POST /wp-json/duplicate-as/v1/duplicate/123
	 * Body: {}
	 *
	 * @example API Request (Transformation):
	 * POST /wp-json/duplicate-as/v1/duplicate/123
	 * Body: { "target_post_type": "post" }
	 *
	 * @example API Response (Success):
	 * {
	 *   "success": true,
	 *   "new_post_id": 456,
	 *   "edit_url": "https://example.com/wp-admin/post.php?post=456&action=edit",
	 *   "is_transform": false
	 * }
	 */
	const handleDuplicate = async () => {
		if (!postId || isLoading) {
			return;
		}

		setIsLoading(true);

		try {
			const requestBody = targetPostType ? { target_post_type: targetPostType } : {};
			
			const response = await apiFetch({
				path: `/duplicate-as/v1/duplicate/${postId}`,
				method: 'POST',
				data: requestBody,
			});

			if (response.success && response.edit_url) {
				// Redirect to edit the newly created post
				window.location.href = response.edit_url;
			}
		} catch (error) {
			console.error('Duplication failed:', error);
			
			// Show user-friendly error message
			const message = isTransform
				? __('Failed to transform post. Please try again.', 'duplicate-as')
				: __('Failed to duplicate post. Please try again.', 'duplicate-as');
			
			alert(message);
			setIsLoading(false);
		}
	};

	return (
		<Button
			variant="secondary"
			isPressed={false}
			isBusy={isLoading}
			onClick={handleDuplicate}
			disabled={isLoading}
			icon={icon}
			className="duplicate-as-button"
		>
			{isLoading ? __('Processing...', 'duplicate-as') : buttonLabel}
		</Button>
	);
}

/**
 * Button configuration object
 * @typedef {Object} ButtonConfig
 * @property {string} key - Unique key for React rendering
 * @property {number} postId - Current post ID
 * @property {string|null} targetPostType - Target post type (null for duplication)
 * @property {string} currentPostType - Current post type
 * @property {PostTypeObject|undefined} targetPostTypeObject - Target post type object
 */

/**
 * Editor select return object
 * @typedef {Object} EditorSelectReturn
 * @property {number} postId - Current post ID
 * @property {string} postType - Current post type slug
 * @property {PostTypeObject|undefined} postTypeObject - Full post type object with supports
 * @property {Array<PostTypeObject>} allPostTypes - All post types (for getting target objects)
 */

/**
 * Duplicate Post Status Info Component
 *
 * Renders one or more buttons at the top of the PluginPostStatusInfo panel in the sidebar.
 * Supports multiple actions when the post type is configured with an array of targets.
 *
 * Logic:
 * - If targets array is empty or undefined: Show single "Duplicate" button
 * - If targets array includes current post type: Show "Duplicate" button for current type
 * - For each target that differs from current type: Show "Duplicate as {Type}" button
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
 * // │ [Duplicate]               │  // page -> page (duplicate)
 * // │ [Duplicate as Post]       │  // page -> post (transform)
 * // │ [Duplicate as Event]      │  // page -> event (transform)
 * // │ Visibility: Public        │
 * // └───────────────────────────┘
 */
function DuplicatePostStatusInfo() {
	/**
	 * Select current post data from WordPress stores
	 *
	 * @type {EditorSelectReturn}
	 */
	const { postId, postType, postTypeObject, allPostTypes } = useSelect(
		(select) => {
			const currentPostType = select(editorStore).getCurrentPostType();
			const postTypeObj = select(coreStore).getPostType(currentPostType);
			const postTypes = select(coreStore).getPostTypes({ per_page: -1 }) || [];
			
			return {
				postId: select(editorStore).getCurrentPostId(),
				postType: currentPostType,
				postTypeObject: postTypeObj,
				allPostTypes: postTypes,
			};
		},
		[]
	);

	/**
	 * Check if duplicate_as support exists
	 * @type {boolean}
	 */
	const supportsDuplicate = postTypeObject?.supports?.duplicate_as ? true : false;

	// Don't render if post ID is missing or post type doesn't support duplication
	if (!postId || !supportsDuplicate) {
		return null;
	}

	/**
	 * Get all transform targets from supports
	 * @type {Array<string>}
	 */
	const transformTargets = getTransformTargets(postTypeObject);
	
	/**
	 * Determine which buttons to render:
	 * 1. If no targets specified (simple duplication): show duplicate button
	 * 2. If targets include current post type: show duplicate button
	 * 3. For each target different from current type: show transform button
	 *
	 * @type {Array<ButtonConfig>}
	 */
	const buttons = [];
	
	// Case 1: No targets specified - simple duplication
	if (transformTargets.length === 0) {
		buttons.push({
			key: `duplicate-${postType}`,
			postId,
			targetPostType: null,
			currentPostType: postType,
			targetPostTypeObject: postTypeObject,
		});
	} else {
		// Case 2 & 3: Targets specified - show duplicate and/or transform buttons
		transformTargets.forEach(targetSlug => {
			// Find the post type object for this target
			const targetObj = allPostTypes.find(pt => pt.slug === targetSlug);
			
			if (targetSlug === postType) {
				// Same as current type - this is duplication
				buttons.push({
					key: `duplicate-${postType}`,
					postId,
					targetPostType: null,
					currentPostType: postType,
					targetPostTypeObject: postTypeObject,
				});
			} else {
				// Different type - this is transformation
				buttons.push({
					key: `transform-${postType}-to-${targetSlug}`,
					postId,
					targetPostType: targetSlug,
					currentPostType: postType,
					targetPostTypeObject: targetObj,
				});
			}
		});
	}

	return (
		<PluginPostStatusInfo className="duplicate-as-status-info">
			{buttons.map(({ key, ...buttonProps }) => (
				<DuplicateButton key={key} {...buttonProps} />
			))}
		</PluginPostStatusInfo>
	);
}

/**
 * Register the duplicate post button plugin
 *
 * Adds the duplicate button(s) to the PluginPostStatusInfo panel in the editor sidebar.
 * The plugin is registered with a unique name and renders the DuplicatePostStatusInfo component.
 *
 * @since 0.1.0
 *
 * @example
 * // Plugin appears in editor sidebar as (single action):
 * // ┌─ Summary ─────────────────┐
 * // │ [Duplicate Button]        │
 * // │ Visibility: Public        │
 * // │ Publish: Immediately      │
 * // └───────────────────────────┘
 *
 * @example
 * // Plugin appears in editor sidebar as (multiple actions):
 * // ┌─ Summary ─────────────────┐
 * // │ [Duplicate Button]        │
 * // │ [Duplicate as Post]       │
 * // │ [Duplicate as Event]      │
 * // │ Visibility: Public        │
 * // └───────────────────────────┘
 */
registerPlugin('duplicate-as', {
	render: DuplicatePostStatusInfo,
});