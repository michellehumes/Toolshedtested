<?php
/**
 * Search Results Template
 *
 * @package Toolshed_Tested
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="tst-container">
        <header class="page-header">
            <h1 class="page-title">
                <?php
                /* translators: %s: search query. */
                printf( esc_html__( 'Search Results for: %s', 'toolshed-tested' ), '<span>' . get_search_query() . '</span>' );
                ?>
            </h1>
        </header>

        <div class="content-area <?php echo is_active_sidebar( 'sidebar-1' ) ? 'has-sidebar' : ''; ?>">
            <div class="main-content">
                <?php if ( have_posts() ) : ?>
                    <div class="posts-grid">
                        <?php
                        while ( have_posts() ) :
                            the_post();
                            get_template_part( 'template-parts/content/content', 'search' );
                        endwhile;
                        ?>
                    </div>

                    <?php tst_pagination(); ?>
                <?php else : ?>
                    <div class="no-results">
                        <h2><?php esc_html_e( 'Nothing Found', 'toolshed-tested' ); ?></h2>
                        <p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'toolshed-tested' ); ?></p>
                        <?php get_search_form(); ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php get_sidebar(); ?>
        </div>
    </div>
</main>

<?php
get_footer();
