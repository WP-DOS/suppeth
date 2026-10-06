<?php
/**
 * Editorial demo; loaded by seed.php only inside the disposable local preview.
 *
 * @package Suppeth
 * @since Suppeth 0.1.6
 */
if (!isset($upsert, $visit, $author_id) || !in_array(wp_parse_url(home_url(), PHP_URL_HOST), array('127.0.0.1', 'localhost'), true)) {
    throw new Exception('Run the local fixture seeder first.');
}

$paragraph = function ($text) {
    return '<!-- wp:paragraph --><p>' . $text . '</p><!-- /wp:paragraph -->';
};
$heading = function ($text) {
    return '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html($text) . '</h2><!-- /wp:heading -->';
};
$link = function ($url, $label) {
    return '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
};
$image = '<!-- wp:image {"id":' . $image_id . ',"sizeSlug":"full"} -->'
    . '<figure class="wp-block-image size-full"><img src="' . esc_url(wp_get_attachment_url($image_id)) . '" alt="A pale sky over layered blue hills." class="wp-image-' . $image_id . '"/><figcaption class="wp-element-caption">A study in space, shape, and a limited palette.</figcaption></figure><!-- /wp:image -->';

// Keep the original regression slugs, but replace placeholder prose with complete articles.
$articles = array(
    array('sample-guide-1', 'A quieter way to design a WordPress site', '2026-09-28 09:00:00', 'Design', array('Typography', 'WordPress'),
        '', // Let WordPress generate the preview from the More-marked content.
        $paragraph('The first version had all the right pieces: a title, a useful article, a place for comments. But every piece seemed to ask for attention at once. The borders were dark, the headings were heavy, and even a short page felt busy.')
        . '<!-- wp:more --><!--more--><!-- /wp:more -->'
        . $heading('Start with the words')
        . $paragraph('I begin by removing everything that does not help me read the next sentence. That does not mean removing personality. It means choosing where the personality belongs: in the writing, in an image, or in one well-placed detail.')
        . $paragraph('A comfortable line length does more work than an extra card or a decorative rule. On a small screen, generous margins matter just as much as the size of the text.')
        . $heading('Let the hierarchy do the work')
        . $paragraph('A heading can be distinct without being black or oversized. Here, a medium weight and a little space above the heading separate the sections. The body stays at a size that is comfortable for a longer read.')
        . '<!-- wp:quote --><blockquote class="wp-block-quote"><p>A page should give you a place to start, then get out of the way.</p><cite>From the design notebook</cite></blockquote><!-- /wp:quote -->'
        . $heading('Three things to check before publishing')
        . '<!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>Read the whole article on a phone, not just the first screen.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Check a long title and a short one side by side.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Follow a link, open the menu, and leave a reply.</li><!-- /wp:list-item --></ul><!-- /wp:list -->'
        . $paragraph('The result should feel familiar rather than stripped down. A quieter site still has a clear beginning, useful navigation, and somewhere to go when the article ends.')),
    array('sample-guide-2', 'What I keep in a small theme', '2026-09-22 11:30:00', 'WordPress', array('Themes', 'Workflow'),
        'Native blocks, a handful of tokens, and less code to maintain. A look inside a deliberately small theme.',
        $paragraph('A small theme is not a theme that can only make small sites. It is a theme with a clear opinion about what belongs in the theme and what belongs in the content.')
        . $heading('One source for the everyday decisions')
        . $paragraph('The palette, text sizes, and spacing live in theme.json. The same decisions appear in the editor and on the published page, which makes it easier to know what a change will do.')
        . '<!-- wp:code --><pre class="wp-block-code"><code>{
  "layout": {
    "contentSize": "720px",
    "wideSize": "1120px"
  }
}</code></pre><!-- /wp:code -->'
        . $heading('Keep content portable')
        . $paragraph('Paragraphs, images, lists, and quotes should still make sense if the theme changes. I prefer a native block with good defaults to a custom block that solves one layout and creates three maintenance jobs.')
        . $heading('A useful boundary')
        . $paragraph('The theme sets the reading experience. The editor chooses the story. When those jobs stay separate, a new article does not need a new stylesheet.')),
    array('sample-guide-3', 'A walk, a notebook, and no notifications', '2026-09-16 08:15:00', 'Field notes', array('Notes', 'Process'),
        'Sometimes the most useful part of a working day happens away from the screen.',
        $paragraph('I left the phone in my bag and took the long route home. Nothing remarkable happened. I noticed the painted sign above a closed shop, the shape of a handrail, and the way a narrow window caught the afternoon light.')
        . $heading('Small observations accumulate')
        . $paragraph('The notebook is mostly fragments. A phrase, a rough rectangle, a question about why something feels easy to use. Most entries never become anything, and that is fine.')
        . $paragraph('Later, while working on a page, one of those fragments turns out to be useful. Not as a style to copy, but as a reminder to look at the thing in front of me.')
        . $heading('Make room for an unfinished thought')
        . $paragraph('There is no template for this part of the process. A blank page and twenty minutes are usually enough.')),
    array('writing-useful-image-descriptions', 'Writing image descriptions that actually help', '2026-09-09 10:00:00', 'WordPress', array('Accessibility', 'Images'),
        'Describe what matters in the image, and leave out what the surrounding text already explains.',
        $paragraph('An image description is part of the article, not a box to tick before publishing. The useful question is simple: what would a reader miss if this image were not available?')
        . $image
        . $heading('Describe the purpose, not every pixel')
        . $paragraph('For a chart, describe the trend. For a screenshot, identify the control or result the reader needs to find. For an illustration that only sets a mood, a short description may be enough—or it may be decorative.')
        . $heading('Captions have a different job')
        . $paragraph('A caption can explain why an image is here. Alt text describes the information it carries. Repeating the same sentence in both places rarely helps.')
        . $paragraph('Read the paragraph, description, and caption together once before publishing. If they sound repetitive, give each one a more specific job.')),
    array('before-you-publish', 'The ten-minute check before you publish', '2026-09-02 09:45:00', 'WordPress', array('Publishing', 'Workflow'),
        'A short, repeatable check for the details that are easy to miss in the editor.',
        $paragraph('By the time an article is finished, I have usually read it too many times to see it clearly. A short checklist catches the things familiarity hides.')
        . $heading('Read it somewhere else')
        . $paragraph('Open the preview in a new window. Check the title, excerpt, image caption, and first paragraph. Then narrow the window until it looks like a phone.')
        . '<!-- wp:table --><figure class="wp-block-table"><table><thead><tr><th>Check</th><th>What to look for</th></tr></thead><tbody><tr><td>Links</td><td>Descriptive labels and the right destination</td></tr><tr><td>Images</td><td>Useful alt text and sensible cropping</td></tr><tr><td>Headings</td><td>A clear order with no skipped sections</td></tr><tr><td>Discussion</td><td>A readable thread and a working reply form</td></tr></tbody></table></figure><!-- /wp:table -->'
        . $heading('Leave one final minute')
        . $paragraph('Search for the article on the site. Does the result tell you what the piece is about? The excerpt often deserves one last edit.')),
    array('choosing-a-reading-width', 'How wide should an article be?', '2026-08-25 14:00:00', 'Design', array('Typography', 'Layout'),
        'The best width is the one that makes it easy to find the beginning of the next line.',
        $paragraph('A reading width is a relationship between type size, line height, and the length of the line. A number that works for one font can feel very different with another.')
        . $heading('Start with a real paragraph')
        . $paragraph('I use a paragraph from the actual site rather than a row of placeholder text. Then I read it at a normal distance from the screen. If my eyes keep losing the next line, the measure is too wide.')
        . $heading('Images can have more room')
        . $paragraph('The text column does not need to determine every image size. A wide figure can give a diagram or photograph enough room while the surrounding paragraphs keep their comfortable measure.')
        . $paragraph('The final check is always on a phone. Good desktop proportions should not leave mobile readers with tiny type or cramped margins.')),
    array('september-notes', 'September notes: fewer things, done well', '2026-08-18 16:30:00', 'Field notes', array('Notes', 'Reading'),
        'A small list of things I am reading, making, and paying attention to this month.',
        $paragraph('This month I am finishing one theme instead of starting three. The interesting part is no longer adding features. It is deciding which defaults make the everyday work feel easier.')
        . $heading('On the desk')
        . $paragraph('A notebook of layout sketches, a stack of articles to reread, and a list of small accessibility checks. I am trying to keep the list short enough to actually use.')
        . $heading('One thing worth keeping')
        . $paragraph('A good default is a quiet form of care. Most people will never change it, so it deserves the same attention as the part of the interface that gets all the screenshots.')),
);
$post_ids = array();
foreach ($articles as $article) {
    list($slug, $title, $date, $category_name, $tags, $excerpt, $body) = $article;
    $visit(parse_blocks($body));
    $id = $upsert('post', $slug, $title, $body, array(
        'post_author' => $author_id, 'post_date' => $date, 'post_date_gmt' => get_gmt_from_date($date),
        'post_excerpt' => $excerpt, 'comment_status' => 'open',
    ));
    $term = term_exists($category_name, 'category');
    if (!$term) $term = wp_insert_term($category_name, 'category');
    if (is_wp_error($term)) throw new Exception($term->get_error_message());
    wp_set_post_categories($id, array((int) $term['term_id']));
    wp_set_post_terms($id, $tags, 'post_tag');
    $post_ids[$slug] = $id;
}
set_post_thumbnail($post_ids['sample-guide-1'], $image_id);
set_post_thumbnail($post_ids['sample-guide-2'], $square_id);
// Keep some articles imageless to exercise the optional Featured Image block.

update_option('blogname', 'The Quiet Edit');
update_option('blogdescription', 'Notes on WordPress, thoughtful design, and the everyday work of making things.');
update_option('posts_per_page', 4);

$page_fixture = function ($name) use ($replace, $post_ids) {
    return str_replace(
        array('__CONTACT_URL__', '__SERVICES_URL__', '__PORTFOLIO_URL__', '__JOURNAL_URL__', '__RESOURCES_URL__', '__FEATURED_POST_URL__'),
        array(home_url('/contact/'), home_url('/services/'), home_url('/portfolio/'), home_url('/journal/'), home_url('/resources/'), get_permalink($post_ids['sample-guide-1'])),
        $replace('/tmp/suppeth-' . $name . '.html')
    );
};
$pages = array(
    'about' => array('About',
        $paragraph('The Quiet Edit is a small independent notebook about building useful things on the web. I am Alex Morgan, a writer and WordPress enthusiast interested in the places where clear explanations and thoughtful design meet.')
        . $heading('What you will find here')
        . $paragraph('Practical WordPress notes, close looks at typography and layout, and occasional entries from away from the desk. The aim is to share work that you can use, not just work that looks good in a screenshot.')
        . $heading('A note about this edition')
        . $paragraph('This is a fictional publication built for testing the Suppeth theme. The articles, author, and discussion are demo content. Nothing here is a live service or a real publication.')
        . $paragraph('Start with ' . $link(get_permalink($post_ids['sample-guide-1']), 'a quieter way to design a WordPress site') . ', or browse the ' . $link(home_url('/journal/'), 'journal') . '.')),
    'now' => array('Now',
        $paragraph('A brief snapshot of what has my attention. Updated September 28, 2026.')
        . $heading('Making')
        . $paragraph('Refining a small WordPress theme: lighter type, fewer hard edges, and a reading experience that works just as well on a phone.')
        . $heading('Learning')
        . $paragraph('Testing keyboard navigation and writing better image descriptions. Both are easier to improve when I check them with real content rather than an empty layout.')
        . $heading('Away from the screen')
        . $paragraph('Walking without a route, taking rough notes, and making time to read one long article instead of twenty short summaries.')),
    'resources' => array('Resources',
        $paragraph('A short shelf of references I return to when building and publishing with WordPress.')
        . $heading('WordPress')
        . $paragraph($link('https://wordpress.org/documentation/', 'WordPress documentation') . ' — the starting point for everyday publishing and site administration.')
        . $paragraph($link('https://developer.wordpress.org/block-editor/', 'Block Editor Handbook') . ' — a closer look at blocks, theme settings, and editor behavior.')
        . $heading('Accessibility')
        . $paragraph($link('https://www.w3.org/WAI/tutorials/images/', 'WAI image tutorials') . ' — practical guidance for choosing useful text alternatives.')
        . $heading('From the journal')
        . $paragraph($link(get_permalink($post_ids['before-you-publish']), 'The ten-minute publishing check') . ' and ' . $link(get_permalink($post_ids['choosing-a-reading-width']), 'choosing a reading width') . ' are good places to start.')),
    'contact' => array('Contact', $page_fixture('contact')),
    'services' => array('Services', $page_fixture('services')),
    'portfolio' => array('Portfolio', $page_fixture('portfolio')),
    'journal' => array('Journal', ''),
);
$page_ids = array();
foreach ($pages as $slug => $page) {
    $visit(parse_blocks($page[1]));
    $page_ids[$slug] = $upsert('page', $slug, $page[0], $page[1], array('post_author' => $author_id, 'comment_status' => 'closed'));
}

$home = $paragraph('A small notebook about WordPress, thoughtful design, and making room for the work that matters.')
    . $image
    . $heading('Start here')
    . $paragraph('I am Alex. I write about making websites that are comfortable to read and straightforward to use. ' . $link(get_permalink($page_ids['about']), 'More about this notebook') . '.')
    . $heading('Recent writing')
    . '<!-- wp:query {"queryId":1,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"layout":{"type":"constrained"}} --><div class="wp-block-query">'
    . '<!-- wp:post-template {"className":"suppeth-home-post-list","layout":{"type":"grid","columnCount":1}} --><!-- wp:group {"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} --><div class="wp-block-group" style="margin-bottom:var(--wp--preset--spacing--40)">'
    . '<!-- wp:post-title {"isLink":true,"level":3,"fontSize":"large"} /--><!-- wp:post-date /--><!-- wp:post-excerpt {"moreText":""} /--></div><!-- /wp:group --><!-- /wp:post-template --></div><!-- /wp:query -->'
    . $paragraph($link(get_permalink($page_ids['journal']), 'Browse the full journal →'))
    . $heading('Elsewhere in the notebook')
    . $paragraph('Explore the ' . $link(get_permalink($page_ids['portfolio']), 'portfolio studies') . ', find out about ' . $link(get_permalink($page_ids['services']), 'working together') . ', or browse the ' . $link(get_permalink($page_ids['resources']), 'resource shelf') . '.');
$visit(parse_blocks($home));
$home_id = $upsert('page', 'home', 'Notes for a quieter web', $home, array('post_author' => $author_id, 'comment_status' => 'closed'));
update_option('show_on_front', 'page');
update_option('page_on_front', $home_id);
update_option('page_for_posts', $page_ids['journal']);

// Explicit native block menus: regression pages remain reachable, but out of the primary menu.
$navigation = function ($slugs) use ($page_ids, $pages, $upsert) {
    $blocks = '';
    foreach ($slugs as $slug) {
        $blocks .= '<!-- wp:navigation-link ' . wp_json_encode(array(
            'label' => $pages[$slug][0], 'type' => 'page', 'id' => $page_ids[$slug],
            'url' => get_permalink($page_ids[$slug]), 'kind' => 'post-type',
        )) . ' /-->';
    }
    return $upsert('wp_navigation', 'suppeth-demo-' . implode('-', $slugs), 'Demo menu', $blocks);
};
$header_menu = $navigation(array('journal', 'services', 'portfolio', 'about'));
$footer_menu = $navigation(array('resources', 'now', 'contact'));
foreach (array('header' => $header_menu, 'footer' => $footer_menu) as $part => $ref) {
    $source = file_get_contents(get_stylesheet_directory() . '/parts/' . $part . '.html');
    $source = serialize_blocks(resolve_pattern_blocks(parse_blocks($source)));
    $source = str_replace('<!-- wp:navigation {', '<!-- wp:navigation {"ref":' . $ref . ',', $source);
    $visit(parse_blocks($source));
    $id = $upsert('wp_template_part', $part, ucfirst($part), $source);
    wp_set_object_terms($id, get_stylesheet(), 'wp_theme');
    wp_set_object_terms($id, $part, 'wp_template_part_area');
}

// Add a second conversation without duplicating it when the seeder is rerun.
$id = $post_ids['writing-useful-image-descriptions'];
$email = 'suppeth-image-reader@example.test';
if (!get_comments(array('post_id' => $id, 'author_email' => $email, 'count' => true))) {
    wp_insert_comment(array(
        'comment_post_ID' => $id, 'comment_author' => 'Robin Chen', 'comment_author_email' => $email,
        'comment_content' => 'The distinction between a caption and a description is useful. I had been repeating the same sentence in both places.',
        'comment_approved' => 1,
    ));
}
echo ' Editorial demo ready: home, journal, six information pages, seven articles, and two menus.';
