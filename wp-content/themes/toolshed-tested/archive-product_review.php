<?php
/**
 * Product Review Archive Template
 *
 * @package Toolshed_Tested
 */

get_header();
?>

<main id="primary" class="site-main archive-reviews">
    <div class="tst-container">
        <header class="page-header">
            <h1 class="page-title"><?php esc_html_e( 'Product Reviews', 'toolshed-tested' ); ?></h1>
            <p class="page-description">
                <?php esc_html_e( 'In-depth, hands-on reviews of outdoor power equipment. We test so you can buy with confidence.', 'toolshed-tested' ); ?>
            </p>
        </header>

        <!-- Category Filter -->
        <div class="category-filter">
            <?php
            $categories = get_terms(
                array(
                    'taxonomy'   => 'product_category',
                    'hide_empty' => true,
                )
            );

            if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) :
                ?>
                <ul class="category-list">
                    <li>
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'product_review' ) ); ?>" 
                           class="<?php echo ! is_tax() ? 'active' : ''; ?>">
                            <?php esc_html_e( 'All Reviews', 'toolshed-tested' ); ?>
                        </a>
                    </li>
                    <?php foreach ( $categories as $category ) : ?>
                        <li>
                            <a href="<?php echo esc_url( get_term_link( $category ) ); ?>"
                               class="<?php echo is_tax( 'product_category', $category->slug ) ? 'active' : ''; ?>">
                                <?php echo esc_html( $category->name ); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="content-area">
            <?php if ( have_posts() ) : ?>
                <div class="reviews-grid">
                    <?php
                    while ( have_posts() ) :
                        the_post();
                        get_template_part( 'template-parts/review/review', 'card' );
                    endwhile;
                    ?>
                </div>

                <?php tst_pagination(); ?>
            <?php else : ?>
                <div class="no-reviews">
                    <h2><?php esc_html_e( 'No Reviews Found', 'toolshed-tested' ); ?></h2>
                    <p><?php esc_html_e( 'Check back soon for new product reviews!', 'toolshed-tested' ); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
get_footer();
