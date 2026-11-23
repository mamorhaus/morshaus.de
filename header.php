<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="mh-skip-link" href="#main">Zum Inhalt springen</a>
<header class="mh-site-header">
  <?php $hero = morshaus_pilot_header_url(); ?>
  <?php if ($hero) : ?><div class="mh-header-image"><img src="<?php echo esc_url($hero); ?>" alt=""></div><?php endif; ?>
  <div class="mh-header-inner mh-container mh-container--wide">
    <a class="mh-brand" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
      <?php $logo=morshaus_pilot_logo_url(); if ($logo): ?><img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>"><?php else: ?><span><?php bloginfo('name'); ?></span><?php endif; ?>
    </a>
  </div>
</header>
<nav class="mh-navigation" aria-label="Hauptnavigation">
  <div class="mh-container mh-container--wide mh-navigation-inner">
    <div class="mh-navigation-bar">
      <a class="mh-navigation-order" href="<?php echo esc_url(home_url('/bestellliste/')); ?>" aria-label="Bestellliste">
        <span class="mh-navigation-order-icon" aria-hidden="true">
          <span class="mh-navigation-order-count" hidden></span>
        </span>
        <span>Bestellliste</span>
      </a>
      <?php if (is_singular() && !is_front_page()) : ?>
        <span class="mh-navigation-title" hidden></span>
        <span class="mh-navigation-separator" aria-hidden="true" hidden>|</span>
      <?php endif; ?>
      <button class="mh-menu-toggle" type="button" aria-expanded="false" aria-controls="mh-primary-menu"><span>Menü</span><i aria-hidden="true"></i></button>
    </div>
    <div id="mh-primary-menu" class="mh-navigation-panel">
      <?php wp_nav_menu(array('theme_location'=>'primary','container'=>false,'menu_class'=>'mh-menu','fallback_cb'=>'morshaus_pilot_menu_fallback','depth'=>2)); ?>
    </div>
  </div>
</nav>
