document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.morshaus-veranstaltung__beschreibung-toggle').forEach(function (toggle) {
        var card = toggle.closest('.morshaus-veranstaltung');
        var content = card && card.querySelector('.morshaus-veranstaltung__beschreibung-content');

        if (!content || !toggle) {
            return;
        }

        toggle.addEventListener('click', function () {
            var expanded = toggle.getAttribute('aria-expanded') === 'true';
            var nextExpanded = !expanded;
            var label = toggle.querySelector('.morshaus-veranstaltung__beschreibung-label');

            toggle.setAttribute('aria-expanded', nextExpanded ? 'true' : 'false');
            toggle.setAttribute(
                'aria-label',
                nextExpanded ? 'Beschreibung ausblenden' : 'Beschreibung anzeigen'
            );
            if (label) {
                label.textContent = nextExpanded ? 'Beschreibung ausblenden' : 'Beschreibung anzeigen';
            }
            content.hidden = !nextExpanded;
        });
    });

    var eventsSection = document.querySelector('[data-mh-begegnung-events], [data-mh-seminare-events]');

    if (eventsSection) {
        var shell = eventsSection.querySelector('[data-morshaus-event-filter]');
        var cards = Array.from(document.querySelectorAll('.mh-begegnung-card'));
        var mainFilter = eventsSection.hasAttribute('data-mh-seminare-events') ? 'seminar' : 'begegnung';

        function getCardFilter(card) {
            if (card.dataset.filterSub) {
                return card.dataset.filterSub;
            }
            var title = card.querySelector('h2');

            if (!title) {
                return '';
            }

            var value = title.textContent.trim().toLowerCase();

            if (value.includes('bewegung')) {
                return 'in-bewegung';
            }

            if (value.includes('sitzen')) {
                return 'im-sitzen';
            }

            return '';
        }

        function syncCards() {
            var main = shell.querySelector('[data-filter-main="' + mainFilter + '"]');
            var selected = shell.querySelector('[data-filter-group="' + mainFilter + '"] [data-filter-sub][aria-pressed="true"]');
            cards.forEach(function (card) {
                var active = main.getAttribute('aria-pressed') === 'true' &&
                    selected && getCardFilter(card) === selected.dataset.filterSub;
                var select = card.querySelector('[data-mh-card-select]') || card;
                var selectedTopic = shell.querySelector('[data-filter-topic-group="' + getCardFilter(card) + '"] [data-filter-topic][aria-pressed="true"]');
                card.classList.toggle('is-filter-active', Boolean(active));
                select.setAttribute('aria-pressed', active ? 'true' : 'false');
                card.querySelectorAll('[data-mh-card-topic]').forEach(function (button) {
                    var topicActive = active && selectedTopic && button.dataset.mhCardTopic === selectedTopic.dataset.filterTopic;
                    button.setAttribute('aria-pressed', topicActive ? 'true' : 'false');
                });
            });
        }

        if (shell) {
            shell.addEventListener('click', syncCards);
            cards.forEach(function (card) {
                var filter = getCardFilter(card);

                if (!filter) {
                    return;
                }

                var select = card.querySelector('[data-mh-card-select]') || card;
                if (select === card) {
                    card.setAttribute('role', 'button');
                    card.setAttribute('tabindex', '0');
                    card.setAttribute(
                        'aria-label',
                        'Termine für ' + card.querySelector('h2').textContent.trim() + ' anzeigen'
                    );
                    card.setAttribute('aria-pressed', 'false');
                }

                function activateFilter(topic) {
                    shell.querySelector('[data-filter-main="' + mainFilter + '"]').click();
                    shell.querySelector('[data-filter-group="' + mainFilter + '"] [data-filter-sub="' + filter + '"]').click();
                    if (topic) {
                        var topicButton = shell.querySelector('[data-filter-topic-group="' + filter + '"] [data-filter-topic="' + topic + '"]');
                        if (!topicButton) {
                            console.error('Der Terminfilter für das Seminarthema fehlt: ' + topic);
                            return;
                        }
                        topicButton.click();
                    }
                    eventsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }

                select.addEventListener('click', function () {
                    activateFilter();
                });
                if (select === card) {
                    card.addEventListener('keydown', function (event) {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            activateFilter();
                        }
                    });
                }
                card.querySelectorAll('[data-mh-card-topic]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        activateFilter(button.dataset.mhCardTopic);
                    });
                });
            });
            syncCards();
        }
    }

    var seminarCarousel = document.querySelector('.mh-seminare-page .mh-begegnung-cards, body.page-id-284 .mh-begegnung-cards');
    var seminarCards = seminarCarousel ? Array.from(seminarCarousel.querySelectorAll(':scope > .mh-begegnung-card')) : [];
    if (seminarCards.length >= 2) {
        var isBegegnungCarousel = document.body.classList.contains('page-id-284');
        var carouselLabel = isBegegnungCarousel ? 'Begegnungen' : 'Seminarwelten';
        var itemLabel = isBegegnungCarousel ? 'Begegnung' : 'Seminarwelt';
        var mobileCarousel = window.matchMedia('(max-width: 782px)');
        var currentSeminar = 0;
        var seminarTouch = null;
        var mirroredCard = null;
        var fadingCard = null;
        var previousSeminar = document.createElement('button');
        var nextSeminar = document.createElement('button');
        var seminarDots = document.createElement('div');

        previousSeminar.type = nextSeminar.type = 'button';
        previousSeminar.className = 'mh-seminar-carousel__side mh-seminar-carousel__side--previous';
        nextSeminar.className = 'mh-seminar-carousel__side mh-seminar-carousel__side--next';
        seminarDots.className = 'mh-seminar-carousel__dots';
        seminarDots.setAttribute('role', 'group');
        seminarDots.setAttribute('aria-label', itemLabel + ' auswählen');
        seminarCarousel.append(previousSeminar, nextSeminar);
        seminarCarousel.insertAdjacentElement('afterend', seminarDots);

        var seminarDotsButtons = seminarCards.map(function (card, index) {
            var dot = document.createElement('button');
            var title = card.querySelector('h2').textContent.trim();
            if (!card.id) card.id = 'mh-' + (isBegegnungCarousel ? 'begegnung' : 'seminar') + '-slide-' + (index + 1);
            dot.type = 'button';
            dot.setAttribute('aria-label', (index + 1) + ' von ' + seminarCards.length + ': ' + title);
            dot.setAttribute('aria-controls', card.id);
            dot.addEventListener('click', function () { showSeminar(index); });
            seminarDots.appendChild(dot);
            return dot;
        });

        function cloneCarouselCard(card) {
            var clone = card.cloneNode(true);
            clone.removeAttribute('id');
            clone.querySelectorAll('[id]').forEach(function (element) { element.removeAttribute('id'); });
            clone.setAttribute('aria-hidden', 'true');
            clone.inert = true;
            return clone;
        }

        function showSeminar(index) {
            var focusedCard = seminarCards.find(function (card) { return card.contains(document.activeElement); });
            var previousOnOriginal = seminarCards.length === 2 && index < currentSeminar;
            var nextIndex = (index + seminarCards.length) % seminarCards.length;
            if (fadingCard) {
                fadingCard.remove();
                fadingCard = null;
            }
            if (nextIndex !== currentSeminar && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                fadingCard = cloneCarouselCard(seminarCards[currentSeminar]);
                fadingCard.classList.remove('is-current');
                fadingCard.classList.add('mh-carousel-fade');
                fadingCard.addEventListener('animationend', function () {
                    if (fadingCard === this) fadingCard = null;
                    this.remove();
                });
                seminarCarousel.appendChild(fadingCard);
            }
            currentSeminar = nextIndex;
            var previous = (currentSeminar - 1 + seminarCards.length) % seminarCards.length;
            var next = (currentSeminar + 1) % seminarCards.length;

            seminarCards.forEach(function (card, cardIndex) {
                var active = cardIndex === currentSeminar;
                card.classList.toggle('is-current', active);
                card.classList.toggle('is-previous', cardIndex === previous && (seminarCards.length > 2 || previousOnOriginal));
                card.classList.toggle('is-next', cardIndex === next && (seminarCards.length > 2 || !previousOnOriginal));
                card.classList.toggle('is-outside', !active && cardIndex !== previous && cardIndex !== next);
                card.inert = !active;
                if (active) card.removeAttribute('aria-hidden');
                else card.setAttribute('aria-hidden', 'true');
                seminarDotsButtons[cardIndex].setAttribute('aria-pressed', active ? 'true' : 'false');
            });

            if (mirroredCard) mirroredCard.remove();
            if (seminarCards.length === 2) {
                mirroredCard = cloneCarouselCard(seminarCards[next]);
                mirroredCard.classList.remove('is-previous', 'is-next');
                mirroredCard.classList.add(previousOnOriginal ? 'is-next' : 'is-previous');
                seminarCarousel.appendChild(mirroredCard);
            }

            previousSeminar.setAttribute('aria-label', 'Vorherige ' + itemLabel + ': ' + seminarCards[previous].querySelector('h2').textContent.trim());
            previousSeminar.setAttribute('aria-controls', seminarCards[previous].id);
            nextSeminar.setAttribute('aria-label', 'Nächste ' + itemLabel + ': ' + seminarCards[next].querySelector('h2').textContent.trim());
            nextSeminar.setAttribute('aria-controls', seminarCards[next].id);
            if (focusedCard && focusedCard !== seminarCards[currentSeminar]) {
                (seminarCards[currentSeminar].querySelector('[data-mh-card-select]') || seminarCards[currentSeminar]).focus({preventScroll: true});
            }
        }

        function updateSeminarCarousel() {
            var enabled = mobileCarousel.matches;
            var focusCarouselControl = !enabled && (
                document.activeElement === seminarCarousel ||
                document.activeElement === previousSeminar ||
                document.activeElement === nextSeminar ||
                seminarDots.contains(document.activeElement)
            );
            seminarCarousel.classList.toggle('is-carousel', enabled);
            previousSeminar.hidden = nextSeminar.hidden = seminarDots.hidden = !enabled;
            if (enabled) {
                seminarCarousel.setAttribute('role', 'region');
                seminarCarousel.setAttribute('aria-label', carouselLabel);
                seminarCarousel.setAttribute('aria-roledescription', 'Karussell');
                seminarCarousel.tabIndex = 0;
                showSeminar(currentSeminar);
            } else {
                if (fadingCard) {
                    fadingCard.remove();
                    fadingCard = null;
                }
                if (mirroredCard) {
                    mirroredCard.remove();
                    mirroredCard = null;
                }
                seminarCarousel.removeAttribute('role');
                seminarCarousel.removeAttribute('aria-label');
                seminarCarousel.removeAttribute('aria-roledescription');
                seminarCarousel.removeAttribute('tabindex');
                seminarCards.forEach(function (card) {
                    card.classList.remove('is-current', 'is-previous', 'is-next', 'is-outside');
                    card.inert = false;
                    card.removeAttribute('aria-hidden');
                });
                seminarTouch = null;
                if (focusCarouselControl) {
                    (seminarCards[currentSeminar].querySelector('[data-mh-card-select]') || seminarCards[currentSeminar]).focus({preventScroll: true});
                }
            }
        }

        previousSeminar.addEventListener('click', function () { showSeminar(currentSeminar - 1); });
        nextSeminar.addEventListener('click', function () { showSeminar(currentSeminar + 1); });
        seminarCarousel.addEventListener('keydown', function (event) {
            if (!mobileCarousel.matches || (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight')) return;
            event.preventDefault();
            showSeminar(currentSeminar + (event.key === 'ArrowRight' ? 1 : -1));
        });
        seminarCarousel.addEventListener('touchstart', function (event) {
            if (mobileCarousel.matches && event.touches.length === 1) {
                seminarTouch = {x: event.touches[0].clientX, y: event.touches[0].clientY};
            }
        }, {passive: true});
        seminarCarousel.addEventListener('touchend', function (event) {
            if (!seminarTouch || !event.changedTouches.length) return;
            var dx = event.changedTouches[0].clientX - seminarTouch.x;
            var dy = event.changedTouches[0].clientY - seminarTouch.y;
            seminarTouch = null;
            if (Math.abs(dx) < 35 || Math.abs(dx) <= Math.abs(dy)) return;
            if (event.cancelable) event.preventDefault();
            showSeminar(currentSeminar + (dx < 0 ? 1 : -1));
        }, {passive: false});
        seminarCarousel.addEventListener('touchcancel', function () { seminarTouch = null; });
        mobileCarousel.addEventListener('change', updateSeminarCarousel);
        updateSeminarCarousel();
    }

    var feedbackTimers = new WeakMap();
    function showEventFeedback(feedback, message, isError) {
        if (!feedback) {
            console.error('Die Rückmeldung zum Veranstaltungseintrag fehlt.');
            return;
        }

        var timers = feedbackTimers.get(feedback) || {};
        window.cancelAnimationFrame(timers.frame);
        window.clearTimeout(timers.fade);
        window.clearTimeout(timers.hide);
        feedback.textContent = message;
        feedback.classList.toggle('is-error', Boolean(isError));
        timers.frame = window.requestAnimationFrame(function () {
            feedback.classList.add('visible');
        });
        if (!isError) {
            timers.fade = window.setTimeout(function () {
                feedback.classList.remove('visible');
                timers.hide = window.setTimeout(function () {
                    feedback.textContent = '';
                }, 300);
            }, 3000);
        }
        feedbackTimers.set(feedback, timers);
    }

    document.addEventListener('click', function (event) {
        var link = event.target.closest('.event-add-btn .event-add-link');

        if (!link) {
            return;
        }

        event.preventDefault();
        var wrapper = link.closest('.event-add-btn');

        if (!wrapper) {
            console.error('Der Veranstaltungs-Button ist keinem Termin zugeordnet.');
            return;
        }
        var feedback = wrapper.querySelector('.event-add-feedback');

        if (link.dataset.state === 'added') {
            window.location.assign(new URL('/bestellliste/', window.location.origin));
            return;
        }

        try {
            var stored = window.localStorage.getItem('bestellliste');
            var list = stored === null ? [] : JSON.parse(stored);

            if (!Array.isArray(list)) {
                throw new Error('Die gespeicherte Bestellliste hat ein ungültiges Format.');
            }

            list.forEach(function (item, index) {
                if (
                    !item ||
                    typeof item !== 'object' ||
                    Array.isArray(item) ||
                    typeof item.name !== 'string' ||
                    !Number.isFinite(Number(item.price)) ||
                    Number(item.price) < 0 ||
                    !Number.isInteger(Number(item.quantity)) ||
                    Number(item.quantity) < 1
                ) {
                    throw new Error('Ein Eintrag der Bestellliste ist ungültig (' + (index + 1) + ').');
                }
            });

            var art = (wrapper.dataset.art || '').trim();
            var date = (wrapper.dataset.date || '').trim();
            var location = (wrapper.dataset.location || '').trim();
            var name = (wrapper.dataset.name || 'Veranstaltung').trim();
            var price = Number(wrapper.dataset.price || 0);

            if (!Number.isFinite(price) || price < 0) {
                throw new Error('Der Preis des Veranstaltungstermins ist ungültig.');
            }

            var title = [art, date, location].filter(Boolean).join(' | ') || name;
            var existing = list.find(function (item) {
                return item.type === 'event' && item.name === title;
            });

            if (existing) {
                existing.quantity = Number(existing.quantity) + 1;
            } else {
                list.push({
                    type: 'event',
                    name: title,
                    variant: '',
                    price: price,
                    quantity: 1
                });
            }

            window.localStorage.setItem('bestellliste', JSON.stringify(list));
            window.dispatchEvent(new Event('mh:order-list-changed'));
            link.dataset.state = 'added';
            link.textContent = 'Zur Bestellliste';
            showEventFeedback(feedback, 'Zur Bestellliste hinzugefügt.', false);
        } catch (error) {
            console.error(error);
            showEventFeedback(feedback, 'Termin konnte nicht zur Bestellliste hinzugefügt werden.', true);
        }
    });
});
