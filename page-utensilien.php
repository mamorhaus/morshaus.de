<?php get_header(); ?>
<main id="main" class="mh-main">
    <div class="mh-container mh-container--wide">
        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class('mh-utensilien-page'); ?>>
                <header class="mh-page-header screen-reader-text">
                    <h1><?php the_title(); ?></h1>
                </header>
                <div class="mh-prose">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</main>
<?php get_footer(); ?>
