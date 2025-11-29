<?php
/**
 * Main Template File
 *
 * @package Toolshed_Tested
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="tst-container">
        <?php if ( is_home() && ! is_paged() ) : ?>
            <section class="hero-section">
                <h1><?php esc_html_e( 'Expert Reviews for Outdoor Power Equipment', 'toolshed-tested' ); ?></h1>
                <p><?php esc_html_e( 'In-depth, hands-on reviews of lawn mowers, chainsaws, leaf blowers, and more. Find the perfect tools for your yard.', 'toolshed-tested' ); ?></p>
                <a href="<?php echo esc_url( get_post_type_archive_link( 'product_review' ) ); ?>" class="tst-btn tst-btn-cta">
                    <?php esc_html_e( 'Browse Reviews', 'toolshed-tested' ); ?>
                </a>
            </section>
        <?php endif; ?>

        <div class="content-area <?php echo is_active_sidebar( 'sidebar-1' ) ? 'has-sidebar' : ''; ?>">
            <div class="main-content">
                <?php if ( have_posts() ) : ?>
                    <div class="posts-grid">
                        <?php while ( have_posts() ) : the_post(); ?>
                            <?php get_template_part( 'template-parts/content/content', get_post_type() ); ?>
                        <?php endwhile; ?>
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
