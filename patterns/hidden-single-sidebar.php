<?php
/**
 * Title: Post with Sidebar
 * Slug: suppeth/hidden-single-sidebar
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
<!-- wp:post-title {"level":1} /-->
<!-- wp:group {"className":"suppeth-post-meta","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group suppeth-post-meta"><!-- wp:post-date /-->
<!-- wp:post-terms {"term":"category"} /--></div>
<!-- /wp:group -->
<!-- wp:post-featured-image /-->
<!-- wp:post-content {"layout":{"type":"constrained","contentSize":"100%","wideSize":"100%"}} /-->
<?php
echo '<!-- wp:post-terms ' . serialize_block_attributes(
	array(
		'term'   => 'post_tag',
		'prefix' => __( 'Tags: ', 'suppeth' ),
	)
) . ' /-->';
?>

<!-- wp:group {"className":"suppeth-author","layout":{"type":"constrained"}} -->
<div class="wp-block-group suppeth-author"><!-- wp:paragraph {"fontSize":"small","textColor":"muted"} -->
<p class="has-muted-color has-text-color has-small-font-size"><?php esc_html_e( 'About the author', 'suppeth' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:post-author {"showBio":true,"avatarSize":48} /--></div>
<!-- /wp:group -->
<!-- wp:group {"tagName":"nav","className":"suppeth-post-navigation","layout":{"type":"default"}} -->
<nav class="wp-block-group suppeth-post-navigation">
<?php
echo '<!-- wp:post-navigation-link ' . serialize_block_attributes(
	array(
		'type'      => 'previous',
		'label'     => __( 'Previous article', 'suppeth' ),
		'showTitle' => true,
		'linkLabel' => true,
	)
) . ' /-->';
?>


<?php
echo '<!-- wp:post-navigation-link ' . serialize_block_attributes(
	array(
		'type'      => 'next',
		'label'     => __( 'Next article', 'suppeth' ),
		'showTitle' => true,
		'linkLabel' => true,
	)
) . ' /-->';
?>
</nav>
<!-- /wp:group -->

<!-- wp:comments {"layout":{"type":"constrained"}} -->
<div class="wp-block-comments"><!-- wp:comments-title {"level":2,"showPostTitle":false} /-->
<!-- wp:comment-template -->
<!-- wp:group {"className":"suppeth-comment","layout":{"type":"constrained"}} -->
<div class="wp-block-group suppeth-comment"><!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group"><!-- wp:avatar {"size":32} /-->
<!-- wp:comment-author-name /-->
<!-- wp:comment-date /--></div>
<!-- /wp:group -->
<!-- wp:comment-content /-->
<!-- wp:comment-reply-link /--></div>
<!-- /wp:group -->
<!-- /wp:comment-template -->
<!-- wp:comments-pagination {"layout":{"type":"flex","justifyContent":"space-between"}} -->
<!-- wp:comments-pagination-previous /-->
<!-- wp:comments-pagination-numbers /-->
<!-- wp:comments-pagination-next /-->
<!-- /wp:comments-pagination -->
<!-- wp:post-comments-form /--></div>
<!-- /wp:comments -->
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
