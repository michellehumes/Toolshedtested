<?php
/**
 * Product Category Archive Template
 *
 * @package Toolshed_Tested
 */

get_header();

$term = get_queried_object();
$term_name = $term ? $term->name : esc_html__( 'Category', 'toolshed-tested' );
$term_description = term_description();
$default_description = sprintf(
    /* translators: %s: category name */
    __( 'Hands-on testing and buyer guides for %s tools to help you choose the right model for your budget and workload.', 'toolshed-tested' ),
    $term_name
);
?>

<main id="primary" class="site-main archive-reviews category-reviews">
    <div class="tst-container">
        <header class="page-header">
            <h1 class="page-title"><?php echo esc_html( $term_name ); ?> <?php esc_html_e( 'Reviews', 'toolshed-tested' ); ?></h1>
            <p class="page-description">
                <?php echo $term_description ? wp_kses_post( $term_description ) : esc_html( $default_description ); ?>
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
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'product_review' ) ); ?>" class="">
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

        <?php get_template_part( 'template-parts/category/top-picks-table' ); ?>

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
