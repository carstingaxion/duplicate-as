/**
 * Custom hook: useDuplicate
 *
 * Encapsulates the duplication / transformation API call,
 * loading state, and error handling.
 *
 * @package DuplicateAs
 * @since   0.4.0
 */

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { dispatch } from '@wordpress/data';

import { REST_PATH } from '../constants';

/**
 * Return value of the useDuplicate hook.
 *
 * @typedef {Object} UseDuplicateReturn
 * @property {boolean}                                   isLoading       - Whether a request is in progress.
 * @property {(postId:number, targetPostType:string|null) => Promise<void>} handleDuplicate - Trigger duplication.
 */

/**
 * Hook that handles the duplicate / transform API call.
 *
 * Manages loading state internally, shows a notice on error,
 * and redirects to the new post's edit screen on success.
 *
 * @since 0.4.0
 * @return {UseDuplicateReturn} Object with `isLoading` flag and `handleDuplicate` callback.
 *
 * @example
 * const { isLoading, handleDuplicate } = useDuplicate();
 * // Call with duplication (no target):
 * handleDuplicate( 123, null );
 * // Call with transformation:
 * handleDuplicate( 123, 'post' );
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
export function useDuplicate() {
	const [ isLoading, setIsLoading ] = useState( false );

	/**
	 * Execute the duplication / transformation request.
	 *
	 * @param {number}      postId         - Post ID to duplicate.
	 * @param {string|null} targetPostType - Target post type for transformation, or null for duplication.
	 * @return {Promise<void>}
	 */
	const handleDuplicate = async ( postId, targetPostType ) => {
		if ( ! postId || isLoading ) {
			return;
		}

		setIsLoading( true );

		try {
			const requestBody = targetPostType
				? { target_post_type: targetPostType }
				: {};

			const response = await apiFetch( {
				path: `${ REST_PATH }/${ postId }`,
				method: 'POST',
				data: requestBody,
			} );

			if ( response.success && response.edit_url ) {
				window.location.href = response.edit_url;
			}
		} catch ( error ) {
			const message = targetPostType
				? __(
						'Failed to transform post. Please try again.',
						'duplicate-as'
				  )
				: __(
						'Failed to duplicate post. Please try again.',
						'duplicate-as'
				  );

			dispatch( 'core/notices' ).createNotice( 'error', message, {
				isDismissible: true,
			} );
			setIsLoading( false );
		}
	};

	return { isLoading, handleDuplicate };
}
