/**
 * Run on any sidebar variant at desktop and mobile widths in the local preview.
 *
 * @package Suppeth
 * @since Suppeth 0.1.6
 */
(() => {
  const layout = document.querySelector('.suppeth-sidebar-layout');
  if (!layout) throw new Error('Open a sidebar template before running this check.');
  const columns = [...layout.children].filter(node => node.classList.contains('wp-block-column'));
  const aside = columns[1]?.querySelector('aside');
  const content = columns[0];
  const failures = [];
  const check = (condition, message) => { if (!condition) failures.push(message); };
  check(columns.length === 2, 'Expected two columns');
  check(document.querySelectorAll('main').length === 1, 'Expected one main landmark');
  check(!!aside, 'Missing sidebar landmark');
  check(aside?.querySelectorAll('h2').length === 2, 'Missing sidebar section headings');
  check(!!aside?.querySelector('.wp-block-categories'), 'Missing dynamic categories');
  check(!!aside?.querySelector('.wp-block-latest-posts'), 'Missing recent posts');
  check(document.documentElement.scrollWidth <= innerWidth + 1, 'Horizontal viewport overflow');
  if (content && aside) {
    const primary = content.getBoundingClientRect();
    const secondary = aside.getBoundingClientRect();
    if (innerWidth < 782) {
      check(secondary.top >= primary.bottom - 1, 'Sidebar must stack below the content');
    } else {
      check(secondary.left >= primary.right - 1, 'Sidebar overlaps the content');
      check(Math.abs(secondary.top - primary.top) <= 1, 'Columns should start together');
    }
    for (const node of content.querySelectorAll('.alignwide, .alignfull')) {
      const rect = node.getBoundingClientRect();
      check(rect.left >= primary.left - 1 && rect.right <= primary.right + 1, 'Wide/full content escapes the primary column');
    }
  }
  if (failures.length) throw new Error(failures.join('; '));
  return { status: 'passed', width: innerWidth, title: document.title };
})();
