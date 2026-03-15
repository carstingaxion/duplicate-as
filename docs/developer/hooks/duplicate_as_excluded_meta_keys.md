# duplicate_as_excluded_meta_keys


Filters the list of meta keys to exclude from duplication.

## Example

```php
add_filter( 'duplicate_as_excluded_meta_keys', function( $excluded_keys, $from_id, $to_id ) {
    // Exclude view count from duplication
    $excluded_keys[] = '_view_count';
    return $excluded_keys;
}, 10, 3 );
```

## Parameters

- *`array`* `$excluded_keys` Array of meta keys to exclude.
- *`int`* `$from_post_id` The original post ID.
- *`int`* `$to_post_id` The duplicate post ID.

## Files

- [includes/classes/class-duplicator.php:462](https://github.com/carstingaxion/duplicate-as/blob/main/includes/classes/class-duplicator.php#L462)
```php
apply_filters( 'duplicate_as_excluded_meta_keys', $excluded_keys, $from_post_id, $to_post_id )
```



[← All Hooks](Hooks.md)
