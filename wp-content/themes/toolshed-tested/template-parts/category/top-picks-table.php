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

// Build top picks dynamically from existing posts.
$products = array();
$badges   = array( 'Best Overall', 'Best Value', 'Budget Pick' );

$query_args = array(
    'posts_per_page' => 3,
    'orderby'        => 'meta_value_num',
    'meta_key'       => '_tst_rating',
    'order'          => 'DESC',
);

if ( is_tax( 'product_category' ) ) {
    $query_args['post_type'] = array( 'product_review' );
    $query_args['tax_query'] = array(
        array(
            'taxonomy' => 'product_category',
            'field'    => 'term_id',
            'terms'    => $current_category->term_id,
        ),
    );
} elseif ( is_category() ) {
    $query_args['post_type'] = array( 'post', 'product_review' );
    $query_args['cat']       = $current_category->term_id;
}

$top_posts = get_posts( $query_args );

if ( empty( $top_posts ) ) {
    $fallback_args = $query_args;
    unset( $fallback_args['meta_key'], $fallback_args['orderby'] );
    $fallback_args['orderby'] = 'date';
    $top_posts = get_posts( $fallback_args );
}

foreach ( $top_posts as $index => $post ) {
    $rating        = floatval( get_post_meta( $post->ID, '_tst_rating', true ) );
    $price         = get_post_meta( $post->ID, '_tst_price', true );
    $affiliate_url = get_post_meta( $post->ID, '_tst_affiliate_url', true );
    $best_for      = get_post_meta( $post->ID, '_tst_best_for', true );
    $excerpt       = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( $post->post_content, 12 );

    $products[] = array(
        'name'          => get_the_title( $post ),
        'badge'         => $badges[ $index ] ?? '',
        'rating'        => $rating,
        'best_for'      => $best_for ? $best_for : $excerpt,
        'price'         => $price ? $price : 'Check price',
        'url'           => $affiliate_url ? tst_get_affiliate_url( $affiliate_url ) : get_permalink( $post ),
        'has_affiliate' => ! empty( $affiliate_url ),
    );
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
                        <?php if ( ! empty( $product['rating'] ) ) : ?>
                            <?php echo tst_render_stars( $product['rating'] ); ?>
                            <span class="rating-number"><?php echo esc_html( $product['rating'] ); ?>/5</span>
                        <?php else : ?>
                            <span class="rating-number">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="product-best-for hide-mobile">
                        <?php echo esc_html( wp_trim_words( $product['best_for'], 5 ) ); ?>
                    </td>
                    <td class="product-price">
                        <?php echo esc_html( $product['price'] ); ?>
                    </td>
                    <td class="product-cta">
                        <?php
                        $cta_class  = $product['has_affiliate'] ? 'tst-btn-amazon affiliate-link' : 'tst-btn-primary';
                        $cta_target = $product['has_affiliate'] ? ' target="_blank"' : '';
                        $cta_rel    = $product['has_affiliate'] ? ' rel="nofollow noopener sponsored"' : '';
                        ?>
                        <a href="<?php echo esc_url( $product['url'] ); ?>"
                           class="tst-btn <?php echo esc_attr( $cta_class ); ?>"<?php echo $cta_target; ?><?php echo $cta_rel; ?>>
                            <?php echo $product['has_affiliate'] ? esc_html__( 'Check Price', 'toolshed-tested' ) : esc_html__( 'Read Review', 'toolshed-tested' ); ?>
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
