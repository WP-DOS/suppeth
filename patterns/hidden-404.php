<?php
/**
 * Title: 404
 * Slug: suppeth/hidden-404
 * Inserter: no
 *
 * @package Suppeth
 * @since Suppeth 0.1.7
 */

?>
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->
<!-- wp:group {"tagName":"main","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
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

<?php
echo '<!-- wp:search ' . serialize_block_attributes(
	array(
		'label'      => __( 'Search', 'suppeth' ),
		'showLabel'  => false,
		'buttonText' => __( 'Search', 'suppeth' ),
	)
) . ' /-->';
?>

</main>
<!-- /wp:group -->
<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
