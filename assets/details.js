/* Animate native Details blocks without changing saved markup or ALT disclosures. */
(() => {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const controllers = [];
    document.querySelectorAll('details.wp-block-details').forEach((details) => {
        const summary = details.querySelector(':scope > summary');
        if (!summary || details.querySelector(':scope > .suppeth-details-content')) return;
        const body = document.createElement('div');
        body.className = 'suppeth-details-content';
        [...details.childNodes].forEach((node) => {
            if (node !== summary) body.append(node);
        });
        details.append(body);
        let desiredOpen = details.open;
        let animation = null;
        const originalOverflow = details.style.overflow;

        const reflect = () => {
            if (!desiredOpen && body.contains(document.activeElement)) summary.focus({ preventScroll: true });
            details.dataset.suppethExpanded = String(desiredOpen);
            summary.setAttribute('aria-expanded', String(desiredOpen));
            body.inert = !desiredOpen;
            body.setAttribute('aria-hidden', String(!desiredOpen));
        };
        const settle = () => {
            const previous = animation;
            animation = null;
            if (previous) previous.cancel();
            details.open = desiredOpen;
            details.style.overflow = originalOverflow;
            reflect();
        };
        const setOpen = (open) => {
            const startHeight = details.getBoundingClientRect().height;
            if (animation) {
                animation.cancel();
                animation = null;
            }
            desiredOpen = open;
            reflect();
            if (reducedMotion.matches || !details.animate) {
                settle();
                return;
            }
            // Keep the body rendered during closing, then remove `open` on completion.
            details.open = true;
            details.style.overflow = originalOverflow;
            const styles = getComputedStyle(details);
            const closedHeight = summary.getBoundingClientRect().height
                + parseFloat(styles.paddingTop) + parseFloat(styles.paddingBottom)
                + parseFloat(styles.borderTopWidth) + parseFloat(styles.borderBottomWidth);
            const endHeight = open ? details.getBoundingClientRect().height : closedHeight;
            details.style.overflow = 'hidden';
            const current = details.animate(
                [{ height: startHeight + 'px' }, { height: endHeight + 'px' }],
                { duration: 280, easing: 'cubic-bezier(0.22, 1, 0.36, 1)' }
            );
            animation = current;
            current.finished.then(() => {
                if (animation === current) settle();
            }).catch(() => {
                // Internal reversals already have a new animation; external cancellation must settle.
                if (animation === current) settle();
            });
        };
        reflect();
        summary.addEventListener('click', (event) => {
            if (event.defaultPrevented || event.target.closest?.('a, button, input, textarea, select')) return;
            event.preventDefault();
            setOpen(!desiredOpen);
        });
        details.addEventListener('toggle', () => {
            if (animation) return;
            desiredOpen = details.open;
            reflect();
        });
        controllers.push({ settle, isAnimating: () => !!animation });
    });
    reducedMotion.addEventListener('change', () => {
        if (reducedMotion.matches) controllers.forEach((controller) => controller.settle());
    });
    window.addEventListener('resize', () => {
        controllers.forEach((controller) => {
            if (controller.isAnimating()) controller.settle();
        });
    });
})();
