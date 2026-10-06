/**
 * Run on /services/ with normal motion, then `set media light reduced-motion` and reload.
 *
 * @package Suppeth
 * @since Suppeth 0.1.6
 */
(async () => {
    const details = [...document.querySelectorAll('details.wp-block-details')];
    if (details.length < 2) throw new Error('Missing accordion fixture');
    const first = details[0];
    const summary = first.querySelector(':scope > summary');
    const body = first.querySelector(':scope > .suppeth-details-content');
    const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const failures = [];
    if (!body) throw new Error('Details enhancement did not load');
    first.open = false;
    await new Promise(requestAnimationFrame);
    const closedHeight = first.getBoundingClientRect().height;
    summary.click();
    const opening = first.getAnimations();
    if (!reduced && !opening.length) failures.push('Opening did not animate');
    if (reduced && opening.length) failures.push('Reduced motion still animates');
    await Promise.all(opening.map(animation => animation.finished));
    if (!first.open || summary.getAttribute('aria-expanded') !== 'true' || body.inert) failures.push('Wrong expanded state');
    if (first.getBoundingClientRect().height <= closedHeight) failures.push('Content is not expanded');
    const icon = getComputedStyle(summary, '::after');
    if (icon.transform === 'none' || icon.transform === 'matrix(1, 0, 0, 1, 0, 0)') failures.push('Plus did not rotate');
    if (reduced && parseFloat(icon.transitionDuration) !== 0) failures.push('Reduced-motion icon still animates');
    const focusTarget = document.createElement('button');
    focusTarget.textContent = 'Temporary focus target';
    body.append(focusTarget);
    focusTarget.focus();
    summary.click();
    if (document.activeElement !== summary) failures.push('Closing lost focus inside hidden content');
    focusTarget.remove();
    const closing = first.getAnimations();
    if (!reduced && (!closing.length || !first.open)) failures.push('Closing hides content before the animation');
    if (!body.inert || summary.getAttribute('aria-expanded') !== 'false') failures.push('Closing content remains focusable');
    await Promise.all(closing.map(animation => animation.finished));
    if (first.open || first.style.overflow === 'hidden') failures.push('Closing did not settle');
    summary.click(); summary.click(); summary.click();
    await Promise.all(first.getAnimations().map(animation => animation.finished));
    if (!first.open) failures.push('Rapid reversal lost the final requested state');
    summary.click();
    await Promise.all(first.getAnimations().map(animation => animation.finished));
    for (let index = 1; index < details.length; index++) {
        const previous = details[index - 1];
        const current = details[index];
        if (previous.nextElementSibling !== current) continue;
        if (Math.abs(current.getBoundingClientRect().top - previous.getBoundingClientRect().bottom) > 1) failures.push('Adjacent Details blocks are separated');
        if (getComputedStyle(current).borderTopWidth !== '0px') failures.push('Divider is doubled');
        if (getComputedStyle(previous).borderBottomLeftRadius !== '0px' || getComputedStyle(current).borderTopLeftRadius !== '0px') failures.push('Inner corners are rounded');
    }
    if (document.documentElement.scrollWidth > innerWidth) failures.push('Horizontal overflow');
    const report = { viewport: innerWidth, reducedMotion: reduced, blocks: details.length, failures };
    if (failures.length) throw new Error(JSON.stringify(report));
    return report;
})()
