/**
 * Run once in the local Site Editor to save the original header, then on the frontend.
 * Set suppeth-header-pattern in sessionStorage to a pattern slug or "restore".
 *
 * @package Suppeth
 * @since Suppeth 0.1.6
 */
(async () => {
  if (!['localhost', '127.0.0.1'].includes(location.hostname)) throw new Error('Local preview only');
  let state = JSON.parse(sessionStorage.getItem('suppeth-header-test'));
  if (!state) {
    if (!window.wp?.apiFetch || !window.wpApiSettings?.nonce) throw new Error('Prepare this preview in the Site Editor first');
    const parts = await wp.apiFetch({ path: '/wp/v2/template-parts?context=edit&per_page=100' });
    const patterns = (await wp.apiFetch({ path: '/wp/v2/block-patterns/patterns' })).filter(item => item.name.startsWith('suppeth/header-'));
    state = { original: parts.find(item => item.slug === 'header'), nonce: wpApiSettings.nonce, patterns };
    if (!state.original || patterns.length !== 4) throw new Error('Missing header or patterns');
    sessionStorage.setItem('suppeth-header-test', JSON.stringify(state));
  }
  const selected = sessionStorage.getItem('suppeth-header-pattern') || 'restore';
  let content = state.original.content.raw;
  if (selected !== 'restore') {
    const pattern = state.patterns.find(item => item.name === 'suppeth/' + selected);
    if (!pattern) throw new Error('Unknown header pattern');
    content = pattern.content;
    const ref = state.original.content.raw.match(/"ref":(\d+)/)?.[1];
    if (ref) content = content.replace('<!-- wp:navigation {', '<!-- wp:navigation {"ref":' + ref + ',');
  }
  const response = await fetch('/wp-json/wp/v2/template-parts/' + state.original.id, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': state.nonce },
    body: JSON.stringify({ content }),
  });
  if (!response.ok) throw new Error('Could not update preview header: ' + response.status);
  return { preview: selected };
})();
