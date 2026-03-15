/**
 * Custom hook: usePostData
 *
 * Selects post-related data from WordPress core and editor stores
 * needed by the duplicate / transform UI.
 *
 * @package DuplicateAs
 * @since   0.4.0
 */

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { store as coreStore } from '@wordpress/core-data';

/**
 * Editor select return object.
 *
 * @typedef {Object} PostData
 * @property {number}                   postId         - Current post ID.
 * @property {string}                   postType       - Current post type slug.
 * @property {import('../utils').PostTypeObject|undefined} postTypeObject - Full post type object with supports.
 * @property {Array<import('../utils').PostTypeObject>}    allPostTypes   - All registered post types.
 */

/**
 * Select current post data from WordPress stores.
 *
 * Memoised via `useSelect` — only re-runs when the subscribed stores change.
 *
 * @since 0.4.0
 * @return {PostData} Post data object.
 *
 * @example
 * const { postId, postType, postTypeObject, allPostTypes } = usePostData();
 */
export function usePostData() {
	return useSelect( ( select ) => {
		const currentPostType = select( editorStore ).getCurrentPostType();
		const postTypeObj = select( coreStore ).getPostType( currentPostType );
		const postTypes =
			select( coreStore ).getPostTypes( { per_page: -1 } ) || [];

		return {
			postId: select( editorStore ).getCurrentPostId(),
			postType: currentPostType,
			postTypeObject: postTypeObj,
			allPostTypes: postTypes,
		};
	}, [] );
}
