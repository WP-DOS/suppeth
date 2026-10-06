<?php
/**
 * Title: Two-row header with search
 * Slug: suppeth/header-two-row
 * Categories: header
 * Block Types: core/template-part/header
 * Description: Branding and search on the first row with navigation below.
 *
 * @package Suppeth
 * @since Suppeth 0.1.5
 */

?>
<!-- wp:group {"align":"full","className":"suppeth-header-two-row","backgroundColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull suppeth-header-two-row has-base-background-color has-background">
<!-- wp:group {"align":"wide","className":"suppeth-header-row","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide suppeth-header-row" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
<!-- wp:group {"className":"suppeth-header-brand","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group suppeth-header-brand"><!-- wp:site-logo {"width":40} /-->
<!-- wp:site-title {"level":0} /--></div>
<!-- /wp:group -->

<?php
echo '<!-- wp:search ' . serialize_block_attributes(
	array(
		'label'         => __( 'Search the site', 'suppeth' ),
		'showLabel'     => false,
		'buttonText'    => __( 'Search', 'suppeth' ),
		'buttonUseIcon' => true,
		'className'     => 'suppeth-header-search',
		'width'         => 260,
		'widthUnit'     => 'px',
	)
) . ' /-->';
?>

</div>
<!-- /wp:group -->
<!-- wp:group {"align":"wide","className":"suppeth-header-menu-row","style":{"spacing":{"padding":{"bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-group alignwide suppeth-header-menu-row" style="padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:navigation {"overlayMenu":"mobile","layout":{"type":"flex","justifyContent":"center"}} /--></div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
