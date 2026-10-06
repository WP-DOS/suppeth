<?php
/**
 * Title: Header with search
 * Slug: suppeth/header-search
 * Categories: header
 * Block Types: core/template-part/header
 * Description: Branding, navigation, and a compact search field in a responsive row.
 *
 * @package Suppeth
 */

?>
<!-- wp:group {"align":"full","className":"suppeth-header-with-search","backgroundColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull suppeth-header-with-search has-base-background-color has-background">
<!-- wp:group {"align":"wide","className":"suppeth-header-row","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide suppeth-header-row" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
<!-- wp:group {"className":"suppeth-header-brand","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group suppeth-header-brand"><!-- wp:site-logo {"width":40} /-->
<!-- wp:site-title {"level":0} /--></div>
<!-- /wp:group -->
<!-- wp:group {"className":"suppeth-header-tools","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"right"}} -->
<div class="wp-block-group suppeth-header-tools"><!-- wp:navigation {"overlayMenu":"mobile","layout":{"type":"flex","justifyContent":"right"}} /-->
<!-- wp:search
<?php
echo serialize_block_attributes(
	array(
		'label'         => __( 'Search the site', 'suppeth' ),
		'showLabel'     => false,
		'buttonText'    => __( 'Search', 'suppeth' ),
		'buttonUseIcon' => true,
		'className'     => 'suppeth-header-search',
	)
);
?>
/--></div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
