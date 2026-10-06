<?php
/**
 * Title: Query no results
 * Slug: suppeth/query-no-results
 * Categories: text
 * Inserter: no
 *
 * @package Suppeth
 */

?>
<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'No posts found. Try a search or return to the homepage.', 'suppeth' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:search
<?php
echo serialize_block_attributes(
	array(
		'label'      => __( 'Search', 'suppeth' ),
		'showLabel'  => false,
		'buttonText' => __( 'Search', 'suppeth' ),
	)
);
?>
/-->
<!-- /wp:query-no-results -->
