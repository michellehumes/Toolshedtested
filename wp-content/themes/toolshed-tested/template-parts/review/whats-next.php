<?php
/**
 * What's Next Section for Single Reviews
 *
 * Provides clear next-step CTAs after reading a review.
 *
 * @package Toolshed_Tested
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current product's categories
$categories = get_the_terms( get_the_ID(), 'product_category' );
$brands     = get_the_terms( get_the_ID(), 'product_brand' );
?>

<section class="whats-next">
    <h3><?php esc_html_e( "What's Next?", 'toolshed-tested' ); ?></h3>
    <p class="whats-next-intro"><?php esc_html_e( 'Continue your research with these resources:', 'toolshed-tested' ); ?></p>

    <div class="next-actions">
        <?php if ( $categories && ! is_wp_error( $categories ) ) :
            $category = $categories[0];
            ?>
            <a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="next-action-card">
                <span class="action-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="M21 21l-4.35-4.35"></path>
                    </svg>
                </span>
                <span class="action-content">
                    <span class="action-title"><?php printf( esc_html__( 'Browse All %s', 'toolshed-tested' ), esc_html( $category->name ) ); ?></span>
                    <span class="action-desc"><?php printf( esc_html__( 'See our complete %s reviews', 'toolshed-tested' ), esc_html( strtolower( $category->name ) ) ); ?></span>
                </span>
                <span class="action-arrow">&rarr;</span>
            </a>
        <?php endif; ?>

        <?php if ( $brands && ! is_wp_error( $brands ) ) :
            $brand = $brands[0];
            ?>
            <a href="<?php echo esc_url( get_term_link( $brand ) ); ?>" class="next-action-card">
                <span class="action-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                    </svg>
                </span>
                <span class="action-content">
                    <span class="action-title"><?php printf( esc_html__( 'More %s Reviews', 'toolshed-tested' ), esc_html( $brand->name ) ); ?></span>
                    <span class="action-desc"><?php printf( esc_html__( 'Explore other %s products', 'toolshed-tested' ), esc_html( $brand->name ) ); ?></span>
                </span>
                <span class="action-arrow">&rarr;</span>
            </a>
        <?php endif; ?>

        <a href="<?php echo esc_url( get_post_type_archive_link( 'product_review' ) ); ?>" class="next-action-card">
            <span class="action-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
            </span>
            <span class="action-content">
                <span class="action-title"><?php esc_html_e( 'Top Rated Tools', 'toolshed-tested' ); ?></span>
                <span class="action-desc"><?php esc_html_e( 'See our highest-rated products', 'toolshed-tested' ); ?></span>
            </span>
            <span class="action-arrow">&rarr;</span>
        </a>

        <a href="<?php echo esc_url( home_url( '/guides/' ) ); ?>" class="next-action-card">
            <span class="action-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
            </span>
            <span class="action-content">
                <span class="action-title"><?php esc_html_e( 'Buying Guides', 'toolshed-tested' ); ?></span>
                <span class="action-desc"><?php esc_html_e( 'In-depth guides to help you choose', 'toolshed-tested' ); ?></span>
            </span>
            <span class="action-arrow">&rarr;</span>
        </a>
    </div>
</section>
