<?php
/**
 * Load the shared synthetic fixture manifest into the local wp-env database.
 * Run through WP-CLI only; never against a real site.
 *
 * @package Suppeth
 * @since Suppeth 0.1.7
 */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! defined( 'ABSPATH' ) ||
	'local' !== wp_get_environment_type() || 'suppeth' !== get_stylesheet() ||
	! in_array( wp_parse_url( home_url(), PHP_URL_HOST ), array( 'localhost', '127.0.0.1' ), true ) ) {
	throw new RuntimeException( 'Fixture seeding requires the local Suppeth wp-env preview.' );
}

$tests     = dirname( __DIR__ ) . '/tests';
$fixtures  = realpath( $tests . '/fixtures' ) . DIRECTORY_SEPARATOR;
$manifest = json_decode( file_get_contents( $fixtures . 'manifest.json' ), true, 512, JSON_THROW_ON_ERROR );
foreach ( $manifest as $fixture ) {
	$source = realpath( $fixtures . $fixture['source'] );
	if ( ! $source || 0 !== strpos( $source, $fixtures ) ) {
		throw new RuntimeException( 'Expected a bundled synthetic fixture.' );
	}
	$destination = $fixture['destination'];
	if ( 'mu-plugins/suppeth-demo-contact.php' === $destination ) {
		$destination = WPMU_PLUGIN_DIR . '/suppeth-demo-contact.php';
	} elseif ( 'mu-plugins/demo-contact.js' === $destination ) {
		$destination = WPMU_PLUGIN_DIR . '/demo-contact.js';
	} elseif ( ! preg_match( '#^/tmp/suppeth-[a-z-]+\.(php|html|png|webp)$#', $destination ) ) {
		throw new RuntimeException( 'Unexpected fixture destination.' );
	}
	if ( ! wp_mkdir_p( dirname( $destination ) ) || ! copy( $source, $destination ) ) {
		throw new RuntimeException( 'Could not stage fixture: ' . basename( $source ) );
	}
}
require '/tmp/suppeth-seed.php';
require '/tmp/suppeth-template-showcase.php';
echo "\nLocal wp-env sample content is ready.\n";
