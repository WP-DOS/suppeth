<?php
/**
 * Title: Page not found
 * Slug: suppeth/not-found
 * Categories: text
 * Inserter: no
 *
 * @package Suppeth
 */

?>
<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php esc_html_e( 'Page not found', 'suppeth' ); ?></h1>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>
<?php
printf(
	/* translators: %s: Link to the site homepage. */
	esc_html__( 'The page may have moved or the address may be incorrect. Try a search or return to the %s.', 'suppeth' ),
	'<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'homepage', 'suppeth' ) . '</a>'
);
?>
</p>
<!-- /wp:paragraph -->
