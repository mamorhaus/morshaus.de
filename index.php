<?php get_header(); ?>
<main id="main" class="mh-main"><div class="mh-container mh-container--content">
<?php if (have_posts()): while (have_posts()): the_post(); ?><article <?php post_class('mh-list-item'); ?>><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></article><?php endwhile; the_posts_pagination(); else: ?><p>Keine Inhalte gefunden.</p><?php endif; ?>
</div></main><?php get_footer(); ?>
