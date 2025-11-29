<?php
/**
 * Template part for displaying single posts
 *
 * @package Toolshed_Tested
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header">
        <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

        <div class="entry-meta">
            <?php tst_posted_on(); ?>
            <?php tst_posted_by(); ?>
        </div>
    </header>

    <!-- Affiliate Disclosure for posts with affiliate links -->
    <?php if ( has_tag( 'affiliate' ) || has_category( 'reviews' ) ) : ?>
        <div class="affiliate-disclosure">
            <?php esc_html_e( 'Disclosure: This post may contain affiliate links. When you purchase through links on our site, we may earn a commission at no additional cost to you.', 'toolshed-tested' ); ?>
        </div>
    <?php endif; ?>

    <?php if ( has_post_thumbnail() ) : ?>
        <div class="post-thumbnail">
            <?php the_post_thumbnail( 'tst-review-featured' ); ?>
        </div>
    <?php endif; ?>

    <!-- Table of Contents -->
    <?php tst_table_of_contents(); ?>

    <div class="entry-content">
        <?php
        the_content(
            sprintf(
                wp_kses(
                    /* translators: %s: Name of current post. */
                    __( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'toolshed-tested' ),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                get_the_title()
            )
        );

        wp_link_pages(
            array(
                'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'toolshed-tested' ),
                'after'  => '</div>',
            )
        );
        ?>
    </div>

    <footer class="entry-footer">
        <?php
        // Display categories
        $categories_list = get_the_category_list( esc_html__( ', ', 'toolshed-tested' ) );
        if ( $categories_list ) {
            printf( '<span class="cat-links">' . esc_html__( 'Posted in: %1$s', 'toolshed-tested' ) . '</span>', $categories_list );
        }

        // Display tags
        $tags_list = get_the_tag_list( '', esc_html__( ', ', 'toolshed-tested' ) );
        if ( $tags_list ) {
            printf( '<span class="tags-links">' . esc_html__( 'Tagged: %1$s', 'toolshed-tested' ) . '</span>', $tags_list );
        }
        ?>
    </footer>
</article>
