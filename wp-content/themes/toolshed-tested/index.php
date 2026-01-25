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
                <h1><?php esc_html_e( 'Hands-On Power Tool Reviews You Can Trust', 'toolshed-tested' ); ?></h1>
                <p><?php esc_html_e( 'Independent testing and straight talk on drills, saws, grinders, sanders, and more so you can buy with confidence.', 'toolshed-tested' ); ?></p>
                <a href="<?php echo esc_url( get_post_type_archive_link( 'product_review' ) ); ?>" class="tst-btn tst-btn-cta">
                    <?php esc_html_e( 'Browse Reviews', 'toolshed-tested' ); ?>
                </a>

                <?php
                $hero_terms = get_terms(
                    array(
                        'taxonomy'   => 'product_category',
                        'hide_empty' => true,
                        'number'     => 6,
                    )
                );
                if ( ! empty( $hero_terms ) && ! is_wp_error( $hero_terms ) ) :
                    ?>
                    <div class="hero-categories">
                        <span class="hero-categories-label"><?php esc_html_e( 'Popular categories:', 'toolshed-tested' ); ?></span>
                        <div class="hero-categories-list">
                            <?php foreach ( $hero_terms as $term ) : ?>
                                <a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
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
