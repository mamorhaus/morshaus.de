<?php
get_header();

$topics = array(
    array(
        'title' => 'Genießen',
        'text' => 'Ausgewählte Tees nach Herkunft, Anlass und Geschmack.',
        'link_text' => 'Zu den Tees',
        'url' => home_url('/tee/'),
    ),
    array(
        'title' => 'Zubereiten',
        'text' => 'Werkzeuge und Gefäße für eine bewusste Zubereitung.',
        'link_text' => 'Zu den Utensilien',
        'url' => home_url('/utensilien-teaser/utensilien/'),
    ),
    array(
        'title' => 'Lernen',
        'text' => 'Grundlagen kennenlernen oder Wissen vertiefen.',
        'link_text' => 'Zu den Seminaren',
        'url' => home_url('/seminare/'),
    ),
    array(
        'title' => 'Begegnen',
        'text' => 'Zwei oder drei Tassen und Zeit für ein gutes Gespräch.',
        'link_text' => 'Zur Begegnung',
        'url' => home_url('/begegnung/'),
    ),
);
?>
<?php while (have_posts()) : the_post(); ?>
<main id="main" class="mh-main mh-hallo-main">
    <div class="mh-container mh-container--wide">
        <article <?php post_class('mh-hallo-page'); ?>>
            <?php if (morshaus_pilot_has_editor_content(get_the_ID())) : ?>
                <header class="mh-page-header screen-reader-text">
                    <h1><?php the_title(); ?></h1>
                </header>
                <div class="mh-prose">
                    <?php the_content(); ?>
                </div>
            <?php else : ?>
            <section class="mh-hallo-intro" aria-labelledby="mh-hallo-title">
                <figure class="mh-hallo-intro__portrait">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('large', array(
                            'class' => 'mh-hallo-intro__image',
                            'alt' => 'Portrait des Morshaus-Inhabers',
                            'loading' => 'eager',
                            'fetchpriority' => 'high',
                        )); ?>
                    <?php endif; ?>
                </figure>
                <div class="mh-hallo-intro__copy">
                    <p class="mh-hallo-intro__eyebrow">Morshaus · Tee &amp; mehr</p>
                    <h1 id="mh-hallo-title">Schön, dass du hier bist.</h1>
                    <p class="mh-hallo-intro__lead">Tee ist mehr als ein Getränk. Kult(ur)pflanze, gesundheitsfördernd, belebend, beruhigend, Leidenschaft und Genuss.</p>
                    <p class="mh-hallo-intro__aside">Diese Leidenschaft möchte ich teilen und einen persönlichen Einblick und Einstieg in die Welt des Tees bieten.</p>
                </div>
            </section>

            <section class="mh-hallo-discover" aria-labelledby="mh-hallo-discover-title">
                <h2 id="mh-hallo-discover-title">Vier Wege zum Tee</h2>
                <div class="mh-hallo-topics">
                    <?php foreach ($topics as $topic) : ?>
                        <a class="mh-hallo-topic" href="<?php echo esc_url($topic['url']); ?>">
                            <h3><?php echo esc_html($topic['title']); ?></h3>
                            <p><?php echo esc_html($topic['text']); ?></p>
                            <span class="mh-hallo-topic__link"><?php echo esc_html($topic['link_text']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="mh-hallo-values" aria-labelledby="mh-hallo-values-title">
                <h2 id="mh-hallo-values-title">Hochwertig. Persönlich. Unkompliziert.</h2>
                <p class="mh-hallo-values__intro">Du möchtest Tee bestellen, deine Ausstattung erweitern, mehr über Zubereitung und Kultur lernen oder einfach gemeinsam einen Tee trinken?</p>
                <div class="mh-hallo-values__grid">
                    <div class="mh-hallo-value">
                        <strong>Hochwertig</strong>
                        <span>Tee, Utensilien und Veranstaltungen erster Güte.</span>
                    </div>
                    <div class="mh-hallo-value">
                        <strong>Persönlich</strong>
                        <span>Fragen und Wünsche direkt besprechen.</span>
                    </div>
                    <div class="mh-hallo-value">
                        <strong>Unkompliziert</strong>
                        <span>Den nächsten Schritt gemeinsam abstimmen.</span>
                    </div>
                </div>
            </section>

            <section class="mh-hallo-contact" aria-labelledby="mh-hallo-contact-title">
                <h2 id="mh-hallo-contact-title">Direkt ins Gespräch kommen</h2>
                <a class="mh-button" href="<?php echo esc_url(home_url('/kontakt/')); ?>">Kontakt</a>
            </section>
            <?php endif; ?>
        </article>
    </div>
</main>
<?php endwhile; ?>
<?php get_footer(); ?>
