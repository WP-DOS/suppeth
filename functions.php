<?php
/**
 * Load the same small stylesheet in the editor and on the front end.
 *
 * @package Suppeth
 */

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'suppeth', get_template_directory() . '/languages' );
		add_editor_style( 'style.css' );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style(
			'suppeth',
			get_theme_file_uri( 'style.css' ),
			array(),
			wp_get_theme()->get( 'Version' )
		);
	}
);

/**
 * Expose non-empty alt text through an optional native disclosure.
 * Saved blocks and their original alt attributes remain unchanged.
 *
 * @param string $content Rendered block HTML.
 * @param array  $block   Parsed block data.
 * @return string Filtered block HTML.
 */
function suppeth_render_image_description( $content, $block = array() ) {
	$attrs = isset( $block['attrs'] ) ? $block['attrs'] : array();
	if ( 'thumbnail' === ( $attrs['sizeSlug'] ?? '' ) || in_array( $attrs['align'] ?? '', array( 'left', 'right' ), true ) ) {
		return $content;
	}
	if ( false !== strpos( $content, 'suppeth-image-frame' ) || false !== stripos( $content, '<picture' ) ) {
		return $content;
	}

	$tags = new WP_HTML_Tag_Processor( $content );
	if ( ! $tags->next_tag( 'IMG' ) ) {
		return $content;
	}
	$alt = $tags->get_attribute( 'alt' );
	if ( ! is_string( $alt ) || '' === trim( $alt ) ) {
		return $content;
	}

	// Quote-aware matching preserves literal > characters inside attributes.
	// Keep linked images inside their anchor, with the disclosure as its sibling.
	$pattern = '~(?:<a\\b(?:[^>"\']|"[^"]*"|\'[^\']*\')*>\\s*)?<img\\b(?:[^>"\']|"[^"]*"|\'[^\']*\')*>(?:\\s*</a>)?~i';
	// Enqueue only on pages with described images. The script checks actual display size.
	wp_enqueue_script(
		'suppeth-image-descriptions',
		get_theme_file_uri( 'assets/image-descriptions.js' ),
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);
	$disclosure = '<details class="suppeth-image-description" hidden>'
		. '<summary aria-label="' . esc_attr__( 'ALT: image description', 'suppeth' ) . '">'
		. '<span class="suppeth-alt-badge">' . esc_html__( 'ALT', 'suppeth' ) . '</span>'
		. '<span class="suppeth-alt-title" aria-hidden="true">' . esc_html__( 'Image description', 'suppeth' ) . '</span>'
		. '<span class="suppeth-alt-close" aria-hidden="true">&times;</span></summary>'
		. '<div class="suppeth-alt-text" tabindex="0"><p>' . esc_html( $alt ) . '</p></div></details>';

	$result = preg_replace_callback(
		$pattern,
		static function ( $matches ) use ( $disclosure ) {
			return '<div class="suppeth-image-frame">' . $matches[0] . $disclosure . '</div>';
		},
		$content,
		1
	);
	return is_string( $result ) ? $result : $content;
}

// Load the accordion enhancement only when a native Details block is rendered.
add_filter(
	'render_block_core/details',
	function ( $content ) {
		if ( ! is_admin() && '' !== $content ) {
			wp_enqueue_script(
				'suppeth-details',
				get_theme_file_uri( 'assets/details.js' ),
				array(),
				wp_get_theme()->get( 'Version' ),
				true
			);
		}
		return $content;
	}
);

add_filter( 'render_block_core/image', 'suppeth_render_image_description', 10, 2 );
add_filter( 'render_block_core/post-featured-image', 'suppeth_render_image_description', 10, 2 );
