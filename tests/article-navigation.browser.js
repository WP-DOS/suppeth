// Run on a middle article, then the oldest/newest article, at desktop and mobile widths.
(() => {
  const navigation = document.querySelector('nav.suppeth-post-navigation');
  if (!navigation) throw new Error('Missing article navigation landmark');
  const links = [...navigation.querySelectorAll('a')];
  const failures = [];
  const check = (condition, message) => { if (!condition) failures.push(message); };
  const author = document.querySelector('.suppeth-author');
  check(navigation.getBoundingClientRect().top >= author.getBoundingClientRect().bottom, 'Navigation overlaps the author');
  check(getComputedStyle(navigation).borderTopWidth === '1px', 'Missing navigation divider');
  check(document.documentElement.scrollWidth <= innerWidth + 1, 'Horizontal overflow');
  for (const link of links) {
    check(!!link.querySelector('.post-navigation-link__label'), 'Missing direction label');
    check(!!link.querySelector('.post-navigation-link__title')?.textContent.trim(), 'Missing destination title');
    check(link.getBoundingClientRect().height >= 44, 'Link target is too small');
    check(new URL(link.href).origin === location.origin, 'Destination must be a local article');
    link.focus();
    check(document.activeElement === link, 'Link is not keyboard focusable');
  }
  if (links.length === 2) {
    const previous = links[0].getBoundingClientRect();
    const next = links[1].getBoundingClientRect();
    check(innerWidth <= 600 ? next.top >= previous.bottom : next.left >= previous.right, 'Navigation does not stack/align correctly');
  }
  for (const block of navigation.children) {
    if (!block.querySelector('a')) check(getComputedStyle(block).display === 'none', 'Missing adjacent article leaves an empty block');
  }
  if (!links.length) check(getComputedStyle(navigation).display === 'none', 'Empty navigation should be hidden');
  if (failures.length) throw new Error(failures.join('; '));
  return { status: 'passed', width: innerWidth, destinations: links.map(link => link.textContent.trim()) };
})();
