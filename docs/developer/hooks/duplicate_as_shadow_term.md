# duplicate_as_shadow_term


Filters the shadow taxonomy terms to assign to the duplicated post.

Allows modification or removal of shadow terms before they are
assigned to the new post during duplication or transformation.

## Example

```php
add_filter( 'duplicate_as_shadow_term', function( $terms, $taxonomy, $from_id, $to_id ) {
    // Don't copy shadow terms for a specific taxonomy.
    if ( '_gatherpress_event' === $taxonomy ) {
        return array();
    }
    return $terms;
}, 10, 4 );
```

## Parameters

- *`WP_Term`* `$shadow_term` The shadow term object retrieved for the source post.
- `$string_shadow_taxonomy_the_shadow_taxonomy_name` Other variable names: `$shadow_taxonomy`
- *`int`* `$from_post_id` The original post ID.
- *`int`* `$to_post_id` The duplicate post ID.
- *`string`* `$source_post_type` Source post type slug.
- *`string`* `$target_post_type` Target post type slug.

## Files

- [includes/classes/class-duplicator.php:415](https://github.com/carstingaxion/duplicate-as/blob/main/includes/classes/class-duplicator.php#L415)
```php
apply_filters(
				'duplicate_as_shadow_term',
				$shadow_term,
				$shadow_taxonomy,
				$from_post_id,
				$to_post_id,
				$source_post_type,
				$target_post_type
			)
```



[← All Hooks](Hooks.md)
