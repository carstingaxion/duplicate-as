/**
 * Duplicate as — Main Entry Point
 *
 * Thin entry point that registers the WordPress editor plugin.
 * All logic is delegated to focused modules under src/.
 *
 * @package DuplicateAs
 * @since   0.1.0
 */

/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import './editor.scss';
import { DuplicatePostStatusInfo } from './components/duplicate-post-status-info';

/**
 * Register the duplicate post button plugin.
 *
 * Adds the duplicate button(s) to the PluginPostStatusInfo panel
 * in the editor sidebar. The plugin is registered with a unique name
 * and renders the DuplicatePostStatusInfo component.
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
registerPlugin( 'duplicate-as', {
	render: DuplicatePostStatusInfo,
} );
