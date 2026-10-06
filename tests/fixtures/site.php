<?php
/**
 * Minimal theme showcase, loaded only by the disposable local seeder.
 *
 * @package Suppeth
 * @since Suppeth 0.1.7
 */
if (!isset($upsert, $visit, $author_id) || get_stylesheet() !== 'suppeth' ||
    !in_array(wp_parse_url(home_url(), PHP_URL_HOST), array('127.0.0.1', 'localhost'), true)) {
    throw new RuntimeException('Run the local Suppeth fixture seeder first.');
}

$paragraph = function ($text) {
    return '<!-- wp:paragraph --><p>' . $text . '</p><!-- /wp:paragraph -->';
};
$heading = function ($text) {
    return '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html($text) . '</h2><!-- /wp:heading -->';
};

// Retire only content owned by the previous preview, never arbitrary local work.
$keep = array('post' => array('sample-guide-1', 'typography', 'blocks'),
    'page' => array('about', 'portfolio', 'contact', 'templates', 'template-index', 'template-index-sidebar',
        'template-page', 'template-page-sidebar', 'template-single', 'template-single-sidebar',
        'template-archive', 'template-archive-sidebar', 'template-search', 'template-404'),
    'wp_navigation' => array('suppeth-preview-menu'),
    'wp_template' => array_map(function ($path) {
        return 'page-template-' . basename($path, '.html');
    }, glob(get_template_directory() . '/templates/*.html')));
foreach (array('post', 'page', 'wp_navigation', 'wp_template') as $type) {
    foreach (get_posts(array('post_type' => $type, 'post_status' => 'any', 'numberposts' => -1,
        'meta_key' => '_suppeth_preview_fixture', 'meta_value' => '1')) as $post) {
        if (in_array($post->post_name, $keep[$type] ?? array(), true)) {
            continue;
        }
        wp_delete_post($post->ID, true);
    }
}
wp_update_user(array('ID' => $author_id, 'display_name' => 'Suppeth',
    'description' => 'A WordPress block theme with editable layouts and native blocks.'));
update_option('blogname', 'Suppeth');
update_option('blogdescription', 'A simple starting point for your WordPress site.');
update_option('posts_per_page', 2);
update_option('show_on_front', 'posts');
update_option('page_on_front', 0);
update_option('page_for_posts', 0);
update_option('sticky_posts', array());

$specimen = function ($name) use ($replace) {
    return str_replace(array('/typography-test/', '/block-style-test/'),
        array('/typography/', '/blocks/'), $replace('/tmp/suppeth-' . $name . '.html'));
};
$articles = array(
    'sample-guide-1' => array('Meet Suppeth',
        $paragraph('Suppeth is a WordPress block theme with a readable starting layout. This preview shows its typography, native blocks, and editable templates.')
        . '<!-- wp:more --><!--more--><!-- /wp:more -->'
        . $heading('Make it your own')
        . $paragraph('Change fonts, colors, and spacing in the Site Editor. Pages and posts use ordinary WordPress blocks.')
        . $heading('Explore the theme')
        . $paragraph('See the <a href="/typography/">typography</a>, browse the <a href="/blocks/">blocks</a>, or compare the <a href="/templates/">templates</a>.')),
    'typography' => array('Typography', $specimen('typography')),
    'blocks' => array('Blocks', $specimen('blocks')),
);
$post_ids = array();
foreach ($articles as $slug => $article) {
    $post_ids[$slug] = $upsert('post', $slug, $article[0], $article[1], array(
        'post_author' => $author_id, 'comment_status' => $slug === 'sample-guide-1' ? 'open' : 'closed',
        'post_excerpt' => '', 'post_date' => '2026-01-' . (20 - count($post_ids)) . ' 09:00:00',
        'post_date_gmt' => '2026-01-' . (20 - count($post_ids)) . ' 09:00:00',
    ));
    wp_set_post_terms($post_ids[$slug], array(), 'post_tag');
    wp_set_post_categories($post_ids[$slug], array((int) get_option('default_category')));
    update_post_meta($post_ids[$slug], '_wp_page_template', 'default');
    delete_post_thumbnail($post_ids[$slug]);
}
$suppeth_image_id = $media('suppeth-featured', 'Suppeth in an elegant serif beside a winking medieval nobleman in blue and gold clothing.', 'webp');
set_post_thumbnail($post_ids['sample-guide-1'], $suppeth_image_id);
$lead = $post_ids['sample-guide-1'];
foreach (get_comments(array('post_id' => $lead, 'status' => 'all')) as $comment) {
    if (strpos($comment->comment_author_email, 'suppeth-') === 0) wp_delete_comment($comment->comment_ID, true);
}
wp_insert_comment(array('comment_post_ID' => $lead, 'comment_author' => 'Sample reader',
    'comment_author_email' => 'suppeth-reader@example.test',
    'comment_content' => 'A sample comment to show the discussion layout.', 'comment_approved' => 1));

$pages = array(
    'about' => array('About', $paragraph('Suppeth is a free WordPress block theme. Use the Site Editor to customize its templates, colors, and typography.')
        . $paragraph('This local preview contains sample content to showcase the theme.')),
    'portfolio' => array('Portfolio / Services', $paragraph('A simple layout for presenting your work and services.')
        . $heading('Selected work') . $paragraph('Add project images, descriptions, and links here.')
        . '<!-- wp:image {"id":' . $image_id . ',"sizeSlug":"full","align":"wide"} --><figure class="wp-block-image alignwide size-full"><img src="' . esc_url(wp_get_attachment_url($image_id)) . '" alt="Geometric landscape with blue hills." class="wp-image-' . $image_id . '"/></figure><!-- /wp:image -->'
        . $heading('Services') . '<!-- wp:details --><details class="wp-block-details"><summary>Design and development</summary>'
        . $paragraph('Describe what you offer and how you work.') . '</details><!-- /wp:details -->'
        . '<!-- wp:details --><details class="wp-block-details"><summary>Support and maintenance</summary>'
        . $paragraph('Outline the support available after a project is finished.') . '</details><!-- /wp:details -->'),
    'contact' => array('Contact', $paragraph('A sample contact page. This local form does not send or store messages.')
        . '<!-- wp:shortcode -->[suppeth_demo_contact]<!-- /wp:shortcode -->'),
);
$page_ids = array();
foreach ($pages as $slug => $page) {
    $visit(parse_blocks($page[1]));
    $page_ids[$slug] = $upsert('page', $slug, $page[0], $page[1], array('post_author' => $author_id, 'comment_status' => 'closed'));
    update_post_meta($page_ids[$slug], '_wp_page_template', 'default');
}
$links = '';
foreach ($pages as $slug => $page) {
    $links .= '<!-- wp:navigation-link ' . wp_json_encode(array('label' => $page[0],
        'type' => 'page', 'id' => $page_ids[$slug], 'url' => get_permalink($page_ids[$slug]), 'kind' => 'post-type')) . ' /-->';
}
$header_menu = $upsert('wp_navigation', 'suppeth-preview-menu', 'Suppeth preview', $links);
foreach (array('header', 'footer') as $part) {
    $source = serialize_blocks(resolve_pattern_blocks(parse_blocks(file_get_contents(get_stylesheet_directory() . '/parts/' . $part . '.html'))));
    $source = str_replace('<!-- wp:navigation {', '<!-- wp:navigation {"ref":' . $header_menu . ',', $source);
    if ($part === 'header') {
        $source = str_replace('<!-- wp:site-title {"level":0} /-->',
            '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group"><!-- wp:site-title {"level":0,"isLink":true} /--><!-- wp:site-tagline /--></div><!-- /wp:group -->', $source);
    }
    $visit(parse_blocks($source));
    $id = $upsert('wp_template_part', $part, ucfirst($part), $source);
    wp_set_object_terms($id, get_stylesheet(), 'wp_theme');
    wp_set_object_terms($id, $part, 'wp_template_part_area');
}
echo ' Suppeth preview ready: three posts, About, Portfolio / Services, and Contact.';
