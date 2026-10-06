<?php
/**
 * Run only inside a disposable local WordPress instance.
 *
 * @package Suppeth
 * @since Suppeth 0.1.6
 */
if ( ! defined( 'ABSPATH' ) ) {
	throw new RuntimeException( 'Run runtime checks through the local wp-env WP-CLI.' );
}
if ( ! in_array( wp_parse_url( home_url(), PHP_URL_HOST ), array( 'localhost', '127.0.0.1' ), true ) ) {
	throw new RuntimeException( 'Standards tests require a local sandbox.' );
}
function suppeth_test_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}
suppeth_test_assert( version_compare( get_bloginfo( 'version' ), '7.1', '>=' ), 'WordPress must be 7.1+.' );
suppeth_test_assert( 'suppeth' === get_stylesheet(), 'Suppeth must be active.' );
// Core discovers theme-scoped partial presets recursively under styles/.
$variations = WP_Theme_JSON_Resolver::get_style_variations();
foreach ( array( 'Serif', 'Sans', 'Paper', 'Neutral' ) as $title ) {
	suppeth_test_assert( in_array( $title, wp_list_pluck( $variations, 'title' ), true ), 'Missing style preset: ' . $title );
}
$registry = WP_Block_Patterns_Registry::get_instance();
foreach ( glob( get_template_directory() . '/patterns/*.php' ) as $file ) {
	$headers = get_file_data( $file, array( 'slug' => 'Slug', 'inserter' => 'Inserter' ) );
	$slug = $headers['slug'];
	suppeth_test_assert( $registry->is_registered( $slug ), 'Missing pattern: ' . $slug );
	$pattern = $registry->get_registered( $slug );
	suppeth_test_assert( ! empty( parse_blocks( $pattern['content'] ) ), 'Unparseable pattern: ' . $slug );
	suppeth_test_assert( ( 'no' !== $headers['inserter'] ) === ( false !== ( $pattern['inserter'] ?? true ) ), 'Wrong inserter visibility: ' . $slug );
	suppeth_test_assert( serialize_blocks( parse_blocks( $pattern['content'] ) ) === $pattern['content'], 'Pattern delimiter or attributes changed during parsing: ' . $slug );
}

// Alternative headers and footers must be discoverable as native template parts.
$parts = get_block_templates( array(), 'wp_template_part' );
foreach ( array( 'header', 'footer' ) as $area ) {
	foreach ( glob( get_template_directory() . '/patterns/' . $area . '-*.php' ) as $file ) {
		$name = basename( $file, '.php' );
		$matches = array_filter( $parts, static function ( $part ) use ( $name ) {
			return 'suppeth//' . $name === $part->id;
		} );
		suppeth_test_assert( 1 === count( $matches ), 'Missing template part: ' . $name );
		$part = reset( $matches );
		suppeth_test_assert( $area === $part->area, 'Wrong template part area: ' . $name );
		suppeth_test_assert( '' !== $part->title, 'Missing template part title: ' . $name );
		$html = do_blocks( $part->content );
		suppeth_test_assert( false !== strpos( $html, 'wp-block-site-title' ), 'Template part failed to render: ' . $name );
		suppeth_test_assert( false === strpos( $html, '<!-- wp:pattern' ), 'Unresolved template part pattern: ' . $name );
	}
}

// Real core serialization must survive translated quotes and comment delimiters.
$translate = static function ( $translation, $text, $domain ) {
	return 'suppeth' === $domain ? 'Translated " < > -- & ' . $text : $translation;
};
add_filter( 'gettext', $translate, 10, 3 );
foreach ( glob( get_template_directory() . '/patterns/*.php' ) as $file ) {
	ob_start();
	include $file;
	$markup = ob_get_clean();
	suppeth_test_assert( serialize_blocks( parse_blocks( $markup ) ) === $markup, 'Translated pattern changed during parsing: ' . $file );
	preg_match_all( '/<!-- wp:[\w\/-]+\s+(\{.*?\})\s*\/?-->/s', $markup, $comments );
	foreach ( $comments[1] as $json ) {
		$attributes = json_decode( $json, true );
		suppeth_test_assert( JSON_ERROR_NONE === json_last_error(), 'Invalid translated JSON: ' . $file );
		foreach ( array( 'label', 'buttonText', 'moreText', 'prefix', 'ariaLabel' ) as $key ) {
			if ( isset( $attributes[ $key ] ) ) {
				suppeth_test_assert( 0 === strpos( $attributes[ $key ], 'Translated " < > -- & ' ), 'Missing translated attribute.' );
				suppeth_test_assert( false === strpos( $json, '--' ) && false === strpos( $json, '<' ), 'Unsafe block delimiter.' );
			}
		}
	}
}
remove_filter( 'gettext', $translate, 10 );

// Theme activation must not add plugin behavior or rewrite image markup.
suppeth_test_assert( ! function_exists( 'suppeth_render_image_description' ), 'Image disclosures do not belong to the theme.' );
$native_details = do_blocks( '<!-- wp:details --><details class="wp-block-details"><summary>Native details</summary><p>Content</p></details><!-- /wp:details -->' );
suppeth_test_assert( false === strpos( $native_details, 'suppeth-details' ) && ! wp_script_is( 'suppeth-details', 'enqueued' ), 'Theme must not enhance native Details.' );

// Exercise a subdirectory homepage without changing the local site's URL.
$subdirectory_home = static function ( $url, $path ) {
	return 'http://127.0.0.1/subdirectory' . $path;
};
add_filter( 'home_url', $subdirectory_home, 10, 2 );
try {
	ob_start();
	include get_template_directory() . '/patterns/hidden-404.php';
	$markup = ob_get_clean();
	suppeth_test_assert( false !== strpos( $markup, 'href="http://127.0.0.1/subdirectory/"' ), 'Homepage link must include subdirectory.' );
} finally {
	remove_filter( 'home_url', $subdirectory_home, 10 );
}

foreach ( array( 'index', 'page', 'single', 'archive', 'search', '404', 'index-sidebar', 'page-sidebar', 'single-sidebar', 'archive-sidebar' ) as $name ) {
	$slug = 'suppeth/hidden-' . $name;
	suppeth_test_assert( $registry->is_registered( $slug ), 'Missing full-template pattern: ' . $slug );
	suppeth_test_assert( false === $registry->get_registered( $slug )['inserter'], 'Template patterns must be hidden: ' . $slug );
	$template = get_block_template( 'suppeth//' . $name, 'wp_template' );
	suppeth_test_assert( null !== $template, 'Missing template: ' . $name );
	$html = do_blocks( $template->content );
	suppeth_test_assert( false !== strpos( $html, '<main' ), 'Missing rendered main: ' . $name );
	suppeth_test_assert( false === strpos( $html, '<!-- wp:pattern' ), 'Unresolved pattern: ' . $name );
	suppeth_test_assert( false !== strpos( $html, 'Made with WordPress.' ), 'Default footer pattern failed to render: ' . $name );
	if ( 'index' === $name ) {
		suppeth_test_assert( false !== strpos( $html, 'Latest posts' ), 'Index heading pattern failed to render.' );
	}
	if ( '404' === $name ) {
		suppeth_test_assert( false !== strpos( $html, 'Page not found' ), 'Recovery pattern failed to render.' );
	}
}
echo 'Suppeth WordPress ' . get_bloginfo( 'version' ) . " runtime standards checks passed.\n";
