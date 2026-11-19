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

    document.addEventListener('click', function (event) {
        var link = event.target.closest('.event-add-btn > .event-add-link');

        if (!link) {
            return;
        }

        event.preventDefault();
        var wrapper = link.closest('.event-add-btn');
        var feedback = wrapper && wrapper.nextElementSibling;

        function showFeedback(message, isError) {
            if (!feedback || !feedback.classList.contains('event-add-feedback')) {
                console.error('Die Rückmeldung zum Veranstaltungseintrag fehlt.');
                return;
            }

            feedback.textContent = message;
            feedback.classList.toggle('is-error', Boolean(isError));
            feedback.classList.add('visible');
        }

        if (!wrapper) {
            console.error('Der Veranstaltungs-Button ist keinem Termin zugeordnet.');
            return;
        }

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
            link.dataset.state = 'added';
            link.textContent = 'Zur Bestellliste';
            showFeedback('Zur Bestellliste hinzugefügt', false);
        } catch (error) {
            console.error(error);
            showFeedback('Termin konnte nicht zur Bestellliste hinzugefügt werden.', true);
        }
    });
});
