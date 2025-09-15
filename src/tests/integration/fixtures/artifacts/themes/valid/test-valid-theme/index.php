<?php
/**
 * Main template file for Test Valid Theme
 */
get_header();
?>

<main id="main" class="site-main">
    <div class="container">
        <?php
        if (have_posts()) {
            while (have_posts()) {
                the_post();
                get_template_part("template-parts/content", get_post_type());
            }
        }
        ?>
    </div>
</main>

<?php
get_footer();
