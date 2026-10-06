/**
 * Run on the frontend after choosing one of Suppeth's header patterns.
 *
 * @package Suppeth
 * @since Suppeth 0.1.6
 */
(async () => {
  const header = document.querySelector('header.wp-block-template-part');
  const failures = [];
  const check = (condition, message) => { if (!condition) failures.push(message); };
  check(!!header?.querySelector('.wp-block-site-title'), 'Missing dynamic site identity');
  check(!!header?.querySelector('.wp-block-navigation'), 'Missing native navigation');
  check(document.documentElement.scrollWidth <= innerWidth + 1, 'Horizontal overflow');
  const row = header?.querySelector('.suppeth-header-row');
  if (row && innerWidth >= 1024) {
    check(row.getBoundingClientRect().width >= 1000, 'Header row is incorrectly constrained');
    const brand = row.querySelector('.suppeth-header-brand');
    const menu = row.querySelector('.wp-block-navigation');
    if (brand && menu) check(Math.abs(brand.getBoundingClientRect().top - menu.getBoundingClientRect().top) < 80, 'Desktop navigation wraps below branding');
  }
  const search = header?.querySelector('form.wp-block-search');
  if (search) {
    const input = search.querySelector('input[type="search"]');
    check(!!input?.labels.length, 'Search must have an accessible label');
    check(!!search.querySelector('button[type="submit"]'), 'Search needs a submit button');
    check(search.method.toLowerCase() === 'get', 'Search must use a native GET form');
    const rect = search.getBoundingClientRect();
    check(rect.left >= 0 && rect.right <= innerWidth + 1, 'Search escapes the viewport');
  }
  const open = header?.querySelector('.wp-block-navigation__responsive-container-open');
  if (open && getComputedStyle(open).display !== 'none') {
    open.click();
    await new Promise(resolve => requestAnimationFrame(resolve));
    check(!!header.querySelector('.wp-block-navigation__responsive-container.is-menu-open'), 'Mobile menu did not open');
    header.querySelector('.wp-block-navigation__responsive-container-close')?.click();
  }
  if (failures.length) throw new Error(failures.join('; '));
  return { status: 'passed', width: innerWidth, search: !!search };
})();
