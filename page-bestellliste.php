<?php get_header(); ?>
<main id="main" class="mh-main mh-order-main">
    <div class="mh-container mh-container--wide">
        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class('mh-order-page'); ?>>
                <h1 class="screen-reader-text"><?php the_title(); ?></h1>

                <div class="mh-order-content">
                    <?php the_content(); ?>

                    <section class="mh-order-section" aria-labelledby="mh-order-list-title">
                        <h2 id="mh-order-list-title">Deine aktuelle Bestellliste</h2>
                        <div class="mh-order-actions">
                            <button type="button" id="refresh-cart">
                                Liste aktualisieren
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 11a8 8 0 1 0-2.3 5.7M20 4v7h-7"/></svg>
                            </button>
                            <div class="mh-order-clear-action">
                                <button type="button" id="clear-cart">
                                    Liste leeren
                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg>
                                </button>
                                <span class="mh-order-action-status" role="status" aria-live="polite"></span>
                            </div>
                        </div>
                        <ul id="cart-items" aria-live="polite" aria-relevant="additions removals text">
                            <li class="mh-order-empty">Bestellliste wird geladen …</li>
                        </ul>

                        <div class="mh-order-total">
                            <span>Gesamtsumme</span>
                            <strong><span id="cart-total">—</span> €</strong>
                        </div>
                    </section>

                    <section class="mh-order-section mh-order-message" aria-labelledby="mh-order-message-title">
                        <h2 id="mh-order-message-title">Bestellnachricht</h2>
                        <p>Du kannst die Nachricht per Button kopieren und anschließend per Signal oder E-Mail senden.</p>
                        <label class="screen-reader-text" for="order-template">Deine Bestellnachricht</label>
                        <textarea id="order-template" rows="10"></textarea>
                        <div class="mh-order-message-actions">
                            <button type="button" class="copy-btn" data-copy="order-template">
                                Bestellnachricht kopieren
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="8" y="7" width="12" height="14" rx="2"/><path d="M16 7V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h2"/></svg>
                            </button>
                            <div class="mh-order-contact-actions">
                                <a class="mh-order-contact-button" href="https://signal.me/#eu/52lRiJxH9Adpr8HYoFmcN44UGDXx_h_fY4O-Ip1lzkBA1AN4BHJNW5Qa6f7G11DV" target="_blank" rel="noopener noreferrer">
                                    Signal
                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 11.5a7.5 7.5 0 0 1-7.5 7.5 8 8 0 0 1-3.6-.8L4 20l1.5-4A7.4 7.4 0 0 1 5 12a7.5 7.5 0 1 1 15 .5Z"/><path d="M9 12h.01M12 12h.01M15 12h.01"/></svg>
                                </a>
                                <button type="button" id="order-mail">
                                    Mail
                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>
                                </button>
                            </div>
                            <p class="mh-order-status" role="status" aria-live="polite"></p>
                        </div>
                    </section>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</main>
<?php get_footer(); ?>
