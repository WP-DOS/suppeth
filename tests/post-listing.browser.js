/**
 * Run on the homepage, /page/2/, a category archive, or a populated search.
 *
 * @package Suppeth
 * @since Suppeth 0.1.6
 */
(async () => {
    await document.fonts.ready;
    const entries = [...document.querySelectorAll('.suppeth-post-summary')];
    const failures = [];
    if (!entries.length) throw new Error('No article summaries');
    for (const entry of entries) {
        for (const selector of ['.wp-block-post-title a', '.wp-block-post-author-name', '.wp-block-post-date', '.wp-block-post-excerpt']) {
            if (!entry.querySelector(selector)) failures.push('Missing ' + selector);
        }
        // Searches also return pages, which correctly have no category terms.
        if (entry.closest('li')?.classList.contains('type-post') && !entry.querySelector('.taxonomy-category')) failures.push('Post is missing categories');
        const title = entry.querySelector('.wp-block-post-title');
        const more = entry.querySelector('.wp-block-post-excerpt__more-link');
        if (!more || more.href !== title.querySelector('a').href) failures.push('Missing or incorrect Continue reading link');
        const image = entry.querySelector('.wp-block-post-featured-image img');
        if (image) {
            image.loading = 'eager';
            await image.decode();
            const rect = image.getBoundingClientRect();
            const figure = image.closest('figure').getBoundingClientRect();
            if (Math.abs(rect.height - figure.height) > 1) failures.push('Featured image does not fill its chosen crop');
        }
    }
    const pages = [...document.querySelectorAll('.wp-block-query-pagination .page-numbers')];
    if (pages.length && !pages.some(page => page.getAttribute('aria-current') === 'page')) failures.push('Missing current page');
    // Typography and pagination appearance are editable in Global Styles.
    if (performance.getEntriesByType('resource').some(resource => /\.(woff2?|ttf)(\?|$)/.test(resource.name) && new URL(resource.name).origin !== location.origin)) failures.push('Remote font request');
    if (document.documentElement.scrollWidth > innerWidth) failures.push('Horizontal overflow');
    const report = { path: location.pathname, viewport: innerWidth, entries: entries.length, featuredImages: entries.filter(entry => entry.querySelector('.wp-block-post-featured-image')).length, failures };
    if (failures.length) throw new Error(JSON.stringify(report));
    return report;
})()
