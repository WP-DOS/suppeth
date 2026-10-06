<?php
/**
 * Title: Centered footer
 * Slug: suppeth/footer-centered
 * Categories: footer
 * Block Types: core/template-part/footer
 * Description: Centered branding, site description, navigation, and WordPress credit.
 *
 * @package Suppeth
 * @since Suppeth 0.1.5
 */

?>
<!-- wp:group {"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull">
<!-- wp:group {"align":"wide","className":"suppeth-footer suppeth-footer-centered","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide suppeth-footer suppeth-footer-centered" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
<!-- wp:site-logo {"width":48,"align":"center"} /-->
<!-- wp:site-title {"level":0,"textAlign":"center","fontSize":"small"} /-->
<!-- wp:site-tagline {"textAlign":"center","fontSize":"small","textColor":"muted"} /-->

<?php
echo '<!-- wp:navigation ' . serialize_block_attributes(
	array(
		'overlayMenu' => 'never',
		'ariaLabel'   => __( 'Footer', 'suppeth' ),
		'fontSize'    => 'small',
		'layout'      => array(
			'type'           => 'flex',
			'flexWrap'       => 'wrap',
			'justifyContent' => 'center',
		),
	)
) . ' /-->';
?>

<!-- wp:paragraph {"align":"center","fontSize":"small","textColor":"muted"} -->
<p class="has-text-align-center has-muted-color has-text-color has-small-font-size"><?php esc_html_e( 'Made with WordPress.', 'suppeth' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
