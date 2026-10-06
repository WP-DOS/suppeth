<?php
/**
 * Title: Archive with Sidebar
 * Slug: suppeth/hidden-archive-sidebar
 * Inserter: no
 *
 * @package Suppeth
 * @since Suppeth 0.1.7
 */

?>
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->
<!-- wp:group {"tagName":"main","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
<!-- wp:columns {"align":"wide","className":"suppeth-sidebar-layout","style":{"spacing":{"margin":{"bottom":"0"}}}} -->
<div class="wp-block-columns alignwide suppeth-sidebar-layout" style="margin-bottom:0">
<!-- wp:column {"width":"66.66%","layout":{"type":"constrained"}} -->
<div class="wp-block-column" style="flex-basis:66.66%">
<!-- wp:query-title {"type":"archive","showPrefix":false} /-->
<!-- wp:term-description /-->
<!-- wp:query {"query":{"inherit":true},"layout":{"type":"constrained"}} -->
<div class="wp-block-query"><!-- wp:post-template {"className":"suppeth-post-list","layout":{"type":"default"}} -->
<!-- wp:group {"tagName":"article","className":"suppeth-post-summary","style":{"spacing":{"blockGap":"1rem"}},"layout":{"type":"constrained"}} -->
<article class="wp-block-group suppeth-post-summary">
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","sizeSlug":"large"} /-->
<!-- wp:post-terms {"term":"category"} /-->
<!-- wp:post-title {"isLink":true,"level":2,"fontSize":"x-large"} /-->
<!-- wp:group {"className":"suppeth-summary-meta","layout":{"type":"flex","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"var:preset|spacing|30"}}} -->
<div class="wp-block-group suppeth-summary-meta"><!-- wp:post-author-name {"isLink":true} /-->
<!-- wp:post-date /--></div>
<!-- /wp:group -->
<?php
echo '<!-- wp:post-excerpt ' . serialize_block_attributes(
	array(
		'moreText'      => __( 'Continue reading', 'suppeth' ),
		'excerptLength' => 40,
	)
) . ' /-->';
?>
</article>
<!-- /wp:group -->
<!-- /wp:post-template -->
<!-- wp:query-pagination {"paginationArrow":"arrow","layout":{"type":"flex","justifyContent":"space-between","flexWrap":"wrap"}} -->
<!-- wp:query-pagination-previous <?php echo serialize_block_attributes( array( 'label' => __( 'Previous', 'suppeth' ) ) ); ?> /-->
<!-- wp:query-pagination-next <?php echo serialize_block_attributes( array( 'label' => __( 'Next', 'suppeth' ) ) ); ?> /-->
<!-- /wp:query-pagination -->

<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'No posts found. Try a search or return to the homepage.', 'suppeth' ); ?></p>
<!-- /wp:paragraph -->

<?php
echo '<!-- wp:search ' . serialize_block_attributes(
	array(
		'label'      => __( 'Search', 'suppeth' ),
		'showLabel'  => false,
		'buttonText' => __( 'Search', 'suppeth' ),
	)
) . ' /-->';
?>

<!-- /wp:query-no-results -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:column -->
<!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%">
<!-- wp:template-part {"slug":"sidebar","tagName":"aside","area":"uncategorized"} /-->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</main>
<!-- /wp:group -->
<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
