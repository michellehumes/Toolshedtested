<?php
/**
 * Template part for displaying product comparison table
 *
 * @package Toolshed_Tested
 */

// Get products from shortcode or custom query
$products = isset( $args['products'] ) ? $args['products'] : array();

if ( empty( $products ) ) {
    return;
}
?>

<div class="comparison-table-wrapper">
    <table class="comparison-table">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Product', 'toolshed-tested' ); ?></th>
                <th><?php esc_html_e( 'Rating', 'toolshed-tested' ); ?></th>
                <th><?php esc_html_e( 'Price', 'toolshed-tested' ); ?></th>
                <th><?php esc_html_e( 'Best For', 'toolshed-tested' ); ?></th>
                <th><?php esc_html_e( 'Action', 'toolshed-tested' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $products as $product_id ) : 
                $product = get_post( $product_id );
                if ( ! $product ) {
                    continue;
                }

                $rating       = get_post_meta( $product_id, '_tst_rating', true );
                $price        = get_post_meta( $product_id, '_tst_price', true );
                $affiliate_url = get_post_meta( $product_id, '_tst_affiliate_url', true );
                $best_for     = get_post_meta( $product_id, '_tst_best_for', true );
                $badge        = get_post_meta( $product_id, '_tst_badge', true );
                ?>
                <tr>
                    <td class="product-cell">
                        <?php if ( has_post_thumbnail( $product_id ) ) : ?>
                            <?php echo get_the_post_thumbnail( $product_id, 'thumbnail', array( 'class' => 'product-thumb' ) ); ?>
                        <?php endif; ?>
                        <div class="product-info">
                            <?php if ( $badge ) : ?>
                                <span class="badge badge-<?php echo esc_attr( sanitize_title( $badge ) ); ?>">
                                    <?php echo esc_html( $badge ); ?>
                                </span>
                            <?php endif; ?>
                            <a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>" class="product-name">
                                <?php echo esc_html( get_the_title( $product_id ) ); ?>
                            </a>
                        </div>
                    </td>
                    <td class="rating-cell">
                        <?php if ( $rating ) : ?>
                            <?php echo wp_kses_post( tst_star_rating( $rating ) ); ?>
                            <span class="rating-score"><?php echo esc_html( $rating ); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="price-cell">
                        <?php echo esc_html( $price ); ?>
                    </td>
                    <td class="best-for-cell">
                        <?php echo esc_html( $best_for ); ?>
                    </td>
                    <td class="cta-cell">
                        <?php if ( $affiliate_url ) : ?>
                            <a href="<?php echo esc_url( $affiliate_url ); ?>" 
                               class="tst-btn tst-btn-amazon affiliate-link" 
                               target="_blank" 
                               rel="nofollow noopener sponsored"
                               data-product-id="<?php echo esc_attr( $product_id ); ?>">
                                <?php esc_html_e( 'Check Price', 'toolshed-tested' ); ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
