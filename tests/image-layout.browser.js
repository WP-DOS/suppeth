/**
 * Native widths, image eligibility, insets, captions, and animated ALT disclosure.
 *
 * @package Suppeth
 * @since Suppeth 0.1.6
 */
(async () => {
	const scope = document.querySelector(".wp-block-post-content");
	const covers = [...scope.querySelectorAll(".wp-block-cover")];
	const byAlign = (align) => covers.find(el => align ? el.classList.contains(align) : !el.classList.contains("alignwide") && !el.classList.contains("alignfull"));
	const failures = [];
	const widths = Object.fromEntries([["normal", null], ["wide", "alignwide"], ["full", "alignfull"]].map(([name, align]) => [name, byAlign(align)?.getBoundingClientRect().width]));
	for (const [name, expected] of Object.entries({ normal: Math.min(720, innerWidth - 48), wide: Math.min(1120, innerWidth - 48), full: innerWidth })) {
		if (Math.abs(widths[name] - expected) > 1) failures.push(name + " width differs");
	}
	const frames = [...scope.querySelectorAll(".suppeth-image-frame")];
	await Promise.all(frames.map(frame => { const img = frame.querySelector("img"); img.loading = "eager"; return img.decode().catch(() => {}); }));
	await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
	let visible = 0;
	for (const frame of frames) {
		const img = frame.querySelector("img");
		const details = frame.querySelector("details");
		const rect = frame.getBoundingClientRect();
		const eligible = rect.width >= 320 && rect.height >= 160;
		if (details.hidden === eligible) failures.push("Incorrect badge eligibility");
		if (details.closest("a")) failures.push("Disclosure nested inside link");
		if (!eligible) continue;
		visible++;
		const summary = details.querySelector("summary");
		const closed = details.getBoundingClientRect();
		summary.click();
		const openingAnimations = details.getAnimations();
		if (!matchMedia("(prefers-reduced-motion: reduce)").matches && !openingAnimations.length) failures.push("ALT does not animate");
		if (matchMedia("(prefers-reduced-motion: reduce)").matches && openingAnimations.length) failures.push("Motion not reduced");
		await Promise.all(openingAnimations.map(a => a.finished.catch(() => {})));
		if (!details.open) failures.push("ALT does not open");
		const panel = details.getBoundingClientRect();
		if (Math.abs(panel.left - closed.left) > 1 || Math.abs(panel.bottom - closed.bottom) > 1) failures.push("ALT anchor moves");
		if (panel.left < rect.left + 7 || panel.right > rect.right - 7 || panel.top < rect.top + 7 || panel.bottom > rect.bottom - 7) failures.push("Panel loses image gutters");
		if (panel.width > 417) failures.push("Panel too wide");
		if (frame.querySelector(".suppeth-alt-text").textContent.trim() !== img.alt.trim()) failures.push("Description differs from alt");
		summary.click();
		await Promise.all(details.getAnimations().map(a => a.finished.catch(() => {})));
		if (details.open) failures.push("ALT does not close");
	}
	const galleryFrames = [...scope.querySelectorAll(".wp-block-gallery .suppeth-image-frame")];
	if (galleryFrames.length > 1) {
		const [first, second] = galleryFrames.map(f => f.getBoundingClientRect());
		if (Math.abs(first.left - second.left) > 1 && Math.abs(first.top - second.top) > 1) failures.push("Gallery images not top-aligned");
	}
	for (const caption of scope.querySelectorAll(".wp-block-gallery figcaption")) {
		const figure = caption.closest("figure.wp-block-image");
		const frame = figure?.querySelector(".suppeth-image-frame");
		if (frame && caption.getBoundingClientRect().top < frame.getBoundingClientRect().bottom - 1) failures.push("Gallery caption overlaps image");
		if (getComputedStyle(caption).position !== "static") failures.push("Caption is an overlay");
	}
	if (scope.querySelector(".wp-block-media-text .suppeth-image-description")) failures.push("ALT present on Media & Text");
	if (scope.querySelector(".wp-block-image.size-thumbnail .suppeth-image-description")) failures.push("ALT present on thumbnail");
	for (const img of scope.querySelectorAll("img[alt='']")) if (img.closest(".suppeth-image-frame")) failures.push("Empty-alt image wrapped");
	const link = scope.querySelector("p a");
	if (getComputedStyle(link).textDecorationStyle !== "dotted") failures.push("Links are not dotted");
	if (document.documentElement.scrollWidth > innerWidth) failures.push("Horizontal overflow");
	const report = { viewport: innerWidth, widths, visibleBadges: visible, reducedMotion: matchMedia("(prefers-reduced-motion: reduce)").matches, failures };
	if (failures.length) throw new Error(JSON.stringify(report));
	return report;
})()
