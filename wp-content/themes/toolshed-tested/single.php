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

                    // Newsletter signup
                    $newsletter_action = get_theme_mod( 'tst_newsletter_action', '' );
                    ?>
                    <div class="newsletter-box">
                        <h3><?php esc_html_e( 'Get Our Best Tool Reviews in Your Inbox', 'toolshed-tested' ); ?></h3>
                        <p><?php esc_html_e( 'Subscribe for hands-on reviews, buying guides, and exclusive deals on the best power tools.', 'toolshed-tested' ); ?></p>
                        <form class="newsletter-form" action="<?php echo esc_url( $newsletter_action ); ?>" method="post">
                            <input type="email" name="email" placeholder="<?php esc_attr_e( 'Enter your email', 'toolshed-tested' ); ?>" required>
                            <button type="submit"><?php esc_html_e( 'Subscribe', 'toolshed-tested' ); ?></button>
                        </form>
                    </div>

                    <?php
                    // Related posts
                    $current_cats = get_the_category();
                    if ( ! empty( $current_cats ) ) :
                        $cat_ids = wp_list_pluck( $current_cats, 'term_id' );
                        $related = new WP_Query(
                            array(
                                'post_type'      => 'post',
                                'posts_per_page' => 3,
                                'post__not_in'   => array( get_the_ID() ),
                                'category__in'   => $cat_ids,
                                'orderby'        => 'date',
                                'order'          => 'DESC',
                            )
                        );
                        if ( $related->have_posts() ) :
                            ?>
                            <section class="related-posts">
                                <h2><?php esc_html_e( 'Related Articles', 'toolshed-tested' ); ?></h2>
                                <div class="reviews-grid small">
                                    <?php
                                    while ( $related->have_posts() ) :
                                        $related->the_post();
                                        get_template_part( 'template-parts/content/content', get_post_type() );
                                    endwhile;
                                    wp_reset_postdata();
                                    ?>
                                </div>
                            </section>
                            <?php
                        endif;
                    endif;

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
