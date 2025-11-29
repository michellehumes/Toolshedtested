<?php
/**
 * Template part for displaying posts
 *
 * @package Toolshed_Tested
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'tst-card' ); ?>>
    <?php if ( has_post_thumbnail() ) : ?>
        <div class="tst-card-image">
            <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail( 'tst-card-image' ); ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="tst-card-content">
        <div class="tst-card-meta">
            <?php tst_posted_on(); ?>
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
        </div>

        <h2 class="tst-card-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h2>

        <div class="tst-card-excerpt">
            <?php the_excerpt(); ?>
        </div>

        <a href="<?php the_permalink(); ?>" class="tst-btn tst-btn-primary">
            <?php esc_html_e( 'Read More', 'toolshed-tested' ); ?>
        </a>
    </div>
</article>
