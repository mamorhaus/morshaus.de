<?php
get_header();
$footer_content_page = is_page(array('datenschutz', 'agb', 'widerrufsrecht', 'bestellung-versand-rechnung'));
?>
<main id="main" class="mh-main"><div class="mh-container <?php echo $footer_content_page ? 'mh-container--wide' : 'mh-container--content'; ?>">
<?php while (have_posts()): the_post(); ?>
<article <?php post_class($footer_content_page ? 'mh-page mh-page--footer-content' : 'mh-page'); ?>><header class="mh-page-header<?php echo is_page('begegnung') ? ' screen-reader-text' : ''; ?>"><h1><?php the_title(); ?></h1></header><div class="mh-prose"><?php the_content(); ?></div></article>
<?php endwhile; ?>
</div></main>
<?php get_footer(); ?>
