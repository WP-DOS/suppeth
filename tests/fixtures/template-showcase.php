<?php
/**
 * Reusable template demonstrations, restricted to the disposable local preview.
 *
 * @package Suppeth
 * @since Suppeth 0.1.6
 */
if (!defined('ABSPATH')) {
    throw new RuntimeException('Run this fixture through the local wp-env WP-CLI.');
}
if (!in_array(wp_parse_url(home_url(), PHP_URL_HOST), array('127.0.0.1', 'localhost'), true) || get_stylesheet() !== 'suppeth') {
    throw new RuntimeException('Template showcases require the local Suppeth sandbox.');
}

$showcase_upsert = function ($type, $slug, $title, $content, $extra = array()) {
    $existing = get_page_by_path($slug, OBJECT, $type);
    $data = array_merge(array(
        'post_type' => $type, 'post_name' => $slug, 'post_title' => $title,
        'post_content' => $content, 'post_status' => 'publish',
        'meta_input' => array('_suppeth_preview_fixture' => true),
    ), $extra);
    if ($existing) $data['ID'] = $existing->ID;
    $id = wp_insert_post(wp_slash($data), true);
    if (is_wp_error($id)) throw new RuntimeException($id->get_error_message());
    return $id;
};
$showcase_paragraph = function ($text) {
    return '<!-- wp:paragraph --><p>' . $text . '</p><!-- /wp:paragraph -->';
};
$showcase_heading = function ($text) {
    return '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html($text) . '</h2><!-- /wp:heading -->';
};
$author = get_user_by('login', 'suppeth-preview-author');
if (!$author) throw new RuntimeException('Seed the editorial preview before the template showcases.');
$sample = $showcase_paragraph('This sample uses the same content in two page layouts. Compare the reading width, image alignment, and the space available beside the text.')
    . $showcase_heading('A comfortable reading column')
    . $showcase_paragraph('Headings, paragraphs, lists, and images remain ordinary editable blocks. The template controls their surroundings: the header, footer, content column, and optional sidebar.')
    . '<!-- wp:quote --><blockquote class="wp-block-quote"><p>Change the layout without rewriting the content.</p></blockquote><!-- /wp:quote -->'
    . $showcase_heading('What to compare')
    . '<!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>The same content with and without a sidebar.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Recent posts and categories in the shared Sidebar template part.</li><!-- /wp:list-item --><!-- wp:list-item --><li>The layout stacking into one column on a small screen.</li><!-- /wp:list-item --></ul><!-- /wp:list -->';
$media = get_page_by_path('suppeth-fixture-landscape', OBJECT, 'attachment');
if ($media) {
    $sample .= '<!-- wp:image ' . serialize_block_attributes(array('id' => $media->ID, 'align' => 'wide', 'sizeSlug' => 'large')) . ' -->'
        . '<figure class="wp-block-image alignwide size-large"><img src="' . esc_url(wp_get_attachment_url($media->ID)) . '" alt="Geometric landscape used to compare template widths." class="wp-image-' . $media->ID . '"/></figure><!-- /wp:image -->';
}
$common = array('post_author' => $author->ID, 'comment_status' => 'closed');
$default_page = $showcase_upsert('page', 'template-page', 'Page without sidebar', $sample, $common);
update_post_meta($default_page, '_wp_page_template', 'default');
$sidebar_page = $showcase_upsert('page', 'template-page-sidebar', 'Page with sidebar', $sample, $common);
update_post_meta($sidebar_page, '_wp_page_template', 'page-sidebar');
$sidebar_post = $showcase_upsert('post', 'template-post-sidebar', 'An article with room beside it',
    $showcase_paragraph('This is the Post with Sidebar template: article metadata, author details, adjacent-post navigation, and discussion sit beside the shared sidebar.') . $sample,
    array_merge($common, array('post_date' => '2026-09-29 09:00:00', 'post_date_gmt' => get_gmt_from_date('2026-09-29 09:00:00'), 'comment_status' => 'open', 'post_excerpt' => 'A sample article for comparing the default and sidebar post templates.')));
update_post_meta($sidebar_post, '_wp_page_template', 'single-sidebar');
wp_set_post_terms($sidebar_post, array('Templates', 'Layout'), 'post_tag');
if ($media) set_post_thumbnail($sidebar_post, $media->ID);

$showcase_template = function ($slug, $title, $source) use ($showcase_upsert) {
    // Match the Site Editor/file-template path: expand patterns before storing
    // a database template so pagination receives its parent Query's context.
    $source = serialize_blocks(resolve_pattern_blocks(parse_blocks($source)));
    $id = $showcase_upsert('wp_template', $slug, $title, $source);
    wp_set_object_terms($id, get_stylesheet(), 'wp_theme');
    return $id;
};
// Page-scoped index demonstration: keep the normal Journal unchanged. A page's
// inherited query would query that page, so use an explicit post query here.
$index_page = $showcase_upsert('page', 'template-index-sidebar', 'Index with sidebar', '', $common);
$index_source = file_get_contents(get_template_directory() . '/templates/index-sidebar.html');
$index_source = serialize_blocks(resolve_pattern_blocks(parse_blocks($index_source)));
$index_source = str_replace('"query":{"inherit":true}', '"query":{"perPage":4,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false}', $index_source);
$showcase_template('page-template-index-sidebar', 'Preview: Index with Sidebar', $index_source);

// A category-specific override exercises the real inherited archive query
// without replacing the site's normal Archive template or other categories.
$category = term_exists('template-showcase', 'category');
if (!$category) $category = wp_insert_term('Template showcase', 'category', array(
    'slug' => 'template-showcase', 'description' => 'Sample articles collected to demonstrate the Archive with Sidebar template.',
));
if (is_wp_error($category)) throw new RuntimeException($category->get_error_message());
foreach (array('sample-guide-1', 'sample-guide-2', 'sample-guide-3', 'writing-useful-image-descriptions', 'before-you-publish', 'choosing-a-reading-width', 'september-notes', 'template-post-sidebar') as $slug) {
    $post = get_page_by_path($slug, OBJECT, 'post');
    if ($post) wp_set_post_categories($post->ID, array((int) $category['term_id']), true);
}
$showcase_template('category-template-showcase', 'Preview: Archive with Sidebar',
    file_get_contents(get_template_directory() . '/templates/archive-sidebar.html'));

$default_post = get_page_by_path('sample-guide-1', OBJECT, 'post');
$default_archive = get_term_by('slug', 'wordpress', 'category');
if (!$default_post || !$default_archive) throw new RuntimeException('The baseline post/archive fixtures are missing.');
$entries = array(
    array('Page', get_permalink($default_page)),
    array('Page with Sidebar', get_permalink($sidebar_page)),
    array('Post', get_permalink($default_post)),
    array('Post with Sidebar', get_permalink($sidebar_post)),
    array('Index', home_url('/journal/')),
    array('Index with Sidebar', get_permalink($index_page)),
    array('Archive', get_category_link($default_archive->term_id)),
    array('Archive with Sidebar', get_category_link((int) $category['term_id'])),
);
$overview = $showcase_paragraph('Browse the default templates and their sidebar counterparts. These examples use synthetic content and native WordPress blocks; they are part of this local preview only.')
    . $showcase_heading('Choose a layout');
foreach ($entries as $entry) {
    $overview .= $showcase_paragraph('<a href="' . esc_url($entry[1]) . '">' . esc_html($entry[0]) . '</a>');
}
$overview .= $showcase_heading('How the demonstrations work')
    . $showcase_paragraph('Page and Post with Sidebar use the theme’s assignable templates. The index demonstration copies the Index with Sidebar layout into a preview-only page template with an explicit post query. The archive demonstration uses the Archive with Sidebar layout only for the Template showcase category.')
    . $showcase_paragraph('The normal Journal and other category archives keep their default layouts. Change the viewport width to see sidebar layouts stack below the main content.');
$overview_id = $showcase_upsert('page', 'templates', 'Template showcase', $overview, $common);

$menu = get_page_by_path('suppeth-demo-journal-services-portfolio-about', OBJECT, 'wp_navigation');
if (!$menu) throw new RuntimeException('The preview header menu is missing.');
$children = array();
foreach ($entries as $entry) {
    $children[] = array('blockName' => 'core/navigation-link', 'attrs' => array(
        'label' => $entry[0], 'type' => 'custom', 'kind' => 'custom', 'url' => $entry[1],
    ), 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array());
}
$blocks = array_values(array_filter(parse_blocks($menu->post_content), function ($block) {
    return !($block['blockName'] === 'core/navigation-submenu' && ($block['attrs']['label'] ?? '') === 'Templates');
}));
$blocks[] = array('blockName' => 'core/navigation-submenu', 'attrs' => array(
    'label' => 'Templates', 'url' => get_permalink($overview_id), 'kind' => 'post-type', 'type' => 'page', 'id' => $overview_id,
), 'innerBlocks' => $children, 'innerHTML' => '', 'innerContent' => array_fill(0, count($children), null));
$result = wp_update_post(wp_slash(array('ID' => $menu->ID, 'post_content' => serialize_blocks($blocks))), true);
if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());

// Re-running this fixture updates its own routes/menu entry rather than duplicating them.
flush_rewrite_rules(false);
echo ' Template showcase ready: default and sidebar layouts, with a Templates submenu.';
