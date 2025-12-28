# duplicate_as_meta_value


Filters the meta value before adding it to the duplicate post

## Example

add_filter( 'duplicate_as_meta_value', function( $value, $key, $from_id, $to_id ) {
if ( $key === '_custom_counter' ) {
// Reset counter to 0 for duplicate
return 0;
}
return $value;
}, 10, 4 );

## Parameters

- *`mixed`* `$meta_value` The meta value to copy.
- *`string`* `$meta_key` The meta key.
- *`int`* `$from_post_id` The original post ID.
- *`int`* `$to_post_id` The duplicate post ID.

## Files

- [plugin.php:1124](https://github.com/carstingaxion/duplicate-as/blob/main/plugin.php#L1124)
```php
apply_filters( 'duplicate_as_meta_value', $meta_value, $meta_key, $from_post_id, $to_post_id )
```



[← All Hooks](Hooks)
