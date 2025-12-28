# duplicate_as_featured_image


Filters the featured image ID to copy

## Example

add_filter( 'duplicate_as_featured_image', function( $thumbnail_id, $from_id, $to_id ) {
// Don't copy featured image
return false;
}, 10, 3 );

## Parameters

- *`int|false`* `$thumbnail_id` The thumbnail ID, or false if none exists.
- *`int`* `$from_post_id` The original post ID.
- *`int`* `$to_post_id` The duplicate post ID.

## Files

- [plugin.php:1163](https://github.com/carstingaxion/duplicate-as/blob/main/plugin.php#L1163)
```php
apply_filters( 'duplicate_as_featured_image', $thumbnail_id, $from_post_id, $to_post_id )
```



[← All Hooks](Hooks)
