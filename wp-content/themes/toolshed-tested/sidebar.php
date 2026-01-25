<?php
/**
 * Sidebar Template
 *
 * @package Toolshed_Tested
 */

?>

<aside id="secondary" class="sidebar widget-area">
    <?php
    $latest_reviews = new WP_Query(
        array(
            'post_type'      => 'product_review',
            'posts_per_page' => 3,
            'no_found_rows'  => true,
        )
    );
    if ( $latest_reviews->have_posts() ) :
        ?>
        <section class="widget widget-latest-reviews">
            <h3 class="widget-title"><?php esc_html_e( 'Latest Reviews', 'toolshed-tested' ); ?></h3>
            <div class="reviews-grid small">
                <?php
                while ( $latest_reviews->have_posts() ) :
                    $latest_reviews->the_post();
                    get_template_part( 'template-parts/review/review', 'card' );
                endwhile;
                ?>
            </div>
        </section>
        <?php
        wp_reset_postdata();
    endif;
    ?>

    <?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
    <?php dynamic_sidebar( 'sidebar-1' ); ?>
    <?php endif; ?>
</aside>
