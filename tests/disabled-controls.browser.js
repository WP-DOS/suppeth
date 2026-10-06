// Run on /elements-proof/ to check the disabled form specimen.
(() => {
    const input = document.querySelector('#proof-disabled-input');
    if (!input) throw new Error('Missing disabled specimen');
    const label = document.querySelector('label[for="proof-disabled-input"]');
    const wrapper = input.closest('.wp-block-post-comments-form');
    const button = wrapper.querySelector('button');
    const failures = [];
    const box = input.getBoundingClientRect();
    const labelBox = label.getBoundingClientRect();
    if (box.top < labelBox.bottom - 1) failures.push('Label is inline beside the input');
    if (Math.abs(box.width - input.parentElement.getBoundingClientRect().width) > 1) failures.push('Input does not fill the available width');
    if (input.scrollWidth > input.clientWidth) failures.push('Disabled value is clipped');
    if (Number(getComputedStyle(button).opacity) > 0.6) failures.push('Disabled button looks enabled');
    if (!input.disabled || !button.disabled) failures.push('Controls are not genuinely disabled');
    if (document.documentElement.scrollWidth > innerWidth) failures.push('Horizontal overflow');
    const report = { viewport: innerWidth, width: box.width, failures };
    if (failures.length) throw new Error(JSON.stringify(report));
    return report;
})()
