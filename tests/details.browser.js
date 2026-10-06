/**
 * Native Details behavior: no theme-injected wrappers, icons, or animation.
 *
 * @package Suppeth
 * @since Suppeth 0.1.8
 */
(async () => {
    const details = document.querySelector('details.wp-block-details');
    const summary = details?.querySelector('summary');
    if (!summary) throw new Error('Missing native Details fixture.');
    if (details.querySelector('.suppeth-details-content')) throw new Error('Theme rewrote Details content.');
    details.open = false;
    summary.click();
    if (!details.open) throw new Error('Native Details did not open.');
    summary.click();
    if (details.open) throw new Error('Native Details did not close.');
    if (details.getAnimations().length) throw new Error('Theme added an animation.');
    return {passed: true, native: true};
})()
