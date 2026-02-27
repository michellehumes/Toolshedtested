<?php
/**
 * Template part for displaying product review cards
 *
 * @package Toolshed_Tested
 */

$rating       = get_post_meta( get_the_ID(), '_tst_rating', true );
$price        = get_post_meta( get_the_ID(), '_tst_price', true );
$affiliate_url = tst_get_affiliate_url( get_post_meta( get_the_ID(), '_tst_affiliate_url', true ) );
$badge        = get_post_meta( get_the_ID(), '_tst_badge', true );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'review-card tst-card' ); ?>>
    <?php if ( $badge ) : ?>
        <span class="badge badge-<?php echo esc_attr( sanitize_title( $badge ) ); ?>">
            <?php echo esc_html( $badge ); ?>
        </span>
    <?php endif; ?>

    <div class="tst-card-image">
        <a href="<?php the_permalink(); ?>">
            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'tst-product-thumb' ); ?>
            <?php else : ?>
                <div class="card-placeholder-image">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor"><path d="M22 9V7h-2V5c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-2h2v-2h-2v-2h2v-2h-2V9h2zm-4 10H4V5h14v14zM6 13h5v4H6zm6-6h4v3h-4zM6 7h5v5H6zm6 4h4v6h-4z"/></svg>
                </div>
            <?php endif; ?>
        </a>
    </div>

    <div class="tst-card-content">
        <?php
        $categories = get_the_terms( get_the_ID(), 'product_category' );
        if ( ! $categories || is_wp_error( $categories ) ) {
            $categories = get_the_category();
        }
        if ( $categories && ! is_wp_error( $categories ) && ! empty( $categories ) ) :
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
