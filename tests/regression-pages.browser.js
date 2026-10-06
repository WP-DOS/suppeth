// Run on either legacy regression page or the new independent proof pages.
(async () => {
	const scope = document.querySelector('.wp-block-post-content');
	if (!scope) throw new Error('Missing post content');
	// Load off-screen lazy images too, so the test does not wait on viewport scrolling.
	const specimens = [...scope.querySelectorAll('img')];
	specimens.forEach(img => { img.loading = 'eager'; });
	await Promise.all(specimens.map(img => img.decode().catch(() => {})));
	const failures = [];
	// The typography page also has a deliberate H2 specimen, not a section label.
	const sections = [...scope.querySelectorAll(':scope > h2')].filter(h => !h.textContent.startsWith('Heading level '));
	if (sections.some(h => !h.nextElementSibling?.matches('.wp-block-separator'))) failures.push('Section without heading/divider pair');
	if (document.documentElement.scrollWidth > innerWidth) failures.push('Horizontal page overflow');
	if ([...scope.querySelectorAll('pre, code')].some(node => !getComputedStyle(node).fontFamily.includes('monospace'))) failures.push('Code is not monospace');
	const expectedScroll = matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
	if (getComputedStyle(document.documentElement).scrollBehavior !== expectedScroll) failures.push('Anchor scrolling does not respect motion preferences');
	const images = [...scope.querySelectorAll('img')];
	if (images.some(img => !img.complete || !img.naturalWidth)) failures.push('Broken image');
	if (/block-style-test|elements-proof/.test(location.pathname)) {
		for (const selector of ['.wp-block-cover', '.wp-block-image', '.wp-block-gallery', '.wp-block-columns', '.wp-block-media-text', '.wp-block-quote', '.wp-block-code', '.wp-block-buttons', '.wp-block-table', '.wp-block-details', '.wp-block-search']) {
			if (!scope.querySelector(selector)) failures.push('Missing block: ' + selector);
		}
		if (!scope.textContent.includes('✓ Completed')) failures.push('Missing icon specimen');
		if (scope.querySelector('.wp-block-cover')?.getBoundingClientRect().height < 360) failures.push('Hero too short');
	} else {
		for (let level = 1; level <= 6; level++) if (!scope.querySelector('h' + level)) failures.push('Missing heading level ' + level);
		for (const selector of ['a', 'strong', 'em', 'mark', 's', 'code', 'sup', 'sub', 'small', '.has-mono-font-family']) {
			if (!scope.querySelector(selector)) failures.push('Missing type specimen: ' + selector);
		}
	}
	const report = { url: location.pathname, viewport: innerWidth, sections: sections.length, loadedImages: images.filter(img => img.naturalWidth).length, failures };
	if (failures.length) throw new Error(JSON.stringify(report));
	return report;
})()
