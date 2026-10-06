// Run on /services/, /portfolio/, or /contact/ in the local preview.
(async () => {
    const content = document.querySelector('.wp-block-post-content');
    const failures = [];
    if (!content) throw new Error('Missing page content');
    const images = [...content.querySelectorAll('img')];
    images.forEach(image => { image.loading = 'eager'; });
    await Promise.all(images.map(image => image.decode().catch(() => {})));
    if (images.some(image => !image.naturalWidth)) failures.push('Broken showcase image');
    if (location.pathname === '/services/') {
        for (const selector of ['.wp-block-columns', '.wp-block-buttons', '.wp-block-details', '.alignfull.has-background']) {
            if (!content.querySelector(selector)) failures.push('Missing service block: ' + selector);
        }
    }
    if (location.pathname === '/portfolio/') {
        if (!content.querySelector('.wp-block-media-text')) failures.push('Missing Media & Text');
        const previews = [...content.querySelectorAll('.wp-block-columns .wp-block-image img')];
        if (innerWidth > 782 && previews.length === 2 && Math.abs(previews[0].getBoundingClientRect().height - previews[1].getBoundingClientRect().height) > 1) failures.push('Project previews have unequal heights');
    }
    if (location.pathname === '/contact/') {
        const form = content.querySelector('.suppeth-demo-contact');
        if (!form || form.querySelector('button').disabled) failures.push('Demo form unavailable');
        else {
            if (form.checkValidity()) failures.push('Empty form should be invalid');
            form.elements.name.value = 'Demo Reader';
            form.elements.email.value = 'reader@example.test';
            form.elements.message.value = 'A fictional project enquiry.';
            if (!form.checkValidity()) failures.push('Valid demo data rejected');
            form.requestSubmit();
            if (!form.querySelector('[role="status"]').textContent.includes('not sent or saved')) failures.push('Missing safe confirmation');
            if (form.elements.message.value !== '') failures.push('Message was not cleared');
        }
    }
    if (document.documentElement.scrollWidth > innerWidth) failures.push('Horizontal overflow');
    if (['/services/', '/portfolio/'].includes(location.pathname)) {
        for (const block of content.querySelectorAll(':scope > .wp-block-columns, :scope > .wp-block-media-text')) {
            const heading = block.previousElementSibling;
            if (Math.abs(heading.getBoundingClientRect().left - block.getBoundingClientRect().left) > 1) failures.push('Section heading is inset from its wide layout');
            if (parseFloat(getComputedStyle(block).marginBottom) < 40) failures.push('Section break too tight');
        }
    }
    const localLinks = [...new Set([...content.querySelectorAll('a[href]')].map(a => a.href).filter(href => new URL(href).origin === location.origin))];
    const responses = await Promise.all(localLinks.map(async href => ({ href, status: (await fetch(href)).status })));
    if (responses.some(response => response.status !== 200)) failures.push('Broken internal link');
    const report = { page: location.pathname, viewport: innerWidth, loadedImages: images.length, checkedLinks: responses.length, failures };
    if (failures.length) throw new Error(JSON.stringify(report));
    return report;
})()
