<?php
/**
 * Minimal WordPress stand-ins for filesystem-only pattern tests, not runtime proof.
 *
 * @package Suppeth
 * @since Suppeth 0.1.6
 */
function __( $text, $domain ) {
	return getenv( 'SUPPETH_TEST_TRANSLATION' ) ? 'Translated " < > -- & ' . $text : $text;
}
function esc_html__( $text, $domain ) {
	return htmlspecialchars( __( $text, $domain ), ENT_QUOTES, 'UTF-8' );
}
function esc_html_e( $text, $domain ) {
	echo esc_html__( $text, $domain );
}
function home_url( $path ) {
	return 'https://example.test/subdirectory' . $path;
}
function esc_url( $url ) {
	return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
}
function serialize_block_attributes( $attributes ) {
	// Match core's block-comment serialization, including hostile translation text.
	return strtr(
		json_encode( $attributes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
		array( '\\\\' => '\u005c', '--' => '\u002d\u002d', '<' => '\u003c', '>' => '\u003e', '&' => '\u0026', '\"' => '\u0022' )
	);
}
include $argv[1];
