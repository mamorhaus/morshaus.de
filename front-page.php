<?php get_header(); ?>
<?php if (have_posts()) : the_post(); ?>
<?php if (morshaus_pilot_has_editor_content(get_the_ID())) : ?>
<main id="main" class="mh-home-main mh-home-main--editable">
  <div class="mh-container mh-container--wide">
    <article <?php post_class('mh-home-content'); ?>>
      <header class="screen-reader-text"><h1><?php the_title(); ?></h1></header>
      <div class="mh-prose"><?php the_content(); ?></div>
    </article>
  </div>
</main>
<?php else : ?>
<main id="main" class="mh-home-main">
<?php
$panels = morshaus_pilot_panel_ids();
if (!$panels) {
    $front_id = absint(get_option('page_on_front'));
    if ($front_id) $panels = array($front_id);
}
foreach ($panels as $index => $panel_id) :
    $post = get_post($panel_id);
    if (!$post) continue;
    $image = get_the_post_thumbnail_url($panel_id, 'full');
    $url = get_permalink($panel_id);
    $title = get_the_title($panel_id);
    $content = apply_filters('the_content', $post->post_content);
    $is_intro = $index === 0;
?>
<section class="mh-home-panel <?php echo $is_intro ? 'mh-home-panel--intro' : ''; ?>">
  <div class="mh-container mh-container--wide mh-home-panel__grid">
    <?php if ($image): ?>
      <a class="mh-home-panel__media" href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr($title); ?> öffnen">
        <img src="<?php echo esc_url($image); ?>" alt="" loading="<?php echo $index < 2 ? 'eager' : 'lazy'; ?>" <?php echo $index < 2 ? 'fetchpriority="high"' : ''; ?>>
      </a>
    <?php endif; ?>
    <div class="mh-home-panel__copy">
      <h2><?php echo esc_html($title); ?></h2>
      <?php edit_post_link('Bearbeiten', '<p class="mh-home-panel__edit">', '</p>', $panel_id); ?>
      <div class="mh-prose"><?php echo $content; ?></div>
      <a class="mh-button" href="<?php echo esc_url($url); ?>">Mehr</a>
    </div>
  </div>
</section>
<?php endforeach; ?>
</main>
<?php endif; ?>
<?php endif; ?>
<?php get_footer(); ?>
