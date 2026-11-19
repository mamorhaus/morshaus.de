<?php
/**
 * Morshaus Child Theme - Veranstaltungen, Post Types und Taxonomien
 * Version 0.12.3
 * Strukturpflege: ersetzte Darstellungs-Generationen und leere Alt-Hooks entfernt;
 * aktive Fachlogik, Interaktionen und der konsolidierte Endzustand bleiben erhalten.
 */

/* ==============================================================
 * Post Types und Taxonomien
 * ============================================================== */

function morshaus_register_content_types() {

    register_post_type(
        'veranstaltung',
        array(
            'labels' => array(
                'name'               => 'Veranstaltungen',
                'singular_name'      => 'Veranstaltung',
                'menu_name'          => 'Veranstaltungen',
                'name_admin_bar'     => 'Veranstaltung',
                'add_new'            => 'Neu hinzufügen',
                'add_new_item'       => 'Neue Veranstaltung hinzufügen',
                'new_item'           => 'Neue Veranstaltung',
                'edit_item'          => 'Veranstaltung bearbeiten',
                'view_item'          => 'Veranstaltung ansehen',
                'all_items'          => 'Alle Veranstaltungen',
                'search_items'       => 'Veranstaltungen suchen',
                'not_found'          => 'Keine Veranstaltungen gefunden',
                'not_found_in_trash' => 'Keine Veranstaltungen im Papierkorb gefunden',
            ),
            'public'             => true,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'show_in_rest'       => true,
            'publicly_queryable' => true,
            'has_archive'        => true,
            'menu_icon'          => 'dashicons-calendar-alt',
            'supports'           => array(
                'title',
                'editor',
                'thumbnail',
                'excerpt',
                'custom-fields',
            ),
            'rewrite' => array(
                'slug' => 'veranstaltungen',
            ),
        )
    );

    register_taxonomy(
        'veranstaltungkategorie',
        array('veranstaltung'),
        array(
            'labels' => array(
                'name'              => 'Veranstaltungskategorien',
                'singular_name'     => 'Veranstaltungskategorie',
                'search_items'      => 'Veranstaltungskategorien durchsuchen',
                'all_items'         => 'Alle Veranstaltungskategorien',
                'parent_item'       => 'Übergeordnete Veranstaltungskategorie',
                'parent_item_colon' => 'Übergeordnete Veranstaltungskategorie:',
                'edit_item'         => 'Veranstaltungskategorie bearbeiten',
                'update_item'       => 'Veranstaltungskategorie aktualisieren',
                'add_new_item'      => 'Neue Veranstaltungskategorie hinzufügen',
                'new_item_name'     => 'Name der neuen Veranstaltungskategorie',
                'menu_name'         => 'Veranstaltungskategorien',
            ),
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'query_var'         => true,
            'rewrite'           => array(
                'slug'         => 'veranstaltungkategorie',
                'hierarchical' => true,
            ),
        )
    );
}
add_action(
    'init',
    'morshaus_register_content_types',
    0
);


/* Kategoriebilder fuer Veranstaltungen 0.5.4-build.2 */
function morshaus_event_category_image_add_field() { ?>
    <div class="form-field morshaus-event-image-field">
        <label for="morshaus_event_category_image_id">Kategoriebild</label>
        <input type="hidden" id="morshaus_event_category_image_id" name="morshaus_event_category_image_id" value="">
        <div class="morshaus-event-category-image-preview"></div>
        <p><button type="button" class="button morshaus-event-category-image-select">Bild auswählen</button> <button type="button" class="button morshaus-event-category-image-remove" hidden>Bild entfernen</button></p>
    </div><?php
}
add_action('veranstaltungkategorie_add_form_fields','morshaus_event_category_image_add_field');

function morshaus_event_category_image_edit_field($term) {
    $image_id=absint(get_term_meta($term->term_id,'morshaus_event_category_image_id',true)); ?>
    <tr class="form-field morshaus-event-image-field"><th><label for="morshaus_event_category_image_id">Kategoriebild</label></th><td>
        <input type="hidden" id="morshaus_event_category_image_id" name="morshaus_event_category_image_id" value="<?php echo esc_attr($image_id); ?>">
        <div class="morshaus-event-category-image-preview"><?php if($image_id) echo wp_kses_post(wp_get_attachment_image($image_id,'medium')); ?></div>
        <p><button type="button" class="button morshaus-event-category-image-select">Bild auswählen</button> <button type="button" class="button morshaus-event-category-image-remove" <?php echo $image_id?'':'hidden'; ?>>Bild entfernen</button></p>
    </td></tr><?php
}
add_action('veranstaltungkategorie_edit_form_fields','morshaus_event_category_image_edit_field');

function morshaus_save_event_category_image($term_id){
    if(!current_user_can('manage_categories')) return;
    $id=isset($_POST['morshaus_event_category_image_id'])?absint(wp_unslash($_POST['morshaus_event_category_image_id'])):0;
    if($id) update_term_meta($term_id,'morshaus_event_category_image_id',$id); else delete_term_meta($term_id,'morshaus_event_category_image_id');
}
add_action('created_veranstaltungkategorie','morshaus_save_event_category_image');
add_action('edited_veranstaltungkategorie','morshaus_save_event_category_image');

function morshaus_event_category_image_enqueue($hook){
    if(!in_array($hook,array('edit-tags.php','term.php'),true)) return;
    $screen=get_current_screen();
    if(!$screen || $screen->taxonomy!=='veranstaltungkategorie') return;
    wp_enqueue_media();
    wp_enqueue_script('jquery');
}
add_action('admin_enqueue_scripts','morshaus_event_category_image_enqueue');

function morshaus_event_category_image_footer(){
    $screen=get_current_screen();
    if(!$screen || $screen->taxonomy!=='veranstaltungkategorie') return; ?>
    <style>.morshaus-event-category-image-preview img{display:block;width:220px;max-width:100%;height:auto;margin:8px 0;border-radius:8px}</style>
    <script>
    jQuery(function($){
        $(document).on('click','.morshaus-event-category-image-select',function(e){
            e.preventDefault();
            if(typeof wp==='undefined'||!wp.media)return;
            var $field=$(this).closest('.morshaus-event-image-field');
            var frame=wp.media({title:'Kategoriebild auswählen',button:{text:'Bild verwenden'},multiple:false});
            frame.on('select',function(){
                var image=frame.state().get('selection').first().toJSON();
                $field.find('input[name="morshaus_event_category_image_id"]').val(image.id);
                $field.find('.morshaus-event-category-image-preview').html('<img src="'+image.url+'" alt="">');
                $field.find('.morshaus-event-category-image-remove').prop('hidden',false);
            });
            frame.open();
        });
        $(document).on('click','.morshaus-event-category-image-remove',function(e){
            e.preventDefault(); var $field=$(this).closest('.morshaus-event-image-field');
            $field.find('input[name="morshaus_event_category_image_id"]').val('');
            $field.find('.morshaus-event-category-image-preview').empty(); $(this).prop('hidden',true);
        });
    });
    </script><?php
}
add_action('admin_footer-edit-tags.php','morshaus_event_category_image_footer');
add_action('admin_footer-term.php','morshaus_event_category_image_footer');


/* ==============================================================
 * Standardkategorien
 * ============================================================== */

function morshaus_get_or_create_term(
    $name,
    $taxonomy,
    $parent = 0
) {
    $terms = get_terms(
        array(
            'taxonomy'   => $taxonomy,
            'name'       => $name,
            'parent'     => (int) $parent,
            'hide_empty' => false,
            'number'     => 1,
        )
    );

    if (
        !is_wp_error($terms) &&
        !empty($terms)
    ) {
        return (int) $terms[0]->term_id;
    }

    $new_term = wp_insert_term(
        $name,
        $taxonomy,
        array(
            'parent' => (int) $parent,
        )
    );

    if (
        is_wp_error($new_term) ||
        !isset($new_term['term_id'])
    ) {
        return 0;
    }

    return (int) $new_term['term_id'];
}


function morshaus_dedupe_and_move_term(
    $term_name,
    $taxonomy,
    $new_parent_id
) {
    $terms = get_terms(
        array(
            'taxonomy'   => $taxonomy,
            'name'       => $term_name,
            'hide_empty' => false,
        )
    );

    if (is_wp_error($terms)) {
        return;
    }

    if (empty($terms)) {
        morshaus_get_or_create_term(
            $term_name,
            $taxonomy,
            $new_parent_id
        );
        return;
    }

    // Bevorzugt den Term, der bereits am Zielort haengt, als
    // "Haupt"-Term. Gibt es den nicht, wird der erste gefundene
    // Term dorthin verschoben.
    $keep = null;

    foreach ($terms as $term) {
        if ((int) $term->parent === (int) $new_parent_id) {
            $keep = $term;
            break;
        }
    }

    if (!$keep) {
        $keep = $terms[0];

        wp_update_term(
            $keep->term_id,
            $taxonomy,
            array(
                'parent' => (int) $new_parent_id,
            )
        );
    }

    // Alle weiteren gleichnamigen Terms sind Duplikate (z. B. durch
    // einen frueheren Migrationslauf entstanden). Ihre verknuepften
    // Beitraege werden auf den Haupt-Term umgehaengt, danach wird
    // das Duplikat geloescht.
    foreach ($terms as $term) {
        if ((int) $term->term_id === (int) $keep->term_id) {
            continue;
        }

        $term_image_id = absint(
            get_term_meta(
                $term->term_id,
                'morshaus_event_category_image_id',
                true
            )
        );
        $keep_image_id = absint(
            get_term_meta(
                $keep->term_id,
                'morshaus_event_category_image_id',
                true
            )
        );
        if ($term_image_id && !$keep_image_id) {
            update_term_meta(
                $keep->term_id,
                'morshaus_event_category_image_id',
                $term_image_id
            );
        }

        $object_ids = get_objects_in_term(
            $term->term_id,
            $taxonomy
        );

        if (
            !is_wp_error($object_ids) &&
            !empty($object_ids)
        ) {
            foreach ($object_ids as $object_id) {
                wp_set_object_terms(
                    (int) $object_id,
                    array((int) $keep->term_id),
                    $taxonomy,
                    true
                );
            }
        }

        wp_delete_term(
            $term->term_id,
            $taxonomy
        );
    }
}

function morshaus_rename_event_term(
    $old_name,
    $new_name,
    $taxonomy,
    $parent_id
) {
    $old_terms = get_terms(
        array(
            'taxonomy'   => $taxonomy,
            'name'       => $old_name,
            'hide_empty' => false,
        )
    );

    if (is_wp_error($old_terms)) {
        return 0;
    }

    $new_id = morshaus_get_or_create_term(
        $new_name,
        $taxonomy,
        $parent_id
    );

    if (!$new_id) {
        return 0;
    }

    foreach ($old_terms as $old_term) {
        $old_image_id = absint(
            get_term_meta(
                $old_term->term_id,
                'morshaus_event_category_image_id',
                true
            )
        );
        $new_image_id = absint(
            get_term_meta(
                $new_id,
                'morshaus_event_category_image_id',
                true
            )
        );
        if ($old_image_id && !$new_image_id) {
            update_term_meta(
                $new_id,
                'morshaus_event_category_image_id',
                $old_image_id
            );
        }

        $children = get_terms(
            array(
                'taxonomy'   => $taxonomy,
                'parent'     => (int) $old_term->term_id,
                'hide_empty' => false,
            )
        );

        if (!is_wp_error($children)) {
            foreach ($children as $child) {
                wp_update_term(
                    $child->term_id,
                    $taxonomy,
                    array('parent' => $new_id)
                );
            }
        }

        $object_ids = get_objects_in_term(
            $old_term->term_id,
            $taxonomy
        );

        if (!is_wp_error($object_ids) && !empty($object_ids)) {
            foreach ($object_ids as $object_id) {
                wp_set_object_terms(
                    (int) $object_id,
                    array($new_id),
                    $taxonomy,
                    true
                );
            }
        }

        wp_delete_term($old_term->term_id, $taxonomy);
    }

    morshaus_dedupe_and_move_term(
        $new_name,
        $taxonomy,
        $parent_id
    );

    return $new_id;
}


function morshaus_insert_default_terms() {
    $version = '13';
    if (get_option('morshaus_default_terms_version') === $version) {
        return;
    }

    $taxonomy = 'veranstaltungkategorie';
    $seminare_id = morshaus_get_or_create_term('Seminare', $taxonomy);
    if ($seminare_id) {
        $am_tisch_id = morshaus_rename_event_term(
            'Im Sitzen',
            'Am Tisch',
            $taxonomy,
            $seminare_id
        );
        if ($am_tisch_id) {
            foreach (array('Grundlagen', 'Vertiefung', 'Spezial') as $name) {
                morshaus_dedupe_and_move_term(
                    $name,
                    $taxonomy,
                    $am_tisch_id
                );
            }
        }

        $in_stille_id = morshaus_get_or_create_term(
            'In Stille',
            $taxonomy,
            $seminare_id
        );
        if ($in_stille_id) {
            morshaus_dedupe_and_move_term(
                'Kontemplation',
                $taxonomy,
                $in_stille_id
            );
            morshaus_dedupe_and_move_term(
                'Bogen',
                $taxonomy,
                $in_stille_id
            );
        }

        morshaus_rename_event_term(
            'Paddeln',
            'Auf dem Wasser',
            $taxonomy,
            $seminare_id
        );
        morshaus_rename_event_term(
            'Wasser',
            'Auf dem Wasser',
            $taxonomy,
            $seminare_id
        );
        morshaus_rename_event_term(
            'Waldbaden',
            'Im Wald',
            $taxonomy,
            $seminare_id
        );
        morshaus_rename_event_term(
            'Wald',
            'Im Wald',
            $taxonomy,
            $seminare_id
        );
    }

    $begegnung_id = morshaus_get_or_create_term(
        'Begegnung',
        $taxonomy
    );
    if ($begegnung_id) {
        $in_bewegung_id = morshaus_rename_event_term(
            'Laufen',
            'In Bewegung',
            $taxonomy,
            $begegnung_id
        );
        if ($in_bewegung_id) {
            morshaus_rename_event_term(
                'Gehen',
                'In Bewegung',
                $taxonomy,
                $begegnung_id
            );
        }
        morshaus_rename_event_term(
            'Sitzen',
            'Im Sitzen',
            $taxonomy,
            $begegnung_id
        );
    }

    update_option('morshaus_default_terms_version', $version);
}
add_action('init','morshaus_insert_default_terms',20);

function morshaus_add_zeremonie_category() {
    $parent = get_term_by('slug', 'in-stille', 'veranstaltungkategorie');
    if (!$parent || is_wp_error($parent)) {
        error_log('Die Veranstaltungskategorie In Stille wurde nicht gefunden.');
        return;
    }

    $existing = term_exists('Zeremonie', 'veranstaltungkategorie', $parent->term_id);
    if (is_wp_error($existing)) {
        error_log('Die Veranstaltungskategorie Zeremonie konnte nicht geprüft werden: ' . $existing->get_error_message());
        return;
    }
    if (!$existing) {
        $created = wp_insert_term('Zeremonie', 'veranstaltungkategorie', array('parent' => $parent->term_id));
        if (is_wp_error($created)) {
            error_log('Die Veranstaltungskategorie Zeremonie konnte nicht erstellt werden: ' . $created->get_error_message());
        }
    }
}
add_action('init', 'morshaus_add_zeremonie_category', 21);

/* ==============================================================
 * Veranstaltungs-Metabox
 * ============================================================== */

function morshaus_add_veranstaltung_meta_box() {
    add_meta_box(
        'morshaus_veranstaltung_details',
        'Veranstaltungsdetails',
        'morshaus_render_veranstaltung_meta_box',
        'veranstaltung',
        'normal',
        'high'
    );
}
add_action(
    'add_meta_boxes',
    'morshaus_add_veranstaltung_meta_box'
);


function morshaus_render_veranstaltung_meta_box($post) {
    wp_nonce_field(
        'morshaus_save_veranstaltung_meta',
        'morshaus_veranstaltung_nonce'
    );

    $felder = array(
        'datum' => get_post_meta(
            $post->ID,
            '_morshaus_veranstaltung_datum',
            true
        ),
        'treffpunkt' => get_post_meta(
            $post->ID,
            '_morshaus_veranstaltung_treffpunkt',
            true
        ),
        'uhrzeit' => get_post_meta(
            $post->ID,
            '_morshaus_veranstaltung_uhrzeit',
            true
        ),
        'dauer' => get_post_meta(
            $post->ID,
            '_morshaus_veranstaltung_dauer',
            true
        ),
        'tee_runden' => get_post_meta(
            $post->ID,
            '_morshaus_veranstaltung_tee_runden',
            true
        ),
        'tee_besonderheit' => get_post_meta(
            $post->ID,
            '_morshaus_veranstaltung_tee_besonderheit',
            true
        ),
        'materialkosten' => get_post_meta(
            $post->ID,
            '_morshaus_veranstaltung_materialkosten',
            true
        ),
        'seminarkosten' => get_post_meta(
            $post->ID,
            '_morshaus_veranstaltung_seminarkosten',
            true
        ),
        'extrakosten' => get_post_meta(
            $post->ID,
            '_morshaus_veranstaltung_extrakosten',
            true
        ),
        'angemeldet' => get_post_meta(
            $post->ID,
            '_morshaus_veranstaltung_angemeldet',
            true
        ),
        'max_teilnehmer' => get_post_meta(
            $post->ID,
            '_morshaus_veranstaltung_max_teilnehmer',
            true
        ),
    );

    if ($felder['angemeldet'] === '') {
        $felder['angemeldet'] = '0';
    }

    if ($felder['tee_runden'] === '') {
        $felder['tee_runden'] = '1';
    }

    ?>

    <style>
        .morshaus-veranstaltung-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 16px;
        }

        .morshaus-veranstaltung-field.full {
            grid-column: 1 / -1;
        }

        .morshaus-veranstaltung-field label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .morshaus-veranstaltung-field input {
            width: 100%;
        }

        .morshaus-euro-input {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .morshaus-euro-input input {
            max-width: 180px;
        }

        .morshaus-euro-input span {
            font-weight: 600;
        }

        .morshaus-veranstaltung-field .description {
            margin-top: 5px;
            color: #666;
            font-size: 13px;
        }

        @media (max-width: 782px) {
            .morshaus-veranstaltung-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="morshaus-veranstaltung-grid">

        <div class="morshaus-veranstaltung-field full">
            <p class="description" style="margin: 0;">
                Die Beschreibung wird im Beitragseditor oberhalb gepflegt.
                Die Art (Seminar/Begegnung) und Unterkategorie werden rechts
                unter <strong>Veranstaltungskategorien</strong> ausgewählt.
            </p>
        </div>

        <div class="morshaus-veranstaltung-field">
            <label for="morshaus_veranstaltung_datum">
                Datum *
            </label>

            <input
                type="date"
                id="morshaus_veranstaltung_datum"
                name="morshaus_veranstaltung_datum"
                value="<?php echo esc_attr(
                    $felder['datum']
                ); ?>"
                required
            >
        </div>

        <div class="morshaus-veranstaltung-field">
            <label for="morshaus_veranstaltung_treffpunkt">
                Treffpunkt / Ort
            </label>

            <input
                type="text"
                id="morshaus_veranstaltung_treffpunkt"
                name="morshaus_veranstaltung_treffpunkt"
                value="<?php echo esc_attr(
                    $felder['treffpunkt']
                ); ?>"
            >
        </div>

        <div class="morshaus-veranstaltung-field">
            <label for="morshaus_veranstaltung_uhrzeit">
                Uhrzeit
            </label>

            <input
                type="time"
                id="morshaus_veranstaltung_uhrzeit"
                name="morshaus_veranstaltung_uhrzeit"
                value="<?php echo esc_attr(
                    $felder['uhrzeit']
                ); ?>"
                step="1800"
            >

            <p class="description">
                Halbstundentakt; die Uhrzeit kann manuell geändert werden.
            </p>
        </div>

        <div class="morshaus-veranstaltung-field">
            <label for="morshaus_veranstaltung_dauer">
                Dauer in Stunden
            </label>

            <input
                type="number"
                id="morshaus_veranstaltung_dauer"
                name="morshaus_veranstaltung_dauer"
                value="<?php echo esc_attr(
                    $felder['dauer']
                ); ?>"
                min="0.5"
                step="0.5"
                placeholder="z. B. 1,5"
            >
        </div>

        <div class="morshaus-veranstaltung-field">
            <label for="morshaus_veranstaltung_tee_runden">
                Tee-Runden
            </label>

            <input
                type="number"
                id="morshaus_veranstaltung_tee_runden"
                name="morshaus_veranstaltung_tee_runden"
                value="<?php echo esc_attr(
                    $felder['tee_runden']
                ); ?>"
                min="1"
                max="6"
                step="1"
            >
        </div>

        <div class="morshaus-veranstaltung-field">
            <label for="morshaus_veranstaltung_tee_besonderheit">
                Tee-Besonderheit
            </label>

            <input
                type="text"
                id="morshaus_veranstaltung_tee_besonderheit"
                name="morshaus_veranstaltung_tee_besonderheit"
                value="<?php echo esc_attr(
                    $felder['tee_besonderheit']
                ); ?>"
                placeholder="optional"
            >
        </div>

        <div class="morshaus-veranstaltung-field">
            <label for="morshaus_veranstaltung_materialkosten">
                Materialkosten
            </label>

            <div class="morshaus-euro-input">
                <input
                    type="number"
                    id="morshaus_veranstaltung_materialkosten"
                    name="morshaus_veranstaltung_materialkosten"
                    value="<?php echo esc_attr(
                        $felder['materialkosten']
                    ); ?>"
                    min="0"
                    step="1"
                >

                <span>€</span>
            </div>
        </div>

        <div class="morshaus-veranstaltung-field">
            <label for="morshaus_veranstaltung_seminarkosten">
                Seminarkosten
            </label>

            <div class="morshaus-euro-input">
                <input
                    type="number"
                    id="morshaus_veranstaltung_seminarkosten"
                    name="morshaus_veranstaltung_seminarkosten"
                    value="<?php echo esc_attr(
                        $felder['seminarkosten']
                    ); ?>"
                    min="0"
                    step="1"
                >

                <span>€</span>
            </div>
        </div>

        <div class="morshaus-veranstaltung-field">
            <label for="morshaus_veranstaltung_extrakosten">
                Extrakosten
            </label>

            <div class="morshaus-euro-input">
                <input
                    type="number"
                    id="morshaus_veranstaltung_extrakosten"
                    name="morshaus_veranstaltung_extrakosten"
                    value="<?php echo esc_attr(
                        $felder['extrakosten']
                    ); ?>"
                    min="0"
                    step="1"
                >

                <span>€</span>
            </div>
        </div>

        <div class="morshaus-veranstaltung-field">
            <label for="morshaus_veranstaltung_angemeldet">
                Angemeldete Teilnehmer
            </label>

            <input
                type="number"
                id="morshaus_veranstaltung_angemeldet"
                name="morshaus_veranstaltung_angemeldet"
                value="<?php echo esc_attr(
                    $felder['angemeldet']
                ); ?>"
                min="0"
                step="1"
            >
        </div>

        <div class="morshaus-veranstaltung-field">
            <label for="morshaus_veranstaltung_max_teilnehmer">
                Maximale Teilnehmerzahl
            </label>

            <input
                type="number"
                id="morshaus_veranstaltung_max_teilnehmer"
                name="morshaus_veranstaltung_max_teilnehmer"
                value="<?php echo esc_attr(
                    $felder['max_teilnehmer']
                ); ?>"
                min="0"
                step="1"
            >
        </div>

    </div>

    <?php
}


/* ==============================================================
 * Veranstaltungsdaten speichern
 * ============================================================== */

function morshaus_sanitize_euro_value($value) {
    $value = sanitize_text_field(
        wp_unslash($value)
    );

    $value = str_replace(
        ',',
        '.',
        $value
    );

    if (
        $value === '' ||
        !is_numeric($value)
    ) {
        return '';
    }

    return number_format(
        max(0, (float) $value),
        2,
        '.',
        ''
    );
}


function morshaus_save_veranstaltung_meta($post_id) {
    if (
        !isset(
            $_POST['morshaus_veranstaltung_nonce']
        ) ||
        !wp_verify_nonce(
            sanitize_text_field(
                wp_unslash(
                    $_POST['morshaus_veranstaltung_nonce']
                )
            ),
            'morshaus_save_veranstaltung_meta'
        )
    ) {
        return;
    }

    if (
        defined('DOING_AUTOSAVE') &&
        DOING_AUTOSAVE
    ) {
        return;
    }

    if (
        wp_is_post_revision($post_id) ||
        wp_is_post_autosave($post_id)
    ) {
        return;
    }

    if (
        get_post_type($post_id) !== 'veranstaltung' ||
        !current_user_can(
            'edit_post',
            $post_id
        )
    ) {
        return;
    }

    $text_fields = array(
        'morshaus_veranstaltung_datum' =>
            '_morshaus_veranstaltung_datum',

        'morshaus_veranstaltung_treffpunkt' =>
            '_morshaus_veranstaltung_treffpunkt',

        'morshaus_veranstaltung_uhrzeit' =>
            '_morshaus_veranstaltung_uhrzeit',

        'morshaus_veranstaltung_dauer' =>
            '_morshaus_veranstaltung_dauer',

        'morshaus_veranstaltung_tee_runden' =>
            '_morshaus_veranstaltung_tee_runden',

        'morshaus_veranstaltung_tee_besonderheit' =>
            '_morshaus_veranstaltung_tee_besonderheit',
    );

    foreach (
        $text_fields as $post_key => $meta_key
    ) {
        $value = isset($_POST[$post_key])
            ? sanitize_text_field(
                wp_unslash(
                    $_POST[$post_key]
                )
            )
            : '';

        if (
            $post_key ===
            'morshaus_veranstaltung_datum' &&
            $value !== ''
        ) {
            $date = DateTime::createFromFormat(
                'Y-m-d',
                $value
            );

            if (
                !$date ||
                $date->format('Y-m-d') !== $value
            ) {
                $value = '';
            }
        }

        if (
            $post_key ===
            'morshaus_veranstaltung_uhrzeit' &&
            $value !== '' &&
            !preg_match(
                '/^(?:[01]\d|2[0-3]):[0-5]\d$/',
                $value
            )
        ) {
            $value = '';
        }

        if (
            $post_key ===
            'morshaus_veranstaltung_dauer' &&
            $value !== ''
        ) {
            $value = str_replace(
                ',',
                '.',
                $value
            );

            if (
                !is_numeric($value) ||
                (float) $value < 0.5
            ) {
                $value = '';
            } else {
                $value = number_format(
                    round(
                        (float) $value * 2
                    ) / 2,
                    1,
                    '.',
                    ''
                );
            }
        }

        if (
            $post_key ===
            'morshaus_veranstaltung_tee_runden' &&
            $value !== ''
        ) {
            $value = absint($value);

            if (
                $value < 1 ||
                $value > 6
            ) {
                $value = '';
            }
        }

        if ($value !== '') {
            update_post_meta(
                $post_id,
                $meta_key,
                $value
            );
        } else {
            delete_post_meta(
                $post_id,
                $meta_key
            );
        }
    }

    $euro_fields = array(
        'morshaus_veranstaltung_materialkosten' =>
            '_morshaus_veranstaltung_materialkosten',

        'morshaus_veranstaltung_seminarkosten' =>
            '_morshaus_veranstaltung_seminarkosten',

        'morshaus_veranstaltung_extrakosten' =>
            '_morshaus_veranstaltung_extrakosten',
    );

    foreach (
        $euro_fields as $post_key => $meta_key
    ) {
        if (
            isset($_POST[$post_key]) &&
            $_POST[$post_key] !== ''
        ) {
            $value = morshaus_sanitize_euro_value(
                $_POST[$post_key]
            );

            if ($value !== '') {
                update_post_meta(
                    $post_id,
                    $meta_key,
                    $value
                );
            } else {
                delete_post_meta(
                    $post_id,
                    $meta_key
                );
            }
        } else {
            delete_post_meta(
                $post_id,
                $meta_key
            );
        }
    }

    $number_fields = array(
        'morshaus_veranstaltung_angemeldet' =>
            '_morshaus_veranstaltung_angemeldet',

        'morshaus_veranstaltung_max_teilnehmer' =>
            '_morshaus_veranstaltung_max_teilnehmer',
    );

    foreach (
        $number_fields as $post_key => $meta_key
    ) {
        $value = isset($_POST[$post_key])
            ? absint(
                wp_unslash(
                    $_POST[$post_key]
                )
            )
            : 0;

        update_post_meta(
            $post_id,
            $meta_key,
            $value
        );
    }
}
add_action(
    'save_post_veranstaltung',
    'morshaus_save_veranstaltung_meta'
);


/* ==============================================================
 * Veranstaltungs-Hilfsfunktionen
 * ============================================================== */

function morshaus_format_euro($value) {
    if (
        $value === '' ||
        !is_numeric($value)
    ) {
        return '';
    }

    return number_format(
        (float) $value,
        2,
        ',',
        '.'
    ) . ' €';
}


function morshaus_get_top_level_event_term($term) {
    if (!$term || is_wp_error($term)) {
        return null;
    }

    while (
        !empty($term->parent)
    ) {
        $parent = get_term(
            $term->parent,
            'veranstaltungkategorie'
        );

        if (
            !$parent ||
            is_wp_error($parent)
        ) {
            break;
        }

        $term = $parent;
    }

    return $term;
}


function morshaus_get_veranstaltung_category( $post_id ) {
    $terms = get_the_terms( $post_id, 'veranstaltungkategorie' );

    if ( ! is_array( $terms ) || empty( $terms ) || is_wp_error( $terms ) ) {
        return '';
    }

    $term      = $terms[0];
    $top_level = morshaus_get_top_level_event_term( $term );

    if ( $top_level && ! is_wp_error( $top_level ) ) {
        return $top_level->slug;
    }

    return $term->slug;
}


// Ermittelt Art und Filtergruppe aus den zugeordneten Kategorien. Die
// oberste Kategorie bestimmt die Art, ihr direktes Kind die Filtergruppe;
// die tiefste zugeordnete Kategorie bildet das Kartenlabel.
function morshaus_get_veranstaltung_art_info( $post_id ) {
    $leer = array(
        'art'            => '',
        'unterkategorie' => '',
        'filter_unter'   => '',
        'label'          => '',
    );

    $terms = get_the_terms( $post_id, 'veranstaltungkategorie' );

    if ( ! is_array( $terms ) || empty( $terms ) || is_wp_error( $terms ) ) {
        return $leer;
    }

    $art_map = array(
        'seminare' => 'Seminar',
        'sessions' => 'Begegnung',
        'begegnung' => 'Begegnung',
    );

    // Die Kategorie mit den meisten Vorfahren ist die spezifischste
    // (tiefste) zugeordnete Kategorie und wird bevorzugt.
    usort(
        $terms,
        function ( $a, $b ) {
            $tiefe_a = count( get_ancestors( $a->term_id, 'veranstaltungkategorie' ) );
            $tiefe_b = count( get_ancestors( $b->term_id, 'veranstaltungkategorie' ) );

            return $tiefe_b - $tiefe_a;
        }
    );

    foreach ( $terms as $term ) {
        $top_level = morshaus_get_top_level_event_term( $term );

        if ( ! $top_level || is_wp_error( $top_level ) ) {
            continue;
        }

        if ( ! isset( $art_map[ $top_level->slug ] ) ) {
            continue;
        }

        $art            = $art_map[ $top_level->slug ];
        $unterkategorie = ( (int) $term->term_id !== (int) $top_level->term_id )
            ? $term->name
            : '';

        $filter_term = $term;
        while (
            (int) $filter_term->parent !== (int) $top_level->term_id &&
            (int) $filter_term->parent !== 0
        ) {
            $parent_term = get_term(
                (int) $filter_term->parent,
                'veranstaltungkategorie'
            );
            if (!$parent_term || is_wp_error($parent_term)) {
                break;
            }
            $filter_term = $parent_term;
        }

        $filter_unter = (int) $filter_term->term_id !== (int) $top_level->term_id
            ? $filter_term->slug
            : '';

        $label = $art;

        if ( $unterkategorie !== '' ) {
            $label .= ' – ' . $unterkategorie;
        }

        return array(
            'art'            => $art,
            'unterkategorie' => $unterkategorie,
            'filter_unter'   => $filter_unter,
            'label'          => $label,
        );
    }

    return $leer;
}

function morshaus_get_veranstaltung_filter_themen($post_id) {
    $terms = get_the_terms($post_id, 'veranstaltungkategorie');
    if (is_wp_error($terms)) {
        error_log('Die Veranstaltungskategorien konnten nicht geladen werden: ' . $terms->get_error_message());
        return array();
    }
    if (!$terms) {
        return array();
    }

    $allowed = array('grundlagen', 'vertiefung', 'spezial', 'bogen', 'zeremonie');
    $topics = array();
    foreach ($terms as $term) {
        $term_ids = array_merge(array($term->term_id), get_ancestors($term->term_id, 'veranstaltungkategorie'));
        foreach ($term_ids as $term_id) {
            $ancestor = get_term($term_id, 'veranstaltungkategorie');
            if (is_wp_error($ancestor)) {
                error_log('Eine Veranstaltungskategorie konnte nicht geladen werden: ' . $ancestor->get_error_message());
                continue;
            }
            if ($ancestor) {
                $topic = sanitize_title($ancestor->name);
                if (in_array($topic, $allowed, true)) {
                    $topics[] = $topic;
                }
            }
        }
    }
    return array_unique($topics);
}


function morshaus_get_veranstaltung_category_image_id($post_id){
    $terms=get_the_terms($post_id,'veranstaltungkategorie');
    if(!is_array($terms)||empty($terms)||is_wp_error($terms)) return 0;
    usort($terms,function($a,$b){return count(get_ancestors($b->term_id,'veranstaltungkategorie'))-count(get_ancestors($a->term_id,'veranstaltungkategorie'));});
    foreach($terms as $term){$candidate=$term; while($candidate&&!is_wp_error($candidate)){$id=absint(get_term_meta($candidate->term_id,'morshaus_event_category_image_id',true)); if($id)return $id; if(empty($candidate->parent))break; $candidate=get_term($candidate->parent,'veranstaltungkategorie');}}
    return 0;
}

function morshaus_get_veranstaltung_costs( $post_id ) {
    $costs = array();

    $materialkosten = get_post_meta( $post_id, '_morshaus_veranstaltung_materialkosten', true );
    $seminarkosten  = get_post_meta( $post_id, '_morshaus_veranstaltung_seminarkosten', true );
    $extrakosten    = get_post_meta( $post_id, '_morshaus_veranstaltung_extrakosten', true );

    // Material immer anzeigen auch 0,00
    if ( $materialkosten !== '' && is_numeric( $materialkosten ) ) {
        $costs[] = array(
            'label' => 'Material',
            'value' => (float) $materialkosten,
        );
    }

    // Seminar nur anzeigen, wenn > 0
    if ( $seminarkosten !== '' && is_numeric( $seminarkosten ) && (float) $seminarkosten > 0 ) {
        $costs[] = array(
            'label' => 'Seminar',
            'value' => (float) $seminarkosten,
        );
    }

    // Extra nur anzeigen, wenn > 0
    if ( $extrakosten !== '' && is_numeric( $extrakosten ) && (float) $extrakosten > 0 ) {
        $costs[] = array(
            'label' => 'Extra',
            'value' => (float) $extrakosten,
        );
    }

    // Gesamtsumme aus allen drei Werten berechnen unabhängig von Anzeige
    $total = 0;

    if ( $materialkosten !== '' && is_numeric( $materialkosten ) ) {
        $total += (float) $materialkosten;
    }
    if ( $seminarkosten !== '' && is_numeric( $seminarkosten ) ) {
        $total += (float) $seminarkosten;
    }
    if ( $extrakosten !== '' && is_numeric( $extrakosten ) ) {
        $total += (float) $extrakosten;
    }

    return array(
        'items' => $costs,
        'total' => $total,
    );
}

/* ==============================================================
 * Einzelne Veranstaltung ausgeben
 * ============================================================== */

function morshaus_render_veranstaltung($post_id) {
    $art_info    = morshaus_get_veranstaltung_art_info($post_id);
    $art_ausgabe = $art_info['label'];

    $datum = get_post_meta(
        $post_id,
        '_morshaus_veranstaltung_datum',
        true
    );

    $treffpunkt = get_post_meta(
        $post_id,
        '_morshaus_veranstaltung_treffpunkt',
        true
    );

    $uhrzeit = get_post_meta(
        $post_id,
        '_morshaus_veranstaltung_uhrzeit',
        true
    );

    $dauer = get_post_meta(
        $post_id,
        '_morshaus_veranstaltung_dauer',
        true
    );

    $tee_runden = get_post_meta(
        $post_id,
        '_morshaus_veranstaltung_tee_runden',
        true
    );

    $tee_besonderheit = get_post_meta(
        $post_id,
        '_morshaus_veranstaltung_tee_besonderheit',
        true
    );

    $angemeldet = get_post_meta(
        $post_id,
        '_morshaus_veranstaltung_angemeldet',
        true
    );

    $max_teilnehmer = get_post_meta(
        $post_id,
        '_morshaus_veranstaltung_max_teilnehmer',
        true
    );

    $category = morshaus_get_veranstaltung_category(
        $post_id
    );

    $cost_data = morshaus_get_veranstaltung_costs(
        $post_id
    );

    if ($angemeldet === '') {
        $angemeldet = '0';
    }

    $datum_ausgabe = '';
    $wochentag_ausgabe = '';

    if ($datum !== '') {
        $timestamp = strtotime($datum);

        if ($timestamp) {
            $datum_ausgabe = date_i18n(
                'd/m/Y',
                $timestamp
            );
            $wochentag_ausgabe = date_i18n(
                'l',
                $timestamp
            );
        }
    }

    $beschreibung = get_post_field(
        'post_content',
        $post_id
    );
    $beschreibung_html = '';

    if ($beschreibung !== '') {
        $beschreibung_html = apply_filters('the_content', $beschreibung);
    }

    $cost_texts = array();

    foreach ($cost_data['items'] as $cost) {
        $cost_texts[] =
            $cost['label'] .
            ' ' .
            morshaus_format_euro(
                $cost['value']
            );
    }

    $cost_text = implode(
        ' | ',
        $cost_texts
    );

    $title = get_the_title($post_id);
    $filter_haupt = sanitize_title($art_info['art']);
    $filter_unter = sanitize_title($art_info['filter_unter']);
    $featured_image_id = get_post_thumbnail_id($post_id);
    $card_image_id = $featured_image_id
        ? $featured_image_id
        : morshaus_get_veranstaltung_category_image_id($post_id);
    $card_image = $card_image_id
        ? wp_get_attachment_image(
            $card_image_id,
            'large',
            false,
            array(
                'class'   => 'morshaus-veranstaltung__bild',
                'loading' => 'lazy',
            )
        )
        : '';
    $beschreibung_id = wp_unique_id('morshaus-event-description-');
    ob_start();
    ?>

<article
    class="morshaus-veranstaltung"
    data-event-id="<?php echo esc_attr( $post_id ); ?>"
    data-filter-haupt="<?php echo esc_attr( $filter_haupt ); ?>"
    data-filter-unter="<?php echo esc_attr( $filter_unter ); ?>"
    data-filter-themen="<?php echo esc_attr(implode(' ', morshaus_get_veranstaltung_filter_themen($post_id))); ?>"
>
    <div class="morshaus-veranstaltung__inner">
        <div class="morshaus-veranstaltung__main<?php echo $card_image !== '' ? ' has-image' : ''; ?>">
            <div class="morshaus-veranstaltung__content">
    <p class="morshaus-veranstaltung__kopf">
        <?php if ( $datum_ausgabe !== '' ) : ?>
            <span class="morshaus-veranstaltung__kopf-datum">
                <?php echo esc_html( $datum_ausgabe ); ?>
                <?php if ( $wochentag_ausgabe !== '' ) : ?>
                    <span class="morshaus-veranstaltung__kopf-trenner" aria-hidden="true">|</span>
                    <?php echo esc_html( $wochentag_ausgabe ); ?>
                <?php endif; ?>
            </span>
        <?php endif; ?>

        <strong class="morshaus-veranstaltung__kopf-titel"><?php echo esc_html( $title ); ?></strong>
    </p>

    <p class="morshaus-veranstaltung__details">

        <?php if ( $art_ausgabe !== '' ) : ?>
            <strong>Art:</strong>
            <span class="morshaus-veranstaltung__wert"><?php echo esc_html( $art_ausgabe ); ?></span><br>
        <?php endif; ?>

        <?php if ( $treffpunkt !== '' ) : ?>
            <strong>Treffpunkt:</strong>
            <span class="morshaus-veranstaltung__wert"><?php echo esc_html( $treffpunkt ); ?></span><br>
        <?php endif; ?>

        <?php if ( $uhrzeit !== '' ) : ?>
            <strong>Uhrzeit:</strong>
            <span class="morshaus-veranstaltung__wert">
                <?php echo esc_html( $uhrzeit ); ?>
                <?php if ( $dauer !== '' ) : ?>
                    | <?php echo esc_html( $dauer ); ?> h
                <?php endif; ?>
            </span>
            <br>
        <?php elseif ( $dauer !== '' ) : ?>
            <strong>Dauer:</strong>
            <span class="morshaus-veranstaltung__wert"><?php echo esc_html( $dauer ); ?> h</span><br>
        <?php endif; ?>

        <?php if ( $tee_runden !== '' ) : ?>
            <strong>Tee:</strong>
            <span class="morshaus-veranstaltung__wert">
                <?php echo esc_html( $tee_runden ); ?>
                <?php echo (
                    $tee_runden === '1'
                    ? ' Runde'
                    : ' Runden'
                ); ?>
                <?php if ( $tee_besonderheit !== '' ) : ?>
                    <?php echo esc_html(
                        ' – ' . $tee_besonderheit
                    ); ?>
                <?php endif; ?>
            </span>

            <br>
        <?php endif; ?>

        <?php if ( $cost_text !== '' ) : ?>
            <strong>Kosten:</strong>
            <span class="morshaus-veranstaltung__wert"><?php echo esc_html( $cost_text ); ?></span><br>
        <?php endif; ?>

        <?php if ( $max_teilnehmer !== '' ) : ?>
            <strong>Teilnehmer:</strong>
            <span class="morshaus-veranstaltung__wert"><?php echo esc_html(
                $angemeldet .
                '/' .
                $max_teilnehmer
            ); ?></span>
        <?php endif; ?>

    </p>

    <?php if ( $beschreibung !== '' ) : ?>
        <div class="morshaus-veranstaltung__beschreibung">
            <button
                class="morshaus-veranstaltung__beschreibung-toggle"
                type="button"
                aria-controls="<?php echo esc_attr($beschreibung_id); ?>"
                aria-label="Beschreibung anzeigen"
                aria-expanded="false"
            >
                <span class="morshaus-veranstaltung__beschreibung-label">Beschreibung anzeigen</span>
                <span class="morshaus-veranstaltung__beschreibung-icon" aria-hidden="true"></span>
            </button>
        </div>
    <?php endif; ?>

            </div>
            <?php if ($card_image !== '') : ?>
                <figure class="morshaus-veranstaltung__media"><?php echo wp_kses_post($card_image); ?></figure>
            <?php endif; ?>
        </div>

        <?php if ( $beschreibung !== '' ) : ?>
            <div class="morshaus-veranstaltung__beschreibung-content" id="<?php echo esc_attr($beschreibung_id); ?>" hidden>
                <?php echo $beschreibung_html; ?>
            </div>
        <?php endif; ?>

        <hr class="morshaus-veranstaltung__trenner">

        <div class="morshaus-veranstaltung__footer<?php echo $card_image !== '' ? ' has-image' : ''; ?>">
            <div
                class="wp-block-button event-add-btn"
                data-event-id="<?php echo esc_attr( $post_id ); ?>"
                data-category="<?php echo esc_attr( $category ); ?>"
                data-art="<?php echo esc_attr( $art_ausgabe ); ?>"
                data-date="<?php echo esc_attr( $datum_ausgabe ); ?>"
                data-time="<?php echo esc_attr( $uhrzeit ); ?>"
                data-location="<?php echo esc_attr( $treffpunkt ); ?>"
                data-name="<?php echo esc_attr( $title ); ?>"
                data-costs="<?php echo esc_attr( $cost_text ); ?>"
                data-price="<?php echo esc_attr( $cost_data['total'] ); ?>"
            >
                <a
                    class="wp-block-button__link wp-element-button event-add-link"
                    href="#"
                >
                    Zur Bestellliste hinzufügen
                </a>
            </div>

            <div class="event-add-feedback" aria-live="polite"></div>
        </div>
    </div>
</article>

    <?php
    return ob_get_clean();
}


/* ==============================================================
 * Veranstaltungs-Shortcode
 *
 * [morshaus_veranstaltungen kategorie="seminare"]
 * [morshaus_veranstaltungen kategorie="begegnung"]
 * [morshaus_veranstaltungen alle="1"]
 * ============================================================== */

function morshaus_veranstaltungen_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'kategorie' => '',
            'alle'      => '',
            'limit'     => -1,
            'vorwahl_haupt' => '',
            'vorwahl_unter' => '',
            'themenfilter' => '',
        ),
        $atts,
        'morshaus_veranstaltungen'
    );

    $args = array(
        'post_type'      => 'veranstaltung',
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['limit']),
        'meta_key'       => '_morshaus_veranstaltung_datum',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
    );

    if (
        empty($atts['alle']) &&
        !empty($atts['kategorie'])
    ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' =>
                    'veranstaltungkategorie',
                'field' =>
                    'slug',
                'terms' =>
                    sanitize_title(
                        $atts['kategorie']
                    ),
                'include_children' => true,
            ),
        );
    }

    $query = new WP_Query($args);

    ob_start();

    static $event_assets_loaded = false;

    if (!$event_assets_loaded) {
        $event_assets_loaded = true;
        ?>

        <style>
            .morshaus-events-shell {
                width: 100%;
                max-width: none;
                margin: 0;
                box-sizing: border-box;
            }

            .morshaus-events-toolbar {
                display: flex;
                align-items: flex-end;
                justify-content: space-between;
                gap: 24px;
                margin-bottom: 28px;
            }

            .morshaus-events-list {
                display: grid !important;
                width: 100%;
                gap: 20px;
            }

            .morshaus-veranstaltung {
                overflow: hidden;
                border: 1px solid var(--mh-color-line);
                border-radius: var(--mh-radius-lg);
                background: var(--mh-color-sage-soft);
                box-shadow: var(--mh-shadow-soft);
            }

            .morshaus-veranstaltung__inner {
                padding: 24px;
            }

            .morshaus-event-filter {
                flex: 1 1 auto;
                min-width: 0;
                margin: 0;
                padding: 0;
                border: 0;
            }

            .morshaus-events-cta {
                display: flex;
                flex: 0 0 calc(40% - 20px);
                justify-content: center;
                margin: 0 24px 0 0;
            }

            .morshaus-events-cta .wp-block-button__link {
                white-space: nowrap;
            }

            .morshaus-event-filter__title {
                margin: 0 0 9px;
                color: #5c6258;
                font-size: 12px;
                font-weight: 700;
                letter-spacing: .08em;
                text-transform: uppercase;
            }

            .morshaus-event-filter__main,
            .morshaus-event-filter__sub {
                display: flex;
                flex-wrap: wrap;
                gap: 6px 16px;
            }

            .morshaus-event-filter__sub {
                margin-top: 10px;
            }

            .morshaus-event-filter__sub[hidden] {
                display: none !important;
            }

            .morshaus-event-filter button {
                appearance: none;
                border: 0;
                border-bottom: 1px solid transparent;
                border-radius: 0;
                background: transparent;
                color: #555b52;
                padding: 2px 0 3px;
                font: inherit;
                font-size: 14px;
                font-weight: 500;
                line-height: 1.35;
                cursor: pointer;
            }

            .morshaus-event-filter button:hover,
            .morshaus-event-filter button:focus-visible,
            .morshaus-event-filter button.is-active {
                background: transparent;
                border-bottom-color: #738d69;
                color: #252823;
            }

            .morshaus-event-filter button.is-active {
                font-weight: 700;
            }

            .morshaus-event-filter__empty {
                margin: 24px 0;
            }

            .morshaus-veranstaltung__kopf {
                margin-bottom: 8px;
            }

            .morshaus-veranstaltung__beschreibung {
                margin-top: 8px;
            }

            .morshaus-veranstaltung__details {
                margin-bottom: 0;
            }

            .morshaus-veranstaltung__main {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(260px, 40%);
                align-items: start;
                gap: 24px;
            }

            .morshaus-veranstaltung__main:not(.has-image) {
                grid-template-columns: minmax(0, 1fr);
            }

            .morshaus-veranstaltung__media {
                width: 100%;
                max-width: 360px;
                margin: 0;
                overflow: hidden;
                border-radius: 10px;
            }

            .morshaus-veranstaltung__bild {
                display: block;
                width: 100%;
                aspect-ratio: 4 / 3;
                object-fit: cover;
            }

            .event-add-btn {
                width: 100%;
                display: flex;
                justify-content: center;
                margin: 0;
            }

            .event-add-feedback {
                min-height: 1.2em;
                margin-top: 8px;
                text-align: center;
                font-size: 0.95rem;
                color: #555;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .event-add-feedback.visible {
                opacity: 1;
            }

            .event-add-feedback .event-feedback-pill {
                display: inline-block;
                background: #e3f0dc;
                color: #3b5a2a;
                border-radius: 999px;
                padding: 4px 10px;
                font-weight: 600;
            }

            @media (max-width: 767px) {
                .morshaus-events-shell {
                    width: 100%;
                    margin-left: 0;
                }

                .morshaus-events-list {
                    gap: 16px;
                }

                .morshaus-veranstaltung__inner {
                    padding: 18px;
                }

                .morshaus-veranstaltung__footer.has-image {
                    grid-template-columns: minmax(0, 1fr) 140px;
                    gap: 14px;
                }

                .event-add-btn {
                    justify-content: center;
                }

                .event-add-feedback {
                    text-align: center;
                }

                .morshaus-events-toolbar {
                    gap: 16px;
                }

                .morshaus-event-filter__main,
                .morshaus-event-filter__sub {
                    gap: 6px 13px;
                }

                .morshaus-event-filter button {
                    font-size: 13px;
                }

                .morshaus-veranstaltung__main {
                    grid-template-columns: minmax(0, 1fr) 140px;
                    gap: 14px;
                }

                .morshaus-events-cta {
                    flex-basis: 140px;
                    margin-right: 18px;
                }

                .morshaus-veranstaltung__media {
                    max-width: 140px;
                }
            }

            @media (max-width: 520px) {
                .morshaus-events-toolbar {
                    align-items: flex-start;
                    flex-direction: column;
                    gap: 16px;
                }

                .morshaus-events-cta {
                    align-self: center;
                    flex-basis: auto;
                    margin-right: 0;
                }

                .morshaus-veranstaltung__main {
                    grid-template-columns: minmax(0, 1fr);
                }

                .morshaus-veranstaltung__media {
                    justify-self: center;
                }

                .morshaus-veranstaltung__footer.has-image {
                    grid-template-columns: minmax(0, 1fr);
                }

                .morshaus-veranstaltung__footer.has-image > .event-add-btn,
                .morshaus-veranstaltung__footer.has-image > .event-add-feedback {
                    grid-column: 1;
                }

                .morshaus-events-list {
                    gap: 12px;
                }

                .morshaus-veranstaltung__inner {
                    padding: 16px;
                }

                .morshaus-veranstaltung__media {
                    grid-row: 1;
                    max-width: 100%;
                }

                .morshaus-veranstaltung__bild {
                    aspect-ratio: 16 / 9;
                }
            }
        </style>

        <?php
    }

    if (!$query->have_posts() && empty($atts['vorwahl_haupt'])) {
        echo '<p>Derzeit sind keine Veranstaltungen geplant.</p>';
        return ob_get_clean();
    }

    ?>
        <div class="morshaus-events-shell" data-morshaus-event-filter data-default-main="<?php echo esc_attr(sanitize_title($atts['vorwahl_haupt'])); ?>" data-default-sub="<?php echo esc_attr(sanitize_title($atts['vorwahl_unter'])); ?>">
            <div class="morshaus-events-toolbar">
                <nav class="morshaus-event-filter" aria-label="Termine filtern">
                    <p class="morshaus-event-filter__title">Termine filtern</p>
                    <div class="morshaus-event-filter__main">
                        <button type="button" class="is-active" data-filter-main="alle" aria-pressed="true">Alle</button>
                        <button type="button" data-filter-main="seminar" aria-pressed="false">Seminare</button>
                        <button type="button" data-filter-main="begegnung" aria-pressed="false">Begegnung</button>
                    </div>
                    <div class="morshaus-event-filter__sub" data-filter-group="seminar" hidden>
                        <button type="button" class="is-active" data-filter-sub="alle" aria-pressed="true">Alle</button>
                        <button type="button" data-filter-sub="am-tisch" aria-pressed="false">Tisch</button>
                        <button type="button" data-filter-sub="in-stille" aria-pressed="false">Stille</button>
                        <button type="button" data-filter-sub="auf-dem-wasser" aria-pressed="false">Wasser</button>
                        <button type="button" data-filter-sub="im-wald" aria-pressed="false">Wald</button>
                    </div>
                    <div class="morshaus-event-filter__sub" data-filter-group="begegnung" hidden>
                        <button type="button" class="is-active" data-filter-sub="alle" aria-pressed="true">Alle</button>
                        <button type="button" data-filter-sub="in-bewegung" aria-pressed="false">Bewegung</button>
                        <button type="button" data-filter-sub="im-sitzen" aria-pressed="false">Sitzen</button>
                    </div>
                    <?php if (!empty($atts['themenfilter'])) : ?>
                        <div class="morshaus-event-filter__sub" data-filter-topic-group="am-tisch" hidden>
                            <button type="button" class="is-active" data-filter-topic="alle" aria-pressed="true">Alle</button>
                            <button type="button" data-filter-topic="grundlagen" aria-pressed="false">Grundlagen</button>
                            <button type="button" data-filter-topic="vertiefung" aria-pressed="false">Vertiefung</button>
                            <button type="button" data-filter-topic="spezial" aria-pressed="false">Spezial</button>
                        </div>
                        <div class="morshaus-event-filter__sub" data-filter-topic-group="in-stille" hidden>
                            <button type="button" class="is-active" data-filter-topic="alle" aria-pressed="true">Alle</button>
                            <button type="button" data-filter-topic="bogen" aria-pressed="false">Bogenschießen</button>
                            <button type="button" data-filter-topic="zeremonie" aria-pressed="false">Zeremonie</button>
                        </div>
                    <?php endif; ?>
                </nav>
                <div class="wp-block-buttons morshaus-events-cta">
                    <div class="wp-block-button">
                        <a class="wp-block-button__link wp-element-button" href="<?php
                            $kontakt = get_page_by_path('kontakt');
                            echo esc_url($kontakt ? get_permalink($kontakt->ID) : home_url('/kontakt/'));
                        ?>">Termin anfragen</a>
                    </div>
                </div>
            </div>
            <div class="morshaus-events-list">
        <?php
        while ($query->have_posts()) {
            $query->the_post();
            echo morshaus_render_veranstaltung(get_the_ID());
        }
        ?>
            </div>
            <p class="morshaus-event-filter__empty" hidden><?php echo $query->post_count ? 'Keine passenden Termine vorhanden.' : 'Derzeit sind keine Veranstaltungen geplant.'; ?></p>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-morshaus-event-filter]').forEach(function (shell) {
                var main = 'alle';
                var sub = 'alle';
                var topic = 'alle';
                var parameters = new URLSearchParams(window.location.search);
                var requestedMain = sanitizeFilterValue(parameters.get('haupt') || shell.dataset.defaultMain);
                var requestedSub = sanitizeFilterValue(parameters.get('unter') || (parameters.get('haupt') ? '' : shell.dataset.defaultSub));
                if (requestedMain === 'session') requestedMain = 'begegnung';
                var legacySubFilters = {
                    grundlagen: 'am-tisch',
                    vertiefung: 'am-tisch',
                    spezial: 'am-tisch',
                    kontemplation: 'in-stille',
                    bogen: 'in-stille',
                    wasser: 'auf-dem-wasser',
                    wald: 'im-wald',
                    laufen: 'in-bewegung',
                    gehen: 'in-bewegung',
                    sitzen: 'im-sitzen'
                };
                requestedSub = legacySubFilters[requestedSub] || requestedSub;
                var mainButtons = shell.querySelectorAll('[data-filter-main]');
                var subGroups = shell.querySelectorAll('[data-filter-group]');
                var topicGroups = shell.querySelectorAll('[data-filter-topic-group]');
                var empty = shell.querySelector('.morshaus-event-filter__empty');

                function sanitizeFilterValue(value) {
                    return String(value || '').toLowerCase().replace(/[^a-z0-9-]/g, '');
                }

                function activate(buttons, activeButton) {
                    buttons.forEach(function (button) {
                        var active = button === activeButton;
                        button.classList.toggle('is-active', active);
                        button.setAttribute('aria-pressed', active ? 'true' : 'false');
                    });
                }

                function applyFilter() {
                    var visible = 0;
                    shell.querySelectorAll('.morshaus-veranstaltung').forEach(function (event) {
                        var matchesMain = main === 'alle' || event.dataset.filterHaupt === main;
                        var matchesSub = sub === 'alle' || event.dataset.filterUnter === sub;
                        var matchesTopic = topic === 'alle' || (event.dataset.filterThemen || '').split(' ').includes(topic);
                        var show = matchesMain && matchesSub && matchesTopic;
                        event.hidden = !show;
                        var separator = event.nextElementSibling;
                        if (separator && separator.classList.contains('wp-block-separator')) {
                            separator.hidden = !show;
                        }
                        if (show) visible += 1;
                    });
                    empty.hidden = visible !== 0;
                }

                mainButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        main = button.dataset.filterMain;
                        sub = 'alle';
                        topic = 'alle';
                        activate(mainButtons, button);
                        subGroups.forEach(function (group) {
                            group.hidden = group.dataset.filterGroup !== main;
                            var buttons = group.querySelectorAll('[data-filter-sub]');
                            activate(buttons, buttons[0]);
                        });
                        topicGroups.forEach(function (group) {
                            group.hidden = true;
                            var buttons = group.querySelectorAll('[data-filter-topic]');
                            activate(buttons, buttons[0]);
                        });
                        applyFilter();
                    });
                });

                subGroups.forEach(function (group) {
                    var buttons = group.querySelectorAll('[data-filter-sub]');
                    buttons.forEach(function (button) {
                        button.addEventListener('click', function () {
                            sub = button.dataset.filterSub;
                            topic = 'alle';
                            activate(buttons, button);
                            topicGroups.forEach(function (topicGroup) {
                                topicGroup.hidden = main !== 'seminar' || topicGroup.dataset.filterTopicGroup !== sub;
                                var topicButtons = topicGroup.querySelectorAll('[data-filter-topic]');
                                activate(topicButtons, topicButtons[0]);
                            });
                            applyFilter();
                        });
                    });
                });

                topicGroups.forEach(function (group) {
                    var buttons = group.querySelectorAll('[data-filter-topic]');
                    buttons.forEach(function (button) {
                        button.addEventListener('click', function () {
                            topic = button.dataset.filterTopic;
                            activate(buttons, button);
                            applyFilter();
                        });
                    });
                });

                if (requestedMain) {
                    var requestedMainButton = shell.querySelector('[data-filter-main="' + requestedMain + '"]');
                    if (requestedMainButton) {
                        main = requestedMain;
                        activate(mainButtons, requestedMainButton);
                        subGroups.forEach(function (group) {
                            group.hidden = group.dataset.filterGroup !== main;
                            var buttons = group.querySelectorAll('[data-filter-sub]');
                            var requestedSubButton = group.querySelector('[data-filter-sub="' + requestedSub + '"]');
                            var activeSubButton = requestedSubButton || buttons[0];
                            if (group.dataset.filterGroup === main && activeSubButton) {
                                sub = activeSubButton.dataset.filterSub;
                            }
                            activate(buttons, activeSubButton);
                        });
                    }
                }
                topicGroups.forEach(function (group) {
                    group.hidden = main !== 'seminar' || group.dataset.filterTopicGroup !== sub;
                });
                applyFilter();
            });
        });
        </script>
        <?php
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode(
    'morshaus_veranstaltungen',
    'morshaus_veranstaltungen_shortcode'
);

function morshaus_begegnungstermine_shortcode() {
    return '<section class="mh-begegnung-events" data-mh-begegnung-events>' .
        morshaus_veranstaltungen_shortcode(array(
            'vorwahl_haupt' => 'begegnung',
            'vorwahl_unter' => 'in-bewegung',
        )) .
        '</section>';
}
add_shortcode(
    'morshaus_begegnungstermine',
    'morshaus_begegnungstermine_shortcode'
);

require_once __DIR__ . '/events-admin.php';
