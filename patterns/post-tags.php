<?php
/**
 * Title: Post tags
 * Slug: suppeth/post-tags
 * Categories: text
 * Inserter: no
 *
 * @package Suppeth
 */

echo '<!-- wp:post-terms ' . serialize_block_attributes(
	array(
		'term'   => 'post_tag',
		'prefix' => __( 'Tags: ', 'suppeth' ),
	)
) . ' /-->';
