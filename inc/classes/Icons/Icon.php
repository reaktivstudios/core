<?php
/**
 * Icon output.
 *
 * Safe output for icons registered with the WordPress Icon API (WP 7.1+).
 *
 * @package RKV\Core
 */

namespace RKV\Core\Icons;

/**
 * Outputs registered icons through an SVG allowlist.
 *
 * Usage: \RKV\Core\Icons\Icon::display( 'collection/icon-name' );
 */
class Icon {

	/**
	 * Gets the allowed SVG elements and attributes for icons.
	 *
	 * Broader than the Icon API's allowlist, which drops attributes like
	 * clip-rule and stroke. No styles, scripts, links, or external references.
	 *
	 * @return array Allowed HTML in the format used by wp_kses().
	 */
	public static function allowed_html() {
		$shape = [
			'class'           => true,
			'fill'            => true,
			'fill-rule'       => true,
			'clip-rule'       => true,
			'opacity'         => true,
			'stroke'          => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'stroke-width'    => true,
			'transform'       => true,
		];

		$allowed = [
			'svg'      => array_merge(
				$shape,
				[
					'xmlns'       => true,
					'viewbox'     => true,
					'width'       => true,
					'height'      => true,
					'role'        => true,
					'aria-hidden' => true,
					'aria-label'  => true,
					'focusable'   => true,
				]
			),
			'g'        => $shape,
			'path'     => array_merge( $shape, [ 'd' => true ] ),
			'polygon'  => array_merge( $shape, [ 'points' => true ] ),
			'polyline' => array_merge( $shape, [ 'points' => true ] ),
			'circle'   => array_merge(
				$shape,
				[
					'cx' => true,
					'cy' => true,
					'r'  => true,
				]
			),
			'rect'     => array_merge(
				$shape,
				[
					'x'      => true,
					'y'      => true,
					'width'  => true,
					'height' => true,
					'rx'     => true,
					'ry'     => true,
				]
			),
			'line'     => array_merge(
				$shape,
				[
					'x1' => true,
					'y1' => true,
					'x2' => true,
					'y2' => true,
				]
			),
		];

		/**
		 * Filters the allowed SVG elements and attributes for icons.
		 *
		 * @param array $allowed Allowed HTML in the format used by wp_kses().
		 */
		return apply_filters( 'rkv_core_icon_allowed_html', $allowed );
	}

	/**
	 * Gets a registered icon as sanitized SVG markup.
	 *
	 * Works like wp_get_icon(), but icons registered with a `file_path` are read
	 * from the file and sanitized with allowed_html(), so attributes the
	 * Icon API strips (clip-rule, stroke) are kept.
	 *
	 * @param  string $name Icon name, `collection/icon` (e.g. `wellspent/arrow-right`).
	 * @param  array  $args {
	 *     Optional. Output arguments, same as wp_get_icon().
	 *
	 *     @type int    $size  Width and height in pixels. Default 24.
	 *     @type string $class Extra classes for the <svg>.
	 *     @type string $label Accessible label. Adds role="img" and aria-label;
	 *                         without it the icon gets aria-hidden="true".
	 * }
	 * @return string Sanitized SVG markup, or an empty string if the icon isn't registered.
	 */
	public static function get( $name, $args = [] ) {
		if ( ! class_exists( '\WP_Icons_Registry' ) ) {
			return '';
		}

		$icon = \WP_Icons_Registry::get_instance()->get_registered_icon( $name );

		if ( ! $icon ) {
			return '';
		}

		$svg  = $icon['content'] ?? '';
		$file = isset( $icon['file_path'] ) ? realpath( (string) $icon['file_path'] ) : false;

		if ( $file && str_ends_with( $file, '.svg' ) && is_readable( $file ) ) {
			$svg = (string) file_get_contents( $file ); // phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown -- Local file.
		}

		// kses keeps the text inside removed elements, so drop these whole.
		$svg = preg_replace( '/<(script|style|metadata|title|desc)\b[^>]*>.*?<\/\1>|<!--.*?-->/is', '', $svg );
		$svg = wp_kses( $svg, self::allowed_html() );

		$args = wp_parse_args(
			$args,
			[
				'size'  => 24,
				'class' => '',
				'label' => '',
			]
		);

		$tags = new \WP_HTML_Tag_Processor( $svg );

		if ( ! $tags->next_tag( 'svg' ) ) {
			return '';
		}

		if ( is_numeric( $args['size'] ) ) {
			$tags->set_attribute( 'width', (string) absint( $args['size'] ) );
			$tags->set_attribute( 'height', (string) absint( $args['size'] ) );
		}

		foreach ( preg_split( '/\s+/', (string) $args['class'], -1, PREG_SPLIT_NO_EMPTY ) as $class_name ) {
			$tags->add_class( sanitize_html_class( $class_name ) );
		}

		if ( '' !== $args['label'] ) {
			$tags->set_attribute( 'role', 'img' );
			$tags->set_attribute( 'aria-label', $args['label'] );
			$tags->remove_attribute( 'aria-hidden' );
			$tags->remove_attribute( 'focusable' );
		} else {
			$tags->set_attribute( 'aria-hidden', 'true' );
			$tags->set_attribute( 'focusable', 'false' );
			$tags->remove_attribute( 'role' );
			$tags->remove_attribute( 'aria-label' );
		}

		return $tags->get_updated_html();
	}

	/**
	 * Echoes a registered icon. See Icon::get().
	 *
	 * @param  string $name Icon name, `collection/icon`.
	 * @param  array  $args Optional. `size`, `class`, `label`.
	 * @return void
	 */
	public static function display( $name, $args = [] ) {
		echo wp_kses( self::get( $name, $args ), self::allowed_html() );
	}
}
