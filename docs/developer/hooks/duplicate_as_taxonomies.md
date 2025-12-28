# duplicate_as_taxonomies


Filters the taxonomies to copy during duplication

## Example

add_filter( 'duplicate_as_taxonomies', function( $taxonomies, $from_id, $to_id, $source, $target ) {
// Remove category from duplication
return array_diff( $taxonomies, ['category'] );
}, 10, 5 );

## Parameters

- *`array`* `$taxonomies` Array of taxonomy names to copy.
- *`int`* `$from_post_id` The original post ID.
- *`int`* `$to_post_id` The duplicate post ID.
- *`string`* `$source_post_type` The source post type.
- *`string`* `$target_post_type` The target post type.

## Files

- [plugin.php:1009](https://github.com/carstingaxion/duplicate-as/blob/main/plugin.php#L1009)
```php
apply_filters( 'duplicate_as_taxonomies', $taxonomies, $from_post_id, $to_post_id, $source_post_type, $target_post_type )
```



[← All Hooks](Hooks)
