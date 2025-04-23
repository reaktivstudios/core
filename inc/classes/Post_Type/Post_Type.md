# Using Post Types
Post types are a core feature of WordPress, and something we rely on frequently. There are certain smart defaults we rely on when creating post types that are codified into our utility class.

## Built-in Post Types vs Custom Post Types
One of the first things you will need to decide is when to use built in post types, versus creating new ones. Though the differences can sometimes be only marginal, there are some built in features of "Posts" and "Pages" which don't always follow along with CPT's. So, in general, if a built-in post type does address a need, it is often a better starting point, even if it needs to be renamed.

## `show_in_rest` and `show_in_graphql`
We almost always want to show a post type in the REST API, which also opts into the block editor, among other things. This has been added as one of our defaults when creating post types.

## Registering rest fields
A helper method `register_rest_fields` is also added to each post type, for instances when additional rest feilds need to be added to the outputted JSON for a particular post type, typically meta or related taxonomy terms.

## Removing the slug
The `remove_slug` property can be used when we want to strip a prefix from URLs for a custom post type, which is prepended by default. For instance, a URL like `/cta/post-slug/` would simply become `/post-slug/`. Be careful when using this option, as it can lead to unintentional collisions between different post types.

## Default Arugments
The default arguments for our post types are:

```
		$default_args = [
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
		];
```

In general, this follows a pattern of setting post types that can be accessed in the menu by site builders, but excluded from search by default. We also default to the `post` capability type.

## The RKV Post Type Base Class

The Post Type class can be extended to create new post types