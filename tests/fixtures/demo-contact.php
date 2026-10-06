<?php
/**
 * Plugin Name: Suppeth Local Demo Contact
 * Description: Local-only form specimen. Never sends or stores messages.
 */
if (!defined('ABSPATH') || !in_array(wp_parse_url(home_url(), PHP_URL_HOST), array('127.0.0.1', 'localhost'), true)) {
    return;
}
add_shortcode('suppeth_demo_contact', function () {
    wp_enqueue_script('suppeth-demo-contact', plugins_url('demo-contact.js', __FILE__), array(), '1', true);
    $id = wp_unique_id('suppeth-demo-contact-');
    ob_start();
    ?>
    <form class="suppeth-demo-contact" aria-describedby="<?php echo esc_attr($id); ?>note">
        <p id="<?php echo esc_attr($id); ?>note" class="has-small-font-size has-muted-color has-text-color">Demo only. Use made-up details; nothing is sent or saved.</p>
        <p><label for="<?php echo esc_attr($id); ?>name">Name <span aria-hidden="true">*</span></label>
        <input id="<?php echo esc_attr($id); ?>name" name="name" autocomplete="off" required maxlength="100"></p>
        <p><label for="<?php echo esc_attr($id); ?>email">Email <span aria-hidden="true">*</span></label>
        <input id="<?php echo esc_attr($id); ?>email" name="email" type="email" autocomplete="off" required maxlength="254"></p>
        <p><label for="<?php echo esc_attr($id); ?>interest">What are you working on?</label>
        <select id="<?php echo esc_attr($id); ?>interest" name="interest"><option value="">Choose an option</option><option>A new website</option><option>A site refresh</option><option>A focused working session</option></select></p>
        <p><label for="<?php echo esc_attr($id); ?>message">Your message <span aria-hidden="true">*</span></label>
        <textarea id="<?php echo esc_attr($id); ?>message" name="message" rows="6" required maxlength="3000"></textarea></p>
        <button class="wp-element-button" type="submit" disabled>Try the demo form</button>
        <noscript><p>This demo needs JavaScript to safely preview submission. Nothing will be sent.</p></noscript>
        <p class="suppeth-demo-contact-status has-small-font-size" role="status" tabindex="-1"></p>
    </form>
    <?php
    return ob_get_clean();
});
