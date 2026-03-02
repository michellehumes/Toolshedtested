<?php
/**
 * Template part for displaying posts
 *
 * @package Toolshed_Tested
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'tst-card' ); ?>>
    <div class="tst-card-image">
        <a href="<?php the_permalink(); ?>">
            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'tst-card-image' ); ?>
            <?php else : ?>
                <div class="card-placeholder-image">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor"><path d="M22 9V7h-2V5c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-2h2v-2h-2v-2h2v-2h-2V9h2zm-4 10H4V5h14v14zM6 13h5v4H6zm6-6h4v3h-4zM6 7h5v5H6zm6 4h4v6h-4z"/></svg>
                </div>
            <?php endif; ?>
        </a>
    </div>

    <div class="tst-card-content">
        <div class="tst-card-meta">
            <?php
            $categories = get_the_category();
            if ( ! empty( $categories ) ) :
                ?>
                <span class="cat-links">
                    <a href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>">
                        <?php echo esc_html( $categories[0]->name ); ?>
                    </a>
                </span>
            <?php endif; ?>
            <?php tst_posted_on(); ?>
        </div>

        <h2 class="tst-card-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h2>

        <?php
        $rating        = get_post_meta( get_the_ID(), '_tst_rating', true );
        $price         = get_post_meta( get_the_ID(), '_tst_price', true );
        $affiliate_url = tst_get_affiliate_url( get_post_meta( get_the_ID(), '_tst_affiliate_url', true ) );
        ?>

        <?php if ( $rating ) : ?>
            <div class="card-rating">
                <?php echo wp_kses_post( tst_star_rating( $rating ) ); ?>
                <span class="rating-text"><?php echo esc_html( $rating ); ?>/5</span>
            </div>
        <?php else : ?>
            <div class="tst-card-excerpt">
                <?php the_excerpt(); ?>
            </div>
        <?php endif; ?>

        <?php if ( $price ) : ?>
            <div class="card-price"><?php echo esc_html( $price ); ?></div>
        <?php endif; ?>

        <div class="card-actions">
            <?php if ( $affiliate_url ) : ?>
                <a href="<?php echo esc_url( $affiliate_url ); ?>"
                   class="tst-btn tst-btn-amazon affiliate-link"
                   target="_blank"
                   rel="nofollow noopener sponsored">
                    <?php esc_html_e( 'Check Price', 'toolshed-tested' ); ?>
                </a>
            <?php endif; ?>
            <a href="<?php the_permalink(); ?>" class="tst-btn tst-btn-primary">
                <?php esc_html_e( 'Read More', 'toolshed-tested' ); ?>
            </a>
        </div>
    </div>
</article>
