/**
 * Verify Suppeth preserves native image and gallery markup without a plugin.
 *
 * @package Suppeth
 * @since Suppeth 0.1.8
 */
(() => {
    const scope = document.querySelector('main .wp-block-post-content');
    if (!scope?.querySelector('.wp-block-image img[alt]')) throw new Error('Missing image specimens.');
    if (scope.querySelector('.suppeth-image-frame, .wpdos-image-frame')) throw new Error('A disclosure plugin is active in the theme sandbox.');
    const captions = [...scope.querySelectorAll('.wp-block-gallery figcaption')];
    if (!captions.length) throw new Error('Missing gallery captions.');
    for (const caption of captions) {
        const style = getComputedStyle(caption);
        if (style.position !== 'absolute') throw new Error('Native gallery caption layout was overridden.');
    }
    return {passed: true, nativeGalleryCaptions: captions.length};
})()
