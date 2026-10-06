<?php
/**
 * Check the shared synthetic preview and showcase seeder idempotency.
 *
 * @package Suppeth
 * @since Suppeth 0.1.7
 */
if (!defined('ABSPATH')) {
    throw new RuntimeException('Run this fixture through the local wp-env WP-CLI.');
}
if (!in_array(wp_parse_url(home_url(), PHP_URL_HOST), array('127.0.0.1', 'localhost'), true)) {
    throw new RuntimeException('Preview checks require a local sandbox.');
}
foreach (array('elements-proof', 'typography-proof', 'journal', 'services', 'portfolio', 'contact') as $slug) {
    if (!get_page_by_path($slug)) { throw new RuntimeException('Missing preview page: ' . $slug); }
}
if (get_stylesheet() !== 'suppeth' || wp_count_posts()->publish < 7 || get_comments(array('count' => true)) < 3) {
    throw new RuntimeException('Preview theme, posts or comments are missing.');
}
$lead_post = get_page_by_path('sample-guide-1', OBJECT, 'post');
$featured_image = get_page_by_path('suppeth-fixture-suppeth-featured', OBJECT, 'attachment');
$home = get_post(get_option('page_on_front'));
if (!$lead_post || !$featured_image || get_post_thumbnail_id($lead_post) !== $featured_image->ID ||
    !$home || strpos($home->post_content, 'wp-image-' . $featured_image->ID . '"') === false) {
    throw new RuntimeException('Suppeth artwork must be the lead post thumbnail and homepage image.');
}
foreach (array('templates', 'template-page', 'template-page-sidebar', 'template-index-sidebar') as $slug) {
    if (!get_page_by_path($slug)) { throw new RuntimeException('Missing template showcase: ' . $slug); }
}
$sidebar_page = get_page_by_path('template-page-sidebar');
$sidebar_post = get_page_by_path('template-post-sidebar', OBJECT, 'post');
if (!$sidebar_post || get_page_template_slug($sidebar_page) !== 'page-sidebar' || get_page_template_slug($sidebar_post) !== 'single-sidebar') {
    throw new RuntimeException('Sidebar template assignments are missing.');
}
foreach (array('page-template-index-sidebar', 'category-template-showcase') as $slug) {
    $template = get_block_template('suppeth//' . $slug, 'wp_template');
    if (!$template) {
        throw new RuntimeException('Missing preview-only template: ' . $slug);
    }
    if (strpos($template->content, '<!-- wp:pattern ') !== false) {
        throw new RuntimeException('Preview template patterns must be expanded to preserve query context.');
    }
}
$menu = get_page_by_path('suppeth-demo-journal-services-portfolio-about', OBJECT, 'wp_navigation');
$submenus = array_filter(parse_blocks($menu->post_content), function($block) {
    return $block['blockName'] === 'core/navigation-submenu' && ($block['attrs']['label'] ?? '') === 'Templates';
});
if (count($submenus) !== 1 || count(reset($submenus)['innerBlocks']) !== 8) {
    throw new RuntimeException('Template submenu is missing or duplicated.');
}
// The showcase seeder is idempotent and owns only its own routes/menu entry.
require '/tmp/suppeth-template-showcase.php';
$menu = get_post($menu->ID);
$submenus = array_filter(parse_blocks($menu->post_content), function($block) {
    return $block['blockName'] === 'core/navigation-submenu' && ($block['attrs']['label'] ?? '') === 'Templates';
});
if (count($submenus) !== 1) { throw new RuntimeException('Re-seeding duplicated the Templates submenu.'); }
echo 'Local preview fixtures and template showcase verified successfully.';
