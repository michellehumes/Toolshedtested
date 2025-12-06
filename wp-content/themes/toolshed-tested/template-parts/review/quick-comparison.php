<?php
/**
 * Quick Comparison Widget for Single Reviews
 *
 * Displays a comparison table showing similar products in the same category.
 *
 * @package Toolshed_Tested
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current product's categories
$current_categories = get_the_terms( get_the_ID(), 'product_category' );
if ( ! $current_categories || is_wp_error( $current_categories ) ) {
    return;
}

// Query for competing products in the same category
$competitors = new WP_Query( array(
    'post_type'      => 'product_review',
    'posts_per_page' => 3,
    'post__not_in'   => array( get_the_ID() ),
    'tax_query'      => array(
        array(
            'taxonomy' => 'product_category',
            'field'    => 'term_id',
            'terms'    => wp_list_pluck( $current_categories, 'term_id' ),
        ),
    ),
    'meta_key'       => '_tst_rating',
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
) );

if ( ! $competitors->have_posts() ) {
    return;
}

$category_name = $current_categories[0]->name;
?>

<section class="quick-comparison">
    <h2><?php printf( esc_html__( 'Compare %s Alternatives', 'toolshed-tested' ), esc_html( $category_name ) ); ?></h2>
    <p class="comparison-intro">
        <?php esc_html_e( 'See how this product stacks up against similar options in the same category.', 'toolshed-tested' ); ?>
    </p>

    <div class="comparison-table-wrapper" data-scrollable>
        <table class="comparison-table category-comparison">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Product', 'toolshed-tested' ); ?></th>
                    <th><?php esc_html_e( 'Rating', 'toolshed-tested' ); ?></th>
                    <th class="hide-mobile"><?php esc_html_e( 'Price', 'toolshed-tested' ); ?></th>
                    <th><?php esc_html_e( 'Action', 'toolshed-tested' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ( $competitors->have_posts() ) :
                    $competitors->the_post();
                    $rating        = get_post_meta( get_the_ID(), '_tst_rating', true );
                    $price         = get_post_meta( get_the_ID(), '_tst_price', true );
                    $affiliate_url = get_post_meta( get_the_ID(), '_tst_affiliate_url', true );
                    $badge         = get_post_meta( get_the_ID(), '_tst_badge', true );
                    ?>
                    <tr>
                        <td class="product-name">
                            <?php if ( $badge ) : ?>
                                <span class="badge badge-<?php echo esc_attr( sanitize_title( $badge ) ); ?>"><?php echo esc_html( $badge ); ?></span>
                            <?php endif; ?>
                            <strong>
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </strong>
                        </td>
                        <td class="product-rating">
                            <?php if ( $rating ) : ?>
                                <?php echo wp_kses_post( tst_star_rating( floatval( $rating ) ) ); ?>
                                <span class="rating-number"><?php echo esc_html( $rating ); ?>/5</span>
                            <?php else : ?>
                                <span class="no-rating"><?php esc_html_e( 'N/A', 'toolshed-tested' ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="product-price hide-mobile">
                            <?php if ( $price ) : ?>
                                <?php echo esc_html( $price ); ?>
                            <?php else : ?>
                                <span class="check-price"><?php esc_html_e( 'Check Price', 'toolshed-tested' ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="product-cta">
                            <?php if ( $affiliate_url ) : ?>
                                <a href="<?php echo esc_url( $affiliate_url ); ?>"
                                   class="tst-btn tst-btn-amazon affiliate-link"
                                   target="_blank"
                                   rel="nofollow noopener sponsored"
                                   data-product-id="<?php echo esc_attr( get_the_ID() ); ?>">
                                    <?php esc_html_e( 'View', 'toolshed-tested' ); ?>
                                </a>
                            <?php else : ?>
                                <a href="<?php the_permalink(); ?>" class="tst-btn tst-btn-primary">
                                    <?php esc_html_e( 'Read Review', 'toolshed-tested' ); ?>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; wp_reset_postdata(); ?>
            </tbody>
        </table>
    </div>

    <p class="comparison-cta">
        <a href="<?php echo esc_url( get_term_link( $current_categories[0] ) ); ?>" class="view-all-link">
            <?php printf( esc_html__( 'View all %s reviews', 'toolshed-tested' ), esc_html( strtolower( $category_name ) ) ); ?> &rarr;
        </a>
    </p>
</section>
