<?php
/**
 * Sidebar Template
 *
 * @package Toolshed_Tested
 */

?>

<aside id="secondary" class="sidebar widget-area">
    <?php
    // Sticky affiliate CTA box for single posts with affiliate URLs
    if ( is_singular( array( 'post', 'product_review' ) ) ) {
        $sidebar_affiliate_url = tst_get_affiliate_url( get_post_meta( get_the_ID(), '_tst_affiliate_url', true ) );
        $sidebar_price         = get_post_meta( get_the_ID(), '_tst_price', true );
        $sidebar_rating        = get_post_meta( get_the_ID(), '_tst_rating', true );
        if ( $sidebar_affiliate_url ) :
            ?>
            <div class="widget sidebar-cta-widget">
                <h3 class="widget-title"><?php echo esc_html( get_the_title() ); ?></h3>
                <?php if ( $sidebar_rating ) : ?>
                    <div class="sidebar-cta-rating">
                        <?php echo wp_kses_post( tst_star_rating( $sidebar_rating ) ); ?>
                        <span><?php echo esc_html( $sidebar_rating ); ?>/5</span>
                    </div>
                <?php endif; ?>
                <?php if ( $sidebar_price ) : ?>
                    <div class="sidebar-cta-price"><?php echo esc_html( $sidebar_price ); ?></div>
                <?php endif; ?>
                <a href="<?php echo esc_url( $sidebar_affiliate_url ); ?>"
                   class="tst-btn tst-btn-amazon affiliate-link sidebar-cta-btn"
                   target="_blank"
                   rel="nofollow noopener sponsored">
                    <?php esc_html_e( 'Check Best Price', 'toolshed-tested' ); ?>
                </a>
                <p class="sidebar-cta-disclosure"><?php esc_html_e( 'Prices may vary. As an Amazon Associate we earn from qualifying purchases.', 'toolshed-tested' ); ?></p>
            </div>
            <?php
        endif;
    }
    ?>

    <?php
    $latest_reviews = new WP_Query(
        array(
            'post_type'      => 'post',
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
