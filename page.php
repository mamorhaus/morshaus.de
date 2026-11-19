<?php get_header(); ?>
<main id="main" class="mh-main"><div class="mh-container mh-container--content">
<?php while (have_posts()): the_post(); ?>
<article <?php post_class('mh-page'); ?>><header class="mh-page-header<?php echo is_page('begegnung') ? ' screen-reader-text' : ''; ?>"><h1><?php the_title(); ?></h1></header><div class="mh-prose"><?php the_content(); ?></div></article>
<?php endwhile; ?>
</div></main>
<?php get_footer(); ?>
