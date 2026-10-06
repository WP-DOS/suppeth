<?php
/**
 * One preview page per theme template, without adding showcase posts.
 *
 * @package Suppeth
 * @since Suppeth 0.1.7
 */
if (!defined('ABSPATH') || !isset($upsert, $paragraph, $author_id) || get_stylesheet() !== 'suppeth' ||
    !in_array(wp_parse_url(home_url(), PHP_URL_HOST), array('127.0.0.1', 'localhost'), true)) {
    throw new RuntimeException('Template showcases require the local Suppeth sandbox.');
}

$labels = array('index' => 'Index', 'index-sidebar' => 'Index with Sidebar',
    'page' => 'Page', 'page-sidebar' => 'Page with Sidebar',
    'single' => 'Post', 'single-sidebar' => 'Post with Sidebar',
    'archive' => 'Archive', 'archive-sidebar' => 'Archive with Sidebar',
    'search' => 'Search', '404' => '404');
$entries = array();
foreach ($labels as $template => $label) {
    $slug = 'template-' . $template;
    $content = $paragraph('This page shows the ' . esc_html($label) . ' template.')
        . '<!-- wp:heading --><h2 class="wp-block-heading">Sample content</h2><!-- /wp:heading -->'
        . $paragraph('The template supplies the header, footer, and content layout. Edit these blocks in the Site Editor.');
    $id = $upsert('page', $slug, $label, $content, array('post_author' => $author_id,
        'comment_status' => strpos($template, 'single') === 0 ? 'open' : 'closed'));
    update_post_meta($id, '_wp_page_template', 'default');
    $source = serialize_blocks(resolve_pattern_blocks(parse_blocks(file_get_contents(get_template_directory() . '/templates/' . $template . '.html'))));
    // Page-scoped copies need an explicit post query instead of inheriting the page.
    $source = str_replace('"query":{"inherit":true}', '"query":{"perPage":2,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":false}', $source);
    $saved = $upsert('wp_template', 'page-' . $slug, 'Preview: ' . $label, $source);
    wp_set_object_terms($saved, get_stylesheet(), 'wp_theme');
    $entries[] = array($label, get_permalink($id));
}
$overview = $paragraph('One page for each Suppeth template. Search and 404 are layout previews; try a search or an unknown URL to see their real behavior.');
$children = array();
foreach ($entries as $entry) {
    $overview .= $paragraph('<a href="' . esc_url($entry[1]) . '">' . esc_html($entry[0]) . '</a>');
    $children[] = array('blockName' => 'core/navigation-link', 'attrs' => array(
        'label' => $entry[0], 'type' => 'custom', 'kind' => 'custom', 'url' => $entry[1],
    ), 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array());
}
$overview_id = $upsert('page', 'templates', 'Templates', $overview, array('post_author' => $author_id, 'comment_status' => 'closed'));
$menu = get_page_by_path('suppeth-preview-menu', OBJECT, 'wp_navigation');
if (!$menu) throw new RuntimeException('The preview header menu is missing.');
$blocks = array_values(array_filter(parse_blocks($menu->post_content), function ($block) {
    return !($block['blockName'] === 'core/navigation-submenu' && ($block['attrs']['label'] ?? '') === 'Templates');
}));
$blocks[] = array('blockName' => 'core/navigation-submenu', 'attrs' => array(
    'label' => 'Templates', 'url' => get_permalink($overview_id), 'kind' => 'post-type', 'type' => 'page', 'id' => $overview_id,
), 'innerBlocks' => $children, 'innerHTML' => '', 'innerContent' => array_fill(0, count($children), null));
$result = wp_update_post(wp_slash(array('ID' => $menu->ID, 'post_content' => serialize_blocks($blocks))), true);
if (is_wp_error($result)) throw new RuntimeException($result->get_error_message());
flush_rewrite_rules(false);
echo ' Ten template pages ready.';
