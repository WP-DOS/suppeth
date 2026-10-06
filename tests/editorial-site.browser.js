/**
 * Run on the Suppeth homepage or an information page at desktop/mobile widths.
 *
 * @package Suppeth
 * @since Suppeth 0.1.6
 */
(() => {
    const failures = [];
    const header = document.querySelector('header');
    const title = header?.querySelector('.wp-block-site-title a');
    if (title?.textContent.trim() !== 'Suppeth' || new URL(title.href).pathname !== '/') failures.push('Title must link home');
    if (header?.querySelector('.wp-block-site-tagline')?.textContent.trim() !== 'A simple starting point for your WordPress site.') failures.push('Missing header tagline');
    const menu = header?.querySelector('.wp-block-navigation__container');
    const primary = [...(menu?.children ?? [])].map(item => item.querySelector('a')?.textContent.trim());
    if (primary.join(',') !== 'About,Portfolio / Services,Contact,Templates') failures.push('Unexpected primary menu');
    if (location.pathname === '/' && document.querySelectorAll('main .suppeth-post-summary').length !== 2) failures.push('Homepage should show two posts');
    if (location.pathname === '/' && !document.querySelector('.wp-block-query-pagination-next')) failures.push('Missing homepage pagination');
    if (['/about/', '/portfolio/', '/contact/'].includes(location.pathname) && !header?.querySelector('[aria-current="page"]')) failures.push('Missing current-page highlight');
    if (document.documentElement.scrollWidth > innerWidth) failures.push('Horizontal overflow');
    const report = { path: location.pathname, viewport: innerWidth, primary, failures };
    if (failures.length) throw new Error(JSON.stringify(report));
    return report;
})()
