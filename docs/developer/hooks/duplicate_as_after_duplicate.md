# duplicate_as_after_duplicate


Fires after a post has been duplicated

## Auto-generated Example

```php
add_action(
   'duplicate_as_after_duplicate',
    function(
        int $new_post_id,
        int $post->ID
    ) {
        // Your code here.
    },
    10,
    2
);
```

## Parameters

- *`int`* `$new_post_id` The ID of the newly created duplicate post.
- *`int`* `$post->ID` The ID of the original post.

## Files

- [plugin.php:352](https://github.com/carstingaxion/duplicate-as/blob/main/plugin.php#L352)
```php
do_action( 'duplicate_as_after_duplicate', $new_post_id, $post->ID )
```

- [plugin.php:794](https://github.com/carstingaxion/duplicate-as/blob/main/plugin.php#L794)
```php
do_action( 'duplicate_as_after_duplicate', $new_post_id, $post_id )
```



[← All Hooks](Hooks)
