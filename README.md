# Duplicate as

**Contributors:** carstenbach & WordPress Telex  
**Tags:** duplicate, post, page, editor, block-editor  
**Tested up to:** 6.8  
**Stable tag:** 0.1.0  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

Adds a convenient duplicate option to the block editor’s More Actions menu for quick post and page duplication.

---

## Description

The **Duplicate as** plugin enhances your WordPress editing experience by adding a duplicate option to the block editor’s **More Actions** menu (⋮). This feature allows you to quickly create copies of posts and pages without leaving the editor.

### Key Features

- **One-Click Duplication** – Duplicate posts and pages instantly from the More Actions menu  
- **Complete Content Copy** – Duplicates title, content, blocks, featured image, categories, tags, and custom fields  
- **Smart Draft Creation** – New duplicates are created as drafts with the same title for easy identification  
- **Error Handling** – Graceful error messages if something goes wrong  
- **Permission-Aware** – Only visible to users with appropriate capabilities  
- **Post Type Support** – Uses WordPress post type supports system for extensibility  
- **Transform Support** – Can transform posts to different post types when configured  
- **Developer Friendly** – Multiple filter hooks for customization  
- **Accessible** – Proper ARIA labels and WordPress admin integration  
- **Native Integration** – Uses standard WordPress UI patterns  

### How It Works

1. Open any post or page in the block editor  
2. Click the More Actions menu (⋮) in the top-right corner  
3. Click **Duplicate** to create a copy  
4. The plugin will copy all content and metadata  
5. You’ll be automatically redirected to edit the new draft  

The plugin works with posts and pages by default and can be extended to support custom post types through the post type supports system.

---

## Installation

1. Upload the plugin files to the `/wp-content/plugins/duplicate-as` directory, ~~or install the plugin through the WordPress Plugins screen~~  
2. Activate the plugin through the **Plugins** screen in WordPress  
3. Open any post or page in the block editor  
4. Click the More Actions menu (⋮) to see the duplicate option  

---

## Frequently Asked Questions

### Where is the duplicate button?

Click the three dots (⋮) menu in the top-right corner of the block editor. The **Duplicate** option will appear in that menu.

### Who can see and use the duplicate button?

Only users with the appropriate post editing capabilities can see and use the duplicate button. This typically includes Editors, Administrators, and Authors.

### What content gets duplicated?

The plugin duplicates:

- Post/Page title  
- Post/Page content and all blocks  
- Featured image  
- Categories  
- Tags  
- Custom fields (except internal WordPress fields like edit locks)  
- Post format  
- Comment and ping status  
- Menu order  

### What happens after I click duplicate?

The plugin creates a new draft copy of your post/page and automatically redirects you to edit it. The new post will have the same title as the original.

### Can I duplicate custom post types?

Yes. To enable duplication for custom post types, add post type support:

```php
add_post_type_support( 'your_post_type', 'duplicate_as' );
```

### Can I transform posts to different post types?

Yes. Add post type support with a target post type:

```php
add_post_type_support( 'page', 'duplicate_as', 'post' );
```

This will change the button label to Transform and create the duplicate as the target post type.

### Does it work with page builders?

Yes. Since it duplicates the raw content and all blocks, it works with any block-based page builder or the standard WordPress block editor.

### Can I customize what gets duplicated?

Yes. The plugin provides several filter hooks:


`duplicate_as_button_post_data` – Filter post data

`duplicate_as_button_taxonomies` – Filter taxonomies to copy

`duplicate_as_button_taxonomy_terms` – Filter terms for a specific taxonomy

`duplicate_as_button_excluded_meta_keys` – Filter excluded meta keys

`duplicate_as_button_meta_value` – Filter individual meta values

`duplicate_as_button_featured_image` – Filter featured image ID

## Screenshots

The duplicate option appears in the More Actions menu (⋮) in the editor header

Processing state shows while the post is being duplicated

Success redirect to the newly created draft

## Changelog
0.1.0

Initial release

Duplicate option in More Actions menu

Complete content and metadata duplication

Post type supports system integration

Transform functionality for different post types

Multiple filter hooks for customization

Loading states and error handling

Permission checks

Accessible interface

# Developer Notes

## Adding Post Type Support

```php
add_post_type_support( 'my_custom_post_type', 'duplicate_as' );
```

## Adding Transform Support

```php
// Transform pages to posts
add_post_type_support( 'page', 'duplicate_as', 'post' );
```

## Filtering Post Data

```php
add_filter(
    'duplicate_as_button_post_data',
    function ( $post_data, $original_post, $target_post_type ) {
        $post_data['post_title'] = 'Copy of ' . $post_data['post_title'];
        return $post_data;
    },
    10,
    3
);
```

## Excluding Meta Keys

```php
add_filter( 'duplicate_as_button_excluded_meta_keys', function ( $excluded_keys ) {
    $excluded_keys[] = 'my_custom_meta_key';
    return $excluded_keys;
} );
```

## Technical Details

The plugin uses the WordPress REST API to handle duplication on the server side, ensuring all data is properly copied and permissions are respected. The menu item is implemented using the PluginMoreMenuItem component, which integrates seamlessly with the block editor’s native More Actions menu (⋮) in the editor header.