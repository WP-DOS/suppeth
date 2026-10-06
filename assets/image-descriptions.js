/**
 * Optional, dependency-free image enhancement; alt attributes remain the fallback.
 *
 * @package Suppeth
 * @since Suppeth 0.1.5
 */
(() => {
	const MIN_WIDTH = 320;
	const MIN_HEIGHT = 160;
	const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

	function init() {
		if (!window.ResizeObserver) return;
		document.querySelectorAll(".suppeth-image-frame").forEach((frame) => {
			const details = frame.querySelector(".suppeth-image-description");
			const image = frame.querySelector("img");
			if (!details || !image) return;
			const summary = details.querySelector("summary");
			let animation = null;
			let desiredOpen = false;
			let revision = 0;

			function setOpen(open) {
				const current = details.getBoundingClientRect();
				const id = ++revision;
				desiredOpen = open;
				animation?.cancel();
				animation = null;
				if (reducedMotion.matches || !details.animate) {
					details.open = open;
					details.style.overflow = "";
					return;
				}
				// Measure both states in the same task, then animate pixel sizes from the
				// fixed bottom-left corner. Text stays clipped inside the growing panel.
				details.open = open;
				const target = details.getBoundingClientRect();
				details.open = true;
				details.style.overflow = "hidden";
				animation = details.animate([
					{ inlineSize: current.width + "px", blockSize: current.height + "px" },
					{ inlineSize: target.width + "px", blockSize: target.height + "px" }
				], { duration: 200, easing: "cubic-bezier(0.2, 0, 0.2, 1)", fill: "forwards" });
				animation.finished.then(() => {
					if (id !== revision) return;
					details.open = open;
					details.style.overflow = "";
					animation.cancel();
					animation = null;
				}).catch(() => {}); // Rapid toggles or resizing legitimately cancel motion.
			}

			summary.addEventListener("click", (event) => {
				event.preventDefault();
				setOpen(animation ? !desiredOpen : !details.open);
			});
			details.addEventListener("keydown", (event) => {
				if (event.key === "Escape" && details.open) {
					event.preventDefault();
					setOpen(false);
					summary.focus({ preventScroll: true });
				}
			});

			function checkSize() {
				const rect = frame.getBoundingClientRect();
				const eligible = rect.width >= MIN_WIDTH && rect.height >= MIN_HEIGHT;
				if (!eligible) {
					++revision;
					animation?.cancel();
					animation = null;
					desiredOpen = false;
					details.open = false;
					details.style.overflow = "";
					if (details.contains(document.activeElement)) {
						frame.tabIndex = -1;
						frame.focus({ preventScroll: true });
					}
				}
				details.hidden = !eligible;
			}
			new ResizeObserver(checkSize).observe(frame);
			image.addEventListener("load", checkSize);
			checkSize();
		});
	}

	if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init, { once: true });
	else init();
})();
