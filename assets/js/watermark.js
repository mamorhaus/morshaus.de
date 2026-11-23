document.addEventListener('DOMContentLoaded', function () {
    const main = document.querySelector('#main');
    const footer = document.querySelector('.mh-site-footer');
    if (!main || !footer || main.classList.contains('mh-product-main')) return;

    const watermark = document.createElement('span');
    watermark.className = 'mh-watermark';
    watermark.setAttribute('aria-hidden', 'true');
    main.classList.add('mh-has-watermark');
    main.prepend(watermark);

    const desktop = window.matchMedia('(min-width: 1024px)');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let frame = 0;

    function placeWatermark() {
        frame = 0;
        if (!desktop.matches) return;

        const top = Math.max(0, main.getBoundingClientRect().top);
        const bottom = Math.min(window.innerHeight, footer.getBoundingClientRect().top);
        const available = bottom - top;
        if (available <= 0) {
            watermark.style.visibility = 'hidden';
            return;
        }

        const height = Math.min(200, available);
        const center = reducedMotion.matches
            ? Math.max(top + height / 2, Math.min(window.innerHeight / 2, bottom - height / 2))
            : (top + bottom) / 2;

        watermark.style.height = height + 'px';
        watermark.style.top = (center - height / 2) + 'px';
        watermark.style.visibility = 'visible';
    }

    function schedulePlacement() {
        if (!frame) frame = window.requestAnimationFrame(placeWatermark);
    }

    window.addEventListener('scroll', schedulePlacement, { passive: true });
    window.addEventListener('resize', schedulePlacement);
    window.addEventListener('load', schedulePlacement);
    reducedMotion.addEventListener('change', schedulePlacement);
    if ('ResizeObserver' in window) {
        const observer = new ResizeObserver(schedulePlacement);
        observer.observe(main);
        observer.observe(footer);
    }
    schedulePlacement();
});
