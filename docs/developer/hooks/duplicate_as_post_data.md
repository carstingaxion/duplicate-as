# duplicate_as_post_data


Filters the post data before creating a duplicate

Allows modification of post data before the duplicate is created.

## Example

add_filter( 'duplicate_as_post_data', function( $post_data, $original_post, $target_post_type ) {
// Add prefix to title
$post_data['post_title'] = 'Copy of ' . $post_data['post_title'];
return $post_data;
}, 10, 3 );

## Parameters

- *`array`* `$new_post_data` The post data for the duplicate.
- *`WP_Post`* `$post` The original post object.
- *`string|null`* `$target_post_type` Target post type if transforming.

## Files

- [plugin.php:946](https://github.com/carstingaxion/duplicate-as/blob/main/plugin.php#L946)
```php
apply_filters( 'duplicate_as_post_data', $new_post_data, $post, $target_post_type )
```



[← All Hooks](Hooks)
