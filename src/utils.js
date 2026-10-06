/**
 * Pure utility functions for the Duplicate As plugin.
 *
 * These functions have no side effects and do not depend on
 * WordPress stores or React state — making them easy to unit test.
 *
 * @package
 * @since   0.4.0
 */

/**
 * WordPress dependencies
 */
import {
	archive,
	audio,
	brush,
	calendar,
	category,
	chevronLeft,
	comment,
	copy,
	dashboard,
	file,
	grid,
	home,
	image,
	link,
	list,
	media,
	menu,
	page,
	pencil,
	people,
	pin,
	plugins,
	plus,
	post,
	search,
	settings,
	sites,
	tag,
	tool,
	update,
	video,
	wordpress,
} from '@wordpress/icons';

/**
 * Internal dependencies
 */
import { DEFAULT_ICON } from './constants';

/**
 * Known icon slugs mapped to their `@wordpress/icons` component.
 *
 * The slugs mirror the names WordPress core uses for built-in post types and
 * admin menu items. They are registered under both collection namespaces in
 * `ICON_MAP`, because core serves the admin icons from the `core-admin`
 * collection while the public icon set uses the `core` collection.
 *
 * @since 0.6.0
 * @type {Object<string, Object>}
 */
const ICONS_BY_SLUG = {
	archive,
	audio,
	brush,
	calendar,
	category,
	'chevron-left': chevronLeft,
	comment,
	copy,
	dashboard,
	file,
	grid,
	home,
	image,
	link,
	list,
	media,
	menu,
	page,
	pencil,
	people,
	pin,
	plugins,
	plus,
	post,
	search,
	settings,
	sites,
	tag,
	tool,
	update,
	video,
	wordpress,
};

/**
 * Namespaced icon names mapped to their `@wordpress/icons` component.
 *
 * Namespaced names look like `collection/icon-name` (for example
 * `core-admin/pin`), as registered through `wp_register_icon()`.
 *
 * @since 0.6.0
 * @type {Object<string, Object>}
 */
const ICON_MAP = Object.fromEntries(
	Object.entries( ICONS_BY_SLUG ).flatMap( ( [ slug, icon ] ) => [
		[ `core-admin/${ slug }`, icon ],
		[ `core/${ slug }`, icon ],
	] )
);

/**
 * Post type object interface (shared across modules).
 *
 * @typedef {Object} PostTypeObject
 * @property {Object}                       supports              - Post type supports object.
 * @property {boolean|Array<string>|string} supports.duplicate_as - Duplicate support config.
 * @property {Object}                       labels                - Post type labels.
 * @property {string}                       labels.singular_name  - Singular name for the post type.
 * @property {string}                       [icon]                - Post type icon (Dashicon name, namespaced icon name, or SVG).
 * @property {string}                       slug                  - Post type slug.
 */

/**
 * Get all transform targets from post type supports.
 *
 * Extracts all target post type slugs from the `duplicate_as` support configuration.
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
 *   supports: { duplicate_as: ['page', 'post', 'event'] }
 * };
 * getTransformTargets(postTypeObj); // Returns: ['page', 'post', 'event']
 *
 * @example
 * // For a post with simple duplication (no transformation):
 * const postTypeObj = { supports: { duplicate_as: true } };
 * getTransformTargets(postTypeObj); // Returns: []
 *
 * @example
 * // For a page with single transformation target:
 * const postTypeObj = { supports: { duplicate_as: 'post' } };
 * getTransformTargets(postTypeObj); // Returns: ['post']
 */
export function getTransformTargets( postTypeObject ) {
	if ( ! postTypeObject?.supports?.duplicate_as ) {
		return [];
	}

	const duplicateSupport = postTypeObject.supports.duplicate_as;

	// If it's an array, filter valid post types.
	if ( Array.isArray( duplicateSupport ) ) {
		return duplicateSupport[ 0 ].filter(
			( target ) => typeof target === 'string' && target.length > 0
		);
	}

	// If it's a string (single target), return as array.
	if ( typeof duplicateSupport === 'string' && duplicateSupport.length > 0 ) {
		return [ duplicateSupport ];
	}

	// Simple duplication (true) — no transformation targets.
	return [];
}

/**
 * Resolve a post type icon value into an editor icon.
 *
 * WordPress post type icons can be any of the following:
 * - A namespaced icon name registered via `wp_register_icon()`, such as
 *   `core-admin/pin` (WordPress 7.2 and later).
 * - A Dashicon class name, such as `dashicons-admin-post`, or the bare
 *   Dashicon name, such as `admin-post`.
 * - An SVG data URI or an image URL.
 * - null or undefined.
 *
 * Dashicons and their bare names are returned as strings, because the
 * `Button` component from `@wordpress/components` renders a string icon as
 * a Dashicon. Namespaced icon names are resolved to the matching
 * `@wordpress/icons` component. Anything that cannot be rendered as a
 * Dashicon (data URIs, image URLs, and unknown namespaced names) falls back
 * to the default icon.
 *
 * @since 0.1.0
 * @since 0.6.0 Renamed from `parseDashicon` and returning `@wordpress/icons`
 *              components for namespaced icon names.
 * @param {string|null|undefined} icon - Post type icon value.
 * @return {string|Object} Dashicon name, or an `@wordpress/icons` component.
 *
 * @example
 * getPostTypeIcon('core-admin/post');      // Returns: the `post` icon component.
 * getPostTypeIcon('core/post');            // Returns: the `post` icon component.
 * getPostTypeIcon('dashicons-admin-post'); // Returns: 'admin-post'
 * getPostTypeIcon('admin-post');           // Returns: 'admin-post'
 * getPostTypeIcon('https://example.com/i');// Returns: DEFAULT_ICON
 * getPostTypeIcon(null);                   // Returns: DEFAULT_ICON
 */
export function getPostTypeIcon( icon ) {
	if ( ! icon || typeof icon !== 'string' ) {
		return DEFAULT_ICON;
	}

	// If it's a data URI (SVG), return default icon.
	if ( icon.startsWith( 'data:' ) ) {
		return DEFAULT_ICON;
	}

	// Namespaced icon name (e.g. `core-admin/post`) registered in WP 7.2+.
	if ( ICON_MAP[ icon ] ) {
		return ICON_MAP[ icon ];
	}

	// Unknown namespaced icon name, or a URL — not renderable as an icon.
	if ( icon.includes( '/' ) || icon.includes( '://' ) ) {
		return DEFAULT_ICON;
	}

	// Remove 'dashicons-' prefix if present.
	if ( icon.startsWith( 'dashicons-' ) ) {
		return icon.replace( 'dashicons-', '' );
	}

	return icon;
}

/**
 * Build the array of button configuration objects for a given post.
 *
 * Determines which buttons to render based on the post type's
 * `duplicate_as` support configuration:
 *
 * 1. No targets specified (simple duplication) → single "Duplicate" button.
 * 2. Targets include current post type → "Duplicate" button for that type.
 * 3. Each target different from current type → "Duplicate as {Type}" button.
 *
 * @since 0.4.0
 *
 * @typedef {Object} ButtonConfig
 * @property {string}                   key                  - Unique React key.
 * @property {number}                   postId               - Current post ID.
 * @property {string|null}              targetPostType       - Target post type (null for duplication).
 * @property {string}                   currentPostType      - Current post type slug.
 * @property {PostTypeObject|undefined} targetPostTypeObject - Target post type object.
 *
 * @param    {number}                   postId               - Current post ID.
 * @param    {string}                   postType             - Current post type slug.
 * @param    {PostTypeObject}           postTypeObject       - Current post type object.
 * @param    {Array<PostTypeObject>}    allPostTypes         - All registered post type objects.
 * @return {Array<ButtonConfig>} Array of button configuration objects.
 */
export function buildButtonConfigs(
	postId,
	postType,
	postTypeObject,
	allPostTypes
) {
	const transformTargets = getTransformTargets( postTypeObject );

	/** @type {Array<ButtonConfig>} */
	const buttons = [];

	// Case 1: No targets specified — simple duplication.
	if ( transformTargets.length === 0 ) {
		buttons.push( {
			key: `duplicate-${ postType }`,
			postId,
			targetPostType: null,
			currentPostType: postType,
			targetPostTypeObject: postTypeObject,
		} );
		return buttons;
	}

	// Case 2 & 3: Targets specified — show duplicate and/or transform buttons.
	transformTargets.forEach( ( targetSlug ) => {
		const targetObj = allPostTypes.find( ( pt ) => pt.slug === targetSlug );

		if ( targetSlug === postType ) {
			// Same as current type — this is duplication.
			buttons.push( {
				key: `duplicate-${ postType }`,
				postId,
				targetPostType: null,
				currentPostType: postType,
				targetPostTypeObject: postTypeObject,
			} );
		} else {
			// Different type — this is transformation.
			buttons.push( {
				key: `transform-${ postType }-to-${ targetSlug }`,
				postId,
				targetPostType: targetSlug,
				currentPostType: postType,
				targetPostTypeObject: targetObj,
			} );
		}
	} );

	return buttons;
}

/**
 * Determine if a post type supports the `duplicate_as` feature.
 *
 * @since 0.4.0
 * @param {PostTypeObject|null|undefined} postTypeObject - Post type object.
 * @return {boolean} True if `duplicate_as` support is present.
 */
export function hasDuplicateSupport( postTypeObject ) {
	return postTypeObject?.supports?.duplicate_as ? true : false;
}
