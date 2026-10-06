<?php
/**
 * Title: Default sidebar
 * Slug: suppeth/default-sidebar
 * Categories: text
 * Inserter: no
 *
 * @package Suppeth
 * @since Suppeth 0.1.5
 */

?>
<!-- wp:group {"className":"suppeth-sidebar","style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"default"}} -->
<div class="wp-block-group suppeth-sidebar">
<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"default"}} -->
<div class="wp-block-group">
<!-- wp:heading {"fontSize":"small"} -->
<h2 class="wp-block-heading has-small-font-size"><?php esc_html_e( 'Categories', 'suppeth' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:categories {"fontSize":"small"} /-->
</div>
<!-- /wp:group -->
<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"default"}} -->
<div class="wp-block-group">
<!-- wp:heading {"fontSize":"small"} -->
<h2 class="wp-block-heading has-small-font-size"><?php esc_html_e( 'Recent posts', 'suppeth' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:latest-posts {"postsToShow":5,"displayPostDate":true,"fontSize":"small"} /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
