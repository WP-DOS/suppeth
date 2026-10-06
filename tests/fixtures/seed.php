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
    $id = wp_insert_post(wp_slash($data), true);
    if (is_wp_error($id)) {
        throw new Exception($id->get_error_message());
    }
    return $id;
};

$media = function ($name, $alt, $extension = 'png') {
    $slug = 'suppeth-fixture-' . $name;
    $existing = get_page_by_path($slug, OBJECT, 'attachment');
    if ($existing) {
        return $existing->ID;
    }
    $upload = wp_upload_bits($slug . '.' . $extension, null, file_get_contents('/tmp/suppeth-' . $name . '.' . $extension));
    if ($upload['error']) {
        throw new Exception($upload['error']);
    }
    $id = wp_insert_attachment(array(
        'post_mime_type' => wp_check_filetype($upload['file'])['type'], 'post_title' => 'Local sample ' . $name,
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
$author = get_user_by('login', 'suppeth-preview-author');
if (!$author) {
    $author_id = wp_insert_user(array(
        'user_login' => 'suppeth-preview-author',
        'user_pass' => wp_generate_password(32),
        'display_name' => 'Suppeth',
        'role' => 'author',
        'description' => 'A WordPress block theme with editable layouts and native blocks.',
    ));
    if (is_wp_error($author_id)) {
        throw new Exception($author_id->get_error_message());
    }
} else {
    $author_id = $author->ID;
}
update_option('thread_comments', 1);
require '/tmp/suppeth-site.php';
