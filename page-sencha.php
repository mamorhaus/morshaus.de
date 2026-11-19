<?php get_header(); ?>
<main id="main" class="mh-main mh-product-main">
    <div class="mh-container mh-container--wide">
        <?php while (have_posts()) : the_post(); ?>
            <?php $product = morshaus_get_produkt_by_id('sencha'); ?>
            <article <?php post_class('mh-product-page'); ?>>
                <?php if (!$product) : ?>
                    <h1><?php the_title(); ?></h1>
                    <p>Dieses Produkt ist derzeit nicht verfügbar.</p>
                <?php else : ?>
                    <div class="mh-product" data-mh-product>
                        <script type="application/json" class="mh-product__data"><?php echo wp_json_encode(morshaus_pilot_product_payload($product), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
                        <div class="mh-product__overview">
                            <div class="mh-product__gallery-column">
                                <button class="mh-product__stage" type="button" aria-label="Produktbilder vergrößern" hidden>
                                    <img class="mh-product__image" alt="">
                                    <span class="mh-product__zoom" aria-hidden="true">⌕</span>
                                </button>
                                <p class="mh-product__no-image" hidden>Kein Produktbild vorhanden.</p>
                            </div>
                            <div class="mh-product__choices">
                                <div class="mh-product__selection-group mh-product__model-selection" hidden>
                                    <p class="mh-product__selection-label">Variante</p>
                                    <div class="mh-product__models-carousel">
                                        <div id="mh-product-models" class="mh-product__models" role="group" aria-label="Variante wählen"></div>
                                    </div>
                                </div>
                                <div class="mh-product__selection-group mh-product__edition-selection" hidden>
                                    <p class="mh-product__selection-label">Ausführung</p>
                                    <div class="mh-product__editions" aria-label="Ausführung wählen"></div>
                                </div>
                            </div>
                            <div class="mh-product__detail-column">
                                <div class="mh-product__heading">
                                    <p class="mh-product__eyebrow">Grüner Tee</p>
                                    <h1 class="mh-product__title"><?php echo esc_html($product['name']); ?></h1>
                                </div>
                                <section class="mh-product__info" aria-label="Teeinformationen">
                                    <div class="mh-product__tabs" role="tablist" aria-label="Teeinformationen">
                                        <button type="button" role="tab" id="mh-tab-description" aria-controls="mh-panel-description" aria-selected="true">Beschreibung</button>
                                        <button type="button" role="tab" id="mh-tab-character" aria-controls="mh-panel-character" aria-selected="false" tabindex="-1">Charakter</button>
                                        <button type="button" role="tab" id="mh-tab-taste" aria-controls="mh-panel-taste" aria-selected="false" tabindex="-1">Geschmack</button>
                                        <button type="button" role="tab" id="mh-tab-preparation" aria-controls="mh-panel-preparation" aria-selected="false" tabindex="-1">Zubereitung</button>
                                    </div>
                                    <div id="mh-panel-description" class="mh-product__panel mh-product__description" role="tabpanel" aria-labelledby="mh-tab-description" tabindex="0"></div>
                                    <div id="mh-panel-character" class="mh-product__panel" role="tabpanel" aria-labelledby="mh-tab-character" tabindex="0" hidden></div>
                                    <div id="mh-panel-taste" class="mh-product__panel" role="tabpanel" aria-labelledby="mh-tab-taste" tabindex="0" hidden></div>
                                    <div id="mh-panel-preparation" class="mh-product__panel" role="tabpanel" aria-labelledby="mh-tab-preparation" tabindex="0" hidden></div>
                                </section>
                            </div>
                            <section class="mh-product__purchase" aria-label="Bestellung">
                                <div class="mh-product__purchase-controls">
                                    <div class="mh-product__field">
                                        <label for="mh-product-quantity">Teemenge</label>
                                        <select id="mh-product-quantity"></select>
                                    </div>
                                    <div class="mh-product__field">
                                        <span>Anzahl</span>
                                        <div class="mh-product__stepper" aria-label="Anzahl wählen">
                                            <button type="button" data-action="minus" aria-label="Anzahl verringern">−</button>
                                            <span class="mh-product__count" aria-live="polite">1</span>
                                            <button type="button" data-action="plus" aria-label="Anzahl erhöhen">+</button>
                                        </div>
                                    </div>
                                    <div class="mh-product__field mh-product__price">
                                        <span>Gesamtpreis</span>
                                        <strong class="mh-product__total"></strong>
                                    </div>
                                </div>
                                <div class="mh-product__purchase-meta">
                                    <span class="mh-product__packaging"></span>
                                    <span class="mh-product__unit-price"></span>
                                </div>
                                <fieldset class="mh-product__extras"><legend>Extras</legend><div></div></fieldset>
                                <p class="mh-product__feedback" role="status" aria-live="polite"></p>
                                <button type="button" class="mh-product__add">Zur Bestellliste hinzufügen</button>
                            </section>
                        </div>
                        <dialog class="mh-product__dialog" aria-label="Vergrößerte Produktbilder">
                            <button type="button" data-action="close" class="mh-product__dialog-close" aria-label="Schließen">×</button>
                            <button type="button" data-action="previous" aria-label="Vorheriges Bild">‹</button>
                            <img alt="">
                            <button type="button" data-action="next" aria-label="Nächstes Bild">›</button>
                        </dialog>
                        <noscript><p>Für die Produktauswahl und die Bestellliste wird JavaScript benötigt.</p></noscript>
                    </div>
                <?php endif; ?>
            </article>
        <?php endwhile; ?>
    </div>
</main>
<?php get_footer(); ?>
