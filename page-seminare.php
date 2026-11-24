<?php
get_header();
?>
<main id="main" class="mh-main">
    <div class="mh-container mh-container--content mh-seminare-container">
        <?php while (have_posts()) : the_post(); ?>
            <?php
            $find_blocks = function ($blocks, $predicate) use (&$find_blocks) {
                $matches = array();
                foreach ($blocks as $block) {
                    if ($predicate($block)) {
                        $matches[] = $block;
                    }
                    if (!empty($block['innerBlocks'])) {
                        $matches = array_merge($matches, $find_blocks($block['innerBlocks'], $predicate));
                    }
                }
                return $matches;
            };
            $blocks = parse_blocks(get_the_content());
            $cards = $find_blocks($blocks, function ($block) {
                return $block['blockName'] === 'core/cover' &&
                    in_array('morshaus-seminar-world-card', preg_split('/\s+/', $block['attrs']['className'] ?? ''), true);
            });
            $leads = $find_blocks($blocks, function ($block) {
                return $block['blockName'] === 'core/paragraph' &&
                    in_array('morshaus-seminar-distributor__lead', preg_split('/\s+/', $block['attrs']['className'] ?? ''), true);
            });
            $filters = array(
                'am-tisch' => 'am-tisch',
                'in-der-stille' => 'in-stille',
                'im-wald' => 'im-wald',
                'auf-dem-wasser' => 'auf-dem-wasser',
            );
            $summaries = array(
                'am-tisch' => 'Zubereitung, Teeanbau und Kulturgeschichte stehen im Mittelpunkt.',
                'in-stille' => 'Kontemplation und Tee verbinden Konzentration und Ruhe.',
                'im-wald' => 'Naturwahrnehmung und gesundheitliche Aspekte von Wald und Tee begleiten die gemeinsamen Teerunden.',
                'auf-dem-wasser' => 'Beim Paddeln folgen wir den Wegen des Tees und seiner Geschichte.',
            );
            $topics = array(
                'am-tisch' => array('grundlagen' => 'Grundlagen', 'vertiefung' => 'Vertiefung', 'spezial' => 'Spezial'),
                'in-stille' => array('bogen' => 'Bogenschießen', 'zeremonie' => 'Zeremonie'),
            );
            ?>
            <article <?php post_class('mh-page mh-seminare-page'); ?>>
                <header class="mh-page-header"><h1><?php the_title(); ?></h1></header>
                <div class="mh-prose">
                    <div class="mh-begegnung-cards">
                        <?php if (!$cards) : ?>
                            <p>Die Seminar-Karten konnten nicht geladen werden. Bitte prüfe die Karten im Seiteneditor.</p>
                        <?php endif; ?>
                        <?php foreach ($cards as $card) :
                            $headings = $find_blocks($card['innerBlocks'], function ($block) {
                                return $block['blockName'] === 'core/heading';
                            });
                            $paragraphs = $find_blocks($card['innerBlocks'], function ($block) {
                                return $block['blockName'] === 'core/paragraph';
                            });
                            $title = isset($headings[0]) ? wp_strip_all_tags($headings[0]['innerHTML']) : '';
                            $filter = $filters[sanitize_title($title)] ?? '';
                            if (!$filter) {
                                echo '<p>Eine Seminar-Karte konnte nicht zugeordnet werden. Bitte prüfe ihren Titel im Seiteneditor.</p>';
                                continue;
                            }
                            $description = $filter === 'in-stille'
                                ? 'Kontemplation · Tee'
                                : (isset($paragraphs[0]) ? wp_strip_all_tags($paragraphs[0]['innerHTML']) : '');
                            $unavailable = morshaus_pilot_seminar_unavailable($filter);
                            ?>
                            <div class="mh-begegnung-card mh-seminar-card" data-filter-sub="<?php echo esc_attr($filter); ?>">
                                <button type="button" class="mh-seminar-card__select" data-mh-card-select aria-label="<?php echo esc_attr('Termine für ' . $title . ' anzeigen'); ?>" aria-pressed="false"></button>
                                <?php if ($unavailable) : ?><span class="mh-seminar-card__unavailable">Unverfügbar</span><?php endif; ?>
                                <?php if (!empty($card['attrs']['id'])) : ?>
                                    <figure class="mh-begegnung-card__image">
                                        <?php echo wp_get_attachment_image($card['attrs']['id'], 'large'); ?>
                                    </figure>
                                <?php endif; ?>
                                <div class="mh-begegnung-card__body">
                                    <p class="mh-begegnung-card__eyebrow"><?php echo esc_html($description); ?></p>
                                    <h2><?php echo esc_html($title); ?></h2>
                                    <p class="mh-begegnung-card__text"><?php echo esc_html($summaries[$filter]); ?></p>
                                    <?php if (isset($topics[$filter])) : ?>
                                        <div class="mh-seminar-card__topics" role="group" aria-label="<?php echo esc_attr('Themen für ' . $title); ?>">
                                            <?php foreach ($topics[$filter] as $slug => $label) : ?>
                                                <?php $topic_unavailable = $slug === 'bogen' && morshaus_pilot_seminar_unavailable('bogen'); ?>
                                                <button type="button" data-mh-card-topic="<?php echo esc_attr($slug); ?>" <?php if ($topic_unavailable) : ?>data-mh-topic-unavailable aria-label="<?php echo esc_attr($label . ' – momentan unverfügbar'); ?>" aria-describedby="mh-bogen-feedback"<?php else : ?>aria-pressed="false"<?php endif; ?>><?php echo esc_html($label); ?></button>
                                            <?php endforeach; ?>
                                            <?php if ($filter === 'in-stille' && morshaus_pilot_seminar_unavailable('bogen')) : ?>
                                                <span id="mh-bogen-feedback" class="mh-seminar-card__topic-feedback" role="status" aria-live="polite"></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($leads[0])) : ?>
                        <div class="mh-begegnung-note">
                            <p><?php foreach (preg_split('/(?<=\.) |\R/', wp_strip_all_tags($leads[0]['innerHTML'])) as $index => $statement) : ?><?php if ($index) : ?><br><?php endif; ?><span><?php echo esc_html($statement); ?></span><?php endforeach; ?></p>
                        </div>
                    <?php endif; ?>
                    <section class="mh-begegnung-events" data-mh-seminare-events>
                        <?php echo morshaus_veranstaltungen_shortcode(array('vorwahl_haupt' => 'seminar', 'themenfilter' => '1')); ?>
                    </section>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</main>
<?php get_footer(); ?>
