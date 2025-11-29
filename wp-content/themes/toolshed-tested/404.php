<?php
/**
 * 404 Error Template
 *
 * @package Toolshed_Tested
 */

get_header();
?>

<main id="primary" class="site-main error-404">
    <div class="tst-container">
        <div class="error-content">
            <h1 class="error-title">404</h1>
            <h2><?php esc_html_e( 'Page Not Found', 'toolshed-tested' ); ?></h2>
            <p><?php esc_html_e( 'Sorry, the page you\'re looking for doesn\'t exist or has been moved.', 'toolshed-tested' ); ?></p>
            
            <div class="error-actions">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tst-btn tst-btn-primary">
                    <?php esc_html_e( 'Go Home', 'toolshed-tested' ); ?>
                </a>
                <a href="<?php echo esc_url( get_post_type_archive_link( 'product_review' ) ); ?>" class="tst-btn tst-btn-secondary">
                    <?php esc_html_e( 'Browse Reviews', 'toolshed-tested' ); ?>
                </a>
            </div>

            <div class="error-search">
                <h3><?php esc_html_e( 'Try searching:', 'toolshed-tested' ); ?></h3>
                <?php get_search_form(); ?>
            </div>

            <div class="popular-reviews">
                <h3><?php esc_html_e( 'Popular Reviews', 'toolshed-tested' ); ?></h3>
                <?php
                $popular = new WP_Query(
                    array(
                        'post_type'      => 'product_review',
                        'posts_per_page' => 4,
                        'orderby'        => 'comment_count',
                        'order'          => 'DESC',
                    )
                );

                if ( $popular->have_posts() ) :
                    ?>
                    <div class="reviews-grid small">
                        <?php
                        while ( $popular->have_posts() ) :
                            $popular->the_post();
                            get_template_part( 'template-parts/review/review', 'card' );
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
