# Disable Comments
Comments are disabled by default. Most of our clients do not use comments. If comments are required, add:
```
add_filter( 'rkv_disable_comments', '__return_false' );
```

The disable comments utility hides the discussion options, meta, and and anything else related to comments, but if the template uses the comment_form or comments template parts, this may still show some indication that comments are closed. To completely remove any reference to comments, remove those template elements.
