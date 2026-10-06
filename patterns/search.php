<?php
/**
 * Title: Search form
 * Slug: suppeth/search
 * Categories: text
 * Inserter: no
 *
 * @package Suppeth
 */

echo '<!-- wp:search ' . serialize_block_attributes(
	array(
		'label'      => __( 'Search', 'suppeth' ),
		'showLabel'  => false,
		'buttonText' => __( 'Search', 'suppeth' ),
	)
) . ' /-->';
