<?php
/**
 * Check the minimal synthetic preview and full-seeder idempotency.
 *
 * @package Suppeth
 * @since Suppeth 0.1.7
 */
if (!defined('ABSPATH') || get_stylesheet() !== 'suppeth' ||
    !in_array(wp_parse_url(home_url(), PHP_URL_HOST), array('127.0.0.1', 'localhost'), true)) {
    throw new RuntimeException('Preview checks require the local Suppeth sandbox.');
}
$check = function () {
    if (get_option('blogname') !== 'Suppeth' || !get_option('blogdescription') || get_option('show_on_front') !== 'posts') {
        throw new RuntimeException('Preview identity or homepage settings are incorrect.');
    }
    $posts = get_posts(array('numberposts' => -1, 'meta_key' => '_suppeth_preview_fixture', 'meta_value' => '1'));
    $slugs = wp_list_pluck($posts, 'post_name');
    sort($slugs);
    if ($slugs !== array('blocks', 'sample-guide-1', 'typography')) {
        throw new RuntimeException('Expected exactly three fixture posts.');
    }
    $lead = get_page_by_path('sample-guide-1', OBJECT, 'post');
    $image = get_page_by_path('suppeth-fixture-suppeth-featured', OBJECT, 'attachment');
    if (!$image || get_post_thumbnail_id($lead) !== $image->ID) {
        throw new RuntimeException('The main post must retain its featured artwork.');
    }
    foreach (array('typography', 'blocks') as $slug) {
        $post = get_page_by_path($slug, OBJECT, 'post');
        if (preg_match('/__[A-Z_]+__/', $post->post_content) || strpos($post->post_content, '-test/') !== false) {
            throw new RuntimeException('Unresolved specimen reference: ' . $slug);
        }
    }
    $expected = array('about', 'portfolio', 'contact', 'templates');
    foreach (glob(get_template_directory() . '/templates/*.html') as $path) {
        $slug = 'template-' . basename($path, '.html');
        $expected[] = $slug;
        if (!get_page_by_path($slug)) throw new RuntimeException('Missing template page: ' . $slug);
        $template = get_block_template('suppeth//page-' . $slug, 'wp_template');
        if (!$template || strpos($template->content, '<!-- wp:pattern ') !== false) {
            throw new RuntimeException('Missing or unresolved preview template: ' . $slug);
        }
    }
    $pages = get_posts(array('post_type' => 'page', 'numberposts' => -1,
        'meta_key' => '_suppeth_preview_fixture', 'meta_value' => '1'));
    $actual = wp_list_pluck($pages, 'post_name');
    sort($actual);
    sort($expected);
    if ($actual !== $expected) throw new RuntimeException('Unexpected fixture pages remain.');
    $header = get_block_template('suppeth//header', 'wp_template_part');
    if (!$header || strpos($header->content, 'wp:site-tagline') === false || strpos($header->content, '"isLink":true') === false) {
        throw new RuntimeException('Header needs the tagline and a linked title.');
    }
    $menu = get_page_by_path('suppeth-preview-menu', OBJECT, 'wp_navigation');
    if (!$menu) throw new RuntimeException('Missing preview menu.');
    $submenus = array_filter(parse_blocks($menu->post_content), function ($block) {
        return $block['blockName'] === 'core/navigation-submenu' && ($block['attrs']['label'] ?? '') === 'Templates';
    });
    if (count($submenus) !== 1 || count(reset($submenus)['innerBlocks']) !== 10) {
        throw new RuntimeException('Expected one submenu with ten template pages.');
    }
};
$check();
require get_stylesheet_directory() . '/scripts/seed-wp-env.php';
$check();
echo ' Minimal preview and full-seeder idempotency verified.';
