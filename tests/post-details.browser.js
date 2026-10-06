/**
 * Run on /sample-guide-1/ in the disposable local Playground.
 *
 * @package Suppeth
 * @since Suppeth 0.1.6
 */
(() => {
    const failures = [];
    for (const selector of ['.wp-block-post-terms.taxonomy-category', '.wp-block-post-terms.taxonomy-post_tag', '.wp-block-post-author__bio', '.wp-block-comment-template .depth-2', '.wp-block-comment-reply-link', '.wp-block-post-comments-form']) {
        if (!document.querySelector(selector)) failures.push('Missing post detail: ' + selector);
    }
    if (document.querySelectorAll('.suppeth-comment').length < 3) failures.push('Missing sample discussion');
    if (getComputedStyle(document.body).color !== 'rgb(68, 68, 68)') failures.push('Body text is not softened');
    const title = document.querySelector('h1.wp-block-post-title');
    if (getComputedStyle(title).fontWeight !== '400') failures.push('Title is too heavy');
    if (document.documentElement.scrollWidth > innerWidth) failures.push('Horizontal overflow');
    const report = { viewport: innerWidth, comments: document.querySelectorAll('.suppeth-comment').length, failures };
    if (failures.length) throw new Error(JSON.stringify(report));
    return report;
})()
