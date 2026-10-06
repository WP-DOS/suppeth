<?php
/** Run only inside a disposable local WordPress instance. */
require_once '/wordpress/wp-load.php';
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
$registry = WP_Block_Patterns_Registry::get_instance();
foreach ( glob( get_template_directory() . '/patterns/*.php' ) as $file ) {
	$slug = 'suppeth/' . basename( $file, '.php' );
	suppeth_test_assert( $registry->is_registered( $slug ), 'Missing pattern: ' . $slug );
	$pattern = $registry->get_registered( $slug );
	suppeth_test_assert( ! empty( parse_blocks( $pattern['content'] ) ), 'Unparseable pattern: ' . $slug );
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

foreach ( array( false, true ) as $linked ) {
	$image = '<img src="example.jpg" alt="A &lt;safe&gt; description &amp; more">';
	if ( $linked ) {
		$image = '<a href="https://example.test/">' . $image . '</a>';
	}
	$html = suppeth_render_image_description( '<figure>' . $image . '</figure>' );
	suppeth_test_assert( false !== strpos( $html, '<div class="suppeth-image-frame">' ), 'Image wrapper must allow flow content.' );
	suppeth_test_assert( false !== strpos( $html, '<p>A &lt;safe&gt; description &amp; more</p>' ), 'ALT text must be escaped.' );
	if ( $linked ) {
		suppeth_test_assert( false !== strpos( $html, '</a><details' ), 'Disclosure must remain outside the image link.' );
	}
}
foreach ( array( '<figure><img alt=""></figure>', '<figure><picture><img alt="Description"></picture></figure>' ) as $html ) {
	suppeth_test_assert( $html === suppeth_render_image_description( $html ), 'Excluded image markup changed.' );
}

// Playground pins WP_HOME, so emulate a subdirectory via core's URL filter.
$subdirectory_home = static function ( $url, $path ) {
	return 'http://127.0.0.1/subdirectory' . $path;
};
add_filter( 'home_url', $subdirectory_home, 10, 2 );
try {
	ob_start();
	include get_template_directory() . '/patterns/not-found.php';
	$markup = ob_get_clean();
	suppeth_test_assert( false !== strpos( $markup, 'href="http://127.0.0.1/subdirectory/"' ), 'Homepage link must include subdirectory.' );
} finally {
	remove_filter( 'home_url', $subdirectory_home, 10 );
}

$stylesheet = WP_Theme_JSON_Resolver::get_merged_data()->get_stylesheet();
suppeth_test_assert( false !== strpos( $stylesheet, '2.5rem clamp(2rem, 4vw, 3rem)' ), 'Directional column gaps must survive core CSS generation.' );

foreach ( array( 'index', 'page', 'single', 'archive', 'search', '404' ) as $name ) {
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
