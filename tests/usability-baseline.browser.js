/**
 * Exercise minimum CSS guards with hostile content, without changing saved data.
 * Run on the frontend Typography post at desktop and mobile widths.
 *
 * @package Suppeth
 * @since Suppeth 0.1.8
 */
(async () => {
	const scope = document.querySelector('main .wp-block-post-content');
	if (!scope) throw new Error('Open a post with native Post Content.');
	const originalUrl = location.href;
	const originalScroll = [scrollX, scrollY];
	const specimen = document.createElement('div');
	const paragraph = document.createElement('p');
	paragraph.textContent = 'Unbroken'.repeat(40);
	const link = document.createElement('a');
	link.href = '#baseline-anchor';
	link.textContent = 'https://example.test/' + 'path'.repeat(80);
	const pre = document.createElement('pre');
	pre.textContent = 'preserved_line_'.repeat(80);
	const anchor = document.createElement('p');
	anchor.id = 'baseline-anchor';
	anchor.textContent = 'Anchor destination';
	specimen.append(paragraph, link, pre, anchor);
	scope.append(specimen);
	try {
		location.hash = anchor.id;
		await new Promise(resolve => requestAnimationFrame(resolve));
		if (document.documentElement.scrollWidth > innerWidth + 1) throw new Error('Long content widens the page.');
		if (getComputedStyle(paragraph).overflowWrap !== 'anywhere') throw new Error('Wrapping guard is missing.');
		if (getComputedStyle(pre).overflowX !== 'auto' || pre.scrollWidth <= pre.clientWidth) throw new Error('Preformatted text must scroll locally.');
		if (parseFloat(getComputedStyle(anchor).scrollMarginBlockStart) < 16) throw new Error('Anchor offset is missing.');
		for (const part of document.querySelectorAll('header.wp-block-template-part, footer.wp-block-template-part')) {
			if (parseFloat(getComputedStyle(part).marginBlockStart) !== 0) throw new Error('Root block gap adds template-part spacing.');
		}
		return {passed: true, viewport: innerWidth, preScrollWidth: pre.scrollWidth};
	} finally {
		specimen.remove();
		history.replaceState(history.state, '', originalUrl);
		scrollTo(...originalScroll);
	}
})()
