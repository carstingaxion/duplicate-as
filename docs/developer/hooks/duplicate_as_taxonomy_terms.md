# duplicate_as_taxonomy_terms


Filters the terms to copy for a specific taxonomy

## Example

add_filter( 'duplicate_as_taxonomy_terms', function( $terms, $taxonomy, $from_id, $to_id ) {
if ( $taxonomy === 'category' ) {
// Only copy the first category
return array_slice( $terms, 0, 1 );
}
return $terms;
}, 10, 4 );

## Parameters

- *`array`* `$terms` Array of term IDs to copy.
- *`string`* `$taxonomy` The taxonomy name.
- *`int`* `$from_post_id` The original post ID.
- *`int`* `$to_post_id` The duplicate post ID.

## Files

- [plugin.php:1082](https://github.com/carstingaxion/duplicate-as/blob/main/plugin.php#L1082)
```php
apply_filters( 'duplicate_as_taxonomy_terms', $terms, $taxonomy, $from_post_id, $to_post_id )
```



[← All Hooks](Hooks)
