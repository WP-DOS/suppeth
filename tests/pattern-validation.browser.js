/**
 * Run in the authenticated Site Editor, where WordPress has registered core blocks.
 *
 * @package Suppeth
 * @since Suppeth 0.1.6
 */
(async () => {
	if (!window.wp?.blocks || !window.wp?.apiFetch) {
		throw new Error('Open the Site Editor before running pattern validation.');
	}
	const required = [
		'header-search', 'header-centered', 'header-two-row',
		'footer-compact', 'footer-centered', 'footer-columns', 'footer-search',
	];
	const patterns = (await wp.apiFetch({ path: '/wp/v2/block-patterns/patterns' }))
		.filter((pattern) => pattern.name.startsWith('suppeth/'));
	for (const name of required) {
		if (!patterns.some((pattern) => pattern.name === `suppeth/${name}`)) {
			throw new Error(`Missing registered pattern: suppeth/${name}`);
		}
	}
	const failures = [];
	for (const pattern of patterns) {
		const blocks = wp.blocks.parse(pattern.content);
		let count = 0;
		const walk = (items) => items.forEach((block) => {
			count++;
			if (block.isValid === false || block.name === 'core/freeform' || block.name === 'core/missing') {
				failures.push(`${pattern.name}: invalid ${block.name}`);
			}
			if (block.name === 'core/search' && !block.attributes.label) {
				failures.push(`${pattern.name}: Search lost its translated label`);
			}
			if (pattern.name.includes('footer') && block.name === 'core/navigation'
				&& (block.attributes.overlayMenu !== 'never' || block.attributes.ariaLabel !== 'Footer')) {
				failures.push(`${pattern.name}: Navigation lost its attributes`);
			}
			walk(block.innerBlocks || []);
		});
		walk(blocks);
		if (!count) failures.push(`${pattern.name}: empty pattern`);
		// Validate the serialized result too, without rewriting the source before parsing.
		const roundTrip = wp.blocks.parse(wp.blocks.serialize(blocks));
		const check = (items) => items.forEach((block) => {
			if (block.isValid === false) failures.push(`${pattern.name}: invalid round-trip ${block.name}`);
			check(block.innerBlocks || []);
		});
		check(roundTrip);
	}
	if (failures.length) throw new Error(failures.join('\n'));
	return { passed: true, patterns: patterns.length, replacementPatterns: required.length };
})();
