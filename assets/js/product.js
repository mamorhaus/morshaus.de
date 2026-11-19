document.addEventListener('DOMContentLoaded', function () {
    var product = document.querySelector('[data-mh-product]');
    if (!product) return;

    var feedback = product.querySelector('.mh-product__feedback');
    var feedbackFadeTimer = null;
    var feedbackHideTimer = null;
    function showFeedback(message, error) {
        window.clearTimeout(feedbackFadeTimer);
        window.clearTimeout(feedbackHideTimer);
        feedback.textContent = message;
        feedback.classList.toggle('is-error', Boolean(error));
        feedback.classList.remove('is-fading');
        if (message && !error) {
            feedbackFadeTimer = window.setTimeout(function () {
                feedback.classList.add('is-fading');
                feedbackHideTimer = window.setTimeout(function () {
                    feedback.textContent = '';
                    feedback.classList.remove('is-fading');
                }, 300);
            }, 3000);
        }
    }

    try {
        var data = JSON.parse(product.querySelector('.mh-product__data').textContent);
        if (!Array.isArray(data.editions) || !data.editions.length) {
            throw new Error('Für dieses Produkt sind keine Ausführungen hinterlegt.');
        }

        var isTea = data.category === 'tee';
        var hasVariants = data.editions.some(function (item) { return item.models.length > 1; });
        var editionIndex = data.initialEdition ? data.editions.findIndex(function (item) { return item.name === data.initialEdition; }) : 0;
        if (editionIndex < 0) editionIndex = 0;
        var modelIndex = 0;
        var quantityIndex = 0;
        var count = 1;
        var added = false;
        var imageIndex = 0;
        var selectedExtraOrder = [];
        var editionContainer = product.querySelector('.mh-product__editions');
        var modelsContainer = product.querySelector('.mh-product__models');
        var modelSelection = product.querySelector('.mh-product__model-selection');
        var modelCarousel = product.querySelector('.mh-product__models-carousel');
        var modelAnimationFrame = null;
        var modelAnimationTimer = null;
        var modelTouchStart = null;
        var stage = product.querySelector('.mh-product__stage');
        var image = product.querySelector('.mh-product__image');
        var galleryThumbs = product.querySelector('.mh-product__gallery-thumbs');
        var select = product.querySelector('#mh-product-quantity');
        var extras = product.querySelector('.mh-product__extras');
        var extrasList = extras.querySelector('div');
        var addButton = product.querySelector('.mh-product__add');
        var dialog = product.querySelector('.mh-product__dialog');
        var dialogViewport = dialog.querySelector('.mh-product__dialog-viewport');
        var dialogLens = dialog.querySelector('.mh-product__dialog-lens');

        function edition() { return data.editions[editionIndex]; }
        function model() { return edition().models[modelIndex]; }
        function quantity() { return model().quantities[quantityIndex]; }
        function money(value) {
            return value.toLocaleString('de-DE', {style: 'currency', currency: 'EUR'});
        }
        function resetAdded() {
            added = false;
            addButton.textContent = 'Zur Bestellliste hinzufügen';
            showFeedback('', false);
        }
        function setTab(name, focus) {
            product.querySelectorAll('[role="tab"]').forEach(function (tab) {
                var active = tab.id === 'mh-tab-' + name;
                tab.setAttribute('aria-selected', active ? 'true' : 'false');
                tab.tabIndex = active ? 0 : -1;
                product.querySelector('#' + tab.getAttribute('aria-controls')).hidden = !active;
                if (active && focus) tab.focus();
            });
        }
        function renderFeatures(features) {
            var panel = product.querySelector('#mh-panel-character');
            panel.replaceChildren();
            var list = document.createElement('dl');
            list.className = 'mh-product__facts';
            features.forEach(function (feature, index) {
                if (!feature.label || !feature.wert) return;
                var item = document.createElement('div');
                var label = document.createElement('dt');
                var value = document.createElement('dd');
                item.className = 'mh-product__fact';
                var icon = document.createElement('span');
                icon.className = 'mh-product__fact-icon';
                icon.setAttribute('aria-hidden', 'true');
                icon.innerHTML = feature.iconSvg;
                label.textContent = feature.label;
                value.textContent = feature.wert;
                if (feature.tooltip_aktiv && feature.tooltip_aktiv !== '0' && feature.tooltip_text) {
                    var help = document.createElement('button');
                    var detail = document.createElement('span');
                    help.type = 'button';
                    help.className = 'mh-product__fact-help';
                    help.setAttribute('aria-label', 'Hinweis zu ' + feature.label);
                    help.setAttribute('aria-describedby', 'mh-product-fact-tooltip-' + index);
                    help.textContent = 'i';
                    detail.id = 'mh-product-fact-tooltip-' + index;
                    detail.className = 'mh-product__fact-tooltip';
                    detail.setAttribute('role', 'tooltip');
                    detail.textContent = feature.tooltip_text;
                    help.appendChild(detail);
                    help.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape') help.classList.add('is-dismissed');
                    });
                    help.addEventListener('blur', function () {
                        if (!help.matches(':hover')) help.classList.remove('is-dismissed');
                    });
                    help.addEventListener('mouseleave', function () {
                        if (document.activeElement !== help) help.classList.remove('is-dismissed');
                    });
                    label.appendChild(help);
                }
                item.append(icon, label, value);
                list.appendChild(item);
            });
            if (list.children.length) panel.appendChild(list);
            else panel.textContent = isTea ? 'Zu diesem Tee sind noch keine Merkmale hinterlegt.' : 'Zu diesem Produkt sind noch keine Merkmale hinterlegt.';
        }
        function renderEditionButtons() {
            editionContainer.replaceChildren();
            editionContainer.parentElement.hidden = data.editions.length < 2;
            if (data.editions.length < 2) return;
            data.editions.forEach(function (item, index) {
                var button = document.createElement('button');
                button.type = 'button';
                button.className = 'mh-product__edition';
                button.textContent = item.name;
                button.setAttribute('aria-pressed', index === editionIndex ? 'true' : 'false');
                button.addEventListener('click', function () {
                    editionIndex = index;
                    modelIndex = 0;
                    count = 1;
                    render();
                    editionContainer.querySelector('[aria-pressed="true"]').focus();
                });
                editionContainer.appendChild(button);
            });
        }
        function selectModel(index, step) {
            if (index === modelIndex) return;
            var previousIndex = modelIndex;
            modelIndex = index;
            count = 1;
            render({center: previousIndex, step: step});
            var selected = modelsContainer.querySelector('.mh-product__model[aria-pressed="true"]:not([aria-hidden])');
            if (selected) selected.focus({preventScroll: true});
        }
        function renderModelButtons(movement) {
            window.cancelAnimationFrame(modelAnimationFrame);
            window.clearTimeout(modelAnimationTimer);
            modelsContainer.replaceChildren();
            modelSelection.hidden = !data.showThumbnails && edition().models.length < 2;
            var items = edition().models;
            var carousel = !modelSelection.hidden && items.length >= 5;
            modelCarousel.classList.toggle('is-carousel', carousel);
            if (modelSelection.hidden) return;

            var animate = carousel && movement && Math.abs(movement.step) <= 2 &&
                !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var center = animate ? movement.center : modelIndex;
            var step = animate ? movement.step : 0;
            var track = document.createElement('div');
            track.className = 'mh-product__models-track';
            track.classList.toggle('has-four', items.length === 4);
            track.style.setProperty('--mh-model-count', items.length);
            modelsContainer.appendChild(track);

            function appendModel(index, position, duplicate) {
                var item = items[index];
                var button = document.createElement('button');
                var label = document.createElement('span');
                button.type = 'button';
                button.className = 'mh-product__model';
                if (!data.showThumbnails) button.classList.add('mh-product__model--text');
                button.setAttribute('aria-pressed', index === modelIndex ? 'true' : 'false');
                if (duplicate) {
                    button.setAttribute('aria-hidden', 'true');
                    button.tabIndex = -1;
                }
                var distance = carousel ? Math.abs(position - step) : 0;
                var scale = carousel ? (distance < 1 ? 1 : distance < 2 ? 0.84 : 0.68) : (index === modelIndex ? 1 : 0.84);
                button.style.setProperty('--mh-model-scale', scale);
                if (data.showThumbnails && item.images.length) {
                    var thumb = document.createElement('img');
                    thumb.src = item.images[0].thumb;
                    thumb.alt = '';
                    button.appendChild(thumb);
                } else if (data.showThumbnails) {
                    var placeholder = document.createElement('span');
                    placeholder.className = 'mh-product__model-placeholder';
                    button.appendChild(placeholder);
                }
                label.textContent = item.name + (item.unavailable ? ' (unverfügbar)' : '');
                button.appendChild(label);
                button.addEventListener('click', function () {
                    var offset = index - modelIndex;
                    if (carousel) {
                        if (offset > items.length / 2) offset -= items.length;
                        if (offset < -items.length / 2) offset += items.length;
                    }
                    selectModel(index, offset);
                });
                track.appendChild(button);
            }
            if (carousel) {
                for (var position = -4; position <= 4; position++) {
                    appendModel((center + position + items.length) % items.length, position, Math.abs(position) > 2);
                }
            } else {
                items.forEach(function (item, index) { appendModel(index, index, false); });
            }
            if (animate) {
                modelAnimationFrame = window.requestAnimationFrame(function () {
                    track.classList.add('is-moving');
                    track.style.setProperty('--mh-model-shift', step);
                    modelAnimationTimer = window.setTimeout(function () {
                        var restoreFocus = modelsContainer.contains(document.activeElement);
                        renderModelButtons();
                        if (restoreFocus) modelsContainer.querySelector('.mh-product__model[aria-pressed="true"]:not([aria-hidden])').focus({preventScroll: true});
                    }, 320);
                });
            }
        }
        function renderExtras(selected) {
            extrasList.replaceChildren();
            var current = quantity();
            var available = model().extras.filter(function (extra) {
                return extra.type !== 'dose' || !current || parseFloat(current.name) !== 10;
            });
            extras.hidden = available.length === 0;
            available.forEach(function (extra) {
                var label = document.createElement('label');
                var input = document.createElement('input');
                var name = document.createElement('span');
                var price = document.createElement('span');
                label.className = 'mh-product__extra';
                input.type = 'checkbox';
                input.value = extra.type;
                input.checked = selected && selected.indexOf(extra.type) !== -1;
                input.dataset.price = String(extra.price);
                var names = {dose: 'Dose', klammer: 'Klammer', teeblatt: 'Teeblatt', morshaus: 'Morshaus'};
                name.textContent = names[extra.type];
                price.className = 'mh-product__extra-price';
                price.textContent = money(extra.price);
                input.addEventListener('change', function () {
                    if (isTea && input.checked) {
                        extrasList.querySelectorAll('input').forEach(function (other) {
                            if (other !== input) other.checked = false;
                        });
                    }
                    selectedExtraOrder = selectedExtraOrder.filter(function (type) { return type !== extra.type; });
                    if (input.checked) selectedExtraOrder.push(extra.type);
                    resetAdded();
                    updatePurchase();
                    renderGallery();
                });
                label.appendChild(input);
                label.append(name, price);
                extrasList.appendChild(label);
            });
        }
        function galleryImages() {
            var checked = Array.from(extrasList.querySelectorAll('input:checked')).map(function (input) { return input.value; });
            var selectedImage = null;
            selectedExtraOrder.slice().reverse().some(function (type) {
                var extra = model().extras.find(function (item) { return item.type === type; });
                if (checked.indexOf(type) !== -1 && extra && extra.image) {
                    selectedImage = extra.image;
                    return true;
                }
                return false;
            });
            if (!selectedImage) return model().images;
            return [selectedImage].concat(model().images.filter(function (item) { return item.full !== selectedImage.full; }));
        }
        function renderGallery() {
            var images = galleryImages();
            imageIndex = 0;
            stage.hidden = !images.length;
            product.querySelector('.mh-product__no-image').hidden = Boolean(images.length);
            galleryThumbs.replaceChildren();
            galleryThumbs.hidden = model().images.length < 2 || hasVariants || !modelSelection.hidden;
            if (!galleryThumbs.hidden) {
                model().images.forEach(function (photo, index) {
                    var button = document.createElement('button');
                    var thumbnail = document.createElement('img');
                    button.type = 'button';
                    button.className = 'mh-product__gallery-thumb';
                    button.setAttribute('aria-label', 'Produktbild ' + (index + 1) + ' von ' + model().images.length + ' anzeigen');
                    thumbnail.src = photo.thumb;
                    thumbnail.alt = '';
                    button.appendChild(thumbnail);
                    button.addEventListener('click', function () {
                        imageIndex = galleryImages().findIndex(function (item) { return item.full === photo.full; });
                        updateGalleryImage();
                    });
                    galleryThumbs.appendChild(button);
                });
            }
            if (images.length) updateGalleryImage();
            else {
                image.removeAttribute('src');
                image.alt = '';
            }
        }
        function updateGalleryImage() {
            var current = galleryImages()[imageIndex];
            image.src = current.full;
            image.alt = current.alt || model().name;
            var selected = model().images.findIndex(function (photo) { return photo.full === current.full; });
            galleryThumbs.querySelectorAll('button').forEach(function (button, index) {
                button.setAttribute('aria-pressed', index === selected ? 'true' : 'false');
            });
        }
        function updatePurchase() {
            var current = quantity();
            var selectedExtras = Array.from(extrasList.querySelectorAll('input:checked'));
            var basePrice = isTea ? (current ? current.price : null) : model().price;
            var price = basePrice === null ? null : basePrice + selectedExtras.reduce(function (sum, extra) { return sum + Number(extra.dataset.price); }, 0);
            var unitPrice = product.querySelector('.mh-product__unit-price');
            product.querySelector('.mh-product__count').textContent = String(count);
            product.querySelector('.mh-product__total').textContent = price !== null && !model().dataError ? money(price * count) : '—';
            product.querySelector('.mh-product__packaging').textContent = isTea && current ? 'Verpackung: ' + current.packaging : '';
            if (isTea && current) {
                var weightMatch = /^\s*(\d+(?:[.,]\d+)?)\s*g\s*$/i.exec(current.name);
                var weight = weightMatch ? Number(weightMatch[1].replace(',', '.')) : 0;
                if (weight > 0) {
                    unitPrice.textContent = money(current.price * 100 / weight) + ' / 100 g';
                } else {
                    console.warn('Der Grundpreis kann für diese Teemenge nicht berechnet werden:', current.name);
                    unitPrice.textContent = 'Grundpreis nicht verfügbar';
                }
            } else {
                unitPrice.textContent = '';
            }
            addButton.disabled = (isTea && (!current || current.unavailable)) || model().unavailable || model().dataError;
            if (model().dataError) {
                showFeedback('Preisdaten unvollständig.', true);
            } else if (model().unavailable || (isTea && current && current.unavailable)) {
                showFeedback('Diese Auswahl ist derzeit nicht verfügbar.', true);
            } else if (isTea && !current) {
                showFeedback('Keine Menge hinterlegt.', true);
            }
        }
        function render(modelMovement) {
            var current = model();
            if (!current) throw new Error('Für diese Ausführung ist keine Variante hinterlegt.');
            quantityIndex = current.quantities.findIndex(function (item) { return !item.unavailable; });
            if (quantityIndex < 0) quantityIndex = 0;
            resetAdded();
            renderEditionButtons();
            renderModelButtons(modelMovement);
            selectedExtraOrder = [];
            product.querySelector('.mh-product__title').textContent = current.name;
            var description = product.querySelector('#mh-panel-description');
            description.innerHTML = current.description + (isTea ? '' : current.taste);
            var knowledge = document.createElement('div');
            knowledge.innerHTML = current.knowledge;
            if (isTea) {
                var tastePanel = product.querySelector('#mh-panel-taste');
                tastePanel.innerHTML = current.taste;
                knowledge.querySelectorAll('p').forEach(function (paragraph) {
                    var first = paragraph.firstChild;
                    while (first && first.nodeType === Node.TEXT_NODE && !first.textContent.trim()) first = first.nextSibling;
                    if (!first) return;
                    var isHeading = first.nodeType === Node.ELEMENT_NODE && /^(STRONG|B)$/.test(first.tagName) &&
                        /^\s*Geschmack\s*:?[\s]*$/i.test(first.textContent);
                    var isText = first.nodeType === Node.TEXT_NODE && /^\s*Geschmack\s*:/i.test(first.textContent);
                    if (!isHeading && !isText) return;
                    if (isText) first.textContent = first.textContent.replace(/^\s*Geschmack\s*:\s*/i, '');
                    var start = isHeading ? first.nextSibling : first;
                    if (isHeading) first.remove();
                    var fragment = document.createDocumentFragment();
                    while (start) {
                        var next = start.nextSibling;
                        if ((start.nodeType === Node.ELEMENT_NODE && /^(STRONG|B)$/.test(start.tagName) &&
                            /^\s*[^:]{1,60}:\s*$/.test(start.textContent)) ||
                            (start.nodeType === Node.TEXT_NODE && /^\s*Zubereitung\s*:/i.test(start.textContent))) break;
                        fragment.appendChild(start);
                        start = next;
                    }
                    while (fragment.firstChild && (fragment.firstChild.nodeName === 'BR' ||
                        (fragment.firstChild.nodeType === Node.TEXT_NODE && !fragment.firstChild.textContent.trim()))) {
                        fragment.firstChild.remove();
                    }
                    var tasteText = fragment.textContent.replace(/\s+/g, ' ').trim();
                    var existingTasteText = tastePanel.textContent.replace(/\s+/g, ' ').trim();
                    if ((tasteText || fragment.querySelector('img, video, audio')) &&
                        (tasteText !== existingTasteText || fragment.querySelector('a, img, video, audio'))) {
                        var taste = document.createElement('p');
                        taste.appendChild(fragment);
                        tastePanel.appendChild(taste);
                    }
                    if (!paragraph.textContent.trim() && !paragraph.querySelector('img, video, audio')) paragraph.remove();
                });
                if (!tastePanel.textContent.trim() && !tastePanel.querySelector('img, video, audio')) {
                    tastePanel.textContent = 'Geschmacksangaben werden ergänzt.';
                }
            }
            var preparation = document.createElement('div');
            preparation.innerHTML = current.preparation;
            var preparationText = preparation.textContent.replace(/\s+/g, ' ').trim();
            knowledge.querySelectorAll('p').forEach(function (paragraph) {
                var heading = paragraph.firstElementChild;
                var text = paragraph.textContent.replace(/^\s*Zubereitung\s*:?\s*/i, '').replace(/\s+/g, ' ').trim();
                if (preparationText && heading && heading.tagName === 'STRONG' &&
                    /^Zubereitung\s*:?$/i.test(heading.textContent.trim()) && text === preparationText &&
                    !paragraph.querySelector('a, img, video, audio')) paragraph.remove();
            });
            if (knowledge.textContent.replace(/Geschmack\s*:\s*|Zubereitung\s*:\s*/gi, '').trim() ||
                knowledge.querySelector('img, video, audio')) {
                description.appendChild(knowledge);
            }
            renderFeatures(current.features);
            var preparationPanel = product.querySelector('#mh-panel-preparation');
            var preparationTab = product.querySelector('#mh-tab-preparation');
            preparationPanel.innerHTML = current.preparation || '<p>Angaben zur Zubereitung werden ergänzt.</p>';
            preparationTab.hidden = !isTea && !preparationText && !preparation.querySelector('img, video, audio');
            if (preparationTab.hidden && preparationTab.getAttribute('aria-selected') === 'true') setTab('description', false);
            select.replaceChildren();
            current.quantities.forEach(function (item, index) {
                var option = document.createElement('option');
                option.value = String(index);
                option.textContent = item.name + (item.unavailable ? ' – unverfügbar' : '');
                option.disabled = item.unavailable;
                option.selected = index === quantityIndex;
                select.appendChild(option);
            });
            select.disabled = !current.quantities.length || current.quantities.every(function (item) { return item.unavailable; });
            renderExtras();
            renderGallery();
            updatePurchase();
        }
        function renderDialog() {
            var images = galleryImages();
            var current = images[imageIndex];
            var dialogImage = dialog.querySelector('.mh-product__dialog-image');
            dialogImage.src = current.full;
            dialogImage.alt = current.alt || model().name;
            dialogLens.classList.remove('is-active');
            dialogLens.style.backgroundImage = 'url(' + JSON.stringify(current.full) + ')';
            dialog.querySelector('[data-action="previous"]').hidden = images.length < 2;
            dialog.querySelector('[data-action="next"]').hidden = images.length < 2;
        }

        modelsContainer.addEventListener('keydown', function (event) {
            if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
            var step = event.key === 'ArrowRight' ? 1 : -1;
            var length = edition().models.length;
            var nextIndex = modelIndex + step;
            if (length >= 5) nextIndex = (nextIndex + length) % length;
            else if (nextIndex < 0 || nextIndex >= length) return;
            event.preventDefault();
            selectModel(nextIndex, step);
        });
        modelsContainer.addEventListener('touchstart', function (event) {
            if (edition().models.length >= 5 && event.touches.length === 1) {
                modelTouchStart = {x: event.touches[0].clientX, y: event.touches[0].clientY};
            }
        }, {passive: true});
        modelsContainer.addEventListener('touchend', function (event) {
            if (!modelTouchStart || !event.changedTouches.length) return;
            var dx = event.changedTouches[0].clientX - modelTouchStart.x;
            var dy = event.changedTouches[0].clientY - modelTouchStart.y;
            modelTouchStart = null;
            if (Math.abs(dx) < 35 || Math.abs(dx) <= Math.abs(dy)) return;
            event.preventDefault();
            var step = dx < 0 ? 1 : -1;
            selectModel((modelIndex + step + edition().models.length) % edition().models.length, step);
        }, {passive: false});
        modelsContainer.addEventListener('touchcancel', function () { modelTouchStart = null; });

        select.addEventListener('change', function () {
            var selected = Array.from(extrasList.querySelectorAll('input:checked')).map(function (input) { return input.value; });
            quantityIndex = Number(select.value);
            resetAdded();
            renderExtras(selected);
            renderGallery();
            updatePurchase();
        });
        product.querySelector('[data-action="minus"]').addEventListener('click', function () {
            count = Math.max(1, count - 1);
            resetAdded();
            updatePurchase();
        });
        product.querySelector('[data-action="plus"]').addEventListener('click', function () {
            count = Math.min(99, count + 1);
            resetAdded();
            updatePurchase();
        });
        product.querySelectorAll('[role="tab"]').forEach(function (tab) {
            tab.addEventListener('click', function () { setTab(tab.id.replace('mh-tab-', ''), false); });
            tab.addEventListener('keydown', function (event) {
                if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
                var tabs = Array.from(product.querySelectorAll('[role="tab"]:not([hidden])'));
                var offset = event.key === 'ArrowRight' ? 1 : -1;
                var next = tabs[(tabs.indexOf(tab) + offset + tabs.length) % tabs.length];
                event.preventDefault();
                setTab(next.id.replace('mh-tab-', ''), true);
            });
        });
        stage.addEventListener('click', function () {
            if (!galleryImages().length) return;
            renderDialog();
            dialog.showModal();
        });
        dialog.querySelector('[data-action="close"]').addEventListener('click', function () { dialog.close(); });
        dialogViewport.addEventListener('pointermove', function (event) {
            if (event.pointerType !== 'mouse') return;
            var rect = dialogViewport.getBoundingClientRect();
            var x = event.clientX - rect.left;
            var y = event.clientY - rect.top;
            dialogLens.classList.add('is-active');
            var lensCenter = dialogLens.clientWidth / 2;
            dialogLens.style.left = x + 'px';
            dialogLens.style.top = y + 'px';
            dialogLens.style.backgroundSize = (rect.width * 2) + 'px ' + (rect.height * 2) + 'px';
            dialogLens.style.backgroundPosition = (lensCenter - x * 2) + 'px ' + (lensCenter - y * 2) + 'px';
        });
        dialogViewport.addEventListener('pointerleave', function () { dialogLens.classList.remove('is-active'); });
        dialog.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                event.preventDefault();
                dialog.close();
            }
        });
        dialog.querySelector('[data-action="previous"]').addEventListener('click', function () {
            var images = galleryImages();
            imageIndex = (imageIndex - 1 + images.length) % images.length;
            updateGalleryImage();
            renderDialog();
        });
        dialog.querySelector('[data-action="next"]').addEventListener('click', function () {
            var images = galleryImages();
            imageIndex = (imageIndex + 1) % images.length;
            updateGalleryImage();
            renderDialog();
        });
        addButton.addEventListener('click', function () {
            if (added) {
                window.location.assign(new URL('/bestellliste/', window.location.origin));
                return;
            }
            try {
                var current = quantity();
                if ((isTea && (!current || current.unavailable)) || model().unavailable || model().dataError) {
                    throw new Error('Diese Auswahl ist derzeit nicht verfügbar.');
                }
                var raw = window.localStorage.getItem('bestellliste');
                var list = raw === null ? [] : JSON.parse(raw);
                if (!Array.isArray(list)) throw new Error('Die gespeicherte Bestellliste hat ein ungültiges Format.');
                list.forEach(function (item) {
                    if (!item || typeof item !== 'object' || Array.isArray(item) ||
                        typeof item.name !== 'string' || !item.name.trim() ||
                        !Number.isFinite(Number(item.price)) || Number(item.price) < 0 ||
                        !Number.isInteger(Number(item.quantity)) || Number(item.quantity) < 1) {
                        throw new Error('Ein Eintrag der Bestellliste ist ungültig.');
                    }
                });
                var selectedExtras = Array.from(extrasList.querySelectorAll('input:checked'));
                var extraNames = {dose: 'Dose', klammer: 'Klammer', teeblatt: 'Teeblatt', morshaus: 'Morshaus'};
                var variant = [edition().name, isTea && current ? current.name : ''].concat(selectedExtras.map(function (extra) { return extraNames[extra.value]; })).filter(Boolean).join(' + ');
                var price = (isTea ? current.price : model().price) + selectedExtras.reduce(function (sum, extra) { return sum + Number(extra.dataset.price); }, 0);
                var existing = list.find(function (item) {
                    return item.type === 'product' && item.name === model().name && item.variant === variant && Number(item.price) === price;
                });
                if (existing) existing.quantity = Number(existing.quantity) + count;
                else list.push({type: 'product', name: model().name, variant: variant, price: price, quantity: count});
                window.localStorage.setItem('bestellliste', JSON.stringify(list));
                added = true;
                addButton.textContent = 'Zur Bestellliste';
                showFeedback('Zur Bestellliste hinzugefügt.', false);
            } catch (error) {
                console.error(error);
                showFeedback('Speichern fehlgeschlagen.', true);
            }
        });
        render();
    } catch (error) {
        console.error(error);
        product.querySelector('.mh-product__add').disabled = true;
        showFeedback('Produktdaten nicht geladen.', true);
    }
});
