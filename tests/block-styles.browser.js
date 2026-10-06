// Run against /block-style-test/ in the local Playground, or the editor canvas.
// npx agent-browser --session suppeth-styles eval --stdin < tests/block-styles.browser.js
(() => {
	const doc = document.querySelector('iframe[name="editor-canvas"]')?.contentDocument || document;
	const view = doc.defaultView;
	const scope = doc.querySelector(".wp-block-post-content") || doc.querySelector(".is-root-container") || doc;
	const find = (selector) => {
		const node = scope.querySelector(selector);
		if (!node) throw new Error("Missing fixture: " + selector);
		return node;
	};
	const quote = find(".wp-block-quote");
	const code = find(".wp-block-code");
	const filled = find(".wp-block-button:not(.is-style-outline) .wp-block-button__link");
	const outline = find(".wp-block-button.is-style-outline .wp-block-button__link");
	const qs = view.getComputedStyle(quote);
	const cs = view.getComputedStyle(code);
	const fs = view.getComputedStyle(filled);
	const os = view.getComputedStyle(outline);
	const failures = [];
	if (parseFloat(qs.borderLeftWidth) !== 2 || qs.borderLeftStyle !== "solid") failures.push("Quote has no visible rule");
	if (parseFloat(cs.borderTopWidth) < 1 || cs.borderTopStyle !== "solid") failures.push("Code has no border");
	if (cs.backgroundColor === "rgba(0, 0, 0, 0)" || cs.backgroundColor === "rgb(255, 255, 255)") failures.push("Code has no distinct background");
	if (Math.abs(filled.getBoundingClientRect().height - outline.getBoundingClientRect().height) > 1) failures.push("Button heights differ");
	for (const key of ["paddingTop", "paddingBottom", "paddingLeft", "paddingRight", "lineHeight", "borderTopWidth"]) {
		if (fs[key] !== os[key]) failures.push("Button geometry differs: " + key);
	}
	const cell = find(".wp-block-table td");
	const header = find(".wp-block-table th");
	if (view.getComputedStyle(cell).borderTopColor !== "rgb(231, 231, 231)") failures.push("Table borders are not the quiet border token");
	if (view.getComputedStyle(header).textAlign !== "start" && view.getComputedStyle(header).textAlign !== "left") failures.push("Table header is not left-aligned");
	if (doc.documentElement.scrollWidth > view.innerWidth) failures.push("Page overflows viewport");
	const report = { failures, quoteBorder: qs.borderLeftWidth, codeBackground: cs.backgroundColor, filledHeight: filled.getBoundingClientRect().height, outlineHeight: outline.getBoundingClientRect().height, viewport: view.innerWidth };
	if (failures.length) throw new Error(JSON.stringify(report));
	return report;
})()
