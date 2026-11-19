document.addEventListener('DOMContentLoaded', function () {
    var page = document.querySelector('.mh-order-page');

    if (!page) {
        return;
    }

    var storageKey = 'bestellliste';
    var itemsElement = page.querySelector('#cart-items');
    var totalElement = page.querySelector('#cart-total');
    var messageElement = page.querySelector('#order-template');
    var messageStatusElement = page.querySelector('.mh-order-status');
    var actionStatusElement = page.querySelector('.mh-order-action-status');

    function createStatusSetter(element) {
        var fadeTimer = null;
        var hideTimer = null;

        return function (message, variant, fadeAfter) {
            window.clearTimeout(fadeTimer);
            window.clearTimeout(hideTimer);
            element.classList.remove('is-visible', 'is-fading', 'is-success', 'is-destructive', 'is-error');
            if (variant) {
                element.classList.add(variant);
            }
            element.textContent = message;
            element.classList.add('is-visible');

            if (fadeAfter) {
                fadeTimer = window.setTimeout(function () {
                    element.classList.add('is-fading');
                    hideTimer = window.setTimeout(function () {
                        element.classList.remove('is-visible', 'is-fading');
                        element.textContent = '';
                        fadeTimer = null;
                        hideTimer = null;
                    }, 300);
                }, 3000);
            }
        };
    }

    var setMessageStatus = createStatusSetter(messageStatusElement);
    var setActionStatus = createStatusSetter(actionStatusElement);

    function readList() {
        var stored = window.localStorage.getItem(storageKey);

        if (stored === null) {
            return [];
        }

        var parsed = JSON.parse(stored);

        if (!Array.isArray(parsed)) {
            throw new Error('Die gespeicherte Bestellliste hat ein ungültiges Format.');
        }

        return parsed.map(function (item, index) {
            if (!item || typeof item !== 'object' || Array.isArray(item)) {
                throw new Error('Ein Eintrag der Bestellliste ist ungültig.');
            }

            var name = typeof item.name === 'string' ? item.name.trim() : '';
            var price = Number(item.price);
            var quantity = Number(item.quantity);
            var variant = item.variant == null ? '' : String(item.variant);

            if (!name || !Number.isFinite(price) || price < 0 ||
                !Number.isInteger(quantity) || quantity < 1) {
                throw new Error('Der Eintrag ' + (index + 1) + ' enthält ungültige Bestelldaten.');
            }

            return Object.assign({}, item, {
                name: name,
                variant: variant,
                price: price,
                quantity: quantity
            });
        });
    }

    function formatPrice(value) {
        return Number(value).toLocaleString('de-DE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function addText(parent, tagName, className, text) {
        var element = document.createElement(tagName);
        element.className = className;
        element.textContent = text;
        parent.appendChild(element);
        return element;
    }

    function createQuantityButton(label, symbol, index, modifier) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'cart-qty-btn ' + modifier;
        button.dataset.index = String(index);
        button.setAttribute('aria-label', label);
        button.textContent = symbol;
        return button;
    }

    function updateMessage(list, total) {
        var lines = list.length
            ? list.map(function (item) {
                var name = item.variant
                    ? item.name + ' (' + item.variant + ')'
                    : item.name;
                var lineTotal = formatPrice(item.price * item.quantity);

                return '- ' + name + ' × ' + item.quantity + ' - ' + lineTotal + ' €';
            })
            : ['- (keine Artikel)'];

        messageElement.value = [
            'Hallo Max,',
            '',
            'ich würde gerne folgende Bestellung bei dir aufgeben:',
            '',
            'Posten',
            lines.join('\n'),
            '',
            'Summe: ' + formatPrice(total) + ' €',
            '',
            'Vielen Dank'
        ].join('\n');
    }

    function render() {
        var list;

        try {
            list = readList();
        } catch (error) {
            itemsElement.replaceChildren();
            addText(itemsElement, 'li', 'mh-order-empty', 'Die Bestellliste konnte nicht geladen werden.');
            totalElement.textContent = '—';
            messageElement.value = '';
            setActionStatus(error.message, 'is-error');
            console.error(error);
            return;
        }

        itemsElement.replaceChildren();

        var total = list.reduce(function (sum, item) {
            return sum + item.price * item.quantity;
        }, 0);

        if (list.length === 0) {
            addText(itemsElement, 'li', 'mh-order-empty', 'Deine Bestellliste ist leer.');
        } else {
            list.forEach(function (item, index) {
                var row = document.createElement('li');
                var info = document.createElement('div');
                var controls = document.createElement('div');
                var stepper = document.createElement('span');

                row.className = 'cart-item';
                info.className = 'cart-item-info';
                controls.className = 'cart-item-controls';
                stepper.className = 'cart-item-stepper';
                stepper.setAttribute('aria-label', 'Menge ändern für ' + item.name);

                addText(info, 'span', 'cart-item-name', item.name);
                if (item.variant) {
                    addText(info, 'span', 'cart-item-variant', item.variant);
                }

                addText(
                    controls,
                    'span',
                    'cart-item-price',
                    formatPrice(item.price * item.quantity) + ' €'
                );
                stepper.appendChild(createQuantityButton(
                    'Menge von ' + item.name + ' verringern',
                    '−',
                    index,
                    'cart-qty-btn--minus'
                ));
                addText(stepper, 'span', 'cart-item-quantity', String(item.quantity));
                stepper.appendChild(createQuantityButton(
                    'Menge von ' + item.name + ' erhöhen',
                    '+',
                    index,
                    'cart-qty-btn--plus'
                ));
                controls.appendChild(stepper);
                row.appendChild(info);
                row.appendChild(controls);
                itemsElement.appendChild(row);
            });
        }

        totalElement.textContent = formatPrice(total);
        updateMessage(list, total);
    }

    function updateQuantity(index, amount) {
        try {
            var list = readList();
            var item = list[index];

            if (!item) {
                throw new Error('Der gewählte Bestellposten wurde nicht gefunden.');
            }

            item.quantity += amount;
            if (item.quantity <= 0) {
                list.splice(index, 1);
            }

            window.localStorage.setItem(storageKey, JSON.stringify(list));
            setActionStatus('Bestellliste aktualisiert.', 'is-success', true);
            render();
        } catch (error) {
            setActionStatus(error.message, 'is-error');
            console.error(error);
        }
    }

    function selectMessageForManualCopy() {
        messageElement.focus();
        messageElement.select();
        setMessageStatus('Die Nachricht ist markiert. Kopiere sie mit Strg+C oder Cmd+C.');
    }

    function copyWithExecCommand() {
        if (typeof document.execCommand !== 'function') {
            return false;
        }

        var activeElement = document.activeElement;
        messageElement.focus();
        messageElement.select();
        var copied = document.execCommand('copy');

        if (copied) {
            messageElement.setSelectionRange(0, 0);

            if (activeElement && typeof activeElement.focus === 'function') {
                activeElement.focus();
            }
        }

        return copied;
    }

    function copyMessageToClipboard() {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(messageElement.value).catch(function (error) {
                if (copyWithExecCommand()) {
                    return;
                }

                throw error;
            });
        }

        if (copyWithExecCommand()) {
            return Promise.resolve();
        }

        return Promise.reject(new Error('Die Nachricht konnte nicht in die Zwischenablage kopiert werden.'));
    }

    page.addEventListener('click', function (event) {
        var button = event.target.closest('button');

        if (!button || !page.contains(button)) {
            return;
        }

        if (button.matches('.cart-qty-btn--minus')) {
            updateQuantity(Number(button.dataset.index), -1);
        } else if (button.matches('.cart-qty-btn--plus')) {
            updateQuantity(Number(button.dataset.index), 1);
        } else if (button.id === 'refresh-cart') {
            render();
            setActionStatus('Bestellliste aktualisiert.', 'is-success', true);
        } else if (button.id === 'clear-cart') {
            try {
                window.localStorage.removeItem(storageKey);
                render();
                setActionStatus('Bestellliste geleert.', 'is-destructive', true);
            } catch (error) {
                setActionStatus('Die Bestellliste konnte nicht geleert werden.', 'is-error');
                console.error(error);
            }
        } else if (button.id === 'order-mail') {
            var subject = encodeURIComponent('Bestellung über Morshaus');
            var body = encodeURIComponent(messageElement.value);
            window.location.href = 'mailto:hallo@morshaus.de?subject=' + subject + '&body=' + body;
        } else if (button.matches('[data-copy="order-template"]')) {
            copyMessageToClipboard().then(function () {
                setMessageStatus('Bestellnachricht in Zwischenablage gelegt', 'is-success', true);
            }).catch(function (error) {
                selectMessageForManualCopy();
                console.error(error);
            });
        }
    });

    window.addEventListener('storage', function (event) {
        if (event.key === storageKey || event.key === null) {
            render();
        }
    });

    render();
});
