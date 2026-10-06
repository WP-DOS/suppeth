/**
 * Run from /templates/ in the local preview; validates menu links and actual layouts.
 *
 * @package Suppeth
 * @since Suppeth 0.1.6
 */
(async () => {
    const submenu = [...document.querySelectorAll('header .wp-block-navigation-submenu')]
        .find((node) => node.querySelector('a')?.textContent.trim() === 'Templates');
    if (!submenu) throw new Error('The header Templates submenu is missing.');
    const links = [...submenu.querySelectorAll('.wp-block-navigation__submenu-container a')];
    if (links.length !== 10) throw new Error('Expected ten template preview pages.');
    const seen = new Set();
    const results = [];
    for (const link of links) {
        if (seen.has(link.href)) throw new Error('Duplicated template menu destination.');
        seen.add(link.href);
        const response = await fetch(link.href);
        if (!response.ok) throw new Error(link.textContent + ': HTTP ' + response.status);
        const page = new DOMParser().parseFromString(await response.text(), 'text/html');
        const sidebar = page.querySelector('main .suppeth-sidebar-layout aside');
        const expectedSidebar = link.textContent.includes('with Sidebar');
        if (!!sidebar !== expectedSidebar) throw new Error(link.textContent + ': wrong template layout.');
        if (page.querySelectorAll('main').length !== 1) throw new Error(link.textContent + ': wrong main landmark count.');
        if (link.textContent.startsWith('Index') || link.textContent.startsWith('Archive')) {
            if (!page.querySelector('main .suppeth-post-summary')) throw new Error(link.textContent + ': empty post query.');
            if (expectedSidebar) {
                const next = page.querySelector('main .wp-block-query-pagination-next');
                if (!next) throw new Error(link.textContent + ': missing next-page link.');
                const nextResponse = await fetch(new URL(next.getAttribute('href'), response.url));
                const nextPage = new DOMParser().parseFromString(await nextResponse.text(), 'text/html');
                const titles = (doc) => [...doc.querySelectorAll('main .suppeth-post-summary .wp-block-post-title a')]
                    .map((anchor) => anchor.getAttribute('href'));
                const firstTitles = titles(page), secondTitles = titles(nextPage);
                if (!nextResponse.ok || !secondTitles.length || firstTitles.some((href) => secondTitles.includes(href))
                    || !nextPage.querySelector('main .suppeth-sidebar-layout aside')) {
                    throw new Error(link.textContent + ': second page did not advance the query.');
                }
            }
        }
        if (link.textContent === 'Post with Sidebar'
            && (!page.querySelector('main .suppeth-author') || !page.querySelector('main .wp-block-comments'))) {
            throw new Error('Post showcase is missing author or discussion blocks.');
        }
        results.push({label: link.textContent.trim(), sidebar: !!sidebar, status: response.status});
    }
    return {passed: true, templates: results};
})();
