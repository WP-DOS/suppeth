<?php
/**
 * Title: Post excerpt
 * Slug: suppeth/post-excerpt
 * Categories: text
 * Inserter: no
 *
 * @package Suppeth
 */

echo '<!-- wp:post-excerpt ' . serialize_block_attributes(
	array(
		'moreText'      => __( 'Continue reading', 'suppeth' ),
		'excerptLength' => 40,
	)
) . ' /-->';
