// Run on a frontend page after replacing the Footer template part with a pattern.
(async () => {
  await document.fonts.ready;
  const failures = [];
  const check = (condition, message) => { if (!condition) failures.push(message); };
  const footer = document.querySelector('footer.wp-block-template-part');
  check(!!footer, 'Footer landmark missing');
  const pattern = footer?.querySelector('.suppeth-footer');
  check(!!pattern, 'Footer pattern missing');
  check(!!pattern?.querySelector('.wp-block-site-title a'), 'Linked site title missing');
  check(!!pattern?.querySelector('nav[aria-label]'), 'Labelled footer navigation missing');
  check(!footer?.querySelector('.wp-block-navigation__responsive-container-open'), 'Footer uses an overlay menu');
  check(document.documentElement.scrollWidth <= innerWidth + 1, 'Page overflows horizontally');
  for (const element of pattern?.querySelectorAll('a, input, button') || []) {
    const bounds = element.getBoundingClientRect();
    if (!bounds.width || !bounds.height) continue;
    check(bounds.left >= -1 && bounds.right <= innerWidth + 1, 'Footer control escapes viewport');
  }
  if (pattern?.classList.contains('suppeth-footer-columns') && innerWidth <= 781) {
    const columns = [...pattern.querySelectorAll('.wp-block-column')];
    for (let i = 1; i < columns.length; i++) {
      check(columns[i].getBoundingClientRect().top >= columns[i - 1].getBoundingClientRect().bottom - 1,
        'Footer columns do not stack on mobile');
    }
  }
  const search = pattern?.querySelector('form.wp-block-search');
  if (search) {
    const input = search.querySelector('input[type="search"]');
    check(!!input && input.name === 's', 'Native search input missing');
    check(!!search.querySelector(`label[for="${input?.id}"]`)?.textContent.trim(), 'Search label missing');
    check(!!search.querySelector('button[type="submit"]'), 'Search submit button missing');
    check((search.method || '').toLowerCase() === 'get', 'Search does not use GET');
  }
  if (failures.length) throw new Error(failures.join('; '));
  return { status: 'passed', width: innerWidth, search: !!search };
})();
