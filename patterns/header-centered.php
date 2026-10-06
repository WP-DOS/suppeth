<?php
/**
 * Title: Centered header
 * Slug: suppeth/header-centered
 * Categories: header
 * Block Types: core/template-part/header
 * Description: Centered logo and site title with the menu underneath.
 *
 * @package Suppeth
 * @since Suppeth 0.1.5
 */

?>
<!-- wp:group {"align":"full","className":"suppeth-header-centered","backgroundColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull suppeth-header-centered has-base-background-color has-background">
<!-- wp:group {"align":"wide","className":"suppeth-header-centered","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"},"blockGap":"var:preset|spacing|30"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
<div class="wp-block-group alignwide suppeth-header-centered" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
<!-- wp:site-logo {"width":64} /-->
<!-- wp:site-title {"level":0,"textAlign":"center"} /-->
<!-- wp:navigation {"overlayMenu":"mobile","layout":{"type":"flex","justifyContent":"center"}} /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
