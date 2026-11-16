<footer class="mh-site-footer">
  <div class="mh-container mh-container--content mh-footer-inner">
    <p>&copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?></p>
    <?php wp_nav_menu(array('theme_location'=>'footer','container'=>false,'menu_class'=>'mh-footer-menu','fallback_cb'=>false,'depth'=>1)); ?>
  </div>
</footer>
<?php wp_footer(); ?>
</body></html>
