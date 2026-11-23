<?php
if (!defined('ABSPATH')) {
    exit;
}

function morshaus_register_event_admin_page() {
    add_menu_page(
        'Veranstaltungen verwalten',
        'Veranstaltungen',
        'edit_posts',
        'morshaus-veranstaltungen',
        'morshaus_render_event_admin_page',
        'dashicons-calendar-alt',
        25
    );

    add_submenu_page(
        'morshaus-veranstaltungen',
        'Veranstaltungskategorien',
        'Kategorien',
        'manage_categories',
        'morshaus-event-categories',
        'morshaus_event_admin_redirect_categories'
    );

    add_submenu_page(
        'morshaus-veranstaltungen',
        'Seminar-Verfügbarkeit',
        'Seminar-Verfügbarkeit',
        'edit_posts',
        'morshaus-seminar-verfuegbarkeit',
        'morshaus_render_seminar_availability_admin_page'
    );
}
add_action('admin_menu', 'morshaus_register_event_admin_page');

function morshaus_seminar_availability_labels() {
    return array(
        'am-tisch' => 'Am Tisch',
        'in-stille' => 'In der Stille',
        'im-wald' => 'Im Wald',
        'auf-dem-wasser' => 'Auf dem Wasser',
    );
}

function morshaus_render_seminar_availability_admin_page() {
    if (!current_user_can('edit_posts')) {
        wp_die('Du darfst die Seminar-Verfügbarkeit nicht bearbeiten.');
    }
    ?>
    <div class="wrap">
        <h1>Seminar-Verfügbarkeit</h1>
        <p>Als unverfügbar markierte Seminarwelten erhalten ein Banner. Ihre Termine können nicht neu zur Bestellliste hinzugefügt werden.</p>
        <?php if (isset($_GET['saved']) && $_GET['saved'] === '1') : ?>
            <div class="notice notice-success is-dismissible"><p>Die Seminar-Verfügbarkeit wurde gespeichert.</p></div>
        <?php endif; ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="morshaus_save_seminar_availability">
            <?php wp_nonce_field('morshaus_save_seminar_availability'); ?>
            <?php foreach (morshaus_seminar_availability_labels() as $key => $label) : ?>
                <p><label><input type="checkbox" name="unavailable[<?php echo esc_attr($key); ?>]" value="1" <?php checked(morshaus_pilot_seminar_unavailable($key)); ?>> <?php echo esc_html($label); ?> – nicht verfügbar</label></p>
            <?php endforeach; ?>
            <?php submit_button('Verfügbarkeit speichern'); ?>
        </form>
    </div>
    <?php
}

function morshaus_save_seminar_availability() {
    if (!current_user_can('edit_posts')) {
        wp_die('Du darfst die Seminar-Verfügbarkeit nicht bearbeiten.');
    }
    check_admin_referer('morshaus_save_seminar_availability');
    $posted = isset($_POST['unavailable']) && is_array($_POST['unavailable']) ? wp_unslash($_POST['unavailable']) : array();
    $settings = array();
    foreach (morshaus_seminar_availability_labels() as $key => $label) {
        $settings[$key] = isset($posted[$key]) && $posted[$key] === '1' ? '1' : '0';
    }
    update_option('morshaus_seminar_unavailable', $settings);
    if (get_option('morshaus_seminar_unavailable') !== $settings) {
        wp_die('Die Seminar-Verfügbarkeit konnte nicht gespeichert werden.');
    }
    wp_safe_redirect(admin_url('admin.php?page=morshaus-seminar-verfuegbarkeit&saved=1'));
    exit;
}
add_action('admin_post_morshaus_save_seminar_availability', 'morshaus_save_seminar_availability');

function morshaus_event_admin_redirect_categories() {
    if (!current_user_can('manage_categories')) {
        wp_die('Du darfst Veranstaltungskategorien nicht bearbeiten.');
    }

    echo '<div class="wrap"><h1>Veranstaltungskategorien</h1><p><a class="button button-primary" href="' .
        esc_url(
            admin_url(
                'edit-tags.php?taxonomy=veranstaltungkategorie&post_type=veranstaltung'
            )
        ) .
        '">Kategorien öffnen</a></p></div>';
}

function morshaus_event_admin_enqueue_assets($hook_suffix) {
    if ($hook_suffix !== 'toplevel_page_morshaus-veranstaltungen') {
        return;
    }

    $theme_dir = get_template_directory();
    $theme_uri = get_template_directory_uri();
    $css_path = $theme_dir . '/assets/css/admin-events.css';
    $js_path = $theme_dir . '/assets/js/admin-events.js';

    wp_enqueue_style(
        'morshaus-event-admin',
        $theme_uri . '/assets/css/admin-events.css',
        array(),
        file_exists($css_path)
            ? filemtime($css_path)
            : MORSHAUS_PILOT_VERSION
    );

    wp_enqueue_media();
    wp_enqueue_editor();
    wp_enqueue_script(
        'morshaus-event-admin',
        $theme_uri . '/assets/js/admin-events.js',
        array('jquery', 'media-views'),
        file_exists($js_path)
            ? filemtime($js_path)
            : MORSHAUS_PILOT_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts', 'morshaus_event_admin_enqueue_assets');

function morshaus_event_admin_default_form_data() {
    return array(
        'post_id'          => 0,
        'title'            => '',
        'content'          => '',
        'status'           => current_user_can('publish_posts')
            ? 'publish'
            : 'draft',
        'image_id'         => 0,
        'terms'            => array(),
        'datum'            => '',
        'treffpunkt'       => '',
        'uhrzeit'          => '',
        'dauer'            => '',
        'tee_runden'       => '1',
        'tee_besonderheit' => '',
        'materialkosten'   => '',
        'seminarkosten'    => '',
        'extrakosten'      => '',
        'angemeldet'       => '0',
        'max_teilnehmer'   => '',
    );
}

function morshaus_event_admin_get_form_data($post_id = 0) {
    $data = morshaus_event_admin_default_form_data();

    if (!$post_id) {
        return $data;
    }

    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'veranstaltung') {
        return $data;
    }

    $data['post_id'] = (int) $post->ID;
    $data['title'] = get_the_title($post);
    $data['content'] = get_post_field('post_content', $post->ID);
    $data['status'] = $post->post_status === 'publish'
        ? 'publish'
        : 'draft';
    $data['image_id'] = get_post_thumbnail_id($post->ID);

    $terms = wp_get_object_terms(
        $post->ID,
        'veranstaltungkategorie',
        array('fields' => 'ids')
    );
    if (!is_wp_error($terms)) {
        $data['terms'] = array_map('absint', $terms);
    }

    $meta_fields = array(
        'datum',
        'treffpunkt',
        'uhrzeit',
        'dauer',
        'tee_runden',
        'tee_besonderheit',
        'materialkosten',
        'seminarkosten',
        'extrakosten',
        'angemeldet',
        'max_teilnehmer',
    );

    foreach ($meta_fields as $field) {
        $data[$field] = get_post_meta(
            $post->ID,
            '_morshaus_veranstaltung_' . $field,
            true
        );
    }

    return $data;
}

function morshaus_event_admin_posted_text($key) {
    if (!isset($_POST[$key]) || !is_scalar($_POST[$key])) {
        return '';
    }

    return sanitize_text_field(wp_unslash($_POST[$key]));
}

function morshaus_event_admin_get_posted_form_data() {
    $data = morshaus_event_admin_default_form_data();
    $data['post_id'] = absint(
        morshaus_event_admin_posted_text('morshaus_event_admin_post_id')
    );
    $data['title'] = morshaus_event_admin_posted_text(
        'morshaus_event_admin_title'
    );
    $data['status'] = sanitize_key(
        morshaus_event_admin_posted_text('morshaus_event_admin_status')
    );
    $data['image_id'] = absint(
        morshaus_event_admin_posted_text('morshaus_event_admin_image_id')
    );

    if (
        isset($_POST['morshaus_event_admin_content']) &&
        is_string($_POST['morshaus_event_admin_content'])
    ) {
        $data['content'] = wp_kses_post(
            wp_unslash($_POST['morshaus_event_admin_content'])
        );
    }

    $meta_fields = array(
        'datum',
        'treffpunkt',
        'uhrzeit',
        'dauer',
        'tee_runden',
        'tee_besonderheit',
        'materialkosten',
        'seminarkosten',
        'extrakosten',
        'angemeldet',
        'max_teilnehmer',
    );

    foreach ($meta_fields as $field) {
        $data[$field] = morshaus_event_admin_posted_text(
            'morshaus_veranstaltung_' . $field
        );
    }

    if (
        isset($_POST['morshaus_event_admin_terms']) &&
        is_array($_POST['morshaus_event_admin_terms'])
    ) {
        $term_ids = array();
        foreach (wp_unslash($_POST['morshaus_event_admin_terms']) as $term_id) {
            if (is_scalar($term_id)) {
                $term_ids[] = absint($term_id);
            }
        }
        $data['terms'] = array_values(array_unique(array_filter($term_ids)));
    }

    return $data;
}

function morshaus_event_admin_validate_form($data) {
    $errors = array();

    if ($data['title'] === '') {
        $errors[] = 'Bitte einen Veranstaltungstitel angeben.';
    }

    $date = DateTime::createFromFormat('Y-m-d', $data['datum']);
    if (
        $data['datum'] === '' ||
        !$date ||
        $date->format('Y-m-d') !== $data['datum']
    ) {
        $errors[] = 'Bitte ein gültiges Veranstaltungsdatum angeben.';
    }

    if (empty($data['terms'])) {
        $errors[] = 'Bitte mindestens eine Veranstaltungskategorie auswählen.';
    } else {
        foreach ($data['terms'] as $term_id) {
            $term = get_term($term_id, 'veranstaltungkategorie');
            if (!$term || is_wp_error($term)) {
                $errors[] = 'Eine ausgewählte Veranstaltungskategorie ist ungültig.';
                break;
            }
        }
    }

    if (
        $data['uhrzeit'] !== '' &&
        !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $data['uhrzeit'])
    ) {
        $errors[] = 'Bitte eine gültige Uhrzeit im Format hh:mm angeben.';
    }

    if ($data['dauer'] !== '') {
        $duration = str_replace(',', '.', $data['dauer']);
        if (!is_numeric($duration) || (float) $duration < 0.5) {
            $errors[] = 'Die Dauer muss mindestens 0,5 Stunden betragen.';
        }
    }

    if ($data['tee_runden'] !== '') {
        if (
            !ctype_digit($data['tee_runden']) ||
            (int) $data['tee_runden'] < 1 ||
            (int) $data['tee_runden'] > 6
        ) {
            $errors[] = 'Die Anzahl der Tee-Runden muss zwischen 1 und 6 liegen.';
        }
    }

    foreach (
        array('materialkosten', 'seminarkosten', 'extrakosten') as $field
    ) {
        if ($data[$field] === '') {
            continue;
        }

        $cost = str_replace(',', '.', $data[$field]);
        if (!is_numeric($cost) || (float) $cost < 0) {
            $errors[] = 'Kosten müssen als nichtnegative Zahl angegeben werden.';
            break;
        }
    }

    foreach (array('angemeldet', 'max_teilnehmer') as $field) {
        if (
            $data[$field] !== '' &&
            !ctype_digit($data[$field])
        ) {
            $errors[] = 'Teilnehmerzahlen müssen ganze, nichtnegative Zahlen sein.';
            break;
        }
    }

    if (!in_array($data['status'], array('draft', 'publish'), true)) {
        $errors[] = 'Bitte einen gültigen Veröffentlichungsstatus auswählen.';
    } else    if (
        $data['status'] === 'publish' &&
        !current_user_can('publish_posts') &&
        (
            !$data['post_id'] ||
            get_post_status($data['post_id']) !== 'publish'
        )
    ) {
        $errors[] = 'Du darfst Veranstaltungen nicht veröffentlichen.';
    }

    if ($data['image_id']) {
        $image = get_post($data['image_id']);
        if (
            !$image ||
            $image->post_type !== 'attachment' ||
            !wp_attachment_is_image($data['image_id']) ||
            !current_user_can('edit_post', $data['image_id'])
        ) {
            $errors[] = 'Das ausgewählte Bild ist ungültig oder darf nicht verwendet werden.';
        }
    }

    return $errors;
}

function morshaus_event_admin_render_term_checkboxes(
    $terms,
    $selected,
    $parent_id = 0,
    $depth = 0
) {
    foreach ($terms as $term) {
        if ((int) $term->parent !== (int) $parent_id) {
            continue;
        }
        ?>
        <label class="morshaus-event-admin__term" style="padding-left: <?php echo esc_attr($depth * 18); ?>px;">
            <input
                type="checkbox"
                name="morshaus_event_admin_terms[]"
                value="<?php echo esc_attr($term->term_id); ?>"
                <?php checked(in_array((int) $term->term_id, $selected, true)); ?>
            >
            <span><?php echo esc_html($term->name); ?></span>
        </label>
        <?php
        morshaus_event_admin_render_term_checkboxes(
            $terms,
            $selected,
            $term->term_id,
            $depth + 1
        );
    }
}

function morshaus_event_admin_handle_request(&$form_data, &$errors) {
    if (
        !isset($_SERVER['REQUEST_METHOD']) ||
        $_SERVER['REQUEST_METHOD'] !== 'POST' ||
        !isset($_POST['morshaus_event_admin_action']) ||
        !is_scalar($_POST['morshaus_event_admin_action'])
    ) {
        return;
    }

    $action = sanitize_key(
        wp_unslash($_POST['morshaus_event_admin_action'])
    );

    if ($action === 'delete') {
        $post_id = isset($_POST['morshaus_event_admin_delete_id']) &&
            is_scalar($_POST['morshaus_event_admin_delete_id'])
            ? absint(wp_unslash($_POST['morshaus_event_admin_delete_id']))
            : 0;
        check_admin_referer(
            'morshaus_event_admin_delete_' . $post_id,
            'morshaus_event_admin_delete_nonce'
        );

        if (
            !$post_id ||
            get_post_type($post_id) !== 'veranstaltung' ||
            !current_user_can('delete_post', $post_id)
        ) {
            wp_die('Du darfst diese Veranstaltung nicht löschen.');
        }

        if (!wp_trash_post($post_id)) {
            wp_die('Die Veranstaltung konnte nicht in den Papierkorb verschoben werden.');
        }

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'    => 'morshaus-veranstaltungen',
                    'message' => 'deleted',
                ),
                admin_url('admin.php')
            )
        );
        exit;
    }

    if ($action !== 'save') {
        return;
    }

    check_admin_referer(
        'morshaus_event_admin_save',
        'morshaus_event_admin_nonce'
    );
    $form_data = morshaus_event_admin_get_posted_form_data();
    $post_id = $form_data['post_id'];

    if ($post_id) {
        if (
            get_post_type($post_id) !== 'veranstaltung' ||
            !current_user_can('edit_post', $post_id)
        ) {
            wp_die('Du darfst diese Veranstaltung nicht bearbeiten.');
        }
    } elseif (!current_user_can('edit_posts')) {
        wp_die('Du darfst keine Veranstaltungen anlegen.');
    }

    $errors = morshaus_event_admin_validate_form($form_data);
    if ($errors) {
        return;
    }

    $post_data = array(
        'post_type'    => 'veranstaltung',
        'post_status'  => $form_data['status'],
        'post_title'   => $form_data['title'],
        'post_content' => $form_data['content'],
    );

    if ($post_id) {
        $post_data['ID'] = $post_id;
    } else {
        $post_data['post_author'] = get_current_user_id();
    }

    $saved_id = wp_insert_post($post_data, true);
    if (is_wp_error($saved_id)) {
        $errors[] = 'Die Veranstaltung konnte nicht gespeichert werden: ' .
            $saved_id->get_error_message();
        return;
    }

    $form_data['post_id'] = (int) $saved_id;
    $term_result = wp_set_object_terms(
        $saved_id,
        $form_data['terms'],
        'veranstaltungkategorie',
        false
    );
    if (is_wp_error($term_result)) {
        $errors[] = 'Die Kategorien konnten nicht gespeichert werden: ' .
            $term_result->get_error_message();
        return;
    }

    if ($form_data['image_id']) {
        set_post_thumbnail($saved_id, $form_data['image_id']);
    } else {
        delete_post_thumbnail($saved_id);
    }

    wp_safe_redirect(
        add_query_arg(
            array(
                'page'    => 'morshaus-veranstaltungen',
                'message' => 'saved',
            ),
            admin_url('admin.php')
        )
    );
    exit;
}

function morshaus_event_admin_process_request() {
    if (
        !is_admin() ||
        !isset($_GET['page']) ||
        !is_scalar($_GET['page'])
    ) {
        return;
    }

    $page = sanitize_key(wp_unslash($_GET['page']));
    if ($page === 'morshaus-event-categories') {
        if (!current_user_can('manage_categories')) {
            wp_die('Du darfst Veranstaltungskategorien nicht bearbeiten.');
        }

        wp_safe_redirect(
            admin_url(
                'edit-tags.php?taxonomy=veranstaltungkategorie&post_type=veranstaltung'
            )
        );
        exit;
    }

    if (
        $page !== 'morshaus-veranstaltungen' ||
        !isset($_SERVER['REQUEST_METHOD']) ||
        $_SERVER['REQUEST_METHOD'] !== 'POST'
    ) {
        return;
    }

    $form_data = null;
    $errors = array();
    morshaus_event_admin_handle_request($form_data, $errors);

    if ($form_data !== null || $errors) {
        $GLOBALS['morshaus_event_admin_request_state'] = array(
            'form_data' => $form_data,
            'errors'    => $errors,
        );
    }
}
add_action('admin_init', 'morshaus_event_admin_process_request', 1);

function morshaus_render_event_admin_page() {
    if (!current_user_can('edit_posts')) {
        wp_die('Du darfst Veranstaltungen nicht verwalten.');
    }

    $request_state = isset($GLOBALS['morshaus_event_admin_request_state'])
        ? $GLOBALS['morshaus_event_admin_request_state']
        : array();
    $errors = isset($request_state['errors'])
        ? $request_state['errors']
        : array();
    $form_data = isset($request_state['form_data'])
        ? $request_state['form_data']
        : null;

    if ($form_data === null) {
        $edit_id = isset($_GET['edit']) && is_scalar($_GET['edit'])
            ? absint(wp_unslash($_GET['edit']))
            : 0;
        if ($edit_id) {
            if (
                get_post_type($edit_id) !== 'veranstaltung' ||
                !current_user_can('edit_post', $edit_id)
            ) {
                wp_die('Du darfst diese Veranstaltung nicht bearbeiten.');
            }
            $form_data = morshaus_event_admin_get_form_data($edit_id);
        } elseif (isset($_GET['new'])) {
            $form_data = morshaus_event_admin_default_form_data();
        }
    }

    $notice = isset($_GET['message']) && is_scalar($_GET['message'])
        ? sanitize_key(wp_unslash($_GET['message']))
        : '';
    $terms = get_terms(
        array(
            'taxonomy'   => 'veranstaltungkategorie',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        )
    );

    ?>
    <div class="wrap morshaus-event-admin">
        <div class="morshaus-event-admin__header">
            <div>
                <h1>Veranstaltungen</h1>
                <p>Veröffentlichte Veranstaltungen werden automatisch als Terminkarten ausgegeben.</p>
            </div>
            <a
                class="page-title-action"
                href="<?php echo esc_url(admin_url('admin.php?page=morshaus-veranstaltungen&new=1')); ?>"
            >Veranstaltung hinzufügen</a>
        </div>

        <?php if ($notice === 'saved') : ?>
            <div class="notice notice-success is-dismissible"><p>Veranstaltung gespeichert.</p></div>
        <?php elseif ($notice === 'deleted') : ?>
            <div class="notice notice-success is-dismissible"><p>Veranstaltung in den Papierkorb verschoben.</p></div>
        <?php endif; ?>

        <?php if ($errors) : ?>
            <div class="notice notice-error">
                <?php foreach ($errors as $error) : ?>
                    <p><?php echo esc_html($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($form_data !== null) : ?>
            <?php if (is_wp_error($terms)) : ?>
                <div class="notice notice-error"><p>Die Veranstaltungskategorien konnten nicht geladen werden: <?php echo esc_html($terms->get_error_message()); ?></p></div>
            <?php else : ?>
                <section class="morshaus-event-admin__form-card">
                    <h2><?php echo $form_data['post_id'] ? 'Veranstaltung bearbeiten' : 'Neue Veranstaltung'; ?></h2>
                    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=morshaus-veranstaltungen')); ?>">
                        <input type="hidden" name="morshaus_event_admin_action" value="save">
                        <input type="hidden" name="morshaus_event_admin_post_id" value="<?php echo esc_attr($form_data['post_id']); ?>">
                        <?php
                        wp_nonce_field(
                            'morshaus_event_admin_save',
                            'morshaus_event_admin_nonce'
                        );
                        wp_nonce_field(
                            'morshaus_save_veranstaltung_meta',
                            'morshaus_veranstaltung_nonce'
                        );
                        ?>

                        <div class="morshaus-event-admin__top-grid">
                            <div class="morshaus-event-admin__field">
                                <label for="morshaus-event-admin-title">Titel der Veranstaltung</label>
                                <input
                                    type="text"
                                    id="morshaus-event-admin-title"
                                    name="morshaus_event_admin_title"
                                    value="<?php echo esc_attr($form_data['title']); ?>"
                                    required
                                >
                            </div>

                            <div class="morshaus-event-admin__field">
                                <label for="morshaus_event_admin_status">Status</label>
                                <?php if (current_user_can('publish_posts')) : ?>
                                    <select id="morshaus_event_admin_status" name="morshaus_event_admin_status">
                                        <option value="publish" <?php selected($form_data['status'], 'publish'); ?>>Veröffentlicht – erscheint als Terminkarte</option>
                                        <option value="draft" <?php selected($form_data['status'], 'draft'); ?>>Entwurf – noch nicht sichtbar</option>
                                    </select>
                                <?php else : ?>
                                    <input type="hidden" name="morshaus_event_admin_status" value="<?php echo esc_attr($form_data['status']); ?>">
                                    <p class="description">
                                        <?php echo $form_data['status'] === 'publish' ? 'Veröffentlicht – erscheint als Terminkarte' : 'Entwurf – noch nicht sichtbar'; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="morshaus-event-admin__field">
                            <label>Beschreibung</label>
                            <?php
                            wp_editor(
                                $form_data['content'],
                                'morshaus_event_description',
                                array(
                                    'textarea_name' => 'morshaus_event_admin_content',
                                    'textarea_rows' => 8,
                                    'media_buttons' => false,
                                    'teeny'         => true,
                                    'quicktags'     => true,
                                )
                            );
                            ?>
                        </div>

                        <div class="morshaus-event-admin__section-grid">
                            <section class="morshaus-event-admin__section">
                                <h3>Bild für die Terminkarte</h3>
                                <input
                                    type="hidden"
                                    id="morshaus-event-admin-image-id"
                                    name="morshaus_event_admin_image_id"
                                    value="<?php echo esc_attr($form_data['image_id']); ?>"
                                >
                                <div class="morshaus-event-admin__image-preview" id="morshaus-event-admin-image-preview">
                                    <?php
                                    if ($form_data['image_id']) {
                                        echo wp_get_attachment_image(
                                            $form_data['image_id'],
                                            'medium'
                                        );
                                    } else {
                                        echo '<p>Noch kein eigenes Bild ausgewählt. Ohne eigenes Bild wird das Kategorienbild verwendet.</p>';
                                    }
                                    ?>
                                </div>
                                <div class="morshaus-event-admin__image-actions">
                                    <button type="button" class="button" id="morshaus-event-admin-select-image">Bild auswählen</button>
                                    <button
                                        type="button"
                                        class="button"
                                        id="morshaus-event-admin-remove-image"
                                        <?php disabled(!$form_data['image_id']); ?>
                                    >Eigenes Bild entfernen</button>
                                </div>
                            </section>

                            <section class="morshaus-event-admin__section">
                                <h3>Veranstaltungskategorien</h3>
                                <p class="description">Die Auswahl bestimmt, ob die Karte bei Begegnung oder Seminaren erscheint und welchen Filter sie erhält.</p>
                                <div class="morshaus-event-admin__terms">
                                    <?php
                                    morshaus_event_admin_render_term_checkboxes(
                                        $terms,
                                        $form_data['terms']
                                    );
                                    ?>
                                </div>
                            </section>
                        </div>

                        <section class="morshaus-event-admin__section">
                            <h3>Termindaten</h3>
                            <div class="morshaus-event-admin__fields">
                                <div class="morshaus-event-admin__field">
                                    <label for="morshaus_veranstaltung_datum">Datum</label>
                                    <input type="date" id="morshaus_veranstaltung_datum" name="morshaus_veranstaltung_datum" value="<?php echo esc_attr($form_data['datum']); ?>" required>
                                </div>
                                <div class="morshaus-event-admin__field">
                                    <label for="morshaus_veranstaltung_treffpunkt">Treffpunkt / Ort</label>
                                    <input type="text" id="morshaus_veranstaltung_treffpunkt" name="morshaus_veranstaltung_treffpunkt" value="<?php echo esc_attr($form_data['treffpunkt']); ?>">
                                </div>
                                <div class="morshaus-event-admin__field">
                                    <label for="morshaus_veranstaltung_uhrzeit">Uhrzeit</label>
                                    <input type="time" id="morshaus_veranstaltung_uhrzeit" name="morshaus_veranstaltung_uhrzeit" value="<?php echo esc_attr($form_data['uhrzeit']); ?>" step="1800">
                                </div>
                                <div class="morshaus-event-admin__field">
                                    <label for="morshaus_veranstaltung_dauer">Dauer in Stunden</label>
                                    <input type="text" inputmode="decimal" id="morshaus_veranstaltung_dauer" name="morshaus_veranstaltung_dauer" value="<?php echo esc_attr($form_data['dauer']); ?>" placeholder="z. B. 1,5">
                                </div>
                                <div class="morshaus-event-admin__field">
                                    <label for="morshaus_veranstaltung_tee_runden">Tee-Runden</label>
                                    <input type="number" id="morshaus_veranstaltung_tee_runden" name="morshaus_veranstaltung_tee_runden" value="<?php echo esc_attr($form_data['tee_runden']); ?>" min="1" max="6" step="1">
                                </div>
                                <div class="morshaus-event-admin__field">
                                    <label for="morshaus_veranstaltung_tee_besonderheit">Tee-Besonderheit</label>
                                    <input type="text" id="morshaus_veranstaltung_tee_besonderheit" name="morshaus_veranstaltung_tee_besonderheit" value="<?php echo esc_attr($form_data['tee_besonderheit']); ?>">
                                </div>
                                <div class="morshaus-event-admin__field">
                                    <label for="morshaus_veranstaltung_materialkosten">Materialkosten (€)</label>
                                    <input type="text" inputmode="decimal" id="morshaus_veranstaltung_materialkosten" name="morshaus_veranstaltung_materialkosten" value="<?php echo esc_attr($form_data['materialkosten']); ?>">
                                </div>
                                <div class="morshaus-event-admin__field">
                                    <label for="morshaus_veranstaltung_seminarkosten">Seminarkosten (€)</label>
                                    <input type="text" inputmode="decimal" id="morshaus_veranstaltung_seminarkosten" name="morshaus_veranstaltung_seminarkosten" value="<?php echo esc_attr($form_data['seminarkosten']); ?>">
                                </div>
                                <div class="morshaus-event-admin__field">
                                    <label for="morshaus_veranstaltung_extrakosten">Extrakosten (€)</label>
                                    <input type="text" inputmode="decimal" id="morshaus_veranstaltung_extrakosten" name="morshaus_veranstaltung_extrakosten" value="<?php echo esc_attr($form_data['extrakosten']); ?>">
                                </div>
                                <div class="morshaus-event-admin__field">
                                    <label for="morshaus_veranstaltung_angemeldet">Angemeldete Teilnehmer</label>
                                    <input type="number" id="morshaus_veranstaltung_angemeldet" name="morshaus_veranstaltung_angemeldet" value="<?php echo esc_attr($form_data['angemeldet']); ?>" min="0" step="1">
                                </div>
                                <div class="morshaus-event-admin__field">
                                    <label for="morshaus_veranstaltung_max_teilnehmer">Maximale Teilnehmerzahl</label>
                                    <input type="number" id="morshaus_veranstaltung_max_teilnehmer" name="morshaus_veranstaltung_max_teilnehmer" value="<?php echo esc_attr($form_data['max_teilnehmer']); ?>" min="0" step="1">
                                </div>
                            </div>
                        </section>

                        <p class="submit">
                            <button type="submit" class="button button-primary">
                                <?php echo $form_data['post_id'] ? 'Veranstaltung speichern' : 'Veranstaltung anlegen'; ?>
                            </button>
                            <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=morshaus-veranstaltungen')); ?>">Abbrechen</a>
                        </p>
                    </form>
                </section>
            <?php endif; ?>
        <?php endif; ?>

        <section class="morshaus-event-admin__list">
            <h2>Vorhandene Veranstaltungen</h2>
            <?php
            $events = get_posts(
                array(
                    'post_type'      => 'veranstaltung',
                    'post_status'    => array('publish', 'draft', 'pending', 'future', 'private'),
                    'posts_per_page' => -1,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                )
            );
            ?>
            <?php if ($events) : ?>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th class="morshaus-event-admin__image-column">Bild</th>
                            <th>Veranstaltung</th>
                            <th>Termin</th>
                            <th>Kategorie</th>
                            <th>Status</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event) : ?>
                            <?php
                            if (!current_user_can('edit_post', $event->ID)) {
                                continue;
                            }
                            $event_date = get_post_meta(
                                $event->ID,
                                '_morshaus_veranstaltung_datum',
                                true
                            );
                            $event_timestamp = $event_date
                                ? strtotime($event_date)
                                : false;
                            $event_terms = get_the_terms(
                                $event->ID,
                                'veranstaltungkategorie'
                            );
                            $event_term_names = (
                                is_array($event_terms) &&
                                !is_wp_error($event_terms)
                            )
                                ? implode(', ', wp_list_pluck($event_terms, 'name'))
                                : '—';
                            $status_object = get_post_status_object(
                                $event->post_status
                            );
                            ?>
                            <tr>
                                <td>
                                    <?php
                                    $thumbnail = get_the_post_thumbnail(
                                        $event->ID,
                                        'thumbnail'
                                    );
                                    echo $thumbnail
                                        ? wp_kses_post($thumbnail)
                                        : '—';
                                    ?>
                                </td>
                                <td><strong><?php echo esc_html(get_the_title($event)); ?></strong></td>
                                <td>
                                    <?php
                                    echo $event_timestamp
                                        ? esc_html(date_i18n('d.m.Y', $event_timestamp))
                                        : '—';
                                    ?>
                                </td>
                                <td><?php echo esc_html($event_term_names); ?></td>
                                <td><?php echo $status_object ? esc_html($status_object->label) : esc_html($event->post_status); ?></td>
                                <td class="morshaus-event-admin__actions">
                                    <a class="button button-small" href="<?php echo esc_url(admin_url('admin.php?page=morshaus-veranstaltungen&edit=' . $event->ID)); ?>">Bearbeiten</a>
                                    <?php if (current_user_can('delete_post', $event->ID)) : ?>
                                        <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=morshaus-veranstaltungen')); ?>">
                                            <input type="hidden" name="morshaus_event_admin_action" value="delete">
                                            <input type="hidden" name="morshaus_event_admin_delete_id" value="<?php echo esc_attr($event->ID); ?>">
                                            <?php
                                            wp_nonce_field(
                                                'morshaus_event_admin_delete_' . $event->ID,
                                                'morshaus_event_admin_delete_nonce'
                                            );
                                            ?>
                                            <button type="submit" class="button button-small" onclick="return confirm('Veranstaltung in den Papierkorb verschieben?');">Papierkorb</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>Noch keine Veranstaltungen angelegt.</p>
            <?php endif; ?>
        </section>
    </div>
    <?php
}
