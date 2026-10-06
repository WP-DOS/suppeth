<?php
/**
 * Seed only a disposable local preview, never a real website.
 *
 * @package Suppeth
 * @since Suppeth 0.1.6
 */
if (!defined('ABSPATH')) {
    throw new RuntimeException('Run this fixture through the local wp-env WP-CLI.');
}

if (!in_array(wp_parse_url(home_url(), PHP_URL_HOST), array('127.0.0.1', 'localhost'), true)) {
    throw new Exception('Suppeth fixtures are restricted to local preview sites.');
}
if (!wp_is_block_theme()) {
    throw new Exception('Expected a block theme.');
}
require_once ABSPATH . 'wp-admin/includes/image.php';

$visit = function ($blocks) use (&$visit) {
    foreach ($blocks as $block) {
        if ($block['blockName'] && !WP_Block_Type_Registry::get_instance()->is_registered($block['blockName'])) {
            throw new Exception('Unregistered block: ' . $block['blockName']);
        }
        $visit($block['innerBlocks']);
    }
};
foreach (glob(get_stylesheet_directory() . '/templates/*.html') as $path) {
    $visit(parse_blocks(file_get_contents($path)));
}

// Exercise the actual WordPress renderer, including escaping and linked images.
$raw_image = '<figure><a href="/typography-test/"><img src="/sample.png" alt="A &amp; B &lt;shape&gt; &quot;quoted&quot; > example"/></a><figcaption>Caption</figcaption></figure>';
$described = suppeth_render_image_description($raw_image);
if (1 !== substr_count($described, '<details') || false === strpos($described, '</a><details') || false !== strpos($described, '<shape>')) {
    throw new Exception('ALT disclosure rendering or escaping failed.');
}
if ($described !== suppeth_render_image_description($described)) {
    throw new Exception('ALT disclosure rendered twice.');
}
foreach (array('<figure><img src="/sample.png" alt=""/></figure>', '<figure><img src="/sample.png"/></figure>') as $decorative) {
    if ($decorative !== suppeth_render_image_description($decorative)) {
        throw new Exception('Decorative image acquired an ALT badge.');
    }
}

$upsert = function ($type, $slug, $title, $content, $extra = array()) {
    $existing = get_page_by_path($slug, OBJECT, $type);
    $data = array_merge(array(
        'post_type' => $type, 'post_name' => $slug, 'post_title' => $title,
        'post_content' => $content, 'post_status' => 'publish',
        'meta_input' => array('_suppeth_preview_fixture' => true),
    ), $extra);
    if ($existing) {
        $data['ID'] = $existing->ID;
    }
    $id = wp_insert_post($data, true);
    if (is_wp_error($id)) {
        throw new Exception($id->get_error_message());
    }
    return $id;
};

$media = function ($name, $alt) {
    $slug = 'suppeth-fixture-' . $name;
    $existing = get_page_by_path($slug, OBJECT, 'attachment');
    if ($existing) {
        return $existing->ID;
    }
    $upload = wp_upload_bits($slug . '.png', null, file_get_contents('/tmp/suppeth-' . $name . '.png'));
    if ($upload['error']) {
        throw new Exception($upload['error']);
    }
    $id = wp_insert_attachment(array(
        'post_mime_type' => 'image/png', 'post_title' => 'Local sample ' . $name,
        'post_name' => $slug, 'post_status' => 'inherit',
    ), $upload['file'], 0, true);
    if (is_wp_error($id)) {
        throw new Exception($id->get_error_message());
    }
    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $upload['file']));
    update_post_meta($id, '_wp_attachment_image_alt', $alt);
    return $id;
};
$image_id = $media('landscape', 'Geometric landscape illustration with a pale sky and blue hills.');
$square_id = $media('square', 'Geometric blue square illustration.');
$thumb_id = $media('thumbnail', 'Small geometric landscape thumbnail.');
$replace = function ($path) use ($image_id, $square_id, $thumb_id, $visit) {
    $content = str_replace(
        array('"mediaId":"__SQUARE_ID__"', '"id":"__IMAGE_ID__"', '"id":"__SQUARE_ID__"', '"id":"__THUMB_ID__"', '__IMAGE_URL__', '__IMAGE_ID__', '__SQUARE_URL__', '__SQUARE_ID__', '__THUMB_URL__', '__THUMB_ID__', '"id":0', '"mediaId":0'),
        array('"mediaId":' . $square_id, '"id":' . $image_id, '"id":' . $square_id, '"id":' . $thumb_id, wp_get_attachment_url($image_id), $image_id, wp_get_attachment_url($square_id), $square_id, wp_get_attachment_url($thumb_id), $thumb_id, '"id":' . $image_id, '"mediaId":' . $image_id),
        file_get_contents($path)
    );
    $visit(parse_blocks($content));
    return $content;
};

// Remove only the known initial WordPress placeholders, not arbitrary user content.
foreach (array(array('hello-world', 'post'), array('sample-page', 'page')) as $default) {
    $post = get_page_by_path($default[0], OBJECT, $default[1]);
    if ($post) {
        wp_delete_post($post->ID, true);
    }
}
update_option('blogname', 'Suppeth local preview');
update_option('posts_per_page', 2);
$category = term_exists('getting-started', 'category');
if (!$category) {
    $category = wp_insert_term('Getting started', 'category', array('slug' => 'getting-started'));
}
$content = '<!-- wp:paragraph --><p>Local preview content for testing Suppeth. Nothing on this site is published to wp-dos.com.</p><!-- /wp:paragraph -->'
    . '<!-- wp:heading --><h2 class="wp-block-heading">A readable starting point</h2><!-- /wp:heading -->'
    . '<!-- wp:paragraph --><p>Check comfortable line lengths, headings, links, and spacing between sections.</p><!-- /wp:paragraph -->';
$author = get_user_by('login', 'suppeth-preview-author');
if (!$author) {
    $author_id = wp_insert_user(array(
        'user_login' => 'suppeth-preview-author',
        'user_pass' => wp_generate_password(32),
        'display_name' => 'Alex Morgan',
        'role' => 'author',
        'description' => 'Writes practical WordPress guides with a focus on clear explanations and thoughtful design.',
    ));
    if (is_wp_error($author_id)) {
        throw new Exception($author_id->get_error_message());
    }
} else {
    $author_id = $author->ID;
}
update_option('thread_comments', 1);
for ($i = 1; $i <= 3; $i++) {
    $id = $upsert('post', 'sample-guide-' . $i, $i === 1 ? 'A clean foundation for clear WordPress guides, even with a longer title' : 'Sample guide ' . $i, $content, array('comment_status' => 'open', 'post_author' => $author_id));
    if (!is_wp_error($category)) {
        wp_set_post_categories($id, array((int) $category['term_id']));
    }
    wp_set_post_terms($id, array('WordPress', 'Design'), 'post_tag');
    if ($i === 1) {
        $comments = array(
            array('reader', 'Jamie Lee', 'The quieter typography makes these guides much easier to read. Could you share how you chose the line length?', ''),
            array('reply', 'Alex Morgan', 'I start with a comfortable reading width, then check it on a phone. Longer examples can still use the wide layout.', 'reader'),
            array('long', 'Sam Rivera', 'I tried this with a longer article, a few code samples, and a table. The extra breathing room helps each section feel distinct without needing a heavy border around everything.', ''),
        );
        $comment_ids = array();
        foreach ($comments as $sample) {
            $email = 'suppeth-' . $sample[0] . '@example.test';
            $existing = get_comments(array('post_id' => $id, 'author_email' => $email, 'number' => 1));
            $data = array(
                'comment_post_ID' => $id, 'comment_author' => $sample[1],
                'comment_author_email' => $email, 'comment_content' => $sample[2],
                'comment_approved' => 1, 'comment_parent' => $sample[3] ? $comment_ids[$sample[3]] : 0,
                'user_id' => $sample[0] === 'reply' ? $author_id : 0,
            );
            if ($existing) {
                $data['comment_ID'] = $existing[0]->comment_ID;
                wp_update_comment($data);
                $comment_ids[$sample[0]] = $data['comment_ID'];
            } else {
                $comment_ids[$sample[0]] = wp_insert_comment($data);
            }
        }
    }
}
$upsert('page', 'about-this-test-site', 'About this test site', $content);
$upsert('page', 'block-style-test', 'Block style regression', $replace('/tmp/suppeth-blocks.html'));
$upsert('page', 'typography-test', 'Typography regression', $replace('/tmp/suppeth-typography.html'));
// Proof pages have their own slugs and never replace demo or legacy regression pages.
$upsert('page', 'elements-proof', 'Elements proof', $replace('/tmp/suppeth-elements-proof.html'), array('comment_status' => 'closed'));
$upsert('page', 'typography-proof', 'Typography proof', $replace('/tmp/suppeth-typography-proof.html'), array('comment_status' => 'closed'));
echo 'Suppeth local fixtures ready: blocks, typography, independent proof pages, sample posts, and local media.';
require '/tmp/suppeth-site.php';
