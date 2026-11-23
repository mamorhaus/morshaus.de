<?php
if (!defined('ABSPATH')) exit;

define('MORSHAUS_PILOT_VERSION', '0.1.1');

require_once get_template_directory() . '/inc/events.php';
require_once get_template_directory() . '/inc/products.php';
require_once get_template_directory() . '/inc/products-admin.php';

function morshaus_pilot_setup() {
    load_theme_textdomain('morshaus-pilot', get_template_directory() . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');

    add_theme_support('custom-logo', array(
        'height'      => 180,
        'width'       => 520,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');

    register_nav_menus(array(
        'primary' => 'Hauptnavigation',
        'footer'  => 'Footernavigation',
    ));
}
add_action('after_setup_theme', 'morshaus_pilot_setup');


/**
 * Theme-Assets.
 */
function morshaus_pilot_assets() {
    $dir = get_template_directory();
    $uri = get_template_directory_uri();

    foreach (array('tokens', 'base', 'components', 'front-page') as $name) {
        $path = $dir . '/assets/css/' . $name . '.css';

        wp_enqueue_style(
            'morshaus-' . $name,
            $uri . '/assets/css/' . $name . '.css',
            array(),
            file_exists($path)
                ? filemtime($path)
                : MORSHAUS_PILOT_VERSION
        );
    }

    if (is_page('hallo')) {
        $path = $dir . '/assets/css/hallo.css';

        wp_enqueue_style(
            'morshaus-hallo',
            $uri . '/assets/css/hallo.css',
            array('morshaus-base'),
            file_exists($path)
                ? filemtime($path)
                : MORSHAUS_PILOT_VERSION
        );
    }

    if (is_page(array('begegnung', 'seminare'))) {
        $path = $dir . '/assets/css/begegnung.css';

        wp_enqueue_style(
            'morshaus-begegnung',
            $uri . '/assets/css/begegnung.css',
            array('morshaus-base'),
            file_exists($path)
                ? filemtime($path)
                : MORSHAUS_PILOT_VERSION
        );
    }

    $post_content = is_singular()
        ? get_post_field('post_content', get_queried_object_id())
        : '';
    $has_event_shortcode = is_string($post_content) && (
        has_shortcode($post_content, 'morshaus_veranstaltungen') ||
        has_shortcode($post_content, 'morshaus_begegnungstermine')
    );

    if (is_page(array('begegnung', 'seminare')) || $has_event_shortcode) {
        $script = $dir . '/assets/js/begegnung.js';

        wp_enqueue_script(
            'morshaus-begegnung',
            $uri . '/assets/js/begegnung.js',
            array(),
            file_exists($script)
                ? filemtime($script)
                : MORSHAUS_PILOT_VERSION,
            true
        );
    }

    if (is_page('tee') || is_page('utensilien')) {
        $path = $dir . '/assets/css/tee.css';

        wp_enqueue_style(
            'morshaus-tee',
            $uri . '/assets/css/tee.css',
            array('morshaus-base'),
            file_exists($path)
                ? filemtime($path)
                : MORSHAUS_PILOT_VERSION
        );

        $script = $dir . '/assets/js/tee.js';

        wp_enqueue_script(
            'morshaus-tee',
            $uri . '/assets/js/tee.js',
            array(),
            file_exists($script)
                ? filemtime($script)
                : MORSHAUS_PILOT_VERSION,
            true
        );
    }

    if (is_page('bestellliste')) {
        $path = $dir . '/assets/css/order-list.css';

        wp_enqueue_style(
            'morshaus-order-list',
            $uri . '/assets/css/order-list.css',
            array('morshaus-base'),
            file_exists($path)
                ? filemtime($path)
                : MORSHAUS_PILOT_VERSION
        );

        $script = $dir . '/assets/js/order-list.js';

        wp_enqueue_script(
            'morshaus-order-list',
            $uri . '/assets/js/order-list.js',
            array(),
            file_exists($script)
                ? filemtime($script)
                : MORSHAUS_PILOT_VERSION,
            true
        );
    }

    if (is_page('sencha') || morshaus_pilot_current_utensil_product()) {
        $path = $dir . '/assets/css/product.css';
        wp_enqueue_style(
            'morshaus-product',
            $uri . '/assets/css/product.css',
            array('morshaus-base'),
            file_exists($path) ? filemtime($path) : MORSHAUS_PILOT_VERSION
        );

        $script = $dir . '/assets/js/product.js';
        wp_enqueue_script(
            'morshaus-product',
            $uri . '/assets/js/product.js',
            array(),
            file_exists($script) ? filemtime($script) : MORSHAUS_PILOT_VERSION,
            true
        );
    }

    $script = $dir . '/assets/js/navigation.js';

    wp_enqueue_script(
        'morshaus-navigation',
        $uri . '/assets/js/navigation.js',
        array(),
        file_exists($script)
            ? filemtime($script)
            : MORSHAUS_PILOT_VERSION,
        true
    );

    $has_product_shortcode = false;
    if (is_page() && is_string($post_content) && preg_match_all('/' . get_shortcode_regex(array('morshaus_produkt')) . '/s', $post_content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            if ($match[1] !== '[' || $match[6] !== ']') {
                $has_product_shortcode = true;
                break;
            }
        }
    }

    if (!$has_product_shortcode) {
        $script = $dir . '/assets/js/watermark.js';
        wp_enqueue_script(
            'morshaus-watermark',
            $uri . '/assets/js/watermark.js',
            array(),
            file_exists($script) ? filemtime($script) : MORSHAUS_PILOT_VERSION,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'morshaus_pilot_assets');

function morshaus_pilot_page_body_classes($classes) {
    if (is_page('termine')) {
        $classes[] = 'mh-termine-page';
    }

    if (
        is_page() &&
        is_singular() &&
        has_shortcode(
            get_post_field('post_content', get_queried_object_id()),
            'morshaus_veranstaltungen'
        )
    ) {
        $classes[] = 'mh-event-list-page';
    }

    return $classes;
}
add_filter('body_class', 'morshaus_pilot_page_body_classes');

function morshaus_pilot_hide_begegnung_intro($content, $block) {
    if (
        is_page('begegnung') &&
        $block['blockName'] === 'core/group' &&
        in_array('mh-begegnung-intro', preg_split('/\s+/', $block['attrs']['className'] ?? ''), true)
    ) {
        return '';
    }

    return $content;
}
add_filter('render_block', 'morshaus_pilot_hide_begegnung_intro', 10, 2);

function morshaus_pilot_has_editor_content($post_id) {
    $content = get_post_field('post_content', $post_id);

    if (!is_string($content)) {
        return false;
    }

    return trim(wp_strip_all_tags($content)) !== ''
        || preg_match('/<(?:img|video|audio|iframe)\b/i', $content) === 1;
}

function morshaus_pilot_register_content_patterns() {
    if (
        !function_exists('register_block_pattern') ||
        !function_exists('register_block_pattern_category')
    ) {
        return;
    }

    register_block_pattern_category(
        'morshaus-pilot-content',
        array('label' => 'Morshaus Inhalte')
    );

    register_block_pattern(
        'morshaus-pilot/startseite-inhalte',
        array(
            'title'       => 'Morshaus: Startseite Inhalte',
            'description' => 'Bearbeitbare Startseiteninhalte mit nativen Gutenberg-Blöcken.',
            'categories'  => array('morshaus-pilot-content'),
            'content'     => <<<'HTML'
<!-- wp:group {"className":"mh-home-panel mh-home-panel--intro"} -->
<div class="wp-block-group mh-home-panel mh-home-panel--intro">
<!-- wp:group {"className":"mh-home-panel__grid"} -->
<div class="wp-block-group mh-home-panel__grid">
<!-- wp:group {"className":"mh-home-panel__copy"} -->
<div class="wp-block-group mh-home-panel__copy">
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Hallo</h2>
<!-- /wp:heading -->
<!-- wp:group {"className":"mh-prose"} -->
<div class="wp-block-group mh-prose">
<!-- wp:paragraph -->
<p>Worum es geht.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/hallo/">Mehr</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<!-- wp:image {"id":2373,"sizeSlug":"large","linkDestination":"none","className":"mh-home-panel__media"} -->
<figure class="wp-block-image size-large mh-home-panel__media"><img src="/wp-content/uploads/2026/10/IMG_20261015_151404-rotated.jpg" alt="" class="wp-image-2373"/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"mh-home-panel"} -->
<div class="wp-block-group mh-home-panel">
<!-- wp:group {"className":"mh-home-panel__grid"} -->
<div class="wp-block-group mh-home-panel__grid">
<!-- wp:group {"className":"mh-home-panel__copy"} -->
<div class="wp-block-group mh-home-panel__copy">
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Tee</h2>
<!-- /wp:heading -->
<!-- wp:group {"className":"mh-prose"} -->
<div class="wp-block-group mh-prose">
<!-- wp:paragraph -->
<p>Tee beziehen.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/tee/">Mehr</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<!-- wp:image {"id":2359,"sizeSlug":"large","linkDestination":"none","className":"mh-home-panel__media"} -->
<figure class="wp-block-image size-large mh-home-panel__media"><img src="/wp-content/uploads/2026/10/IMG_20261014_162944-rotated.jpg" alt="" class="wp-image-2359"/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"mh-home-panel"} -->
<div class="wp-block-group mh-home-panel">
<!-- wp:group {"className":"mh-home-panel__grid"} -->
<div class="wp-block-group mh-home-panel__grid">
<!-- wp:group {"className":"mh-home-panel__copy"} -->
<div class="wp-block-group mh-home-panel__copy">
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Utensilien</h2>
<!-- /wp:heading -->
<!-- wp:group {"className":"mh-prose"} -->
<div class="wp-block-group mh-prose">
<!-- wp:paragraph -->
<p>Formschönes für die Zubereitung.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/utensilien-teaser/utensilien/">Mehr</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<!-- wp:image {"id":132,"sizeSlug":"large","linkDestination":"none","className":"mh-home-panel__media"} -->
<figure class="wp-block-image size-large mh-home-panel__media"><img src="/wp-content/uploads/2026/06/IMG_20260601_112506-rotated.jpg" alt="" class="wp-image-132"/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"mh-home-panel"} -->
<div class="wp-block-group mh-home-panel">
<!-- wp:group {"className":"mh-home-panel__grid"} -->
<div class="wp-block-group mh-home-panel__grid">
<!-- wp:group {"className":"mh-home-panel__copy"} -->
<div class="wp-block-group mh-home-panel__copy">
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Begegnung</h2>
<!-- /wp:heading -->
<!-- wp:group {"className":"mh-prose"} -->
<div class="wp-block-group mh-prose">
<!-- wp:paragraph -->
<p>Genießen und Begegnen.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/begegnung/">Mehr</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<!-- wp:image {"id":133,"sizeSlug":"large","linkDestination":"none","className":"mh-home-panel__media"} -->
<figure class="wp-block-image size-large mh-home-panel__media"><img src="/wp-content/uploads/2026/06/IMG_20260529_142012-scaled.jpg" alt="" class="wp-image-133"/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"mh-home-panel mh-home-panel--contact"} -->
<div class="wp-block-group mh-home-panel mh-home-panel--contact">
<!-- wp:group {"className":"mh-home-panel__grid"} -->
<div class="wp-block-group mh-home-panel__grid">
<!-- wp:group {"className":"mh-home-panel__copy"} -->
<div class="wp-block-group mh-home-panel__copy">
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Kontakt</h2>
<!-- /wp:heading -->
<!-- wp:group {"className":"mh-prose"} -->
<div class="wp-block-group mh-prose">
<!-- wp:paragraph -->
<p><strong>Interesse geweckt?</strong><br>Du möchtest Tee bestellen?<br>Deine Ausstattung erweitern?<br>Mehr über Zubereitung und Kultur lernen?<br>Oder einfach gemeinsam einen Tee trinken?</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p>Nutze den Shop und schreibe mir persönlich, lies <a href="/bestellung-versand-rechnung/">hier</a>, wie es geht. Informiere dich über aktuelle Veranstaltungen und Neuigkeiten in der Gruppe.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p><strong>E-Mail:</strong> hallo@morshaus.de</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p><strong>Signal:</strong></p>
<!-- /wp:paragraph -->
<!-- wp:group {"className":"mh-home-signal"} -->
<div class="wp-block-group mh-home-signal">
<!-- wp:group {"className":"mh-home-signal__item"} -->
<div class="wp-block-group mh-home-signal__item">
<!-- wp:image {"sizeSlug":"full","linkDestination":"custom"} -->
<figure class="wp-block-image size-full"><a href="https://signal.me/#eu/52lRiJxH9Adpr8HYoFmcN44UGDXx_h_fY4O-Ip1lzkBA1AN4BHJNW5Qa6f7G11DV"><img src="/wp-content/uploads/2026/11/QR-Signal-Maxmor.png" alt="QR-Code für Signal-Kontakt Maxmor"/></a></figure>
<!-- /wp:image -->
<!-- wp:paragraph -->
<p><a href="https://signal.me/#eu/52lRiJxH9Adpr8HYoFmcN44UGDXx_h_fY4O-Ip1lzkBA1AN4BHJNW5Qa6f7G11DV">Maxmor</a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"mh-home-signal__item"} -->
<div class="wp-block-group mh-home-signal__item">
<!-- wp:image {"sizeSlug":"full","linkDestination":"custom"} -->
<figure class="wp-block-image size-full"><a href="https://signal.group/#CjQKILPz4WdygTFwWyT90uq_NEza_Rp6hhz1wXwWe725UW9lEhA1HCBb-nhvW989MLazYpzi"><img src="/wp-content/uploads/2026/11/QR-Signal-Gruppe.png" alt="QR-Code für die Morshaus Signal-Gruppe"/></a></figure>
<!-- /wp:image -->
<!-- wp:paragraph -->
<p><a href="https://signal.group/#CjQKILPz4WdygTFwWyT90uq_NEza_Rp6hhz1wXwWe725UW9lEhA1HCBb-nhvW989MLazYpzi">Morshaus Gruppe</a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
HTML
        )
    );

    register_block_pattern(
        'morshaus-pilot/hallo-inhalte',
        array(
            'title'       => 'Morshaus: Hallo Inhalte',
            'description' => 'Bearbeitbare Hallo-Inhalte mit nativen Gutenberg-Blöcken.',
            'categories'  => array('morshaus-pilot-content'),
            'content'     => <<<'HTML'
<!-- wp:group {"className":"mh-hallo-intro"} -->
<div class="wp-block-group mh-hallo-intro">
<!-- wp:image {"id":2289,"sizeSlug":"large","linkDestination":"none","className":"mh-hallo-intro__portrait"} -->
<figure class="wp-block-image size-large mh-hallo-intro__portrait"><img src="/wp-content/uploads/2026/10/IMG_20260307_181709-768x1024.jpg" alt="Portrait des Morshaus-Inhabers" class="wp-image-2289"/></figure>
<!-- /wp:image -->
<!-- wp:group {"className":"mh-hallo-intro__copy"} -->
<div class="wp-block-group mh-hallo-intro__copy">
<!-- wp:paragraph {"className":"mh-hallo-intro__eyebrow"} -->
<p class="wp-block-paragraph mh-hallo-intro__eyebrow">Morshaus · Tee &amp; mehr</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":1,"anchor":"mh-hallo-title"} -->
<h1 class="wp-block-heading" id="mh-hallo-title">Schön, dass du hier bist.</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"mh-hallo-intro__lead"} -->
<p class="wp-block-paragraph mh-hallo-intro__lead">Tee ist mehr als ein Getränk. Kult(ur)pflanze, gesundheitsfördernd, belebend, beruhigend, Leidenschaft und Genuss.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"mh-hallo-intro__aside"} -->
<p class="wp-block-paragraph mh-hallo-intro__aside">Diese Leidenschaft möchte ich teilen und einen persönlichen Einblick und Einstieg in die Welt des Tees bieten.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"mh-hallo-discover"} -->
<div class="wp-block-group mh-hallo-discover">
<!-- wp:heading {"anchor":"mh-hallo-discover-title"} -->
<h2 class="wp-block-heading" id="mh-hallo-discover-title">Vier Wege zum Tee</h2>
<!-- /wp:heading -->
<!-- wp:list {"className":"mh-hallo-topics"} -->
<ul class="wp-block-list mh-hallo-topics">
<li><strong>Genießen</strong> Ausgewählte Tees nach Herkunft, Anlass und Geschmack. <a href="/tee/">Zu den Tees</a></li>
<li><strong>Zubereiten</strong> Werkzeuge und Gefäße für eine bewusste Zubereitung. <a href="/utensilien-teaser/utensilien/">Zu den Utensilien</a></li>
<li><strong>Lernen</strong> Grundlagen kennenlernen oder Wissen vertiefen. <a href="/seminare/">Zu den Seminaren</a></li>
<li><strong>Begegnen</strong> Zwei oder drei Tassen und Zeit für ein gutes Gespräch. <a href="/begegnung/">Zur Begegnung</a></li>
</ul>
<!-- /wp:list -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"mh-hallo-values"} -->
<div class="wp-block-group mh-hallo-values">
<!-- wp:heading {"anchor":"mh-hallo-values-title"} -->
<h2 class="wp-block-heading" id="mh-hallo-values-title">Hochwertig. Persönlich. Unkompliziert.</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"mh-hallo-values__intro"} -->
<p class="wp-block-paragraph mh-hallo-values__intro">Du möchtest Tee bestellen, deine Ausstattung erweitern, mehr über Zubereitung und Kultur lernen oder einfach gemeinsam einen Tee trinken?</p>
<!-- /wp:paragraph -->
<!-- wp:list {"className":"mh-hallo-values__grid"} -->
<ul class="wp-block-list mh-hallo-values__grid">
<li><strong>Hochwertig</strong> – Tee, Utensilien und Veranstaltungen erster Güte.</li>
<li><strong>Persönlich</strong> – Fragen und Wünsche direkt besprechen.</li>
<li><strong>Unkompliziert</strong> – Den nächsten Schritt gemeinsam abstimmen.</li>
</ul>
<!-- /wp:list -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"mh-hallo-contact"} -->
<div class="wp-block-group mh-hallo-contact">
<!-- wp:heading {"anchor":"mh-hallo-contact-title"} -->
<h2 class="wp-block-heading" id="mh-hallo-contact-title">Direkt ins Gespräch kommen</h2>
<!-- /wp:heading -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/kontakt/">Kontakt</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
HTML
        )
    );

    register_block_pattern(
        'morshaus-pilot/bestellliste-hinweise',
        array(
            'title'       => 'Morshaus: Bestellliste Hinweise',
            'description' => 'Bearbeitbare Bestellhinweise mit einem aufklappbaren Gutenberg-Block.',
            'categories'  => array('morshaus-pilot-content'),
            'content'     => <<<'HTML'
<!-- wp:details {"className":"mh-order-help"} -->
<details class="wp-block-details mh-order-help"><summary>Wie funktioniert die Bestellung?</summary>
<!-- wp:list -->
<ul class="wp-block-list">
<li>Wähle Produkte oder Veranstaltungen aus und füge sie deiner Bestellliste hinzu.</li>
<li>Prüfe deine Auswahl und passe bei Bedarf die Anzahl an.</li>
<li>Die automatisch erstellte Bestellnachricht kannst du anschließend bearbeiten und per Signal oder E-Mail an mich senden.</li>
<li>Bezahlung, Versand und Rechnung klären wir persönlich.</li>
<li>Weitere Infos findest du <a href="/bestellung-versand-rechnung/">hier</a>.</li>
</ul>
<!-- /wp:list -->
</details>
<!-- /wp:details -->
HTML
        )
    );
}
add_action('init', 'morshaus_pilot_register_content_patterns');

function morshaus_pilot_redirect_sessions_slug() {
    if (!is_404()) {
        return;
    }

    $request_path = isset($_SERVER['REQUEST_URI'])
        ? trim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/')
        : '';

    if ($request_path === 'sessions') {
        wp_safe_redirect(home_url('/begegnung/'), 301);
        exit;
    }
}
add_action('template_redirect', 'morshaus_pilot_redirect_sessions_slug', 1);


/**
 * Liest für den Pilot bewusst die bisherigen
 * Twenty-Seventeen-Einstellungen.
 */
function morshaus_pilot_legacy_mods() {
    static $mods = null;

    if (is_array($mods)) {
        return $mods;
    }

    global $wpdb;

    $mods = array();

    $rows = $wpdb->get_results(
        "SELECT option_name, option_value
         FROM {$wpdb->options}
         WHERE option_name LIKE 'theme_mods_%'",
        ARRAY_A
    );

    /*
     * Child-Theme-Werte sollen Parent-Werte überschreiben.
     */
    usort($rows, function ($a, $b) {
        $a_child = strpos(
            $a['option_name'],
            'twentyseventeen-child'
        ) !== false ? 1 : 0;

        $b_child = strpos(
            $b['option_name'],
            'twentyseventeen-child'
        ) !== false ? 1 : 0;

        return $a_child <=> $b_child;
    });

    foreach ($rows as $row) {
        $value = maybe_unserialize($row['option_value']);

        if (!is_array($value)) {
            continue;
        }

        $relevant = strpos(
            $row['option_name'],
            'twentyseventeen'
        ) !== false;

        if (!$relevant) {
            foreach (
                array(
                    'custom_logo',
                    'header_image',
                    'panel_1',
                    'panel_2',
                    'panel_3'
                ) as $key
            ) {
                if (array_key_exists($key, $value)) {
                    $relevant = true;
                    break;
                }
            }
        }

        if ($relevant) {
            $mods = array_replace($mods, $value);
        }
    }

    return $mods;
}


function morshaus_pilot_legacy_mod($key, $default = null) {
    $mods = morshaus_pilot_legacy_mods();

    return array_key_exists($key, $mods)
        ? $mods[$key]
        : $default;
}


function morshaus_pilot_logo_url() {
    $id = absint(
        morshaus_pilot_legacy_mod('custom_logo', 0)
    );

    return $id
        ? wp_get_attachment_image_url($id, 'full')
        : '';
}


function morshaus_pilot_header_url() {
    $value = morshaus_pilot_legacy_mod(
        'header_image',
        ''
    );

    if (is_numeric($value)) {
        return wp_get_attachment_image_url(
            absint($value),
            'full'
        );
    }

    if (
        is_string($value) &&
        $value !== 'remove-header' &&
        $value !== 'random-default-image'
    ) {
        return $value;
    }

    return '';
}


function morshaus_pilot_panel_ids() {
    $ids = array();

    for ($i = 1; $i <= 7; $i++) {
        $id = absint(
            morshaus_pilot_legacy_mod(
                'panel_' . $i,
                0
            )
        );

        if (
            $id &&
            get_post_status($id) === 'publish'
        ) {
            $ids[] = $id;
        }
    }

    return array_values(
        array_unique($ids)
    );
}


function morshaus_pilot_menu_fallback() {
    echo '<ul class="mh-menu">';

    wp_list_pages(array(
        'title_li' => '',
        'depth'    => 1,
    ));

    echo '</ul>';
}


/**
 * Kennzeichnet spezielle Menüpunkte.
 *
 * Die Darstellung selbst bleibt vollständig
 * im CSS bzw. in den dafür vorgesehenen Assets.
 */
function morshaus_pilot_menu_item_classes($classes, $item, $args) {

    if (
        isset($args->theme_location) &&
        $args->theme_location === 'primary' &&
        is_page('sencha')
    ) {
        $tee_page = get_page_by_path('tee');
        if ($tee_page && isset($item->object_id, $item->object) && $item->object === 'page' && (int) $item->object_id === $tee_page->ID) {
            $classes[] = 'current-menu-ancestor';
        }
    }

    if (isset($args->theme_location) && $args->theme_location === 'primary' && morshaus_pilot_current_utensil_product()) {
        $utensil_page = get_page_by_path('utensilien-teaser/utensilien');
        if ($utensil_page && isset($item->object_id, $item->object) && $item->object === 'page' && (int) $item->object_id === $utensil_page->ID) {
            $classes[] = 'current-menu-ancestor';
        }
    }

    if (
        isset($args->theme_location) &&
        $args->theme_location === 'primary' &&
        trim(wp_strip_all_tags($item->title)) === 'Bestellliste'
    ) {
        $classes[] = 'mh-menu-order';
    }

    return $classes;
}
add_filter(
    'nav_menu_css_class',
    'morshaus_pilot_menu_item_classes',
    10,
    3
);

function morshaus_pilot_product_page_menu_classes($classes, $page) {
    if (is_page('sencha') && $page->post_name === 'tee') {
        $classes[] = 'current-menu-ancestor';
    }
    if (morshaus_pilot_current_utensil_product() && get_page_uri($page) === 'utensilien-teaser/utensilien') {
        $classes[] = 'current-menu-ancestor';
    }
    return $classes;
}
add_filter('page_css_class', 'morshaus_pilot_product_page_menu_classes', 10, 2);


function morshaus_pilot_body_classes($classes) {
    $classes[] = 'mh-theme';

    if (is_front_page()) {
        $classes[] = 'mh-front-page';
    }

    return $classes;
}
add_filter(
    'body_class',
    'morshaus_pilot_body_classes'
);