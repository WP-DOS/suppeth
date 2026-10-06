/**
 * Run on the demo homepage, journal, or an information page at desktop/mobile widths.
 *
 * @package Suppeth
 * @since Suppeth 0.1.6
 */
(() => {
    const failures = [];
    const headerLinks = [...document.querySelectorAll('header .wp-block-navigation-item__content')];
    const footerLinks = [...document.querySelectorAll('footer .wp-block-navigation-item__content')];
    if (headerLinks.map(link => link.textContent.trim()).join(',') !== 'Journal,Services,Portfolio,About') failures.push('Primary menu is not the editorial menu');
    if (!footerLinks.some(link => link.textContent.trim() === 'Contact')) failures.push('Footer has no contact page');
    if (!document.querySelector('footer .wp-block-site-tagline')) failures.push('Missing footer description');
    if (!document.querySelector('h1')) failures.push('Missing page heading');
    if (location.pathname === '/' && document.querySelectorAll('.wp-block-post-template > li').length !== 3) failures.push('Homepage should have three recent articles');
    if (location.pathname === '/journal/' && document.querySelectorAll('.wp-block-post-template > li').length !== 4) failures.push('Journal should show four articles per page');
    if (location.pathname === '/journal/' && !document.querySelector('.wp-block-query-pagination-next')) failures.push('Journal is missing pagination');
    const current = headerLinks.find(link => link.getAttribute('aria-current') === 'page');
    if (['/journal/', '/about/', '/services/', '/portfolio/'].includes(location.pathname) && !current) failures.push('Missing current-page highlight');
    if (current && getComputedStyle(current).fontWeight !== '600') failures.push('Current page is not highlighted');
    if (document.querySelector('.suppeth-navigation-dot')) failures.push('Removed navigation dot returned');
    if (document.documentElement.scrollWidth > innerWidth) failures.push('Horizontal overflow');
    const report = { path: location.pathname, viewport: innerWidth, header: headerLinks.length, footer: footerLinks.length, failures };
    if (failures.length) throw new Error(JSON.stringify(report));
    return report;
})()
