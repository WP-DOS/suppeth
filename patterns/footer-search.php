<?php
/**
 * Title: Footer with search
 * Slug: suppeth/footer-search
 * Categories: footer
 * Block Types: core/template-part/footer
 * Description: Branding and navigation above a labelled site search and WordPress credit.
 *
 * @package Suppeth
 * @since Suppeth 0.1.5
 */

?>
<!-- wp:group {"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull">
<!-- wp:group {"align":"wide","className":"suppeth-footer suppeth-footer-with-search","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide suppeth-footer suppeth-footer-with-search" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group">
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
<!-- wp:site-title {"level":0,"fontSize":"small"} /-->
<!-- wp:site-tagline {"fontSize":"small","textColor":"muted"} /-->
</div>
<!-- /wp:group -->

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
<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group">

<?php
echo '<!-- wp:search ' . serialize_block_attributes(
	array(
		'label'         => __( 'Search the site', 'suppeth' ),
		'showLabel'     => true,
		'buttonText'    => __( 'Search', 'suppeth' ),
		'buttonUseIcon' => true,
		'className'     => 'suppeth-footer-search',
		'width'         => 384,
		'widthUnit'     => 'px',
	)
) . ' /-->';
?>

<!-- wp:paragraph {"fontSize":"small","textColor":"muted"} -->
<p class="has-muted-color has-text-color has-small-font-size"><?php esc_html_e( 'Made with WordPress.', 'suppeth' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
