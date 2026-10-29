# Icons

[`/inc/classes/Icons/Icon.php`](../../inc/classes/Icons/Icon.php)

Safe output for icons registered with the WordPress Icon API (WP 7.1+).

## Register icons

Register icons in the theme or plugin with core's Icon API:

```php
add_action(
	'init',
	function () {
		wp_register_icon_collection( 'my-theme', [ 'label' => __( 'My Theme Icons', 'my-theme' ) ] );

		wp_register_icon(
			'my-theme/arrow-right',
			[
				'label'     => __( 'Arrow Right', 'my-theme' ),
				'file_path' => get_theme_file_path( 'assets/icons/arrow-right.svg' ),
			]
		);
	}
);
```

## Output an icon

```php
use RKV\Core\Icons\Icon;

Icon::display( 'my-theme/arrow-right' );

$svg = Icon::get( 'my-theme/search', [ 'size' => 20, 'label' => __( 'Search', 'my-theme' ) ] );
```

Arguments match `wp_get_icon()`:

| Arg | Default | Notes |
| --- | --- | --- |
| `size` | `24` | Width and height in pixels. |
| `class` | `''` | Extra classes for the `<svg>`. |
| `label` | `''` | Adds `role="img"` and `aria-label`. Without it, the icon gets `aria-hidden="true"`. |

Use `label` only when the icon stands alone. Icons inside a button or link should be hidden, with the label on the button.

## Why not `wp_get_icon()`?

The Icon API sanitizes icons with a small allowlist (`svg`, `path`, `polygon`) that strips attributes like `clip-rule`, `stroke`, and the root `fill="none"`. Stroke-based icons render wrong as a result.

For icons registered with a `file_path`, `Icon::get()` reads the file and sanitizes it with its own allowlist instead (`Icon::allowed_html()`, filterable with `rkv_core_icon_allowed_html`). It keeps basic shapes and presentation attributes, and removes scripts, styles, event handlers, links, and `<use>`. Icons registered with inline `content` were already sanitized by core and are used as-is.

The Icon block still uses core's allowlist, so stroke-based icons may not render correctly there.
