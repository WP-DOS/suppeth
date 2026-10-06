<?php
/**
 * Title: Search
 * Slug: suppeth/hidden-search
 * Inserter: no
 *
 * @package Suppeth
 * @since Suppeth 0.1.7
 */

?>
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->
<!-- wp:group {"tagName":"main","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
<!-- wp:query-title {"type":"search"} /-->
<?php
echo '<!-- wp:search ' . serialize_block_attributes(
	array(
		'label'      => __( 'Search', 'suppeth' ),
		'showLabel'  => false,
		'buttonText' => __( 'Search', 'suppeth' ),
	)
) . ' /-->';
?>

<!-- wp:query {"query":{"inherit":true},"layout":{"type":"constrained"}} -->
<div class="wp-block-query"><!-- wp:post-template {"className":"suppeth-post-list","layout":{"type":"default"}} -->
<!-- wp:group {"tagName":"article","className":"suppeth-post-summary","style":{"spacing":{"blockGap":"1rem"}},"layout":{"type":"constrained"}} -->
<article class="wp-block-group suppeth-post-summary">
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","sizeSlug":"large"} /-->
<!-- wp:post-terms {"term":"category"} /-->
<!-- wp:post-title {"isLink":true,"level":2,"fontSize":"x-large"} /-->
<!-- wp:group {"className":"suppeth-summary-meta","layout":{"type":"flex","flexWrap":"wrap"}} -->
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
<!-- wp:query-pagination {"paginationArrow":"arrow","layout":{"type":"flex","justifyContent":"center","flexWrap":"wrap"}} -->
<!-- wp:query-pagination-previous <?php echo serialize_block_attributes( array( 'label' => __( 'Previous', 'suppeth' ) ) ); ?> /-->
<!-- wp:query-pagination-numbers /-->
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
</main>
<!-- /wp:group -->
<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
