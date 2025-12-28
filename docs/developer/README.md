# Developer Documentation

## Adding Post Type Support

```php
add_post_type_support( 'my_custom_post_type', 'duplicate_as' );
```

## Adding Transform Support

```php
// Transform pages to posts
add_post_type_support( 'page', 'duplicate_as', 'post' );
```

