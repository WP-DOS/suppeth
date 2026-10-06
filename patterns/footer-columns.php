<?php
/**
 * Title: Footer with columns
 * Slug: suppeth/footer-columns
 * Categories: footer
 * Block Types: core/template-part/footer
 * Description: Branding, a vertical menu, and recent posts in three columns that stack on mobile.
 *
 * @package Suppeth
 * @since Suppeth 0.1.5
 */

?>
<!-- wp:group {"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull">
<!-- wp:group {"align":"wide","className":"suppeth-footer suppeth-footer-columns","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide suppeth-footer suppeth-footer-columns" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
<!-- wp:columns -->
<div class="wp-block-columns">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:site-title {"level":0,"fontSize":"small"} /-->
<!-- wp:site-tagline {"fontSize":"small","textColor":"muted"} /-->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":2,"fontSize":"small"} -->
<h2 class="wp-block-heading has-small-font-size"><?php esc_html_e( 'Explore', 'suppeth' ); ?></h2>
<!-- /wp:heading -->

<?php
echo '<!-- wp:navigation ' . serialize_block_attributes(
	array(
		'overlayMenu' => 'never',
		'ariaLabel'   => __( 'Footer', 'suppeth' ),
		'fontSize'    => 'small',
		'layout'      => array(
			'type'        => 'flex',
			'orientation' => 'vertical',
		),
	)
) . ' /-->';
?>

</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":2,"fontSize":"small"} -->
<h2 class="wp-block-heading has-small-font-size"><?php esc_html_e( 'Recent posts', 'suppeth' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:latest-posts {"postsToShow":3,"className":"suppeth-footer-posts","fontSize":"small"} /-->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
<!-- wp:paragraph {"fontSize":"small","textColor":"muted"} -->
<p class="has-muted-color has-text-color has-small-font-size"><?php esc_html_e( 'Made with WordPress.', 'suppeth' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
