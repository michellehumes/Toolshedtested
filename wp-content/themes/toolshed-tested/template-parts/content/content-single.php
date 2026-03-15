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
            <?php
            // Show "Updated" date if modified after published
            $published = get_the_time( 'U' );
            $modified  = get_the_modified_time( 'U' );
            if ( $modified > $published + DAY_IN_SECONDS ) :
                ?>
                <span class="updated-date">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: -2px;"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
                    <?php
                    printf(
                        esc_html__( 'Updated %s', 'toolshed-tested' ),
                        '<time datetime="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . esc_html( get_the_modified_date() ) . '</time>'
                    );
                    ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if ( tst_should_show_affiliate_disclosure( get_the_ID() ) ) : ?>
            <div class="entry-meta-links">
                <a href="<?php echo esc_url( home_url( '/how-we-test/' ) ); ?>" class="how-we-test-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: -2px;"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM10 17l-3.5-3.5 1.41-1.41L10 14.17l5.09-5.09 1.41 1.42L10 17z"/></svg>
                    <?php esc_html_e( 'How We Test', 'toolshed-tested' ); ?>
                </a>
            </div>
        <?php endif; ?>
    </header>

    <!-- Affiliate Disclosure for posts with affiliate links -->
    <?php if ( tst_should_show_affiliate_disclosure( get_the_ID() ) ) : ?>
        <div class="affiliate-disclosure">
            <?php echo wp_kses_post( tst_get_affiliate_disclosure_text() ); ?>
            <a href="<?php echo esc_url( home_url( '/affiliate-disclosure/' ) ); ?>"><?php esc_html_e( 'Learn more', 'toolshed-tested' ); ?></a>
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
