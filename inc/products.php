<?php
if (!defined('ABSPATH')) exit;

function morshaus_get_produkte() {
    $products = get_option('morshaus_produkte_liste', array());
    return is_array($products) ? $products : array();
}

function morshaus_save_produkte($products) {
    update_option('morshaus_produkte_liste', $products);
}

function morshaus_get_produkt_by_id($id) {
    foreach (morshaus_get_produkte() as $product) {
        if (isset($product['id']) && $product['id'] === $id) {
            return $product;
        }
    }
    return null;
}

function morshaus_pilot_unavailable_cards($path, $label_block) {
    static $cache = array();
    $key = $path . ':' . $label_block;
    if (isset($cache[$key])) return $cache[$key];

    $page = get_page_by_path($path);
    if (!$page) {
        error_log('Morshaus: Verteilerseite nicht gefunden: ' . $path);
        return $cache[$key] = array();
    }

    $labels = array();
    $find_label = function ($blocks) use (&$find_label, $label_block) {
        foreach ($blocks as $block) {
            if (($block['blockName'] ?? '') === $label_block) {
                return trim(wp_strip_all_tags($block['innerHTML'] ?? ''));
            }
            $label = $find_label($block['innerBlocks'] ?? array());
            if ($label !== '') return $label;
        }
        return '';
    };
    $collect = function ($blocks) use (&$collect, &$labels, $find_label) {
        foreach ($blocks as $block) {
            if (($block['blockName'] ?? '') === 'core/cover') {
                $classes = preg_split('/\s+/', $block['attrs']['className'] ?? '');
                if (in_array('unverfugbar', $classes, true)) {
                    $label = $find_label($block['innerBlocks'] ?? array());
                    if ($label !== '') $labels[] = sanitize_title($label);
                }
            }
            $collect($block['innerBlocks'] ?? array());
        }
    };
    $collect(parse_blocks($page->post_content));
    return $cache[$key] = $labels;
}

function morshaus_pilot_product_unavailable($product) {
    $id = $product['id'] ?? '';
    if (($product['kategorie'] ?? '') === 'tee') {
        return $id === 'sencha' && in_array('sencha', morshaus_pilot_unavailable_cards('tee', 'core/button'), true);
    }
    $cards = array(
        'loeffel' => 'Löffel',
        'thermometer' => 'Thermometer',
        'tasse' => 'Tassen',
        'kanne' => 'Kannen',
        'untersetzer' => 'Tasse/Kanne',
        'tablett' => 'Tablett',
        'dose' => 'Dosen',
    );
    return isset($cards[$id]) && in_array(sanitize_title($cards[$id]), morshaus_pilot_unavailable_cards('utensilien-teaser/utensilien', 'core/button'), true);
}

function morshaus_pilot_seminar_unavailable($filter) {
    $cards = array(
        'am-tisch' => 'am-tisch',
        'in-stille' => 'in-der-stille',
        'im-wald' => 'im-wald',
        'auf-dem-wasser' => 'auf-dem-wasser',
    );
    if (!isset($cards[$filter])) return false;
    $settings = get_option('morshaus_seminar_unavailable', array());
    if (is_array($settings) && array_key_exists($filter, $settings)) {
        return $settings[$filter] === '1';
    }
    return in_array($cards[$filter], morshaus_pilot_unavailable_cards('seminare', 'core/heading'), true);
}

function morshaus_pilot_current_utensil_product() {
    if (!is_page()) return null;
    $legacy_page = get_page_by_path('utensilien-teaser/untersetzer-tasse');
    if ($legacy_page && (int) $legacy_page->ID === (int) get_queried_object_id()) {
        $product = morshaus_get_produkt_by_id('untersetzer');
        return $product && ($product['kategorie'] ?? '') === 'utensil' ? $product : null;
    }
    $content = get_post_field('post_content', get_queried_object_id());
    if (!is_string($content) || strpos($content, '[morshaus_produkt') === false) return null;
    if (!preg_match_all('/' . get_shortcode_regex(array('morshaus_produkt')) . '/s', $content, $matches, PREG_SET_ORDER)) return null;
    foreach ($matches as $match) {
        if ($match[1] === '[' && $match[6] === ']') continue;
        $atts = shortcode_parse_atts($match[3]);
        $product = morshaus_get_produkt_by_id(sanitize_text_field($atts['id'] ?? ''));
        if ($product && ($product['kategorie'] ?? '') === 'utensil') return $product;
    }
    return null;
}

function morshaus_pilot_utensil_template($template) {
    if (morshaus_pilot_current_utensil_product()) {
        return get_template_directory() . '/page-product-utensil.php';
    }
    return $template;
}
add_filter('template_include', 'morshaus_pilot_utensil_template');

function morshaus_pilot_utensil_card_links($content, $block) {
    if (is_admin() || !is_page('utensilien') || ($block['blockName'] ?? '') !== 'core/button') return $content;
    $label = trim(wp_strip_all_tags($content));
    $targets = array(
        'Löffel' => array('utensilien-teaser/loeffel-einfach', 'loeffel'),
        'Thermometer' => array('utensilien-teaser/thermometer', 'thermometer'),
        'Tassen' => array('utensilien-teaser/tasse', 'tasse'),
        'Kannen' => array('utensilien-teaser/kanne', 'kanne'),
        'Tasse/Kanne' => array('utensilien-teaser/untersetzer-tasse', 'untersetzer'),
        'Tablett' => array('utensilien-teaser/tablett', 'tablett'),
        'Dosen' => array('utensilien-teaser/dose', 'dose'),
    );
    $pending = array('Regal', 'Tee To-Go', 'Gong Fu', 'To-Go', 'Fairness', 'Waage', 'Isolierkanne');
    if (!isset($targets[$label]) && !in_array($label, $pending, true)) return $content;
    $processor = new WP_HTML_Tag_Processor($content);
    if (!$processor->next_tag('a')) return $content;
    if (isset($targets[$label])) {
        list($path, $id) = $targets[$label];
        $page = get_page_by_path($path);
        if ($page && $page->post_status === 'publish' && (!$id || morshaus_get_produkt_by_id($id))) {
            $processor->set_attribute('href', get_permalink($page));
            return $processor->get_updated_html();
        }
        error_log('Morshaus: Produktseite oder Produkt fehlt für Verteilerkarte ' . $label);
    }
    $processor->remove_attribute('href');
    $processor->set_attribute('aria-disabled', 'true');
    return $processor->get_updated_html() . '<span class="mh-utensil-coming-soon">Produktseite folgt</span>';
}
add_filter('render_block', 'morshaus_pilot_utensil_card_links', 10, 2);

function morshaus_pilot_edition_name($name, $category) {
    if ($category === 'tee') {
        if ($name === 'Einfach') return 'Alltag';
        if ($name === 'Besonders') return 'Erlesen';
    }
    return $name;
}

function morshaus_get_kategorie_konfiguration($category, $type = '') {
    $configs = array(
        'tee' => array('icon'=>'tee','extra'=>'dose','editions'=>array('Alltag','Erlesen'),'features'=>array(array('standort','Herkunft'),array('blatt','Kultivar'),array('kalender','Ernte'),array('wellen','Dämpfung / Verarbeitung'))),
        'loeffel' => array('icon'=>'loeffel','extra'=>'branding','editions'=>array('Einfach','Besonders','Matcha'),'features'=>array(array('lineal','Länge'),array('blatt','Holzart'),array('schichten','Fassung'),array('funkeln','Besonderheit'))),
        'thermometer' => array('icon'=>'thermometer','extra'=>'branding','editions'=>array('Standard'),'features'=>array(array('lineal','Maße'),array('material','Holzart'))),
        'untersetzer' => array('icon'=>'untersetzer','extra'=>'branding','editions'=>array('Tasse','Kanne'),'features'=>array(array('lineal','Maße'),array('funkeln','Besonderheit'))),
        'tablett' => array('icon'=>'tablett','extra'=>'branding','editions'=>array('Standard'),'features'=>array(array('lineal','Maße'),array('funkeln','Besonderheit'))),
        'zubehoer' => array('icon'=>'teeschale','extra'=>'','editions'=>array(),'features'=>array()),
        'gefaess' => array('icon'=>'teeschale','extra'=>'','editions'=>array(),'features'=>array(array('lineal','Maße'),array('gefaess','Füllmenge'),array('funkeln','Glasur'))),
    );
    $key = $category === 'tee' ? 'tee' : $type;
    return isset($configs[$key]) ? $configs[$key] : $configs['zubehoer'];
}

function morshaus_sanitize_merkmale($raw) {
    if (!is_array($raw)) return array();
    $allowed = array('lineal','blatt','schichten','funkeln','thermometer','standort','kalender','wellen','ziel','material','gefaess','kreis');
    $features = array();
    foreach (array_slice($raw, 0, 4) as $item) {
        if (!is_array($item)) continue;
        $label = sanitize_text_field(wp_unslash($item['label'] ?? ''));
        $value = sanitize_text_field(wp_unslash($item['wert'] ?? ''));
        $icon = sanitize_key(wp_unslash($item['icon'] ?? 'funkeln'));
        $tooltip = sanitize_textarea_field(wp_unslash($item['tooltip_text'] ?? ''));
        if ($label === '' || $value === '') continue;
        $features[] = array(
            'icon' => in_array($icon, $allowed, true) ? $icon : 'funkeln',
            'label' => $label,
            'wert' => $value,
            'tooltip_aktiv' => !empty($item['tooltip_aktiv']) && $tooltip !== '' ? '1' : '0',
            'tooltip_text' => $tooltip,
        );
    }
    return $features;
}

function morshaus_sanitize_aufguesse($raw, $unslash = false) {
    if (!is_array($raw)) {
        error_log('Morshaus: Ungültige Aufgussdaten in der Produktverwaltung.');
        return array();
    }
    $aufguesse = array();
    foreach ($raw as $item) {
        if (!is_array($item) || !is_string($item['temperatur'] ?? '') || !is_string($item['dauer'] ?? '')) {
            error_log('Morshaus: Ungültige Aufgussdaten in der Produktverwaltung.');
            continue;
        }
        $temperatur = $item['temperatur'] ?? '';
        $dauer = $item['dauer'] ?? '';
        if ($unslash && $temperatur === '' && isset($item['temperatur_alt']) && is_string($item['temperatur_alt'])) $temperatur = $item['temperatur_alt'];
        if ($unslash && $dauer === '' && isset($item['dauer_alt']) && is_string($item['dauer_alt'])) $dauer = $item['dauer_alt'];
        $temperatur = sanitize_text_field($unslash ? wp_unslash($temperatur) : $temperatur);
        $dauer = sanitize_text_field($unslash ? wp_unslash($dauer) : $dauer);
        $temperatur = morshaus_aufguss_zahl($temperatur, 'temperatur');
        $dauer = morshaus_aufguss_zahl($dauer, 'dauer');
        if ($temperatur === '' && $dauer === '') continue;
        $aufguesse[] = array('temperatur' => $temperatur, 'dauer' => $dauer);
    }
    return $aufguesse;
}

function morshaus_aufguss_zahl($value, $field) {
    $value = trim((string) $value);
    $unit = $field === 'temperatur' ? '(?:°\s*C?)' : '(?:s(?:ek(?:unden?)?\.?)?)';
    if (preg_match('/^(\d+(?:[.,]\d+)?)\s*' . $unit . '?$/iu', $value, $match)) {
        return $match[1];
    }
    return $value;
}

function morshaus_aufguss_ist_zahl($value) {
    return $value === '' || (is_string($value) && preg_match('/^\d+(?:[.,]\d+)?$/D', $value));
}

function morshaus_pilot_feature_icon($icon) {
    $icons = array(
        'lineal' => '<path d="M4 16 16 4l4 4L8 20 4 16Z"/><path d="m13 7 4 4M10 10l2 2M7 13l2 2"/>',
        'blatt' => '<path d="M19.5 3.5C12 3.8 6.8 7.2 5.2 13.3c-.7 2.7.5 5.2 3 6.2 2.8 1.1 6-.2 7.7-3.1 2-3.5 2.3-8.2 3.6-12.9Z"/><path d="M5 20c2.4-4.3 5.6-7.8 10.2-10.7"/>',
        'schichten' => '<path d="m12 3 8 4-8 4-8-4 8-4Z"/><path d="m4 12 8 4 8-4M4 17l8 4 8-4"/>',
        'funkeln' => '<path d="m12 3 1.7 5.3L19 10l-5.3 1.7L12 17l-1.7-5.3L5 10l5.3-1.7L12 3Z"/><path d="m18.5 16 .8 2.2 2.2.8-2.2.8-.8 2.2-.8-2.2-2.2-.8 2.2-.8.8-2.2Z"/>',
        'thermometer' => '<path d="M10 14.8V5a2 2 0 1 1 4 0v9.8a4.5 4.5 0 1 1-4 0Z"/><path d="M12 8v8"/>',
        'uhr' => '<circle cx="12" cy="12" r="8"/><path d="M12 7v5l3 2"/>',
        'standort' => '<path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
        'kalender' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4M17 3v4M3 10h18"/>',
        'wellen' => '<path d="M3 7c2-2 4-2 6 0s4 2 6 0 4-2 6 0M3 12c2-2 4-2 6 0s4 2 6 0 4-2 6 0M3 17c2-2 4-2 6 0s4 2 6 0 4-2 6 0"/>',
        'ziel' => '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="4"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/>',
        'material' => '<path d="M4 7h16v10H4z"/><path d="m4 7 4-3h12v13l-4 3H4V7Z"/>',
        'gefaess' => '<path d="M5 7h14l-1 12H6L5 7Z"/><path d="M8 4h8"/>',
        'kreis' => '<circle cx="12" cy="12" r="8"/>',
    );
    $content = isset($icons[$icon]) ? $icons[$icon] : $icons['funkeln'];
    return '<svg viewBox="0 0 24 24">' . $content . '</svg>';
}

function morshaus_pilot_product_image($id, $fallback_alt) {
    $id = absint($id);
    if (!$id) return null;
    $full = wp_get_attachment_image_url($id, 'large');
    if (!$full) {
        error_log('Morshaus: Produktbild nicht gefunden (Medien-ID ' . $id . ').');
        return null;
    }
    return array(
        'full' => $full,
        'thumb' => wp_get_attachment_image_url($id, 'thumbnail') ?: $full,
        'alt' => get_post_meta($id, '_wp_attachment_image_alt', true) ?: $fallback_alt,
    );
}

function morshaus_pilot_product_payload($product) {
    $is_tea = ($product['kategorie'] ?? '') === 'tee';
    $editions = array();
    foreach ((array) ($product['ausfuehrungen'] ?? array()) as $edition) {
        $models = array();
        foreach ((array) ($edition['modelle'] ?? array()) as $model) {
            $preparation = (string) ($model['zubereitung'] ?? '');
            $knowledge = (string) ($model['wissenswertes'] ?? '');
            $knowledge_title = sanitize_text_field($model['wissenswertes_titel'] ?? '');
            // Ältere Produkte speichern die Zubereitung im gemeinsamen Wissenswertes-Feld.
            if (trim($preparation) === '' && preg_match('~<strong>\s*Zubereitung\s*:?\s*</strong>\s*(.*?)(?=<strong>|$)~isu', $knowledge, $match)) {
                $preparation = trim($match[1]);
            }
            $images = array();
            foreach (array_merge((array) ($model['galerie'] ?? array()), (array) ($model['detailgalerie'] ?? array())) as $id) {
                $image = morshaus_pilot_product_image($id, $model['name'] ?? '');
                if ($image) $images[] = $image;
            }
            $quantities = array();
            $data_error = false;
            $source = !empty($model['mengen']) ? $model['mengen'] : ($edition['mengen'] ?? array());
            foreach ((array) $source as $quantity) {
                if (empty($quantity['name']) || !isset($quantity['preis']) || !is_numeric($quantity['preis']) || (float) $quantity['preis'] < 0) {
                    $data_error = true;
                    continue;
                }
                $quantities[] = array(
                    'name' => (string) ($quantity['name'] ?? ''),
                    'price' => (float) $quantity['preis'],
                    'unavailable' => !empty($quantity['unverfuegbar']),
                    'packaging' => !empty($quantity['aromaschutzbeutel']) ? 'Aromaschutzbeutel' : 'Papiertüte',
                );
            }
            $extras = array();
            foreach ((array) ($model['extra_typen'] ?? array()) as $type) {
                if (!in_array($type, array('dose', 'klammer'), true)) continue;
                $price = $model['extra_preise'][$type] ?? null;
                if (!is_numeric($price) || (float) $price < 0) {
                    $data_error = true;
                    continue;
                }
                $image = morshaus_pilot_product_image($model['extra_bilder'][$type] ?? 0, ($model['name'] ?? '') . ' – ' . ucfirst($type));
                $extras[] = array('type' => $type, 'price' => (float) $price, 'image' => $image);
            }
            if (!$is_tea) {
                $branding_types = (array) ($model['branding_arten'] ?? array());
                if (!$branding_types && (in_array('branding', (array) ($model['extra_typen'] ?? array()), true) || ($model['extra_typ'] ?? '') === 'branding')) {
                    $branding_types = empty($model['branding_art']) ? array() : array($model['branding_art']);
                }
                foreach ($branding_types as $type) {
                    if (!in_array($type, array('teeblatt', 'morshaus'), true)) continue;
                    $price = $model['branding_preise'][$type] ?? ($model['extra_preis'] ?? null);
                    if (!is_numeric($price) || (float) $price < 0) {
                        $data_error = true;
                        continue;
                    }
                    $image = morshaus_pilot_product_image($model['branding_bilder'][$type] ?? 0, ($model['name'] ?? '') . ' – ' . ucfirst($type));
                    $extras[] = array('type' => $type, 'price' => (float) $price, 'image' => $image);
                }
            }
            $price = $model['preis'] ?? null;
            if (!$is_tea && (!is_numeric($price) || (float) $price < 0)) $data_error = true;
            if ($data_error) {
                error_log('Morshaus: Ungültige Mengen- oder Preisdaten für Produktmodell ' . ($model['name'] ?? ''));
            }
            $features = array();
            foreach ((array) ($model['merkmale'] ?? array()) as $feature) {
                if (!is_array($feature)) continue;
                $icon = isset($feature['icon']) && is_string($feature['icon']) ? $feature['icon'] : '';
                $feature['iconSvg'] = morshaus_pilot_feature_icon($icon);
                $features[] = $feature;
            }
            $models[] = array(
                'name' => (string) ($model['name'] ?? ''),
                'description' => wpautop(wp_kses_post($model['beschreibung'] ?? '')),
                'taste' => wpautop(wp_kses_post($model['geschmack'] ?? '')),
                'preparation' => wpautop(wp_kses_post($preparation)),
                'infusions' => $is_tea ? morshaus_sanitize_aufguesse($model['aufguesse'] ?? array()) : array(),
                'knowledge' => wpautop(wp_kses_post($knowledge)),
                'knowledgeTitle' => $knowledge_title !== '' ? $knowledge_title : 'Wissenswertes',
                'knowledgeEnabled' => isset($model['wissenswertes_aktiv']) ? !empty($model['wissenswertes_aktiv']) : trim($knowledge) !== '',
                'features' => $features,
                'images' => $images,
                'quantities' => $quantities,
                'extras' => $extras,
                'price' => !$is_tea && is_numeric($price) ? (float) $price : null,
                'dataError' => $data_error,
                'unavailable' => !empty($model['unverfuegbar']),
            );
        }
        if (!$models) continue;
        $name = morshaus_pilot_edition_name((string) ($edition['name'] ?? ''), $product['kategorie'] ?? '');
        $editions[] = array('name' => $name, 'models' => $models);
    }
    return array(
        'name' => (string) ($product['name'] ?? ''),
        'category' => $is_tea ? 'tee' : 'utensil',
        'preparationIcons' => array(
            'temperature' => morshaus_pilot_feature_icon('thermometer'),
            'duration' => morshaus_pilot_feature_icon('uhr'),
        ),
        'unavailable' => morshaus_pilot_product_unavailable($product),
        'showThumbnails' => !isset($product['show_thumbnails']) || !empty($product['show_thumbnails']),
        'editions' => $editions,
    );
}
