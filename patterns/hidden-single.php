<?php
/**
 * Title: Post
 * Slug: suppeth/hidden-single
 * Inserter: no
 *
 * @package Suppeth
 * @since Suppeth 0.1.7
 */

?>
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->
<!-- wp:group {"tagName":"main","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
<!-- wp:post-title {"level":1} /-->
<!-- wp:group {"className":"suppeth-post-meta","layout":{"type":"flex","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"var:preset|spacing|30"}}} -->
<div class="wp-block-group suppeth-post-meta"><!-- wp:post-date /-->
<!-- wp:post-terms {"term":"category"} /--></div>
<!-- /wp:group -->
<!-- wp:post-featured-image /-->
<!-- wp:post-content {"align":"full","layout":{"type":"constrained"}} /-->
<?php
echo '<!-- wp:post-terms ' . serialize_block_attributes(
	array(
		'term'   => 'post_tag',
		'prefix' => __( 'Tags: ', 'suppeth' ),
	)
) . ' /-->';
?>

<!-- wp:group {"className":"suppeth-author","layout":{"type":"constrained"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"},"padding":{"top":"var:preset|spacing|40"}}}} -->
<div class="wp-block-group suppeth-author" style="margin-top:var(--wp--preset--spacing--50);padding-top:var(--wp--preset--spacing--40)"><!-- wp:paragraph {"fontSize":"small","textColor":"muted"} -->
<p class="has-muted-color has-text-color has-small-font-size"><?php esc_html_e( 'About the author', 'suppeth' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"}} -->
<div class="wp-block-group"><!-- wp:avatar {"size":48,"style":{"border":{"radius":"50%"}}} /-->
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:post-author-name {"isLink":true} /-->
<!-- wp:post-author-biography {"fontSize":"small"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
<!-- wp:group {"tagName":"nav","className":"suppeth-post-navigation","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"},"style":{"spacing":{"blockGap":"var:preset|spacing|40","margin":{"top":"var:preset|spacing|40"}}}} -->
<nav class="wp-block-group suppeth-post-navigation" style="margin-top:var(--wp--preset--spacing--40)">
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

<!-- wp:comments {"anchor":"comments","layout":{"type":"constrained"}} -->
<div id="comments" class="wp-block-comments"><!-- wp:comments-title {"level":2,"showPostTitle":false} /-->
<!-- wp:comment-template -->
<!-- wp:group {"className":"suppeth-comment","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}}} -->
<div class="wp-block-group suppeth-comment" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap"}} -->
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
<!-- wp:comments-pagination-next /-->
<!-- /wp:comments-pagination -->
<!-- wp:post-comments-form /--></div>
<!-- /wp:comments -->
</main>
<!-- /wp:group -->
<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
