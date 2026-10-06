<?php
/**
 * Title: Default footer
 * Slug: suppeth/default-footer
 * Categories: text
 * Inserter: no
 *
 * @package Suppeth
 * @since Suppeth 0.1.5
 */

?>
<!-- wp:group {"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull">
<!-- wp:group {"align":"wide","className":"suppeth-footer","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide suppeth-footer" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group"><!-- wp:site-title {"level":0,"fontSize":"small"} /-->

<?php
echo '<!-- wp:navigation ' . serialize_block_attributes(
	array(
		'overlayMenu' => 'never',
		'ariaLabel'   => __( 'Footer', 'suppeth' ),
		'layout'      => array(
			'type'     => 'flex',
			'flexWrap' => 'wrap',
		),
	)
) . ' /-->';
?>
</div>
<!-- /wp:group -->
<!-- wp:site-tagline {"fontSize":"small","textColor":"muted"} /-->
<!-- wp:paragraph {"fontSize":"small","textColor":"muted"} -->
<p class="has-muted-color has-text-color has-small-font-size"><?php esc_html_e( 'Made with WordPress.', 'suppeth' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
