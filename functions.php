<?php
/**
 * Load translations and shared usability guards; Global Styles own appearance.
 *
 * @package Suppeth
 * @since Suppeth 0.1.5
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
		wp_enqueue_style( 'suppeth', get_theme_file_uri( 'style.css' ), array(), wp_get_theme()->get( 'Version' ) );
	}
);
