<?php
/**
 * Morshaus Child Theme - Produktverwaltung im Backend
 * Arbeitsstand: Morshaus 0.10.1-B3
 */

/* ==============================================================
 * Produkte-Verwaltung (Admin-Seite)
 * ============================================================== */

function morshaus_register_produkte_admin_page() {
    add_menu_page(
        'Produkte',
        'Produkte',
        'manage_options',
        'morshaus-produkte',
        'morshaus_render_produkte_admin_page',
        'dashicons-products',
        26
    );
}
add_action(
    'admin_menu',
    'morshaus_register_produkte_admin_page'
);

function morshaus_produkte_admin_media($hook) {
    if ($hook === 'toplevel_page_morshaus-produkte') {
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
    }
}
add_action('admin_enqueue_scripts', 'morshaus_produkte_admin_media');



function morshaus_generate_produkt_id(
    $name,
    $exclude_id = ''
) {
    $base = sanitize_title($name);

    if ($base === '') {
        $base = 'produkt';
    }

    $existing_ids = array();

    foreach (
        morshaus_get_produkte() as $produkt
    ) {
        if (
            isset($produkt['id']) &&
            $produkt['id'] !== $exclude_id
        ) {
            $existing_ids[] = $produkt['id'];
        }
    }

    $id = $base;
    $i  = 2;

    while (
        in_array($id, $existing_ids, true)
    ) {
        $id = $base . '-' . $i;
        $i++;
    }

    return $id;
}


function morshaus_render_merkmale_admin($name_prefix, $merkmale = array()) {
    $options = array('lineal'=>'Lineal','blatt'=>'Blatt','schichten'=>'Schichten','funkeln'=>'Funkeln','thermometer'=>'Thermometer','standort'=>'Standort','kalender'=>'Kalender','wellen'=>'Wellen / Verarbeitung','ziel'=>'Ziel / Genauigkeit','material'=>'Material','gefaess'=>'Gefäß','kreis'=>'Kreis / Durchmesser');
    ?>
    <div class="morshaus-merkmale-wrap">
        <strong>Produktmerkmale</strong>
        <p class="description">Bis zu vier frei benennbare Merkmale. Leere Zeilen werden nicht angezeigt.</p>
        <?php for ($i=0; $i<4; $i++) : $m = isset($merkmale[$i]) ? $merkmale[$i] : array(); ?>
            <div class="morshaus-merkmal-row">
                <select name="<?php echo esc_attr($name_prefix); ?>[merkmale][<?php echo esc_attr($i); ?>][icon]">
                    <?php foreach ($options as $key=>$label) : ?><option value="<?php echo esc_attr($key); ?>" <?php selected(isset($m['icon'])?$m['icon']:'funkeln',$key); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?>
                </select>
                <input type="text" name="<?php echo esc_attr($name_prefix); ?>[merkmale][<?php echo esc_attr($i); ?>][label]" value="<?php echo esc_attr(isset($m['label'])?$m['label']:''); ?>" placeholder="Bezeichnung, z. B. Herkunft">
                <input type="text" name="<?php echo esc_attr($name_prefix); ?>[merkmale][<?php echo esc_attr($i); ?>][wert]" value="<?php echo esc_attr(isset($m['wert'])?$m['wert']:''); ?>" placeholder="Wert, z. B. Uji, Kyoto">
                <label class="morshaus-tooltip-toggle"><input type="checkbox" name="<?php echo esc_attr($name_prefix); ?>[merkmale][<?php echo esc_attr($i); ?>][tooltip_aktiv]" value="1" <?php checked(!empty($m['tooltip_aktiv'])); ?>> Tooltip</label>
                <input type="text" class="morshaus-tooltip-text" name="<?php echo esc_attr($name_prefix); ?>[merkmale][<?php echo esc_attr($i); ?>][tooltip_text]" value="<?php echo esc_attr(isset($m['tooltip_text'])?$m['tooltip_text']:''); ?>" placeholder="Text der Hinweisbox">
            </div>
        <?php endfor; ?>
    </div>
    <?php
}

function morshaus_render_aufguesse_admin($name_prefix, $aufguesse = array()) {
    ?>
    <div class="morshaus-tee-inhalte-section morshaus-tee-mengen-only morshaus-aufguesse">
        <strong>Aufgüsse</strong>
        <p class="description">Temperatur in Grad und Ziehzeit in Sekunden: nur Zahlen eingeben. Leere Angaben werden nicht angezeigt.</p>
        <input type="hidden" name="<?php echo esc_attr($name_prefix); ?>[aufguesse_marker]" value="1">
        <div class="morshaus-aufguesse-rows">
            <?php foreach ((array) $aufguesse as $i => $aufguss) :
                $temperatur = $aufguss['temperatur'] ?? '';
                $dauer = $aufguss['dauer'] ?? '';
                $temperatur_alt = !morshaus_aufguss_ist_zahl($temperatur);
                $dauer_alt = !morshaus_aufguss_ist_zahl($dauer);
            ?>
                <div class="morshaus-aufguss-row">
                    <strong class="morshaus-aufguss-number"><?php echo esc_html(($i + 1) . '. Aufguss'); ?></strong>
                    <label>Temperatur (°) <?php if ($temperatur_alt) : ?><span class="description">Bisher: <?php echo esc_html($temperatur); ?> (Zahl ersetzt die Angabe)</span><input type="hidden" name="<?php echo esc_attr($name_prefix); ?>[aufguesse][<?php echo esc_attr($i); ?>][temperatur_alt]" value="<?php echo esc_attr($temperatur); ?>"><?php endif; ?><input type="text" inputmode="decimal" pattern="[0-9]+([.,][0-9]+)?" title="Bitte nur eine Zahl eingeben." name="<?php echo esc_attr($name_prefix); ?>[aufguesse][<?php echo esc_attr($i); ?>][temperatur]" value="<?php echo esc_attr($temperatur_alt ? '' : $temperatur); ?>" placeholder="z. B. 70"></label>
                    <label>Ziehzeit (s) <?php if ($dauer_alt) : ?><span class="description">Bisher: <?php echo esc_html($dauer); ?> (Zahl ersetzt die Angabe)</span><input type="hidden" name="<?php echo esc_attr($name_prefix); ?>[aufguesse][<?php echo esc_attr($i); ?>][dauer_alt]" value="<?php echo esc_attr($dauer); ?>"><?php endif; ?><input type="text" inputmode="decimal" pattern="[0-9]+([.,][0-9]+)?" title="Bitte nur eine Zahl eingeben." name="<?php echo esc_attr($name_prefix); ?>[aufguesse][<?php echo esc_attr($i); ?>][dauer]" value="<?php echo esc_attr($dauer_alt ? '' : $dauer); ?>" placeholder="z. B. 60"></label>
                    <button type="button" class="button morshaus-remove-aufguss">Entfernen</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button morshaus-add-aufguss">+ Aufguss hinzufügen</button>
    </div>
    <?php
}

function morshaus_render_produkte_admin_page() {
    wp_enqueue_editor();
    if (!current_user_can('manage_options')) {
        return;
    }

    $notice = '';

    if (
        isset(
            $_GET['action'],
            $_GET['id'],
            $_GET['_wpnonce']
        ) &&
        $_GET['action'] === 'delete' &&
        wp_verify_nonce(
            sanitize_text_field(
                wp_unslash($_GET['_wpnonce'])
            ),
            'morshaus_delete_produkt'
        )
    ) {
        $delete_id = sanitize_text_field(
            wp_unslash($_GET['id'])
        );

        $produkte = array_values(
            array_filter(
                morshaus_get_produkte(),
                function ($produkt) use ($delete_id) {
                    return !isset($produkt['id']) ||
                        $produkt['id'] !== $delete_id;
                }
            )
        );

        morshaus_save_produkte($produkte);

        $notice = 'Produkt gelöscht.';
    }

    if (
        isset($_POST['morshaus_produkt_nonce']) &&
        wp_verify_nonce(
            sanitize_text_field(
                wp_unslash(
                    $_POST['morshaus_produkt_nonce']
                )
            ),
            'morshaus_save_produkt'
        )
    ) {
        foreach ((array) ($_POST['morshaus_ausfuehrungen'] ?? array()) as $edition) {
            foreach ((array) ($edition['modelle'] ?? array()) as $model) {
                foreach ((array) ($model['aufguesse'] ?? array()) as $infusion) {
                    foreach (array('temperatur', 'dauer') as $field) {
                        $value = $infusion[$field] ?? '';
                        if (!is_string($value) || !morshaus_aufguss_ist_zahl(trim(wp_unslash($value)))) {
                            wp_die('Aufgüsse wurden nicht gespeichert: Bitte bei Temperatur und Ziehzeit nur Zahlen eingeben. Gehe zurück, um die Eingaben zu korrigieren.', 'Ungültige Aufgussdaten', array('response' => 400));
                        }
                    }
                }
            }
        }
        $name = isset(
            $_POST['morshaus_produkt_name']
        )
            ? sanitize_text_field(
                wp_unslash(
                    $_POST['morshaus_produkt_name']
                )
            )
            : '';

        $original_id = isset(
            $_POST['morshaus_produkt_original_id']
        )
            ? sanitize_text_field(
                wp_unslash(
                    $_POST['morshaus_produkt_original_id']
                )
            )
            : '';

        if ($name === '') {
            $notice = 'Bitte einen Namen angeben.';
        } else {
            $kategorie = isset($_POST['morshaus_produkt_kategorie']) ? sanitize_key(wp_unslash($_POST['morshaus_produkt_kategorie'])) : 'tee';
            if (!in_array($kategorie, array('tee','utensil'), true)) $kategorie = 'tee';
            $utensil_typ = isset($_POST['morshaus_utensil_typ']) ? sanitize_key(wp_unslash($_POST['morshaus_utensil_typ'])) : '';
            if (!in_array($utensil_typ, array('thermometer','loeffel','untersetzer','tablett','zubehoer','gefaess'), true)) $utensil_typ = '';
            if ($kategorie === 'tee') $utensil_typ = '';
            $beschreibung = '';
            $kat_config = morshaus_get_kategorie_konfiguration($kategorie, $utensil_typ);
            $show_thumbnails = isset($_POST['morshaus_show_thumbnails']) && $_POST['morshaus_show_thumbnails'] === '1' ? '1' : '0';
            /* Build 1: Der alte varianten-POST-Pfad wird nicht mehr verarbeitet. */
            $extra_typ = isset($_POST['morshaus_extra_typ']) ? sanitize_key(wp_unslash($_POST['morshaus_extra_typ'])) : '';
            if (!in_array($extra_typ, array('', 'dose', 'branding'), true)) { $extra_typ = ''; }
            $extra_preis_raw = isset($_POST['morshaus_extra_preis']) ? sanitize_text_field(wp_unslash($_POST['morshaus_extra_preis'])) : '';
            $extra_preis_raw = str_replace(',', '.', $extra_preis_raw);
            $extra_preis = is_numeric($extra_preis_raw) ? number_format(max(0, (float) $extra_preis_raw), 2, '.', '') : '0.00';
            if ($extra_typ !== 'branding') { $extra_preis = '0.00'; }
            $extra_typ = $kat_config['extra'];
            if ($extra_typ !== 'branding') $extra_preis = '0.00';
            $piktogramm = $kat_config['icon'];
            /* legacy field is intentionally ignored */
            $legacy_piktogramm = isset($_POST['morshaus_piktogramm']) ? sanitize_key(wp_unslash($_POST['morshaus_piktogramm'])) : 'auto';
            if (!in_array($legacy_piktogramm, array('auto', 'tee', 'thermometer', 'tablett', 'untersetzer', 'teeschale'), true)) {
                $legacy_piktogramm = 'auto';
            }
            $ausfuehrungen = array();
            if (isset($_POST['morshaus_ausfuehrungen']) && is_array($_POST['morshaus_ausfuehrungen'])) {
                foreach ($_POST['morshaus_ausfuehrungen'] as $ausfuehrung_raw) {
                    if (!is_array($ausfuehrung_raw)) continue;
                    $ausf_name = isset($ausfuehrung_raw['name']) ? sanitize_text_field(wp_unslash($ausfuehrung_raw['name'])) : '';
                    $ausf_name_anzeige = $ausf_name;
                    if ($ausf_name === '') $ausf_name = 'Standard';
                    $mengen = array();
                    foreach (array('10g','50g','100g') as $menge_name) {
                        $menge_raw = isset($ausfuehrung_raw['mengen'][$menge_name]) && is_array($ausfuehrung_raw['mengen'][$menge_name]) ? $ausfuehrung_raw['mengen'][$menge_name] : array();
                        if (empty($menge_raw['aktiv'])) continue;
                        $preis_raw = isset($menge_raw['preis']) ? str_replace(',', '.', sanitize_text_field(wp_unslash($menge_raw['preis']))) : '';
                        if ($preis_raw === '' || !is_numeric($preis_raw)) continue;
                        $mengen[] = array('id'=>sanitize_title($menge_name),'name'=>$menge_name,'preis'=>number_format((float)$preis_raw,2,'.',''),'unverfuegbar'=>!empty($menge_raw['unverfuegbar'])?'1':'0','aromaschutzbeutel'=>!empty($menge_raw['aromaschutzbeutel'])?'1':'0');
                    }
                    $modelle = array();
                    if (!empty($ausfuehrung_raw['modelle']) && is_array($ausfuehrung_raw['modelle'])) {
                        foreach ($ausfuehrung_raw['modelle'] as $modell_raw) {
                            if (!is_array($modell_raw)) continue;
                            $modell_name = isset($modell_raw['name']) ? sanitize_text_field(wp_unslash($modell_raw['name'])) : '';
                            $preis_raw = isset($modell_raw['preis']) ? str_replace(',', '.', sanitize_text_field(wp_unslash($modell_raw['preis']))) : '';
                            if ($modell_name === '') continue;
                            if ($kategorie === 'tee') $preis_raw = '0';
                            elseif (!is_numeric($preis_raw)) continue;
                            $galerie = array();
                            if (!empty($modell_raw['galerie'])) {
                                foreach (explode(',', sanitize_text_field(wp_unslash($modell_raw['galerie']))) as $bild_id) {
                                    $bild_id = absint($bild_id);
                                    if ($bild_id) $galerie[] = $bild_id;
                                }
                            }
                            $detailgalerie = array();
                            if (!empty($modell_raw['detailgalerie'])) {
                                foreach (explode(',', sanitize_text_field(wp_unslash($modell_raw['detailgalerie']))) as $bild_id) {
                                    $bild_id = absint($bild_id);
                                    if ($bild_id) $detailgalerie[] = $bild_id;
                                }
                            }
                            $modell_extra_typ_alt = isset($modell_raw['extra_typ']) ? sanitize_key(wp_unslash($modell_raw['extra_typ'])) : $kat_config['extra'];
                            $modell_extra_typen = array();
                            if (isset($modell_raw['extra_typen']) && is_array($modell_raw['extra_typen'])) {
                                foreach ($modell_raw['extra_typen'] as $extra_key) {
                                    $extra_key = sanitize_key(wp_unslash($extra_key));
                                    if (in_array($extra_key, array('dose','klammer','branding'), true)) $modell_extra_typen[] = $extra_key;
                                }
                            } elseif (in_array($modell_extra_typ_alt, array('dose','branding'), true)) {
                                $modell_extra_typen[] = $modell_extra_typ_alt;
                            }
                            $modell_extra_typen = array_values(array_unique($modell_extra_typen));
                            $modell_extra_typ = in_array('branding', $modell_extra_typen, true) ? 'branding' : (in_array('dose', $modell_extra_typen, true) ? 'dose' : '');
                            $modell_branding_arten=array();
                            if(isset($modell_raw['branding_arten'])&&is_array($modell_raw['branding_arten']))foreach($modell_raw['branding_arten'] as $ba){$ba=sanitize_key(wp_unslash($ba));if(in_array($ba,array('teeblatt','morshaus'),true))$modell_branding_arten[]=$ba;}
                            $modell_branding_arten=array_values(array_unique($modell_branding_arten));
                            $modell_branding_preise=array('teeblatt'=>'0.00','morshaus'=>'0.00');
                            $modell_branding_bilder=array('teeblatt'=>absint($modell_raw['branding_bilder']['teeblatt']??0),'morshaus'=>absint($modell_raw['branding_bilder']['morshaus']??0));
                            foreach(array('teeblatt','morshaus') as $branding_key){
                                $raw=isset($modell_raw['branding_preise'][$branding_key])?str_replace(',','.',sanitize_text_field(wp_unslash($modell_raw['branding_preise'][$branding_key]))):'';
                                if(is_numeric($raw))$modell_branding_preise[$branding_key]=number_format(max(0,(float)$raw),2,'.','');
                            }
                            $modell_extra_preis='0.00';
                            $modell_extra_preise = array('dose'=>'0.00','klammer'=>'0.00');
                            $modell_extra_bilder = array('dose'=>0,'klammer'=>0);
                            foreach (array('dose','klammer') as $extra_key) {
                                $raw_extra_preis = isset($modell_raw['extra_preise'][$extra_key]) ? str_replace(',', '.', sanitize_text_field(wp_unslash($modell_raw['extra_preise'][$extra_key]))) : '';
                                if (is_numeric($raw_extra_preis)) $modell_extra_preise[$extra_key] = number_format(max(0,(float)$raw_extra_preis),2,'.','');
                                $modell_extra_bilder[$extra_key] = absint($modell_raw['extra_bilder'][$extra_key] ?? 0);
                            }
                            $modell_mengen = array();
                            $modell_mengen_raw = isset($modell_raw['mengen']) && is_array($modell_raw['mengen']) ? $modell_raw['mengen'] : array();
                            foreach (array('10g','50g','100g') as $menge_name) {
                                $menge_raw = isset($modell_mengen_raw[$menge_name]) && is_array($modell_mengen_raw[$menge_name]) ? $modell_mengen_raw[$menge_name] : array();
                                if (empty($menge_raw['aktiv'])) continue;
                                $menge_preis_raw = isset($menge_raw['preis']) ? str_replace(',', '.', sanitize_text_field(wp_unslash($menge_raw['preis']))) : '';
                                if ($menge_preis_raw === '' || !is_numeric($menge_preis_raw)) continue;
                                $modell_mengen[] = array('id'=>sanitize_title($menge_name),'name'=>$menge_name,'preis'=>number_format((float)$menge_preis_raw,2,'.',''),'unverfuegbar'=>!empty($menge_raw['unverfuegbar'])?'1':'0','aromaschutzbeutel'=>!empty($menge_raw['aromaschutzbeutel'])?'1':'0');
                            }
                            if ($kategorie === 'tee' && empty($modell_mengen) && !empty($mengen)) $modell_mengen = $mengen;
                            $modelle[] = array(
                                'id' => sanitize_title($modell_name),
                                'name' => $modell_name,
                                'beschreibung' => isset($modell_raw['beschreibung']) ? wp_kses_post(wp_unslash($modell_raw['beschreibung'])) : '',
                                'wissenswertes' => isset($modell_raw['wissenswertes']) ? wp_kses_post(wp_unslash($modell_raw['wissenswertes'])) : (isset($modell_raw['geschmack']) ? wp_kses_post(wp_unslash($modell_raw['geschmack'])) : ''),
                                'wissenswertes_titel' => isset($modell_raw['wissenswertes_titel']) ? sanitize_text_field(wp_unslash($modell_raw['wissenswertes_titel'])) : '',
                                'wissenswertes_aktiv' => !empty($modell_raw['wissenswertes_aktiv']) ? '1' : '0',
                                'geschmack' => isset($modell_raw['geschmack']) ? wp_kses_post(wp_unslash($modell_raw['geschmack'])) : '',
                                'zubereitung' => isset($modell_raw['zubereitung']) ? wp_kses_post(wp_unslash($modell_raw['zubereitung'])) : '',
                                'aufguesse' => morshaus_sanitize_aufguesse($modell_raw['aufguesse'] ?? array(), true),
                                'preis' => number_format((float)$preis_raw, 2, '.', ''),
                                'mengen' => $modell_mengen,
                                'galerie' => array_values(array_unique($galerie)),
                                'detailgalerie' => array_values(array_unique($detailgalerie)),
                                'merkmale' => morshaus_sanitize_merkmale(isset($modell_raw['merkmale']) ? $modell_raw['merkmale'] : array()),
                                'extra_typ'=>$modell_extra_typ, 'extra_typen'=>$modell_extra_typen, 'extra_preis'=>$modell_extra_preis, 'extra_preise'=>$modell_extra_preise, 'extra_bilder'=>$modell_extra_bilder, 'branding_arten'=>$modell_branding_arten, 'branding_preise'=>$modell_branding_preise, 'branding_bilder'=>$modell_branding_bilder,
                                'unverfuegbar' => !empty($modell_raw['unverfuegbar']) ? '1' : '0',
                            );
                        }
                    }
                    if ($kategorie === 'tee' && empty($modelle) && !empty($mengen)) {
                        $fallback_name = $name !== '' ? $name : 'Tee';
                        $modelle[] = array(
                            'id' => sanitize_title($fallback_name),
                            'name' => $fallback_name,
                            'beschreibung' => '',
                            'preis' => '0.00',
                            'mengen' => $mengen,
                            'galerie' => array(),
                            'detailgalerie' => array(),
                            'merkmale' => array(),
                            'extra_typ' => 'dose',
                            'extra_preis' => '0.00',
                            'branding_arten' => array(),
                            'unverfuegbar' => '0',
                        );
                    }
                    if ($modelle || $mengen) {
                        $ausfuehrungen[] = array('id'=>sanitize_title($ausf_name),'name'=>$ausf_name_anzeige,'mengen'=>$mengen,'modelle'=>$modelle);
                    }
                }
            }

            $produkte = morshaus_get_produkte();
            $found    = false;

            if ($original_id !== '') {
                foreach ($produkte as &$produkt) {
                    if (
                        isset($produkt['id']) &&
                        $produkt['id'] === $original_id
                    ) {
                        $produkt['name']                    = $name;
                        $produkt['kategorie'] = $kategorie;
                        $produkt['utensil_typ'] = $utensil_typ;
                        $produkt['beschreibung'] = $beschreibung;
                        $produkt['extra_typ'] = $extra_typ;
                        $produkt['extra_preis'] = $extra_preis;
                        $produkt['piktogramm'] = $piktogramm;
                        $produkt['ausfuehrungen'] = $ausfuehrungen;
                        $produkt['show_thumbnails'] = $show_thumbnails;
                        /* 4.10.0-build2: Nach erfolgreichem Speichern im aktuellen
                           Datenmodell wird nur fuer dieses Produkt der Altbestand entfernt. */
                        unset($produkt['varianten']);
                        unset($produkt['branding_teeblatt_aktiv'], $produkt['branding_teeblatt_preis'], $produkt['branding_morshaus_aktiv'], $produkt['branding_morshaus_preis']);
                        $found                = true;
                        break;
                    }
                }
                unset($produkt);
            }

            if (!$found) {
                $produkte[] = array(
                    'id'                        => morshaus_generate_produkt_id($name),
                    'name'                      => $name,
                    'kategorie' => $kategorie,
                    'utensil_typ' => $utensil_typ,
                    'beschreibung' => $beschreibung,
                    'extra_typ'   => $extra_typ,
                    'extra_preis' => $extra_preis,
                    'piktogramm'  => $piktogramm,
                    'ausfuehrungen' => $ausfuehrungen,
                    'show_thumbnails' => $show_thumbnails,
                );
            }

            morshaus_save_produkte($produkte);

            $notice = 'Produkt gespeichert.';
        }
    }

    $produkte     = morshaus_get_produkte();
    $edit_produkt = null;

    if (
        isset($_GET['action'], $_GET['id']) &&
        $_GET['action'] === 'edit'
    ) {
        $edit_produkt = morshaus_get_produkt_by_id(
            sanitize_text_field(
                wp_unslash($_GET['id'])
            )
        );
    }

    ?>

    <div class="wrap">
        <h1>Produkte</h1>

        <?php if ($notice !== '') : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html($notice); ?></p>
            </div>
        <?php endif; ?>

        <style>

            .morshaus-category-note{margin:12px 0;padding:10px 12px;background:#eef4f8;border-left:4px solid #2271b1;}
            .morshaus-ausfuehrung-card { background:#fff; border:1px solid #ccd0d4; padding:16px; margin:16px 0; max-width:980px; }
            .morshaus-card-head { display:flex; gap:10px; justify-content:space-between; align-items:center; margin-bottom:12px; }
            .morshaus-card-title { display:flex; align-items:center; gap:8px; min-width:0; flex:1; }
            .morshaus-card-title input { min-width:220px; }
            .morshaus-card-actions { display:flex; gap:6px; flex-wrap:wrap; }
            .morshaus-collapse-toggle { width:32px; min-width:32px; text-align:center; }
            .morshaus-ausfuehrung-card.is-collapsed > .morshaus-ausfuehrung-body { display:none; }
            .morshaus-modell-card { display:block; grid-template-columns:minmax(260px,1fr) 140px; gap:14px; padding:18px; margin:12px 0; background:#f6f7f7; border-left:4px solid #8c8f94; align-items:start; }
            .morshaus-modell-head { display:flex; gap:10px; justify-content:space-between; align-items:center; margin-bottom:12px; }
            .morshaus-modell-title { display:flex; align-items:center; gap:8px; flex:1; }
            .morshaus-modell-title input { min-width:220px; }
            .morshaus-modell-actions { display:flex; gap:6px; flex-wrap:wrap; }
            .morshaus-modell-card.is-collapsed > .morshaus-modell-body { display:none; }
            .morshaus-modell-body { display:grid; grid-template-columns:minmax(260px,1fr) 140px; gap:14px; align-items:start; }
            .morshaus-beschreibung-field { grid-column:1/-1; display:flex; flex-direction:column; gap:7px; padding:14px; background:#fff; border:1px solid #c3c4c7; border-radius:9px; }
            .morshaus-beschreibung-field strong { font-size:14px; }
            .morshaus-beschreibung-field textarea { width:100%; min-height:96px; height:96px; resize:vertical; font-size:14px; line-height:1.5; }
            .morshaus-extras-admin{grid-column:1/-1;padding:14px 16px;background:#fff;border:1px solid #dcdcde;border-radius:9px}
            .morshaus-extras-head{display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:12px}
            .morshaus-extras-head strong{font-size:14px}
            .morshaus-varianten-extra-typ{display:flex;align-items:center;gap:16px;flex-wrap:wrap}
            .morshaus-varianten-extra-typ label{display:inline-flex;align-items:center;gap:6px;margin:0}
            .morshaus-veredelung-options{display:grid;grid-template-columns:repeat(4,minmax(210px,1fr));gap:12px;margin:0;padding:0;border:0}
            .morshaus-veredelung-options[hidden]{display:none!important}
            .morshaus-veredelung-option{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:8px;min-width:230px;padding:10px 12px;border:1px solid #dcdcde;border-radius:9px;background:#f6f7f7}
            .morshaus-veredelung-option input[type="text"]{width:70px}.morshaus-branding-admin-option{grid-template-columns:1fr auto}.morshaus-branding-image-slot{grid-column:1/-1;display:flex;align-items:center;gap:8px}.morshaus-branding-image-preview{width:72px;height:72px;border:1px dashed #aaa;background:#fff}.morshaus-branding-image-preview img{width:100%;height:100%;object-fit:cover}
            .morshaus-euro-field{display:inline-flex;align-items:center;gap:6px}
            .morshaus-tee-inhalte-admin{grid-column:1/-1;padding:16px;background:#fff;border:1px solid #c3c4c7;border-radius:9px}.morshaus-tee-inhalte-admin>h3{margin:0 0 14px}.morshaus-tee-inhalte-section{padding:14px 0;border-top:1px solid #dcdcde}.morshaus-tee-inhalte-section:first-of-type{border-top:0;padding-top:0}.morshaus-tee-inhalte-section>strong{display:block;margin-bottom:8px;font-size:14px}.morshaus-editor-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.morshaus-editor-grid.is-utensil{grid-template-columns:1fr}.morshaus-tee-editor{min-width:0}.morshaus-tee-editor .wp-editor-area{min-height:220px!important}@media(max-width:1100px){.morshaus-editor-grid{grid-template-columns:1fr}}
            .morshaus-aufguss-row{display:flex;align-items:end;flex-wrap:wrap;gap:10px;margin:12px 0}.morshaus-aufguss-number{min-width:85px;align-self:center}.morshaus-aufguss-row label{display:flex;flex-direction:column;gap:4px}.morshaus-aufguss-row input{width:150px;max-width:100%}
            .morshaus-galerie-preview, .morshaus-detailgalerie-preview { grid-column:1/-1; display:flex; gap:8px; flex-wrap:wrap; }
            .morshaus-galerie-preview img, .morshaus-detailgalerie-preview img { width:64px; height:64px; object-fit:cover; border:1px solid #ccd0d4; }
            .morshaus-merkmal-row { display:grid; grid-template-columns:190px 1fr 1fr auto minmax(240px,1.4fr); gap:10px; margin:8px 0; align-items:center; }
            .morshaus-tooltip-toggle{display:inline-flex;align-items:center;gap:5px;white-space:nowrap}.morshaus-tooltip-text{width:100%}
            .morshaus-tee-mengen-only { grid-column:1/-1; }
            .morshaus-menge-row { display:grid; grid-template-columns:100px 140px max-content max-content; gap:10px; align-items:center; justify-content:start; margin:8px 0; }

            /* 0.5.1-build.1: Ampelstatus und Drag & Drop mit separatem Griff */
            .morshaus-status-symbol{display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;margin-left:2px;font-size:13px;line-height:1}
            .morshaus-drag-handle{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;margin-left:2px;border:1px solid #c3c4c7;border-radius:4px;background:#fff;color:#646970;font-size:18px;line-height:1;cursor:grab;user-select:none}
            .morshaus-drag-handle:hover{border-color:#8c8f94;color:#1d2327;background:#f6f7f7}
            .morshaus-drag-handle:active{cursor:grabbing}
            .morshaus-sort-placeholder{min-height:56px;border:2px dashed #8c8f94;border-radius:6px;background:#f6f7f7;margin:8px 0}
            .morshaus-galerie-preview img,.morshaus-detailgalerie-preview img{cursor:grab}
            .morshaus-galerie-preview img:active,.morshaus-detailgalerie-preview img:active{cursor:grabbing}
        </style>

        <h2>
            <?php echo $edit_produkt
                ? 'Produkt bearbeiten'
                : 'Neues Produkt anlegen'; ?>
        </h2>

        <form method="post">
            <?php wp_nonce_field(
                'morshaus_save_produkt',
                'morshaus_produkt_nonce'
            ); ?>

            <input
                type="hidden"
                name="morshaus_produkt_original_id"
                value="<?php echo esc_attr(
                    $edit_produkt ? $edit_produkt['id'] : ''
                ); ?>"
            >

            <table class="form-table">
                <tr>
                    <th>
                        <label for="morshaus_produkt_name">
                            Name
                        </label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="morshaus_produkt_name"
                            name="morshaus_produkt_name"
                            class="regular-text"
                            value="<?php echo esc_attr(
                                $edit_produkt
                                    ? $edit_produkt['name']
                                    : ''
                            ); ?>"
                            required
                        >
                    </td>
                </tr>
                <tr>
                    <th><label for="morshaus_produkt_kategorie">Produktkategorie</label></th>
                    <td><select id="morshaus_produkt_kategorie" name="morshaus_produkt_kategorie">
                        <?php $akt_kategorie = $edit_produkt && !empty($edit_produkt['kategorie']) ? $edit_produkt['kategorie'] : 'tee'; ?>
                        <option value="tee" <?php selected($akt_kategorie,'tee'); ?>>Tee</option>
                        <option value="utensil" <?php selected($akt_kategorie,'utensil'); ?>>Utensil</option>
                    </select><p class="description">Das Produkt- und Bestellpiktogramm wird automatisch gewählt.</p></td>
                </tr>
                <tr id="morshaus-utensil-typ-row">
                    <th><label for="morshaus_utensil_typ">Utensiltyp</label></th>
                    <td><select id="morshaus_utensil_typ" name="morshaus_utensil_typ">
                        <?php $akt_typ = $edit_produkt && !empty($edit_produkt['utensil_typ']) ? $edit_produkt['utensil_typ'] : 'loeffel'; ?>
                        <option value="thermometer" <?php selected($akt_typ,'thermometer'); ?>>Thermometer</option>
                        <option value="loeffel" <?php selected($akt_typ,'loeffel'); ?>>Löffel</option>
                        <option value="untersetzer" <?php selected($akt_typ,'untersetzer'); ?>>Untersetzer</option>
                        <option value="tablett" <?php selected($akt_typ,'tablett'); ?>>Tablett</option>
                        <option value="zubehoer" <?php selected($akt_typ,'zubehoer'); ?>>Zubehör</option>
                        <option value="gefaess" <?php selected($akt_typ,'gefaess'); ?>>Gefäß</option>
                    </select></td>
                </tr>
                <tr>
                    <th>Galerie auf der Produktseite</th>
                    <td>
                        <label>
                            <input type="checkbox" name="morshaus_show_thumbnails" value="1" <?php checked(!$edit_produkt || !isset($edit_produkt['show_thumbnails']) || !empty($edit_produkt['show_thumbnails'])); ?>>
                            Varianten-Thumbnails auf der Produktseite anzeigen
                        </label>
                        <p class="description">Deaktiviert nur die Varianten-Thumbnails. Produktbilder und Zoom-/Detailgalerie bleiben erhalten. Die Bilderauswahl unter dem Hauptbild erscheint nur ohne Variantenauswahl.</p>
                    </td>
                </tr>
            </table>

            <hr>
            <h2>Ausführungen und Varianten</h2>
            <p class="description">
                Die Felder werden anhand der Produktkategorie vorkonfiguriert. Varianten werden über die Thumbnails ausgewählt.
            </p>
            <?php
            $form_ausfuehrungen = $edit_produkt && !empty($edit_produkt['ausfuehrungen'])
                ? $edit_produkt['ausfuehrungen'] : array();
            ?>
            <div id="morshaus-ausfuehrungen-wrap">
                <?php foreach ($form_ausfuehrungen as $a_index => $ausfuehrung) : ?>
                    <div class="morshaus-ausfuehrung-card" data-a-index="<?php echo esc_attr($a_index); ?>">
                        <div class="morshaus-card-head">
                            <div class="morshaus-card-title"><button type="button" class="button morshaus-collapse-toggle" aria-expanded="true">▾</button><input type="text" class="regular-text morshaus-ausfuehrung-name" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][name]" value="<?php echo esc_attr(morshaus_pilot_edition_name($ausfuehrung['name'], $akt_kategorie)); ?>" placeholder="Ausführung, z. B. Alltag"></div>
                            <div class="morshaus-card-actions"><button type="button" class="button morshaus-duplicate-ausfuehrung">Duplizieren</button><button type="button" class="button morshaus-remove-ausfuehrung">Entfernen</button></div>
                        </div>
                        <div class="morshaus-ausfuehrung-body">
                        <div class="morshaus-modelle-wrap">
                            <?php foreach ($ausfuehrung['modelle'] as $m_index => $modell) : ?>
                                <div class="morshaus-modell-card">
                                    <div class="morshaus-modell-head"><div class="morshaus-modell-title"><button type="button" class="button morshaus-collapse-toggle" aria-expanded="true">▾</button><input type="text" class="morshaus-modell-name" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][name]" value="<?php echo esc_attr($modell['name']); ?>" placeholder="Variantenname"></div><div class="morshaus-modell-actions"><button type="button" class="button morshaus-duplicate-modell">Duplizieren</button><button type="button" class="button morshaus-remove-modell">Entfernen</button></div></div>
                                    <div class="morshaus-modell-body">
                                    <input type="text" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][preis]" value="<?php echo esc_attr(str_replace('.', ',', $modell['preis'])); ?>" placeholder="Preis" class="morshaus-varianten-grundpreis">
                                    <div class="morshaus-tee-mengen-only"><h3>Mengen und Preise</h3><div class="morshaus-mengen-wrap">
                                    <?php $mqm=array(); foreach (($modell['mengen'] ?? ($ausfuehrung['mengen'] ?? array())) as $q) $mqm[$q['name']]=$q; foreach (array('10g','50g','100g') as $qname) : $q=$mqm[$qname] ?? array(); ?>
                                    <div class="morshaus-menge-row"><label><input type="checkbox" class="morshaus-menge-checkbox" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][mengen][<?php echo esc_attr($qname); ?>][aktiv]" value="1" <?php checked(!empty($q)); ?>> <?php echo esc_html($qname); ?></label><input type="text" class="morshaus-menge-preis" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][mengen][<?php echo esc_attr($qname); ?>][preis]" value="<?php echo esc_attr(!empty($q)?str_replace('.',',',$q['preis']):''); ?>" placeholder="Preis" <?php disabled(empty($q)); ?>><label><input type="checkbox" class="morshaus-menge-unverfuegbar" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][mengen][<?php echo esc_attr($qname); ?>][unverfuegbar]" value="1" <?php checked(!empty($q['unverfuegbar'])); ?> <?php disabled(empty($q)); ?>> unverfügbar</label><?php if (in_array($qname,array('50g','100g'),true)) : ?><label><input type="checkbox" class="morshaus-menge-aromaschutz" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][mengen][<?php echo esc_attr($qname); ?>][aromaschutzbeutel]" value="1" <?php checked(!empty($q['aromaschutzbeutel'])); ?> <?php disabled(empty($q)); ?>> Aromaschutzbeutel</label><?php endif; ?></div>
                                    <?php endforeach; ?></div></div>
                                    <label class="morshaus-beschreibung-field"><strong>Beschreibung</strong><textarea name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][beschreibung]" placeholder="Produktbeschreibung"><?php echo esc_textarea($modell['beschreibung']); ?></textarea></label>
                                    <?php $geschmack = isset($modell['geschmack']) ? $modell['geschmack'] : ''; $wissenswertes = isset($modell['wissenswertes']) ? $modell['wissenswertes'] : ''; $zubereitung = isset($modell['zubereitung']) ? $modell['zubereitung'] : ''; ?>
                                    <input type="hidden" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][zubereitung]" value="<?php echo esc_attr($zubereitung); ?>">
                                    <div class="morshaus-tee-inhalte-admin"><h3>Produktinformationen</h3>
                                        <div class="morshaus-tee-inhalte-section"><strong>Charakter</strong><?php morshaus_render_merkmale_admin('morshaus_ausfuehrungen[' . $a_index . '][modelle][' . $m_index . ']', isset($modell['merkmale']) ? $modell['merkmale'] : array()); ?></div>
                                        <div class="morshaus-tee-inhalte-section morshaus-editor-grid">
                                            <div class="morshaus-tee-editor morshaus-tee-detailfelder"><strong>Geschmack</strong><?php $editor_id='morshaus_geschmack_'.$a_index.'_'.$m_index; wp_editor($geschmack,$editor_id,array('textarea_name'=>'morshaus_ausfuehrungen['.$a_index.'][modelle]['.$m_index.'][geschmack]','textarea_rows'=>10,'media_buttons'=>false,'teeny'=>false,'quicktags'=>true)); ?></div>
                                            <div class="morshaus-tee-editor">
                                                <strong>Zusätzlicher Reiter</strong>
                                                <p><label>Reitername <input type="text" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][wissenswertes_titel]" value="<?php echo esc_attr($modell['wissenswertes_titel'] ?? ''); ?>" placeholder="Wissenswertes"></label></p>
                                                <p><label><input type="checkbox" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][wissenswertes_aktiv]" value="1" <?php checked(isset($modell['wissenswertes_aktiv']) ? !empty($modell['wissenswertes_aktiv']) : trim($wissenswertes) !== ''); ?>> Reiter auf der Produktseite anzeigen</label></p>
                                                <?php $editor_id='morshaus_wissenswertes_'.$a_index.'_'.$m_index; wp_editor($wissenswertes,$editor_id,array('textarea_name'=>'morshaus_ausfuehrungen['.$a_index.'][modelle]['.$m_index.'][wissenswertes]','textarea_rows'=>10,'media_buttons'=>false,'teeny'=>false,'quicktags'=>true)); ?>
                                            </div>
                                        </div>
                                        <?php morshaus_render_aufguesse_admin('morshaus_ausfuehrungen[' . $a_index . '][modelle][' . $m_index . ']', morshaus_sanitize_aufguesse($modell['aufguesse'] ?? array())); ?>
                                    </div>
                                    <div class="morshaus-extras-admin">
                                        <div class="morshaus-extras-head"><strong>Extras</strong><span class="description">Jede Karte aktiviert das Extra und enthält Preis sowie Bild.</span></div>
                                        <?php $modell_extra_typen = !empty($modell['extra_typen']) && is_array($modell['extra_typen']) ? $modell['extra_typen'] : (!empty($modell['extra_typ']) ? array($modell['extra_typ']) : array()); ?>
                                        <fieldset class="morshaus-veredelung-options">
                                            <?php foreach (array('dose'=>'Dose','klammer'=>'Klammer') as $extra_key=>$extra_label) : $extra_bild_id=absint($modell['extra_bilder'][$extra_key]??0); $extra_thumb=$extra_bild_id?wp_get_attachment_image_url($extra_bild_id,'thumbnail'):''; ?>
                                            <div class="morshaus-veredelung-option morshaus-branding-admin-option"><label><input type="checkbox" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][extra_typen][]" value="<?php echo esc_attr($extra_key); ?>" <?php checked(in_array($extra_key,$modell_extra_typen,true)); ?>> <span><?php echo esc_html($extra_label); ?></span></label><span class="morshaus-euro-field"><input type="text" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][extra_preise][<?php echo esc_attr($extra_key); ?>]" value="<?php echo esc_attr(str_replace('.',',',$modell['extra_preise'][$extra_key]??($extra_key==='klammer'?'0.50':'0.00'))); ?>"><span>€</span></span><div class="morshaus-branding-image-slot"><input type="hidden" class="morshaus-branding-image-id" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][extra_bilder][<?php echo esc_attr($extra_key); ?>]" value="<?php echo esc_attr($extra_bild_id); ?>"><div class="morshaus-branding-image-preview"><?php if($extra_thumb): ?><img src="<?php echo esc_url($extra_thumb); ?>" alt=""><?php endif; ?></div><button type="button" class="button morshaus-select-branding-image">Bild wählen</button><button type="button" class="button-link-delete morshaus-remove-branding-image" <?php echo $extra_bild_id?'':'hidden'; ?>>Entfernen</button></div></div>
                                            <?php endforeach; ?>
                                            <?php foreach (array('teeblatt'=>'Teeblatt','morshaus'=>'Morshaus') as $branding_key=>$branding_label) : $branding_bild_id=absint($modell['branding_bilder'][$branding_key]??0); $branding_thumb=$branding_bild_id?wp_get_attachment_image_url($branding_bild_id,'thumbnail'):''; ?>
                                            <div class="morshaus-veredelung-option morshaus-branding-admin-option"><label><input type="checkbox" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][branding_arten][]" value="<?php echo esc_attr($branding_key); ?>" <?php checked(in_array($branding_key,(array)($modell['branding_arten']??array()),true)); ?>> <span><?php echo esc_html($branding_label); ?></span></label><span class="morshaus-euro-field"><input type="text" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][branding_preise][<?php echo esc_attr($branding_key); ?>]" value="<?php echo esc_attr(str_replace('.',',',$modell['branding_preise'][$branding_key]??'0.00')); ?>"><span>€</span></span><div class="morshaus-branding-image-slot"><input type="hidden" class="morshaus-branding-image-id" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][branding_bilder][<?php echo esc_attr($branding_key); ?>]" value="<?php echo esc_attr($branding_bild_id); ?>"><div class="morshaus-branding-image-preview"><?php if($branding_thumb): ?><img src="<?php echo esc_url($branding_thumb); ?>" alt=""><?php endif; ?></div><button type="button" class="button morshaus-select-branding-image">Bild wählen</button><button type="button" class="button-link-delete morshaus-remove-branding-image" <?php echo $branding_bild_id?'':'hidden'; ?>>Entfernen</button></div></div>
                                            <?php endforeach; ?>
                                        </fieldset>
                                    </div>
                                    <input type="hidden" class="morshaus-galerie-ids" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][galerie]" value="<?php echo esc_attr(implode(',', $modell['galerie'])); ?>">
                                    <div class="morshaus-galerie-preview">
                                        <?php foreach ($modell['galerie'] as $bild_id) : $thumb = wp_get_attachment_image_url($bild_id, 'thumbnail'); if ($thumb) : ?>
                                            <img src="<?php echo esc_url($thumb); ?>" alt="">
                                        <?php endif; endforeach; ?>
                                    </div>
                                    <button type="button" class="button morshaus-select-gallery">Varianten-/Vorschaubilder wählen</button>
                                    <input type="hidden" class="morshaus-detailgalerie-ids" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][detailgalerie]" value="<?php echo esc_attr(implode(',', isset($modell['detailgalerie']) ? $modell['detailgalerie'] : array())); ?>">
                                    <div class="morshaus-detailgalerie-preview">
                                        <?php foreach ((isset($modell['detailgalerie']) ? $modell['detailgalerie'] : array()) as $bild_id) : $thumb = wp_get_attachment_image_url($bild_id, 'thumbnail'); if ($thumb) : ?>
                                            <img src="<?php echo esc_url($thumb); ?>" alt="">
                                        <?php endif; endforeach; ?>
                                    </div>
                                    <button type="button" class="button morshaus-select-detailgallery">Zoom-/Detailbilder wählen (Mehrfachauswahl)</button>
                                    <label><input type="checkbox" name="morshaus_ausfuehrungen[<?php echo esc_attr($a_index); ?>][modelle][<?php echo esc_attr($m_index); ?>][unverfuegbar]" value="1" <?php checked(!empty($modell['unverfuegbar'])); ?>> unverfügbar</label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="button morshaus-add-modell">+ Variante hinzufügen</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <p><button type="button" class="button" id="morshaus-add-ausfuehrung">+ Ausführung hinzufügen</button></p>

            <div class="notice notice-info inline"><p><strong>Extras:</strong> Dose, Klammer und Branding können bei jeder Variante unabhängig gewählt werden.</p></div>
            <input type="hidden" id="morshaus_extra_typ" name="morshaus_extra_typ" value="">
            <input type="hidden" id="morshaus_extra_preis" name="morshaus_extra_preis" value="<?php echo esc_attr($edit_produkt && isset($edit_produkt['extra_preis']) ? str_replace('.', ',', $edit_produkt['extra_preis']) : '0,00'); ?>">
            <p class="submit">
                <button
                    type="submit"
                    class="button button-primary"
                >
                    <?php echo $edit_produkt
                        ? 'Speichern'
                        : 'Produkt anlegen'; ?>
                </button>

                <?php if ($edit_produkt) : ?>
                    <a
                        href="<?php echo esc_url(
                            admin_url('admin.php?page=morshaus-produkte')
                        ); ?>"
                        class="button"
                    >
                        Abbrechen
                    </a>
                <?php endif; ?>
            </p>
        </form>

        <hr>

        <h2>Vorhandene Produkte</h2>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Varianten</th>
                    <th>Extras</th>
                    <th>Shortcode</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($produkte)) : ?>
                    <tr>
                        <td colspan="5">
                            Noch keine Produkte angelegt.
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($produkte as $produkt) : ?>
                        <tr>
                            <td>
                                <?php echo esc_html(
                                    isset($produkt['name'])
                                        ? $produkt['name']
                                        : ''
                                ); ?>
                            </td>
                            <td>
                                <?php
                                $parts = array();
                                foreach ((array) ($produkt['ausfuehrungen'] ?? array()) as $ausfuehrung) {
                                    $models = array();
                                    foreach ((array) ($ausfuehrung['modelle'] ?? array()) as $modell) {
                                        $modell_name = isset($modell['name']) ? (string) $modell['name'] : '';
                                        if ($modell_name === '') continue;
                                        if (!empty($modell['unverfuegbar'])) $modell_name .= ' - unverfügbar -';
                                        $models[] = esc_html($modell_name);
                                    }
                                    if (!$models) continue;
                                    $edition_name = morshaus_pilot_edition_name($ausfuehrung['name'] ?? '', $produkt['kategorie'] ?? '');
                                    $parts[] = '<strong>' . esc_html($edition_name !== '' ? $edition_name : 'Standard') . '</strong><br>' . implode('<br>', $models);
                                }
                                echo implode('<br><br>', $parts);
                                ?>
                            </td>
                            <td>
                                <?php
                                $produkt_extra_typ = isset($produkt['extra_typ']) ? $produkt['extra_typ'] : '';
                                $produkt_extra_preis = isset($produkt['extra_preis']) && is_numeric($produkt['extra_preis']) ? (float) $produkt['extra_preis'] : 0;
                                if ($produkt_extra_typ === 'dose') echo 'Dose: 50g +3,50 € / 100g +5,00 €';
                                elseif ($produkt_extra_typ === 'branding') echo esc_html('Branding (+' . number_format($produkt_extra_preis, 2, ',', '.') . ' €)');
                                else echo '–';
                                ?>
                            </td>
                            <td>
                                <code>[morshaus_produkt id="<?php echo esc_attr(
                                    $produkt['id']
                                ); ?>"]</code>
                            </td>
                            <td>
                                <a
                                    href="<?php echo esc_url(
                                        admin_url(
                                            'admin.php?page=morshaus-produkte&action=edit&id=' .
                                            rawurlencode($produkt['id'])
                                        )
                                    ); ?>"
                                    class="button button-small"
                                >
                                    Bearbeiten
                                </a>

                                <a
                                    href="<?php echo esc_url(
                                        wp_nonce_url(
                                            admin_url(
                                                'admin.php?page=morshaus-produkte&action=delete&id=' .
                                                rawurlencode($produkt['id'])
                                            ),
                                            'morshaus_delete_produkt'
                                        )
                                    ); ?>"
                                    class="button button-small"
                                    onclick="return confirm('Produkt wirklich löschen?');"
                                >
                                    Löschen
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const categorySelect=document.getElementById('morshaus_produkt_kategorie');
                const utensilSelect=document.getElementById('morshaus_utensil_typ');
                const utensilRow=document.getElementById('morshaus-utensil-typ-row');
                const categoryConfigs={
                    tee:{extra:'dose',editions:['Alltag','Erlesen'],features:[['standort','Herkunft'],['blatt','Kultivar'],['kalender','Ernte'],['wellen','Dämpfung / Verarbeitung']]},
                    loeffel:{extra:'branding',editions:['Einfach','Besonders','Matcha'],features:[['lineal','Länge'],['blatt','Holzart'],['schichten','Fassung'],['funkeln','Besonderheit']]},
                    thermometer:{extra:'branding',editions:['Standard'],features:[['lineal','Maße'],['material','Holzart']]},
                    untersetzer:{extra:'branding',editions:['Tasse','Kanne'],features:[['lineal','Maße'],['funkeln','Besonderheit']]},
                    tablett:{extra:'branding',editions:['Standard'],features:[['lineal','Maße'],['funkeln','Besonderheit']]},
                    gefaess:{extra:'',editions:[],features:[['lineal','Maße'],['gefaess','Füllmenge'],['funkeln','Glasur']]},
                    zubehoer:{extra:'',editions:[],features:[]}
                };
                function activeConfig(){return categorySelect.value==='tee'?categoryConfigs.tee:(categoryConfigs[utensilSelect.value]||categoryConfigs.zubehoer);}
                function applyFeaturePreset(scope){const cfg=activeConfig();scope.querySelectorAll('.morshaus-merkmal-row').forEach((row,i)=>{const preset=cfg.features[i]; if(!preset)return; const sel=row.querySelector('select');const label=row.querySelector('input[name$="[label]"]');if(sel)sel.value=preset[0];if(label&&!label.value)label.value=preset[1];});}
                function updateExtraFields(card, applyDefault) {
                    const cfg=activeConfig();
                    const group=card.querySelector('.morshaus-varianten-extra-typ');
                    const branding=card.querySelector('.morshaus-veredelung-options');
                    if(!group)return;
                    if(applyDefault || group.dataset.autoDefault==='1') {
                        group.querySelectorAll('input[type="checkbox"]').forEach(function(input){
                            input.checked=Boolean(cfg.extra) && input.value===cfg.extra;
                        });
                        delete group.dataset.autoDefault;
                    }
                    const brandingCheckbox=group.querySelector('input[value="branding"]');
                    if(branding) branding.hidden=false;
                }
                function refreshCategoryUi(applyExtraDefaults){const isTea=categorySelect.value==='tee';utensilRow.style.display=isTea?'none':'';document.querySelectorAll('.morshaus-tee-mengen-only,.morshaus-tee-detailfelder').forEach(el=>el.style.display=isTea?'':'none');document.querySelectorAll('.morshaus-editor-grid').forEach(el=>el.classList.toggle('is-utensil',!isTea));document.querySelectorAll('.morshaus-modell-card').forEach(function(card){applyFeaturePreset(card);updateExtraFields(card,Boolean(applyExtraDefaults));});}
                categorySelect.addEventListener('change',function(){refreshCategoryUi(true);});
                utensilSelect.addEventListener('change',function(){refreshCategoryUi(true);});
                refreshCategoryUi(false);
                const extraTyp = document.getElementById('morshaus_extra_typ');
                const extraPreisRow = document.getElementById('morshaus-extra-preis-row');
                function toggleExtraPreisRow() { if (extraTyp && extraPreisRow) extraPreisRow.style.display = extraTyp.value === 'branding' ? '' : 'none'; }
                if (extraTyp && extraPreisRow) { extraTyp.addEventListener('change', toggleExtraPreisRow); toggleExtraPreisRow(); }

                const ausfWrap = document.getElementById('morshaus-ausfuehrungen-wrap');
                const addAusf = document.getElementById('morshaus-add-ausfuehrung');
                function aufguesseHtml(prefix) {
                    return `<div class="morshaus-tee-inhalte-section morshaus-tee-mengen-only morshaus-aufguesse"><strong>Aufgüsse</strong><p class="description">Temperatur in Grad und Ziehzeit in Sekunden: nur Zahlen eingeben. Leere Angaben werden nicht angezeigt.</p><input type="hidden" name="${prefix}[aufguesse_marker]" value="1"><div class="morshaus-aufguesse-rows"></div><button type="button" class="button morshaus-add-aufguss">+ Aufguss hinzufügen</button></div>`;
                }
                function reindexAufguesse(section) {
                    section.querySelectorAll('.morshaus-aufguss-row').forEach(function(row, index) {
                        const label = (index + 1) + '. Aufguss';
                        row.querySelector('.morshaus-aufguss-number').textContent = label;
                        row.querySelectorAll('input').forEach(function(input) {
                            input.name = input.name.replace(/\[aufguesse\]\[\d+\]/, '[aufguesse][' + index + ']');
                            if (input.type !== 'hidden') input.setAttribute('aria-label', (input.name.endsWith('[temperatur]') ? 'Temperatur in Grad' : 'Ziehzeit in Sekunden') + ' für ' + label);
                        });
                        row.querySelector('.morshaus-remove-aufguss').setAttribute('aria-label', label + ' entfernen');
                    });
                }
                if (ausfWrap) ausfWrap.querySelectorAll('.morshaus-aufguesse').forEach(reindexAufguesse);
                function merkmaleHtml(prefix) { const icons={lineal:'Lineal',blatt:'Blatt',schichten:'Schichten',funkeln:'Funkeln',thermometer:'Thermometer',standort:'Standort',kalender:'Kalender',wellen:'Wellen / Verarbeitung',ziel:'Ziel / Genauigkeit',material:'Material',gefaess:'Gefäß',kreis:'Kreis / Durchmesser'}; return `<div class="morshaus-merkmale-wrap"><strong>Produktmerkmale</strong><p class="description">Bis zu vier frei benennbare Merkmale.</p>${[0,1,2,3].map(i=>`<div class="morshaus-merkmal-row"><select name="${prefix}[merkmale][${i}][icon]">${Object.entries(icons).map(([k,v])=>`<option value="${k}">${v}</option>`).join('')}</select><input type="text" name="${prefix}[merkmale][${i}][label]" placeholder="Bezeichnung"><input type="text" name="${prefix}[merkmale][${i}][wert]" placeholder="Wert"><label class="morshaus-tooltip-toggle"><input type="checkbox" name="${prefix}[merkmale][${i}][tooltip_aktiv]" value="1"> Tooltip</label><input type="text" class="morshaus-tooltip-text" name="${prefix}[merkmale][${i}][tooltip_text]" placeholder="Text der Hinweisbox"></div>`).join('')}</div>`; }
                function mengenHtml(ai, mi) { return `<div class="morshaus-tee-mengen-only"><h3>Mengen und Preise</h3><div class="morshaus-mengen-wrap">${['10g','50g','100g'].map(m=>`<div class="morshaus-menge-row"><label><input type="checkbox" class="morshaus-menge-checkbox" name="morshaus_ausfuehrungen[${ai}][modelle][${mi}][mengen][${m}][aktiv]" value="1"> ${m}</label><input type="text" class="morshaus-menge-preis" name="morshaus_ausfuehrungen[${ai}][modelle][${mi}][mengen][${m}][preis]" placeholder="Preis" disabled><label><input type="checkbox" class="morshaus-menge-unverfuegbar" name="morshaus_ausfuehrungen[${ai}][modelle][${mi}][mengen][${m}][unverfuegbar]" value="1" disabled> unverfügbar</label>${m==='50g'||m==='100g'?`<label><input type="checkbox" class="morshaus-menge-aromaschutz" name="morshaus_ausfuehrungen[${ai}][modelle][${mi}][mengen][${m}][aromaschutzbeutel]" value="1" disabled> Aromaschutzbeutel</label>`:''}</div>`).join('')}</div></div>`; }
                function modellHtml(ai, mi) {
                    return `<div class="morshaus-modell-card">
                        <div class="morshaus-modell-head"><div class="morshaus-modell-title"><button type="button" class="button morshaus-collapse-toggle" aria-expanded="true">▾</button><input type="text" class="morshaus-modell-name" name="morshaus_ausfuehrungen[${ai}][modelle][${mi}][name]" placeholder="Variantenname"></div><div class="morshaus-modell-actions"><button type="button" class="button morshaus-duplicate-modell">Duplizieren</button><button type="button" class="button morshaus-remove-modell">Entfernen</button></div></div>
                        <div class="morshaus-modell-body"><input type="text" class="morshaus-varianten-grundpreis" name="morshaus_ausfuehrungen[${ai}][modelle][${mi}][preis]" placeholder="Preis">${mengenHtml(ai,mi)}
                        <label class="morshaus-beschreibung-field"><strong>Beschreibung</strong><textarea name="morshaus_ausfuehrungen[${ai}][modelle][${mi}][beschreibung]" placeholder="Produktbeschreibung"></textarea></label><input type="hidden" name="morshaus_ausfuehrungen[${ai}][modelle][${mi}][zubereitung]"><div class="morshaus-tee-inhalte-admin"><h3>Produktinformationen</h3><div class="morshaus-tee-inhalte-section"><strong>Charakter</strong>${merkmaleHtml(`morshaus_ausfuehrungen[${ai}][modelle][${mi}]`)}</div><div class="morshaus-tee-inhalte-section morshaus-editor-grid"><div class="morshaus-tee-editor morshaus-tee-detailfelder"><strong>Geschmack</strong><textarea id="morshaus_geschmack_${ai}_${mi}_${Date.now()}" class="morshaus-wissenswertes-source" name="morshaus_ausfuehrungen[${ai}][modelle][${mi}][geschmack]" rows="10"></textarea></div><div class="morshaus-tee-editor"><strong>Zusätzlicher Reiter</strong><p><label>Reitername <input type="text" name="morshaus_ausfuehrungen[${ai}][modelle][${mi}][wissenswertes_titel]" placeholder="Wissenswertes"></label></p><p><label><input type="checkbox" name="morshaus_ausfuehrungen[${ai}][modelle][${mi}][wissenswertes_aktiv]" value="1"> Reiter auf der Produktseite anzeigen</label></p><textarea id="morshaus_wissenswertes_${ai}_${mi}_${Date.now()}" class="morshaus-wissenswertes-source" name="morshaus_ausfuehrungen[${ai}][modelle][${mi}][wissenswertes]" rows="10"></textarea></div></div>${aufguesseHtml(`morshaus_ausfuehrungen[${ai}][modelle][${mi}]`)}</div>
                        <div class="morshaus-extras-admin"><div class="morshaus-extras-head"><strong>Extras</strong><span class="description">Jede Karte aktiviert das Extra und enthält Preis sowie Bild.</span></div><fieldset class="morshaus-veredelung-options">${[{key:'dose',label:'Dose',group:'extra',price:'0,00'},{key:'klammer',label:'Klammer',group:'extra',price:'0,50'},{key:'teeblatt',label:'Teeblatt',group:'branding',price:'0,00'},{key:'morshaus',label:'Morshaus',group:'branding',price:'0,00'}].map(x=>`<div class="morshaus-veredelung-option morshaus-branding-admin-option"><label><input type="checkbox" name="morshaus_ausfuehrungen[${ai}][modelle][${mi}][${x.group==='extra'?'extra_typen':'branding_arten'}][]" value="${x.key}"> <span>${x.label}</span></label><span class="morshaus-euro-field"><input type="text" name="morshaus_ausfuehrungen[${ai}][modelle][${mi}][${x.group==='extra'?'extra_preise':'branding_preise'}][${x.key}]" value="${x.price}"><span>€</span></span><div class="morshaus-branding-image-slot"><input type="hidden" class="morshaus-branding-image-id" name="morshaus_ausfuehrungen[${ai}][modelle][${mi}][${x.group==='extra'?'extra_bilder':'branding_bilder'}][${x.key}]"><div class="morshaus-branding-image-preview"></div><button type="button" class="button morshaus-select-branding-image">Bild wählen</button><button type="button" class="button-link-delete morshaus-remove-branding-image" hidden>Entfernen</button></div></div>`).join('')}</fieldset></div>
                        <input type="hidden" class="morshaus-galerie-ids" name="morshaus_ausfuehrungen[${ai}][modelle][${mi}][galerie]"><div class="morshaus-galerie-preview"></div><button type="button" class="button morshaus-select-gallery">Varianten-/Vorschaubilder wählen</button>
                        <input type="hidden" class="morshaus-detailgalerie-ids" name="morshaus_ausfuehrungen[${ai}][modelle][${mi}][detailgalerie]"><div class="morshaus-detailgalerie-preview"></div><button type="button" class="button morshaus-select-detailgallery">Zoom-/Detailbilder wählen (Mehrfachauswahl)</button><label><input type="checkbox" name="morshaus_ausfuehrungen[${ai}][modelle][${mi}][unverfuegbar]" value="1"> unverfügbar</label></div></div>`;
                }
                function initWissenswertesEditor(scope) {
                    if (!window.wp || !wp.editor || !wp.editor.initialize) return;
                    (scope || document).querySelectorAll('textarea.morshaus-wissenswertes-source').forEach(function(textarea) {
                        if (!textarea.id || textarea.dataset.editorReady === '1') return;
                        textarea.dataset.editorReady='1';
                        wp.editor.initialize(textarea.id,{tinymce:{wpautop:true,toolbar1:'formatselect,bold,italic,bullist,numlist,link,unlink,undo,redo',block_formats:'Absatz=p;Überschrift 2=h2;Überschrift 3=h3;Überschrift 4=h4'},quicktags:true,mediaButtons:false});
                    });
                }
                function removeEditors(scope) {
                    if (!window.wp || !wp.editor || !wp.editor.remove) return;
                    scope.querySelectorAll('.wp-editor-area[id],textarea.morshaus-wissenswertes-source[id]').forEach(function(field){ try{wp.editor.remove(field.id);}catch(err){} });
                }
                function reindexAdminCards() {
                    ausfWrap.querySelectorAll(':scope > .morshaus-ausfuehrung-card').forEach(function(card, ai) {
                        card.dataset.aIndex=ai;
                        card.querySelectorAll('.morshaus-modell-card').forEach(function(model, mi) {
                            model.querySelectorAll('[name]').forEach(function(field) {
                                field.name=field.name.replace(/morshaus_ausfuehrungen\[\d+\]\[modelle\]\[\d+\]/,`morshaus_ausfuehrungen[${ai}][modelle][${mi}]`);
                            });
                        });
                        card.querySelectorAll('[name]').forEach(function(field) {
                            field.name=field.name.replace(/morshaus_ausfuehrungen\[\d+\](?!\[modelle\])/,`morshaus_ausfuehrungen[${ai}]`);
                        });
                    });
                }
                function copyName(input) { const base=(input.value||'').replace(/\s*\(Kopie(?: \d+)?\)$/,''); input.value=base+' (Kopie)'; }
                if (ausfWrap && addAusf) {
                    addAusf.addEventListener('click', function () {
                        const ai = ausfWrap.querySelectorAll('.morshaus-ausfuehrung-card').length;
                        const card = document.createElement('div'); card.className='morshaus-ausfuehrung-card'; card.dataset.aIndex=ai;
                        card.innerHTML=`<div class="morshaus-card-head"><div class="morshaus-card-title"><button type="button" class="button morshaus-collapse-toggle" aria-expanded="true">▾</button><input type="text" class="regular-text morshaus-ausfuehrung-name" name="morshaus_ausfuehrungen[${ai}][name]" placeholder="Ausführung"></div><div class="morshaus-card-actions"><button type="button" class="button morshaus-duplicate-ausfuehrung">Duplizieren</button><button type="button" class="button morshaus-remove-ausfuehrung">Entfernen</button></div></div><div class="morshaus-ausfuehrung-body"><div class="morshaus-modelle-wrap">${modellHtml(ai,0)}</div><button type="button" class="button morshaus-add-modell">+ Variante hinzufügen</button></div>`;
                        ausfWrap.appendChild(card); applyFeaturePreset(card); refreshCategoryUi(); reindexAdminCards(); initWissenswertesEditor(card);
                    });
                    ausfWrap.addEventListener('click', function(e) {
                        const card=e.target.closest('.morshaus-ausfuehrung-card');
                        if (!card) return;
                        if (e.target.classList.contains('morshaus-add-aufguss')) {
                            const section=e.target.closest('.morshaus-aufguesse');
                            const rows=section.querySelector('.morshaus-aufguesse-rows');
                            const prefix=section.querySelector('input[type="hidden"]').name.replace(/\[aufguesse_marker\]$/, '');
                            const index=rows.children.length;
                            rows.insertAdjacentHTML('beforeend', `<div class="morshaus-aufguss-row"><strong class="morshaus-aufguss-number"></strong><label>Temperatur (°) <input type="text" inputmode="decimal" pattern="[0-9]+([.,][0-9]+)?" title="Bitte nur eine Zahl eingeben." name="${prefix}[aufguesse][${index}][temperatur]" placeholder="z. B. 70"></label><label>Ziehzeit (s) <input type="text" inputmode="decimal" pattern="[0-9]+([.,][0-9]+)?" title="Bitte nur eine Zahl eingeben." name="${prefix}[aufguesse][${index}][dauer]" placeholder="z. B. 60"></label><button type="button" class="button morshaus-remove-aufguss">Entfernen</button></div>`);
                            reindexAufguesse(section);
                            rows.lastElementChild.querySelector('input').focus();
                            return;
                        }
                        if (e.target.classList.contains('morshaus-remove-aufguss')) {
                            const section=e.target.closest('.morshaus-aufguesse');
                            e.target.closest('.morshaus-aufguss-row').remove();
                            reindexAufguesse(section);
                            section.querySelector('.morshaus-add-aufguss').focus();
                            return;
                        }
                        if (e.target.classList.contains('morshaus-collapse-toggle')) { const target=e.target.closest('.morshaus-modell-card') || card; target.classList.toggle('is-collapsed'); const collapsed=target.classList.contains('is-collapsed'); e.target.textContent=collapsed?'▸':'▾'; e.target.setAttribute('aria-expanded',collapsed?'false':'true'); return; }
                        if (e.target.classList.contains('morshaus-duplicate-ausfuehrung')) { if(window.tinyMCE) tinyMCE.triggerSave(); const clone=card.cloneNode(true); clone.querySelectorAll('.wp-editor-wrap').forEach(function(w){const ta=w.querySelector('textarea');if(ta){const fresh=ta.cloneNode(true);fresh.classList.add('morshaus-wissenswertes-source');fresh.dataset.editorReady='';fresh.id=fresh.id+'_copy_'+Date.now();w.replaceWith(fresh);}}); copyName(clone.querySelector('.morshaus-ausfuehrung-name')); clone.classList.remove('is-collapsed'); card.after(clone); reindexAdminCards(); refreshCategoryUi(false); initWissenswertesEditor(clone); return; }
                        if (e.target.classList.contains('morshaus-duplicate-modell')) { if(window.tinyMCE) tinyMCE.triggerSave(); const model=e.target.closest('.morshaus-modell-card'); const clone=model.cloneNode(true); clone.querySelectorAll('.wp-editor-wrap').forEach(function(w){const ta=w.querySelector('textarea');if(ta){const fresh=ta.cloneNode(true);fresh.classList.add('morshaus-wissenswertes-source');fresh.dataset.editorReady='';fresh.id=fresh.id+'_copy_'+Date.now();w.replaceWith(fresh);}}); copyName(clone.querySelector('.morshaus-modell-name')); clone.classList.remove('is-collapsed'); model.after(clone); reindexAdminCards(); refreshCategoryUi(false); initWissenswertesEditor(clone); return; }
                        if (e.target.classList.contains('morshaus-remove-ausfuehrung')) { if(confirm('Ausführung einschließlich aller Varianten wirklich entfernen?')){removeEditors(card);card.remove();reindexAdminCards();} return; }
                        if (e.target.classList.contains('morshaus-remove-modell')) { if(confirm('Variante wirklich entfernen?')){const doomed=e.target.closest('.morshaus-modell-card');removeEditors(doomed);doomed.remove();reindexAdminCards();} return; }
                        if (e.target.classList.contains('morshaus-add-modell')) { const w=card.querySelector('.morshaus-modelle-wrap'); w.insertAdjacentHTML('beforeend',modellHtml(card.dataset.aIndex,w.children.length));const added=w.lastElementChild;applyFeaturePreset(added);refreshCategoryUi(false);reindexAdminCards();initWissenswertesEditor(added);return; }
                        if(e.target.classList.contains('morshaus-select-branding-image')){const slot=e.target.closest('.morshaus-branding-image-slot');const frame=wp.media({title:'Extrabild wählen',button:{text:'Bild übernehmen'},multiple:false});frame.on('select',function(){const x=frame.state().get('selection').first().toJSON();slot.querySelector('.morshaus-branding-image-id').value=x.id;slot.querySelector('.morshaus-branding-image-preview').innerHTML=`<img src="${x.sizes&&x.sizes.thumbnail?x.sizes.thumbnail.url:x.url}" alt="">`;slot.querySelector('.morshaus-remove-branding-image').hidden=false;});frame.open();return;}
                        if(e.target.classList.contains('morshaus-remove-branding-image')){const slot=e.target.closest('.morshaus-branding-image-slot');slot.querySelector('.morshaus-branding-image-id').value='';slot.querySelector('.morshaus-branding-image-preview').innerHTML='';e.target.hidden=true;return;}
                        if (e.target.classList.contains('morshaus-select-gallery')) {
                            const model=e.target.closest('.morshaus-modell-card');
                            const frame=wp.media({title:'Galeriebilder wählen',button:{text:'Galerie übernehmen'},multiple:true});
                            frame.on('select',function(){ const items=frame.state().get('selection').toJSON(); model.querySelector('.morshaus-galerie-ids').value=items.map(x=>x.id).join(','); model.querySelector('.morshaus-galerie-preview').innerHTML=items.map(x=>`<img src="${x.sizes && x.sizes.thumbnail ? x.sizes.thumbnail.url : x.url}" alt="">`).join(''); });
                            frame.open();
                        }
                        if (e.target.classList.contains('morshaus-select-detailgallery')) {
                            const model=e.target.closest('.morshaus-modell-card');
                            const frame=wp.media({title:'Zoom- und Detailbilder wählen',button:{text:'Detailbilder übernehmen'},multiple:true});
                            frame.on('select',function(){ const items=frame.state().get('selection').toJSON(); model.querySelector('.morshaus-detailgalerie-ids').value=items.map(x=>x.id).join(','); model.querySelector('.morshaus-detailgalerie-preview').innerHTML=items.map(x=>`<img src="${x.sizes && x.sizes.thumbnail ? x.sizes.thumbnail.url : x.url}" alt="">`).join(''); });
                            frame.open();
                        }
                    });
                }

                if (ausfWrap) {
                    refreshCategoryUi();
                    ausfWrap.addEventListener('change', function(e) {
                        if (e.target.closest('.morshaus-varianten-extra-typ')) {
                            updateExtraFields(e.target.closest('.morshaus-modell-card'), false);
                            return;
                        }
                        if (!e.target.classList.contains('morshaus-menge-checkbox')) return;
                        const row=e.target.closest('.morshaus-menge-row'); const active=e.target.checked;
                        const price=row.querySelector('.morshaus-menge-preis'); const unavailable=row.querySelector('.morshaus-menge-unverfuegbar'); const aroma=row.querySelector('.morshaus-menge-aromaschutz');
                        price.disabled=!active; unavailable.disabled=!active; if(aroma) aroma.disabled=!active;
                        if(!active){price.value=''; unavailable.checked=false;if(aroma)aroma.checked=false;} else price.focus();
                    });
                }


            }
        );


                /* 0.5.1-build.1: Collapse, Ampelstatus, Speicherwarnung und Sortierung */
                document.addEventListener('DOMContentLoaded', function () {
                    const adminForm = document.querySelector('.wrap form');
                    const adminWrap = document.getElementById('morshaus-ausfuehrungen-wrap');
                    let morshausDirty = false;
                    let morshausBooting = true;

                    function markDirty(){ if(!morshausBooting) morshausDirty=true; }

                    function reindexSortedCards() {
                        if (!adminWrap) return;
                        adminWrap.querySelectorAll(':scope > .morshaus-ausfuehrung-card').forEach(function(card, ai) {
                            card.dataset.aIndex=ai;
                            card.querySelectorAll('.morshaus-modell-card').forEach(function(model, mi) {
                                model.querySelectorAll('[name]').forEach(function(field) {
                                    field.name=field.name.replace(/morshaus_ausfuehrungen\[\d+\]\[modelle\]\[\d+\]/,`morshaus_ausfuehrungen[${ai}][modelle][${mi}]`);
                                });
                            });
                            card.querySelectorAll('[name]').forEach(function(field) {
                                field.name=field.name.replace(/morshaus_ausfuehrungen\[\d+\](?!\[modelle\])/,`morshaus_ausfuehrungen[${ai}]`);
                            });
                        });
                    }

                    function setCollapsed(card, collapsed){
                        card.classList.toggle('is-collapsed', collapsed);
                        const toggle=card.querySelector(':scope > .morshaus-card-head .morshaus-collapse-toggle, :scope > .morshaus-modell-head .morshaus-collapse-toggle');
                        if(toggle){toggle.textContent=collapsed?'▸':'▾';toggle.setAttribute('aria-expanded',collapsed?'false':'true');}
                    }

                    function ensureHeaderTools(card){
                        const isModel=card.classList.contains('morshaus-modell-card');
                        const title=card.querySelector(':scope > .morshaus-card-head .morshaus-card-title, :scope > .morshaus-modell-head .morshaus-modell-title');
                        if(!title)return;
                        let status=title.querySelector('.morshaus-status-symbol');
                        if(isModel && !status){
                            status=document.createElement('span');
                            status.className='morshaus-status-symbol';
                            status.setAttribute('aria-label','Variantenstatus');
                            title.appendChild(status);
                        }
                        if(!isModel && status)status.remove();
                        let handle=title.querySelector('.morshaus-drag-handle');
                        if(!handle){
                            handle=document.createElement('span');
                            handle.className='morshaus-drag-handle';
                            handle.textContent='⋮⋮';
                            handle.setAttribute('role','button');
                            handle.setAttribute('tabindex','0');
                            handle.setAttribute('aria-label',isModel?'Variante verschieben':'Ausführung verschieben');
                            handle.title=isModel?'Variante verschieben':'Ausführung verschieben';
                            title.appendChild(handle);
                        }
                    }

                    function editorValue(card, suffix){
                        const field=card.querySelector('textarea[name$="['+suffix+']"]');
                        if(!field)return '';
                        if(window.tinyMCE && field.id){
                            const editor=window.tinyMCE.get(field.id);
                            if(editor)return String(editor.getContent({format:'text'})||'').trim();
                        }
                        return String(field.value||'').replace(/<[^>]*>/g,' ').trim();
                    }

                    function hasPrice(card){
                        const quantityPrices=Array.from(card.querySelectorAll('.morshaus-menge-row')).some(function(row){
                            const active=row.querySelector('.morshaus-menge-checkbox');
                            const price=row.querySelector('.morshaus-menge-preis');
                            return Boolean(active&&active.checked&&price&&String(price.value||'').trim());
                        });
                        const base=card.querySelector('.morshaus-varianten-grundpreis');
                        return quantityPrices || Boolean(base&&String(base.value||'').trim()&&Number(String(base.value).replace(',','.'))>0);
                    }

                    function hasFeatures(card){
                        return Array.from(card.querySelectorAll('.morshaus-merkmal-row')).some(function(row){
                            const label=row.querySelector('input[name$="[label]"]');
                            const value=row.querySelector('input[name$="[wert]"]');
                            return Boolean(label&&value&&label.value.trim()&&value.value.trim());
                        });
                    }

                    function ensureStatus(card){
                        ensureHeaderTools(card);
                        if(!card.classList.contains('morshaus-modell-card'))return;
                        const status=card.querySelector(':scope > .morshaus-modell-head .morshaus-status-symbol');
                        if(!status)return;
                        const name=card.querySelector(':scope > .morshaus-modell-head .morshaus-modell-name');
                        const gallery=card.querySelector('.morshaus-galerie-ids');
                        const description=card.querySelector('textarea[name$="[beschreibung]"]');
                        const coreComplete=Boolean(name&&name.value.trim()) && hasPrice(card) && Boolean(gallery&&gallery.value.trim()) && Boolean(description&&description.value.trim());
                        const knowledgeEnabled=card.querySelector('input[name$="[wissenswertes_aktiv]"]');
                        const infusionsComplete=Array.from(card.querySelectorAll('.morshaus-aufguss-row')).some(row=>Array.from(row.querySelectorAll('input')).some(input=>input.value.trim()));
                        const fullyComplete=coreComplete && hasFeatures(card) && Boolean(editorValue(card,'geschmack')) && (!knowledgeEnabled || !knowledgeEnabled.checked || Boolean(editorValue(card,'wissenswertes'))) && infusionsComplete;
                        if(fullyComplete){
                            status.textContent='🟢';
                            status.title='Vollständig: Name, Preis, Bild, Beschreibung, Charakter, Geschmack, Wissenswertes und Zubereitung vorhanden';
                        }else if(coreComplete){
                            status.textContent='🟡';
                            status.title='Grunddaten vollständig; Charakter, Geschmack, Wissenswertes oder Zubereitung fehlen';
                        }else{
                            status.textContent='🔴';
                            status.title='Unvollständig: Name, Preis, Bild oder Beschreibung fehlt';
                        }
                    }

                    function refreshStatuses(){
                        document.querySelectorAll('.morshaus-ausfuehrung-card,.morshaus-modell-card').forEach(function(card){ensureHeaderTools(card);ensureStatus(card);});
                    }

                    function labelPreviewImages(preview){
                        if(!preview)return;
                        const hidden=preview.previousElementSibling;
                        const ids=hidden&&hidden.matches('input[type="hidden"]')?hidden.value.split(',').filter(Boolean):[];
                        preview.querySelectorAll('img').forEach((img,i)=>{if(ids[i])img.dataset.attachmentId=ids[i];});
                    }

                    function syncPreviewOrder(preview){
                        const hidden=preview.previousElementSibling;
                        if(!hidden||!hidden.matches('input[type="hidden"]'))return;
                        hidden.value=Array.from(preview.querySelectorAll('img')).map(img=>img.dataset.attachmentId||'').filter(Boolean).join(',');
                        hidden.dispatchEvent(new Event('change',{bubbles:true}));
                    }

                    function initSortables(scope=document){
                        if(!window.jQuery||!jQuery.fn.sortable)return;
                        const $=jQuery;
                        $(scope).find('#morshaus-ausfuehrungen-wrap').addBack('#morshaus-ausfuehrungen-wrap').each(function(){
                            if($(this).data('mh-sort'))return;
                            $(this).data('mh-sort',1).sortable({items:'> .morshaus-ausfuehrung-card',handle:'.morshaus-drag-handle',placeholder:'morshaus-sort-placeholder',forcePlaceholderSize:true,update:function(){reindexSortedCards();markDirty();refreshStatuses();}});
                        });
                        $(scope).find('.morshaus-modelle-wrap').addBack('.morshaus-modelle-wrap').each(function(){
                            if($(this).data('mh-sort'))return;
                            $(this).data('mh-sort',1).sortable({items:'> .morshaus-modell-card',handle:'.morshaus-drag-handle',connectWith:'.morshaus-modelle-wrap',placeholder:'morshaus-sort-placeholder',forcePlaceholderSize:true,update:function(){reindexSortedCards();markDirty();refreshStatuses();}});
                        });
                        $(scope).find('.morshaus-galerie-preview,.morshaus-detailgalerie-preview').addBack('.morshaus-galerie-preview,.morshaus-detailgalerie-preview').each(function(){
                            labelPreviewImages(this);
                            if($(this).data('mh-sort'))return;
                            $(this).data('mh-sort',1).sortable({items:'> img',placeholder:'morshaus-sort-placeholder',update:function(){syncPreviewOrder(this);markDirty();refreshStatuses();}});
                        });
                    }

                    function applyInitialCollapse(){
                        const editions=Array.from(document.querySelectorAll('#morshaus-ausfuehrungen-wrap > .morshaus-ausfuehrung-card'));
                        editions.forEach(function(edition){
                            setCollapsed(edition,true);
                            edition.querySelectorAll('.morshaus-modell-card').forEach(model=>setCollapsed(model,true));
                        });
                        if(editions.length===1)setCollapsed(editions[0],false);
                        editions.forEach(function(edition){
                            const editionName=edition.querySelector(':scope > .morshaus-card-head .morshaus-ausfuehrung-name');
                            const models=Array.from(edition.querySelectorAll(':scope > .morshaus-ausfuehrung-body > .morshaus-modelle-wrap > .morshaus-modell-card'));
                            const unnamed=Boolean(editionName&&!editionName.value.trim());
                            if(unnamed){
                                setCollapsed(edition,false);
                                if(models[0])setCollapsed(models[0],false);
                            }else if(models.length===1){
                                setCollapsed(models[0],false);
                            }
                        });
                    }

                    applyInitialCollapse();
                    refreshStatuses();
                    initSortables();

                    const galleryObserver=new MutationObserver(function(mutations){
                        mutations.forEach(function(mutation){
                            const preview=mutation.target.closest&&mutation.target.closest('.morshaus-galerie-preview,.morshaus-detailgalerie-preview');
                            if(preview){labelPreviewImages(preview);initSortables(preview);markDirty();refreshStatuses();}
                        });
                    });
                    document.querySelectorAll('.morshaus-galerie-preview,.morshaus-detailgalerie-preview').forEach(function(preview){galleryObserver.observe(preview,{childList:true});});

                    requestAnimationFrame(()=>{morshausBooting=false;});
                    document.addEventListener('input',function(e){if(e.target.closest('#morshaus-ausfuehrungen-wrap')){markDirty();refreshStatuses();}});
                    document.addEventListener('change',function(e){if(e.target.closest('.wrap')){markDirty();refreshStatuses();}});
                    document.addEventListener('click',function(e){
                        if(e.target.closest('.morshaus-add-modell,#morshaus-add-ausfuehrung,.morshaus-duplicate-ausfuehrung,.morshaus-duplicate-modell')){
                            setTimeout(function(){
                                document.querySelectorAll('.morshaus-ausfuehrung-card,.morshaus-modell-card').forEach(card=>{if(!card.dataset.mhKnown){card.dataset.mhKnown='1';setCollapsed(card,false);}});
                                refreshStatuses();initSortables();markDirty();
                            },0);
                        }
                        if(e.target.closest('.morshaus-remove-ausfuehrung,.morshaus-remove-modell,.morshaus-remove-branding-image,.morshaus-select-gallery,.morshaus-select-detailgallery,.morshaus-add-aufguss,.morshaus-remove-aufguss'))setTimeout(function(){refreshStatuses();initSortables();markDirty();},50);
                    });
                    document.querySelectorAll('.morshaus-ausfuehrung-card,.morshaus-modell-card').forEach(card=>card.dataset.mhKnown='1');
                    if(adminForm)adminForm.addEventListener('submit',function(){if(window.tinyMCE)window.tinyMCE.triggerSave();morshausDirty=false;});
                    window.addEventListener('beforeunload',function(e){if(!morshausDirty)return;e.preventDefault();e.returnValue='';});
                });
    </script>

    <?php
}


function morshaus_render_bestellliste_button() {
    ?>

    <div
        class="wp-block-buttons is-layout-flex"
        style="justify-content:center;margin-top:28px;"
    >
        <div class="wp-block-button">
            <a
                class="wp-block-button__link wp-element-button"
                href="<?php echo esc_url(
                    home_url('/bestellliste/')
                ); ?>"
            >
                Bestellliste
            </a>
        </div>
    </div>

    <?php
}