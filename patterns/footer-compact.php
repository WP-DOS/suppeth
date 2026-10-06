<?php
/**
 * Title: Compact footer
 * Slug: suppeth/footer-compact
 * Categories: footer
 * Block Types: core/template-part/footer
 * Description: Site title and wrapping navigation in a quiet row, followed by a WordPress credit.
 *
 * @package Suppeth
 * @since Suppeth 0.1.5
 */

?>
<!-- wp:group {"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull">
<!-- wp:group {"align":"wide","className":"suppeth-footer suppeth-footer-compact","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide suppeth-footer suppeth-footer-compact" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group">
<!-- wp:site-title {"level":0,"fontSize":"small"} /-->

<?php
echo '<!-- wp:navigation ' . serialize_block_attributes(
	array(
		'overlayMenu' => 'never',
		'ariaLabel'   => __( 'Footer', 'suppeth' ),
		'fontSize'    => 'small',
		'layout'      => array(
			'type'     => 'flex',
			'flexWrap' => 'wrap',
		),
	)
) . ' /-->';
?>

</div>
<!-- /wp:group -->
<!-- wp:paragraph {"fontSize":"small","textColor":"muted"} -->
<p class="has-muted-color has-text-color has-small-font-size"><?php esc_html_e( 'Made with WordPress.', 'suppeth' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
