<?php
/**
 * Single Post Template
 *
 * @package Toolshed_Tested
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="tst-container">
        <div class="content-area <?php echo is_active_sidebar( 'sidebar-1' ) ? 'has-sidebar' : ''; ?>">
            <div class="main-content">
                <?php
                while ( have_posts() ) :
                    the_post();
                    get_template_part( 'template-parts/content/content', 'single' );

                    // Post navigation
                    the_post_navigation(
                        array(
                            'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous:', 'toolshed-tested' ) . '</span> <span class="nav-title">%title</span>',
                            'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'toolshed-tested' ) . '</span> <span class="nav-title">%title</span>',
                        )
                    );

                    // Author box
                    tst_author_box();

                    // Comments
                    if ( comments_open() || get_comments_number() ) :
                        comments_template();
                    endif;

                endwhile;
                ?>
            </div>

            <?php get_sidebar(); ?>
        </div>
    </div>
</main>

<?php
get_footer();
