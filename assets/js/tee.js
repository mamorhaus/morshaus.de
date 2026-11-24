document.addEventListener('DOMContentLoaded', function () {
    var content = document.querySelector('.mh-prose');

    if (!content) {
        return;
    }

    var page = content.closest('.mh-tee-page, .mh-utensilien-page');
    var itemType = page && page.classList.contains('mh-utensilien-page') ? 'Utensilien' : 'Tees';
    var groups = Array.from(content.querySelectorAll(':scope > .wp-block-group'));
    var menus = [];

    groups.forEach(function (track, index) {
        var heading = track.previousElementSibling;
        var category = heading && heading.tagName === 'H2'
            ? heading.textContent.trim()
            : itemType;
        var carousel = document.createElement('div');
        var controls = document.createElement('div');
        var arrows = document.createElement('div');
        var previous = document.createElement('button');
        var next = document.createElement('button');
        var dots = document.createElement('span');
        var position = document.createElement('span');
        var cards = Array.from(track.querySelectorAll(':scope > .wp-block-cover'));

        if (page && page.classList.contains('mh-tee-page')) {
            cards.forEach(function (card) {
                var link = card.querySelector('.wp-block-button__link');
                if (link && link.textContent.trim() === 'Sencha') {
                    card.classList.add('mh-tee-card--sencha');
                }
            });
        }

        carousel.className = 'mh-tee-carousel';
        track.parentNode.insertBefore(carousel, track);
        carousel.appendChild(track);
        track.classList.add('mh-tee-track');
        track.id = 'mh-tee-track-' + (index + 1);
        track.setAttribute('role', 'region');
        track.setAttribute('aria-label', itemType + ': ' + category);

        track.querySelectorAll('.wp-block-cover.unverfugbar').forEach(function (card) {
            var status = document.createElement('span');

            status.className = 'mh-tee-unavailable';
            status.textContent = 'Unverfügbar';
            card.appendChild(status);
        });

        controls.className = 'mh-tee-carousel__controls';
        arrows.className = 'mh-tee-carousel__arrows';
        dots.className = 'mh-tee-carousel__dots';
        dots.setAttribute('aria-hidden', 'true');
        position.className = 'screen-reader-text';
        position.setAttribute('aria-live', 'polite');
        [previous, next].forEach(function (button) {
            button.className = 'mh-tee-carousel__button';
            button.type = 'button';
            button.setAttribute('aria-controls', track.id);
        });
        previous.textContent = '‹';
        next.textContent = '›';
        previous.setAttribute('aria-label', 'Vorherige ' + itemType + ' in ' + category + ' anzeigen');
        next.setAttribute('aria-label', 'Weitere ' + itemType + ' in ' + category + ' anzeigen');
        arrows.append(previous, next);
        controls.append(arrows, dots, position);
        carousel.appendChild(controls);

        var menu = {
            track: track,
            cards: cards,
            controls: controls,
            previous: previous,
            next: next,
            dots: dots,
            position: position
        };
        menus.push(menu);

        previous.addEventListener('click', function () {
            move(menu, -1);
        });
        next.addEventListener('click', function () {
            move(menu, 1);
        });
        track.addEventListener('scroll', function () {
            updateMenu(menu);
        });
    });

    function move(menu, direction) {
        var button = direction < 0 ? menu.previous : menu.next;
        if (button.getAttribute('aria-disabled') === 'true') return;
        var firstCard = menu.cards[0];
        if (!firstCard) return;
        var gap = parseFloat(window.getComputedStyle(menu.track).columnGap) || 0;
        var step = firstCard.getBoundingClientRect().width + gap;
        var index = Math.round(menu.track.scrollLeft / step);
        menu.track.scrollLeft = (index + direction) * step;
        updateMenu(menu);
    }

    function updateMenu(menu) {
        var mobile = window.matchMedia('(max-width: 782px)').matches;
        if (mobile) {
            var moveFocus = menu.controls.contains(document.activeElement);
            menu.controls.hidden = true;
            if (menu.track.scrollWidth > menu.track.clientWidth || moveFocus) {
                menu.track.tabIndex = 0;
            } else {
                menu.track.removeAttribute('tabindex');
            }
            if (moveFocus) menu.track.focus();
            return;
        }

        menu.track.removeAttribute('tabindex');
        var firstCard = menu.cards[0];
        var gap = parseFloat(window.getComputedStyle(menu.track).columnGap) || 0;
        var width = firstCard ? firstCard.getBoundingClientRect().width : 0;
        var visible = width ? Math.max(1, Math.floor((menu.track.clientWidth + gap + 1) / (width + gap))) : 0;
        var first = width ? Math.round(menu.track.scrollLeft / (width + gap)) : 0;
        var lastStart = Math.max(0, menu.cards.length - visible);
        first = Math.min(first, lastStart);
        menu.controls.hidden = menu.cards.length <= visible;
        menu.previous.setAttribute('aria-disabled', first === 0 ? 'true' : 'false');
        menu.next.setAttribute('aria-disabled', first >= lastStart ? 'true' : 'false');
        while (menu.dots.children.length > lastStart + 1) menu.dots.lastElementChild.remove();
        while (menu.dots.children.length < lastStart + 1) {
            var dot = document.createElement('span');
            dot.className = 'mh-tee-carousel__dot';
            menu.dots.appendChild(dot);
        }
        Array.from(menu.dots.children).forEach(function (dot, index) {
            dot.classList.toggle('is-active', index === first);
        });
        var text = (first + 1) + '–' + Math.min(first + visible, menu.cards.length) + ' von ' + menu.cards.length;
        if (menu.position.textContent !== text) menu.position.textContent = text;
    }

    window.addEventListener('resize', function () {
        menus.forEach(updateMenu);
    });
    menus.forEach(updateMenu);
});
