<?php
/**
 * Archive Template
 *
 * @package Toolshed_Tested
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="tst-container">
        <header class="page-header">
            <?php
            the_archive_title( '<h1 class="page-title">', '</h1>' );
            the_archive_description( '<div class="archive-description">', '</div>' );
            ?>
        </header>

        <?php
        // Show top picks comparison table on category pages
        if ( is_category() || is_tax( 'product_category' ) ) :
            get_template_part( 'template-parts/category/top-picks-table' );
        endif;
        ?>

        <div class="content-area <?php echo is_active_sidebar( 'sidebar-1' ) ? 'has-sidebar' : ''; ?>">
            <div class="main-content">
                <?php if ( have_posts() ) : ?>
                    <div class="posts-grid">
                        <?php
                        while ( have_posts() ) :
                            the_post();
                            get_template_part( 'template-parts/content/content', get_post_type() );
                        endwhile;
                        ?>
                    </div>

                    <?php tst_pagination(); ?>
                <?php else : ?>
                    <?php get_template_part( 'template-parts/content/content', 'none' ); ?>
                <?php endif; ?>
            </div>

            <?php get_sidebar(); ?>
        </div>
    </div>
</main>

<?php
get_footer();
