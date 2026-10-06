/**
 * Shared constants for the Duplicate As plugin.
 *
 * @package
 * @since   0.4.0
 */

/**
 * WordPress dependencies
 */
import { page } from '@wordpress/icons';

/**
 * REST API base path for duplication endpoint.
 *
 * @since 0.4.0
 * @type {string}
 */
export const REST_PATH = '/duplicate-as/v1/duplicate';

/**
 * Default icon used when a post type has no icon or the icon
 * cannot be rendered as an editor icon (for example an SVG data
 * URI or an image URL).
 *
 * This is the `page` icon from `@wordpress/icons`, matching the
 * fallback WordPress core uses for post types without a usable icon.
 *
 * @since 0.4.0
 * @since 0.6.0 Changed from a Dashicon name to an `@wordpress/icons` component.
 * @type {Object}
 */
export const DEFAULT_ICON = page;
