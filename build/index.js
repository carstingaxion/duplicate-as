/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/editor.scss"
/*!*************************!*\
  !*** ./src/editor.scss ***!
  \*************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "@wordpress/api-fetch"
/*!**********************************!*\
  !*** external ["wp","apiFetch"] ***!
  \**********************************/
(module) {

module.exports = window["wp"]["apiFetch"];

/***/ },

/***/ "@wordpress/components"
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
(module) {

module.exports = window["wp"]["components"];

/***/ },

/***/ "@wordpress/core-data"
/*!**********************************!*\
  !*** external ["wp","coreData"] ***!
  \**********************************/
(module) {

module.exports = window["wp"]["coreData"];

/***/ },

/***/ "@wordpress/data"
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
(module) {

module.exports = window["wp"]["data"];

/***/ },

/***/ "@wordpress/editor"
/*!********************************!*\
  !*** external ["wp","editor"] ***!
  \********************************/
(module) {

module.exports = window["wp"]["editor"];

/***/ },

/***/ "@wordpress/element"
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
(module) {

module.exports = window["wp"]["element"];

/***/ },

/***/ "@wordpress/i18n"
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
(module) {

module.exports = window["wp"]["i18n"];

/***/ },

/***/ "@wordpress/plugins"
/*!*********************************!*\
  !*** external ["wp","plugins"] ***!
  \*********************************/
(module) {

module.exports = window["wp"]["plugins"];

/***/ },

/***/ "react/jsx-runtime"
/*!**********************************!*\
  !*** external "ReactJSXRuntime" ***!
  \**********************************/
(module) {

module.exports = window["ReactJSXRuntime"];

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Check if module exists (development only)
/******/ 		if (__webpack_modules__[moduleId] === undefined) {
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_plugins__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/plugins */ "@wordpress/plugins");
/* harmony import */ var _wordpress_plugins__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_plugins__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/editor */ "@wordpress/editor");
/* harmony import */ var _wordpress_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_editor__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_core_data__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/core-data */ "@wordpress/core-data");
/* harmony import */ var _wordpress_core_data__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_core_data__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__);
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./editor.scss */ "./src/editor.scss");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__);
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










/**
 * Internal dependencies
 */


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
function DuplicateButton({
  postId,
  targetPostType,
  currentPostType,
  targetPostTypeObject
}) {
  const [isLoading, setIsLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)(false);

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
  const buttonLabel = isTransform ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.sprintf)(/* translators: %s: target post type singular name */
  (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)('Duplicate as %s', 'duplicate-as'), targetPostTypeObject?.labels?.singular_name || targetPostType) : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)('Duplicate', 'duplicate-as');

  /**
   * Icon for the button
   * Uses target post type icon for transform, or default duplicate icon.
   * Handles Dashicons format (e.g., 'dashicons-admin-post' -> 'admin-post').
   * @type {string}
   */
  const icon = isTransform ? parseDashicon(targetPostTypeObject?.icon) : 'admin-page';

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
      const requestBody = targetPostType ? {
        target_post_type: targetPostType
      } : {};
      const response = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_4___default()({
        path: `/duplicate-as/v1/duplicate/${postId}`,
        method: 'POST',
        data: requestBody
      });
      if (response.success && response.edit_url) {
        // Redirect to edit the newly created post
        window.location.href = response.edit_url;
      }
    } catch (error) {
      console.error('Duplication failed:', error);

      // Show user-friendly error message
      const message = isTransform ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)('Failed to transform post. Please try again.', 'duplicate-as') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)('Failed to duplicate post. Please try again.', 'duplicate-as');
      alert(message);
      setIsLoading(false);
    }
  };
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_7__.Button, {
    variant: "secondary",
    isPressed: false,
    isBusy: isLoading,
    onClick: handleDuplicate,
    disabled: isLoading,
    icon: icon,
    className: "duplicate-as-button",
    children: isLoading ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__.__)('Processing...', 'duplicate-as') : buttonLabel
  });
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
  const {
    postId,
    postType,
    postTypeObject,
    allPostTypes
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_5__.useSelect)(select => {
    const currentPostType = select(_wordpress_editor__WEBPACK_IMPORTED_MODULE_1__.store).getCurrentPostType();
    const postTypeObj = select(_wordpress_core_data__WEBPACK_IMPORTED_MODULE_6__.store).getPostType(currentPostType);
    const postTypes = select(_wordpress_core_data__WEBPACK_IMPORTED_MODULE_6__.store).getPostTypes({
      per_page: -1
    }) || [];
    return {
      postId: select(_wordpress_editor__WEBPACK_IMPORTED_MODULE_1__.store).getCurrentPostId(),
      postType: currentPostType,
      postTypeObject: postTypeObj,
      allPostTypes: postTypes
    };
  }, []);

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
      targetPostTypeObject: postTypeObject
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
          targetPostTypeObject: postTypeObject
        });
      } else {
        // Different type - this is transformation
        buttons.push({
          key: `transform-${postType}-to-${targetSlug}`,
          postId,
          targetPostType: targetSlug,
          currentPostType: postType,
          targetPostTypeObject: targetObj
        });
      }
    });
  }
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_editor__WEBPACK_IMPORTED_MODULE_1__.PluginPostStatusInfo, {
    className: "duplicate-as-status-info",
    children: buttons.map(({
      key,
      ...buttonProps
    }) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(DuplicateButton, {
      ...buttonProps
    }, key))
  });
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
(0,_wordpress_plugins__WEBPACK_IMPORTED_MODULE_0__.registerPlugin)('duplicate-as', {
  render: DuplicatePostStatusInfo
});
})();

/******/ })()
;
//# sourceMappingURL=index.js.map