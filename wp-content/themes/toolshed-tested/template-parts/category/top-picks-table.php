<?php
/**
 * Category Top Picks Comparison Table
 *
 * Displays a quick comparison table of top products at the top of category pages.
 * Uses existing product data and shortcode styling.
 *
 * @package Toolshed_Tested
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get the current category
$current_category = get_queried_object();

if ( ! $current_category || ! is_a( $current_category, 'WP_Term' ) ) {
    return;
}

// Define top picks for each category (can be customized via theme options later)
$category_top_picks = array(
    'drills' => array(
        array(
            'name'     => 'DeWalt DCD800 20V MAX XR',
            'badge'    => 'Best Overall',
            'rating'   => 4.9,
            'best_for' => 'Professional contractors',
            'price'    => '$199',
            'url'      => 'https://www.amazon.com/dp/B09BKRXXXX?tag=toolshedtested-20',
        ),
        array(
            'name'     => 'DeWalt DCD777C2',
            'badge'    => 'Best Value',
            'rating'   => 4.7,
            'best_for' => 'DIY homeowners',
            'price'    => '$139',
            'url'      => 'https://www.amazon.com/dp/B07XXXXXX?tag=toolshedtested-20',
        ),
        array(
            'name'     => 'BLACK+DECKER LDX120C',
            'badge'    => 'Budget Pick',
            'rating'   => 4.3,
            'best_for' => 'Light tasks',
            'price'    => '$49',
            'url'      => 'https://www.amazon.com/dp/B005XXXXXX?tag=toolshedtested-20',
        ),
    ),
    'saws' => array(
        array(
            'name'     => 'DeWalt DWE7491RS',
            'badge'    => 'Best Overall',
            'rating'   => 4.8,
            'best_for' => 'Jobsite work',
            'price'    => '$649',
            'url'      => 'https://www.amazon.com/dp/B00F2XXXXXX?tag=toolshedtested-20',
        ),
        array(
            'name'     => 'Bosch 4100XC-10',
            'badge'    => 'Best Value',
            'rating'   => 4.7,
            'best_for' => 'Home workshops',
            'price'    => '$599',
            'url'      => 'https://www.amazon.com/dp/B07XXXXXX?tag=toolshedtested-20',
        ),
        array(
            'name'     => 'SKIL TS6307-00',
            'badge'    => 'Budget Pick',
            'rating'   => 4.4,
            'best_for' => 'Beginners',
            'price'    => '$299',
            'url'      => 'https://www.amazon.com/dp/B08XXXXXX?tag=toolshedtested-20',
        ),
    ),
    'grinders' => array(
        array(
            'name'     => 'DeWalt DCG413B',
            'badge'    => 'Best Overall',
            'rating'   => 4.8,
            'best_for' => 'Heavy-duty grinding',
            'price'    => '$159',
            'url'      => 'https://www.amazon.com/dp/B07XXXXXX?tag=toolshedtested-20',
        ),
        array(
            'name'     => 'Makita XAG04Z',
            'badge'    => 'Best Value',
            'rating'   => 4.7,
            'best_for' => 'All-around use',
            'price'    => '$129',
            'url'      => 'https://www.amazon.com/dp/B01XXXXXX?tag=toolshedtested-20',
        ),
        array(
            'name'     => 'BLACK+DECKER BDEG400',
            'badge'    => 'Budget Pick',
            'rating'   => 4.2,
            'best_for' => 'Occasional use',
            'price'    => '$39',
            'url'      => 'https://www.amazon.com/dp/B00XXXXXX?tag=toolshedtested-20',
        ),
    ),
    'sanders' => array(
        array(
            'name'     => 'Festool ETS EC 150/5',
            'badge'    => 'Best Overall',
            'rating'   => 4.9,
            'best_for' => 'Professional finish work',
            'price'    => '$395',
            'url'      => 'https://www.amazon.com/dp/B00XXXXXX?tag=toolshedtested-20',
        ),
        array(
            'name'     => 'Bosch ROS20VSC',
            'badge'    => 'Best Value',
            'rating'   => 4.6,
            'best_for' => 'Home projects',
            'price'    => '$79',
            'url'      => 'https://www.amazon.com/dp/B00XXXXXX?tag=toolshedtested-20',
        ),
        array(
            'name'     => 'BLACK+DECKER BDERO100',
            'badge'    => 'Budget Pick',
            'rating'   => 4.1,
            'best_for' => 'Light sanding',
            'price'    => '$29',
            'url'      => 'https://www.amazon.com/dp/B00XXXXXX?tag=toolshedtested-20',
        ),
    ),
    'outdoor-power' => array(
        array(
            'name'     => 'EGO LM2135SP',
            'badge'    => 'Best Overall',
            'rating'   => 4.8,
            'best_for' => 'Large lawns',
            'price'    => '$649',
            'url'      => 'https://www.amazon.com/dp/B08XXXXXX?tag=toolshedtested-20',
        ),
        array(
            'name'     => 'Greenworks 40V 21"',
            'badge'    => 'Best Value',
            'rating'   => 4.5,
            'best_for' => 'Medium lawns',
            'price'    => '$349',
            'url'      => 'https://www.amazon.com/dp/B07XXXXXX?tag=toolshedtested-20',
        ),
        array(
            'name'     => 'Sun Joe MJ401E',
            'badge'    => 'Budget Pick',
            'rating'   => 4.2,
            'best_for' => 'Small yards',
            'price'    => '$129',
            'url'      => 'https://www.amazon.com/dp/B00XXXXXX?tag=toolshedtested-20',
        ),
    ),
);

// Get products for current category
$category_slug = $current_category->slug;
$products      = isset( $category_top_picks[ $category_slug ] ) ? $category_top_picks[ $category_slug ] : array();

// If no predefined products, try to get from actual posts
if ( empty( $products ) ) {
    $top_posts = get_posts( array(
        'post_type'      => array( 'post', 'product_review' ),
        'posts_per_page' => 3,
        'cat'            => $current_category->term_id,
        'meta_key'       => '_tst_rating',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
    ) );

    foreach ( $top_posts as $index => $post ) {
        $badges = array( 'Best Overall', 'Best Value', 'Budget Pick' );
        $products[] = array(
            'name'     => get_the_title( $post ),
            'badge'    => $badges[ $index ] ?? '',
            'rating'   => floatval( get_post_meta( $post->ID, '_tst_rating', true ) ) ?: 4.5,
            'best_for' => get_the_excerpt( $post ) ?: 'General use',
            'price'    => get_post_meta( $post->ID, '_tst_price', true ) ?: 'Check price',
            'url'      => get_post_meta( $post->ID, '_tst_affiliate_url', true ) ?: get_permalink( $post ),
        );
    }
}

// Don't display if no products
if ( empty( $products ) ) {
    return;
}

// Helper function for star rating
if ( ! function_exists( 'tst_render_stars' ) ) {
    function tst_render_stars( $rating ) {
        $output     = '<span class="stars">';
        $full_stars = floor( $rating );
        $half_star  = ( $rating - $full_stars ) >= 0.5;

        for ( $i = 0; $i < $full_stars; $i++ ) {
            $output .= '<span class="star full">&#9733;</span>';
        }
        if ( $half_star ) {
            $output .= '<span class="star half">&#9733;</span>';
        }
        $empty_stars = 5 - $full_stars - ( $half_star ? 1 : 0 );
        for ( $i = 0; $i < $empty_stars; $i++ ) {
            $output .= '<span class="star empty">&#9734;</span>';
        }
        $output .= '</span>';

        return $output;
    }
}
?>

<div class="category-top-picks">
    <div class="top-picks-header">
        <h2><?php printf( esc_html__( 'Top %s Picks', 'toolshed-tested' ), esc_html( $current_category->name ) ); ?></h2>
        <p class="top-picks-subtitle"><?php esc_html_e( 'Quick comparison of our top-rated products in this category', 'toolshed-tested' ); ?></p>
    </div>

    <div class="comparison-table-wrapper">
        <table class="comparison-table category-comparison">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Product', 'toolshed-tested' ); ?></th>
                    <th><?php esc_html_e( 'Rating', 'toolshed-tested' ); ?></th>
                    <th class="hide-mobile"><?php esc_html_e( 'Best For', 'toolshed-tested' ); ?></th>
                    <th><?php esc_html_e( 'Price', 'toolshed-tested' ); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $products as $product ) : ?>
                <tr>
                    <td class="product-name">
                        <?php if ( ! empty( $product['badge'] ) ) : ?>
                            <span class="badge badge-<?php echo esc_attr( sanitize_title( $product['badge'] ) ); ?>">
                                <?php echo esc_html( $product['badge'] ); ?>
                            </span>
                        <?php endif; ?>
                        <strong><?php echo esc_html( $product['name'] ); ?></strong>
                    </td>
                    <td class="product-rating">
                        <?php echo tst_render_stars( $product['rating'] ); ?>
                        <span class="rating-number"><?php echo esc_html( $product['rating'] ); ?>/5</span>
                    </td>
                    <td class="product-best-for hide-mobile">
                        <?php echo esc_html( wp_trim_words( $product['best_for'], 5 ) ); ?>
                    </td>
                    <td class="product-price">
                        <?php echo esc_html( $product['price'] ); ?>
                    </td>
                    <td class="product-cta">
                        <a href="<?php echo esc_url( $product['url'] ); ?>"
                           class="tst-btn tst-btn-amazon"
                           target="_blank"
                           rel="nofollow noopener sponsored">
                            <?php esc_html_e( 'Check Price', 'toolshed-tested' ); ?>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p class="top-picks-footer">
        <small><?php esc_html_e( 'Prices and availability may vary. Last updated:', 'toolshed-tested' ); ?> <?php echo esc_html( date_i18n( get_option( 'date_format' ) ) ); ?></small>
    </p>
</div>
