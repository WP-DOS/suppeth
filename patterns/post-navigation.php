<?php
/**
 * Title: Article navigation
 * Slug: suppeth/post-navigation
 * Categories: text
 * Inserter: no
 *
 * @package Suppeth
 */

?>
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
