<?php
/**
 * Single Product Review Template
 *
 * @package Toolshed_Tested
 */

get_header();
?>

<main id="primary" class="site-main single-product-review">
    <?php
    while ( have_posts() ) :
        the_post();

        // Get review meta data
        $rating       = get_post_meta( get_the_ID(), '_tst_rating', true );
        $price        = get_post_meta( get_the_ID(), '_tst_price', true );
        $affiliate_url = get_post_meta( get_the_ID(), '_tst_affiliate_url', true );
        $pros         = get_post_meta( get_the_ID(), '_tst_pros', true );
        $cons         = get_post_meta( get_the_ID(), '_tst_cons', true );
        $specs        = get_post_meta( get_the_ID(), '_tst_specifications', true );
        $badge        = get_post_meta( get_the_ID(), '_tst_badge', true );
        ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <div class="tst-container">
                <!-- Affiliate Disclosure -->
                <div class="affiliate-disclosure">
                    <?php esc_html_e( 'Disclosure: This post may contain affiliate links. When you purchase through links on our site, we may earn a commission at no additional cost to you.', 'toolshed-tested' ); ?>
                </div>

                <!-- Review Header -->
                <header class="entry-header review-header">
                    <?php if ( $badge ) : ?>
                        <span class="badge badge-<?php echo esc_attr( sanitize_title( $badge ) ); ?>">
                            <?php echo esc_html( $badge ); ?>
                        </span>
                    <?php endif; ?>

                    <h1 class="entry-title"><?php the_title(); ?></h1>

                    <div class="entry-meta">
                        <?php tst_posted_on(); ?>
                        <?php tst_posted_by(); ?>
                        <?php echo wp_kses_post( tst_reading_time() ); ?>
                        <?php
                        $categories = get_the_terms( get_the_ID(), 'product_category' );
                        if ( $categories && ! is_wp_error( $categories ) ) :
                            ?>
                            <span class="category-links">
                                <?php
                                foreach ( $categories as $category ) {
                                    echo '<a href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category->name ) . '</a>';
                                }
                                ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </header>

                <!-- Review Box (Summary) -->
                <div class="review-box">
                    <div class="review-box-header">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="review-box-image">
                                <?php the_post_thumbnail( 'tst-product-large' ); ?>
                            </div>
                        <?php endif; ?>

                        <div class="review-box-info">
                            <h2 class="review-box-title"><?php the_title(); ?></h2>

                            <?php if ( $rating ) : ?>
                                <div class="review-box-rating">
                                    <?php echo wp_kses_post( tst_star_rating( $rating ) ); ?>
                                    <span class="rating-score"><?php echo esc_html( $rating ); ?>/5</span>
                                </div>
                            <?php endif; ?>

                            <?php if ( $price ) : ?>
                                <div class="review-box-price">
                                    <?php echo esc_html( $price ); ?>
                                </div>
                            <?php endif; ?>

                            <div class="review-box-cta">
                                <?php if ( $affiliate_url ) : ?>
                                    <a href="<?php echo esc_url( $affiliate_url ); ?>" 
                                       class="tst-btn tst-btn-amazon affiliate-link" 
                                       target="_blank" 
                                       rel="nofollow noopener sponsored"
                                       data-product-id="<?php echo esc_attr( get_the_ID() ); ?>">
                                        <?php esc_html_e( 'Check Price on Amazon', 'toolshed-tested' ); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Pros & Cons -->
                    <?php if ( $pros || $cons ) : ?>
                        <div class="pros-cons">
                            <?php if ( $pros ) : ?>
                                <div class="pros-list">
                                    <h4><?php esc_html_e( 'Pros', 'toolshed-tested' ); ?></h4>
                                    <ul>
                                        <?php
                                        $pros_array = is_array( $pros ) ? $pros : explode( "\n", $pros );
                                        foreach ( $pros_array as $pro ) :
                                            $pro = trim( $pro );
                                            if ( ! empty( $pro ) ) :
                                                ?>
                                                <li><?php echo esc_html( $pro ); ?></li>
                                                <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if ( $cons ) : ?>
                                <div class="cons-list">
                                    <h4><?php esc_html_e( 'Cons', 'toolshed-tested' ); ?></h4>
                                    <ul>
                                        <?php
                                        $cons_array = is_array( $cons ) ? $cons : explode( "\n", $cons );
                                        foreach ( $cons_array as $con ) :
                                            $con = trim( $con );
                                            if ( ! empty( $con ) ) :
                                                ?>
                                                <li><?php echo esc_html( $con ); ?></li>
                                                <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Table of Contents -->
                <?php tst_table_of_contents(); ?>

                <!-- Review Content -->
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>

                <!-- Specifications Table -->
                <?php if ( $specs && is_array( $specs ) ) : ?>
                    <section class="specifications-section">
                        <h2><?php esc_html_e( 'Specifications', 'toolshed-tested' ); ?></h2>
                        <table class="spec-table">
                            <tbody>
                                <?php foreach ( $specs as $spec ) : ?>
                                    <tr>
                                        <th><?php echo esc_html( $spec['label'] ); ?></th>
                                        <td><?php echo esc_html( $spec['value'] ); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </section>
                <?php endif; ?>

                <!-- Verdict Box -->
                <?php tst_verdict_box(); ?>

                <!-- Compare Alternatives -->
                <?php
                $categories     = get_the_terms( get_the_ID(), 'product_category' );
                $category_ids   = array();
                if ( $categories && ! is_wp_error( $categories ) ) {
                    foreach ( $categories as $cat ) {
                        $category_ids[] = $cat->term_id;
                    }
                }

                if ( ! empty( $category_ids ) ) :
                    $alternatives = new WP_Query(
                        array(
                            'post_type'      => 'product_review',
                            'posts_per_page' => 3,
                            'post__not_in'   => array( get_the_ID() ),
                            'tax_query'      => array(
                                array(
                                    'taxonomy' => 'product_category',
                                    'field'    => 'term_id',
                                    'terms'    => $category_ids,
                                ),
                            ),
                            'meta_key'       => '_tst_rating',
                            'orderby'        => 'meta_value_num',
                            'order'          => 'DESC',
                        )
                    );

                    if ( $alternatives->have_posts() ) :
                        ?>
                        <section class="compare-alternatives">
                            <h2><?php esc_html_e( 'Compare With Alternatives', 'toolshed-tested' ); ?></h2>
                            <p class="section-subtitle"><?php esc_html_e( 'See how this product stacks up against similar options.', 'toolshed-tested' ); ?></p>

                            <div class="comparison-table-wrapper">
                                <table class="comparison-table alternatives-table">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'Product', 'toolshed-tested' ); ?></th>
                                            <th><?php esc_html_e( 'Rating', 'toolshed-tested' ); ?></th>
                                            <th class="hide-mobile"><?php esc_html_e( 'Price', 'toolshed-tested' ); ?></th>
                                            <th><?php esc_html_e( 'Action', 'toolshed-tested' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Current Product Row -->
                                        <tr class="current-product">
                                            <td class="product-name">
                                                <span class="badge badge-current"><?php esc_html_e( 'This Product', 'toolshed-tested' ); ?></span>
                                                <strong><?php the_title(); ?></strong>
                                            </td>
                                            <td class="product-rating">
                                                <?php if ( $rating ) : ?>
                                                    <?php echo wp_kses_post( tst_star_rating( $rating ) ); ?>
                                                    <span class="rating-number"><?php echo esc_html( $rating ); ?>/5</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="product-price hide-mobile"><?php echo $price ? esc_html( $price ) : '—'; ?></td>
                                            <td class="product-cta">
                                                <?php if ( $affiliate_url ) : ?>
                                                    <a href="<?php echo esc_url( $affiliate_url ); ?>"
                                                       class="tst-btn tst-btn-amazon"
                                                       target="_blank"
                                                       rel="nofollow noopener sponsored">
                                                        <?php esc_html_e( 'Check Price', 'toolshed-tested' ); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <!-- Alternative Products -->
                                        <?php
                                        while ( $alternatives->have_posts() ) :
                                            $alternatives->the_post();
                                            $alt_rating       = get_post_meta( get_the_ID(), '_tst_rating', true );
                                            $alt_price        = get_post_meta( get_the_ID(), '_tst_price', true );
                                            $alt_affiliate    = get_post_meta( get_the_ID(), '_tst_affiliate_url', true );
                                            ?>
                                            <tr>
                                                <td class="product-name">
                                                    <a href="<?php the_permalink(); ?>">
                                                        <strong><?php the_title(); ?></strong>
                                                    </a>
                                                </td>
                                                <td class="product-rating">
                                                    <?php if ( $alt_rating ) : ?>
                                                        <?php echo wp_kses_post( tst_star_rating( $alt_rating ) ); ?>
                                                        <span class="rating-number"><?php echo esc_html( $alt_rating ); ?>/5</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="product-price hide-mobile"><?php echo $alt_price ? esc_html( $alt_price ) : '—'; ?></td>
                                                <td class="product-cta">
                                                    <?php if ( $alt_affiliate ) : ?>
                                                        <a href="<?php echo esc_url( $alt_affiliate ); ?>"
                                                           class="tst-btn tst-btn-amazon"
                                                           target="_blank"
                                                           rel="nofollow noopener sponsored">
                                                            <?php esc_html_e( 'Check Price', 'toolshed-tested' ); ?>
                                                        </a>
                                                    <?php else : ?>
                                                        <a href="<?php the_permalink(); ?>" class="tst-btn tst-btn-primary">
                                                            <?php esc_html_e( 'Read Review', 'toolshed-tested' ); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php
                                        endwhile;
                                        wp_reset_postdata();
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                        <?php
                    endif;
                endif;
                ?>

                <!-- Final CTA -->
                <div class="final-cta">
                    <?php if ( $affiliate_url ) : ?>
                        <a href="<?php echo esc_url( $affiliate_url ); ?>"
                           class="tst-btn tst-btn-amazon affiliate-link"
                           target="_blank"
                           rel="nofollow noopener sponsored"
                           data-product-id="<?php echo esc_attr( get_the_ID() ); ?>">
                            <?php esc_html_e( 'Check Best Price on Amazon', 'toolshed-tested' ); ?>
                            <?php if ( $price ) : ?>
                                <span class="btn-price"><?php echo esc_html( $price ); ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Author Box -->
                <?php tst_author_box(); ?>

                <!-- Related Reviews -->
                <?php tst_related_reviews(); ?>

                <!-- Comments -->
                <?php
                if ( comments_open() || get_comments_number() ) :
                    comments_template();
                endif;
                ?>
            </div>
        </article>

        <?php
        // Output Schema markup
        echo tst_product_review_schema();
        ?>

    <?php endwhile; ?>
</main>

<?php
get_footer();
