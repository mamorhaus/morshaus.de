document.addEventListener('DOMContentLoaded', function () {
    var content = document.querySelector('.mh-prose');

    if (!content) {
        return;
    }

    var page = content.closest('.mh-tee-page, .mh-utensilien-page');
    var itemType = page && page.classList.contains('mh-utensilien-page') ? 'Utensilien' : 'Tees';
    var groups = Array.from(content.querySelectorAll(':scope > .wp-block-group'));
    var menus = [];
    var openMenu = null;

    groups.forEach(function (track, index) {
        var heading = track.previousElementSibling;
        var category = heading && heading.tagName === 'H2'
            ? heading.textContent.trim()
            : itemType;
        var carousel = document.createElement('div');
        var button = document.createElement('button');
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

        button.className = 'mh-tee-carousel__button';
        button.type = 'button';
        button.setAttribute('aria-controls', track.id);
        carousel.appendChild(button);

        var menu = {
            carousel: carousel,
            track: track,
            cards: cards,
            button: button,
            category: category
        };
        menus.push(menu);

        button.addEventListener('click', function () {
            openMenu = openMenu === menu ? null : menu;
            updateMenus();
        });
    });

    function updateMenus() {
        if (page) {
            page.classList.toggle('has-expanded-carousel', Boolean(openMenu));
        }

        menus.forEach(function (menu) {
            var expanded = menu === openMenu;
            menu.carousel.classList.toggle('is-expanded', expanded);
        });

        menus.forEach(function (menu) {
            var expanded = menu === openMenu;
            var firstCard = menu.cards[0];
            var gap = parseFloat(window.getComputedStyle(menu.track).columnGap) || 0;
            var cardWidth = firstCard ? firstCard.getBoundingClientRect().width : 0;
            var availableWidth = Math.min(menu.track.clientWidth, 640);
            var visibleCount = cardWidth ? Math.max(1, Math.floor((availableWidth + gap) / (cardWidth + gap))) : 0;
            var canExpand = menu.cards.length > visibleCount;

            menu.button.hidden = !canExpand && !expanded;
            menu.cards.forEach(function (card, index) {
                var hide = !expanded && index >= visibleCount;
                if (hide && card.contains(document.activeElement)) {
                    menu.button.focus();
                }
                card.hidden = hide;
            });

            if (expanded && menu.cards.length) {
                var lastCard = menu.cards[menu.cards.length - 1];
                menu.button.style.setProperty('--mh-chevron-left', (lastCard.offsetLeft + lastCard.offsetWidth) + 'px');
                menu.button.style.setProperty('--mh-chevron-top', (lastCard.offsetTop + lastCard.offsetHeight / 2) + 'px');
            }

            menu.button.textContent = expanded ? '‹' : '›';
            menu.button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            menu.button.setAttribute(
                'aria-label',
                expanded ? itemType + ' in ' + menu.category + ' einklappen' : 'Weitere ' + itemType + ' in ' + menu.category + ' anzeigen'
            );
        });
    }

    window.addEventListener('resize', updateMenus);
    updateMenus();
});
