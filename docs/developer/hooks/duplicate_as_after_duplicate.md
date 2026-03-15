# duplicate_as_after_duplicate


Fires after a post has been duplicated.

## Auto-generated Example

```php
add_action(
   'duplicate_as_after_duplicate',
    function(
        int $new_post_id,
        int $post_id
    ) {
        // Your code here.
    },
    10,
    2
);
```

## Parameters

- *`int`* `$new_post_id` The ID of the newly created duplicate post.
- *`int`* `$post_id` The ID of the original post.

## Files

- [includes/classes/class-duplicator.php:103](https://github.com/carstingaxion/duplicate-as/blob/main/includes/classes/class-duplicator.php#L103)
```php
do_action( 'duplicate_as_after_duplicate', $new_post_id, $post->ID )
```



[← All Hooks](Hooks.md)
