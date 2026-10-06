/**
 * Only mounted in the disposable local preview. No network calls or storage.
 *
 * @package Suppeth
 * @since Suppeth 0.1.6
 */
document.querySelectorAll('.suppeth-demo-contact').forEach((form) => {
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        const status = form.querySelector('.suppeth-demo-contact-status');
        form.reset();
        status.textContent = 'Demo complete. Your message was not sent or saved.';
        status.focus();
    });
    form.querySelector('button[type="submit"]').disabled = false;
});
