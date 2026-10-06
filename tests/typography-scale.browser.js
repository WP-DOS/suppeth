/**
 * Run on the Typography fixture at desktop and mobile widths.
 *
 * @package Suppeth
 * @since Suppeth 0.1.8
 */
(() => {
    const content = document.querySelector('main .wp-block-post-content');
    const headings = [...content.querySelectorAll('.wp-block-heading')]
        .filter(node => /^Heading level [1-6]:/.test(node.textContent));
    if (headings.length !== 6) throw new Error('Expected six heading specimens.');
    const bodySize = parseFloat(getComputedStyle(content.querySelector('p')).fontSize);
    const sizes = headings.map(node => parseFloat(getComputedStyle(node).fontSize));
    if (sizes.some(size => size < bodySize)) throw new Error('Headings smaller than body text: ' + sizes.join(', '));
    if (sizes.some((size, index) => index > 0 && size >= sizes[index - 1])) throw new Error('Heading sizes must descend: ' + sizes.join(', '));
    if (document.documentElement.scrollWidth > innerWidth) throw new Error('Horizontal overflow.');
    return {passed: true, viewport: innerWidth, bodySize, sizes};
})()
