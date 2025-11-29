<?php
/**
 * Template part for displaying search results
 *
 * @package Toolshed_Tested
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'tst-card search-result' ); ?>>
    <?php if ( has_post_thumbnail() ) : ?>
        <div class="tst-card-image">
            <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail( 'tst-card-image' ); ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="tst-card-content">
        <div class="tst-card-meta">
            <span class="post-type">
                <?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ); ?>
            </span>
            <?php tst_posted_on(); ?>
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
