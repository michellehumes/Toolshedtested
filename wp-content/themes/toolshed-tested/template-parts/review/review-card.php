<?php
/**
 * Template part for displaying product review cards
 *
 * @package Toolshed_Tested
 */

$rating       = get_post_meta( get_the_ID(), '_tst_rating', true );
$price        = get_post_meta( get_the_ID(), '_tst_price', true );
$affiliate_url = get_post_meta( get_the_ID(), '_tst_affiliate_url', true );
$badge        = get_post_meta( get_the_ID(), '_tst_badge', true );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'review-card tst-card' ); ?>>
    <?php if ( $badge ) : ?>
        <span class="badge badge-<?php echo esc_attr( sanitize_title( $badge ) ); ?>">
            <?php echo esc_html( $badge ); ?>
        </span>
    <?php endif; ?>

    <?php if ( has_post_thumbnail() ) : ?>
        <div class="tst-card-image">
            <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail( 'tst-product-thumb' ); ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="tst-card-content">
        <?php
        $categories = get_the_terms( get_the_ID(), 'product_category' );
        if ( $categories && ! is_wp_error( $categories ) ) :
            ?>
            <div class="tst-card-meta">
                <a href="<?php echo esc_url( get_term_link( $categories[0] ) ); ?>">
                    <?php echo esc_html( $categories[0]->name ); ?>
                </a>
            </div>
        <?php endif; ?>

        <h3 class="tst-card-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>

        <?php if ( $rating ) : ?>
            <div class="card-rating">
                <?php echo wp_kses_post( tst_star_rating( $rating ) ); ?>
                <span class="rating-text"><?php echo esc_html( $rating ); ?>/5</span>
            </div>
        <?php endif; ?>

        <?php if ( $price ) : ?>
            <div class="card-price"><?php echo esc_html( $price ); ?></div>
        <?php endif; ?>

        <div class="card-actions">
            <a href="<?php the_permalink(); ?>" class="tst-btn tst-btn-primary">
                <?php esc_html_e( 'Read Review', 'toolshed-tested' ); ?>
            </a>
            <?php if ( $affiliate_url ) : ?>
                <a href="<?php echo esc_url( $affiliate_url ); ?>" 
                   class="tst-btn tst-btn-amazon affiliate-link" 
                   target="_blank" 
                   rel="nofollow noopener sponsored"
                   data-product-id="<?php echo esc_attr( get_the_ID() ); ?>">
                    <?php esc_html_e( 'Check Price', 'toolshed-tested' ); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</article>
