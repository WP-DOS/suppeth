<?php
/**
 * Title: Sticky header
 * Slug: suppeth/header-sticky
 * Categories: header
 * Block Types: core/template-part/header
 * Description: Compact branding and navigation that remain visible while scrolling.
 *
 * @package Suppeth
 * @since Suppeth 0.1.5
 */

?>
<!-- wp:group {"align":"full","className":"suppeth-header-sticky","backgroundColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull suppeth-header-sticky has-base-background-color has-background">
<!-- wp:group {"align":"wide","className":"suppeth-header-row","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide suppeth-header-row" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
<!-- wp:group {"className":"suppeth-header-brand","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group suppeth-header-brand"><!-- wp:site-logo {"width":40} /-->
<!-- wp:site-title {"level":0} /--></div>
<!-- /wp:group -->
<!-- wp:navigation {"overlayMenu":"mobile","layout":{"type":"flex","justifyContent":"right"}} /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
