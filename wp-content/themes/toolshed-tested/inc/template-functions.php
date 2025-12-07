<?php
/**
 * Template Functions
 *
 * @package Toolshed_Tested
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Display pagination
 */
function tst_pagination() {
    global $wp_query;

    if ( $wp_query->max_num_pages <= 1 ) {
        return;
    }

    $big = 999999999;

    $pages = paginate_links(
        array(
            'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
            'format'    => '?paged=%#%',
            'current'   => max( 1, get_query_var( 'paged' ) ),
            'total'     => $wp_query->max_num_pages,
            'type'      => 'array',
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
        )
    );

    if ( is_array( $pages ) ) {
        echo '<nav class="pagination" aria-label="' . esc_attr__( 'Posts navigation', 'toolshed-tested' ) . '">';
        foreach ( $pages as $page ) {
            echo wp_kses_post( $page );
        }
        echo '</nav>';
    }
}

/**
 * Display breadcrumbs
 */
function tst_breadcrumbs() {
    if ( is_front_page() ) {
        return;
    }

    $separator = '<span class="separator">/</span>';

    echo '<a href="' . esc_url( home_url() ) . '">' . esc_html__( 'Home', 'toolshed-tested' ) . '</a>';

    if ( is_singular( 'product_review' ) ) {
        echo wp_kses_post( $separator );
        echo '<a href="' . esc_url( get_post_type_archive_link( 'product_review' ) ) . '">' . esc_html__( 'Reviews', 'toolshed-tested' ) . '</a>';

        $categories = get_the_terms( get_the_ID(), 'product_category' );
        if ( $categories && ! is_wp_error( $categories ) ) {
            echo wp_kses_post( $separator );
            echo '<a href="' . esc_url( get_term_link( $categories[0] ) ) . '">' . esc_html( $categories[0]->name ) . '</a>';
        }

        echo wp_kses_post( $separator );
        echo '<span class="current">' . esc_html( get_the_title() ) . '</span>';
    } elseif ( is_singular( 'post' ) ) {
        $categories = get_the_category();
        if ( ! empty( $categories ) ) {
            echo wp_kses_post( $separator );
            echo '<a href="' . esc_url( get_category_link( $categories[0]->term_id ) ) . '">' . esc_html( $categories[0]->name ) . '</a>';
        }

        echo wp_kses_post( $separator );
        echo '<span class="current">' . esc_html( get_the_title() ) . '</span>';
    } elseif ( is_page() ) {
        echo wp_kses_post( $separator );
        echo '<span class="current">' . esc_html( get_the_title() ) . '</span>';
    } elseif ( is_tax( 'product_category' ) ) {
        echo wp_kses_post( $separator );
        echo '<a href="' . esc_url( get_post_type_archive_link( 'product_review' ) ) . '">' . esc_html__( 'Reviews', 'toolshed-tested' ) . '</a>';
        echo wp_kses_post( $separator );
        echo '<span class="current">' . esc_html( single_term_title( '', false ) ) . '</span>';
    } elseif ( is_tax( 'product_brand' ) ) {
        echo wp_kses_post( $separator );
        echo '<a href="' . esc_url( get_post_type_archive_link( 'product_review' ) ) . '">' . esc_html__( 'Reviews', 'toolshed-tested' ) . '</a>';
        echo wp_kses_post( $separator );
        echo '<span class="current">' . esc_html( single_term_title( '', false ) ) . ' ' . esc_html__( 'Reviews', 'toolshed-tested' ) . '</span>';
    } elseif ( is_post_type_archive( 'product_review' ) ) {
        echo wp_kses_post( $separator );
        echo '<span class="current">' . esc_html__( 'All Reviews', 'toolshed-tested' ) . '</span>';
    } elseif ( is_category() ) {
        echo wp_kses_post( $separator );
        echo '<span class="current">' . esc_html( single_cat_title( '', false ) ) . '</span>';
    } elseif ( is_search() ) {
        echo wp_kses_post( $separator );
        echo '<span class="current">' . esc_html__( 'Search Results', 'toolshed-tested' ) . '</span>';
    } elseif ( is_archive() ) {
        echo wp_kses_post( $separator );
        echo '<span class="current">' . esc_html( get_the_archive_title() ) . '</span>';
    }
}

/**
 * Display star rating HTML
 *
 * @param float $rating Rating value (0-5).
 * @return string Star rating HTML.
 */
function tst_star_rating( $rating ) {
    $rating   = floatval( $rating );
    $full     = floor( $rating );
    $half     = ( $rating - $full ) >= 0.5 ? 1 : 0;
    $empty    = 5 - $full - $half;
    $output   = '<div class="star-rating" aria-label="' . sprintf( esc_attr__( 'Rating: %s out of 5', 'toolshed-tested' ), $rating ) . '">';

    // Full stars
    for ( $i = 0; $i < $full; $i++ ) {
        $output .= '<svg class="star filled" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
    }

    // Half star
    if ( $half ) {
        $output .= '<svg class="star half" viewBox="0 0 24 24"><defs><linearGradient id="half-star-gradient"><stop offset="50%" stop-color="#ffc107"/><stop offset="50%" stop-color="#e9ecef"/></linearGradient></defs><path fill="url(#half-star-gradient)" d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
    }

    // Empty stars
    for ( $i = 0; $i < $empty; $i++ ) {
        $output .= '<svg class="star empty" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
    }

    $output .= '</div>';

    return $output;
}

/**
 * Display table of contents
 */
function tst_table_of_contents() {
    global $post;

    if ( ! is_singular() ) {
        return;
    }

    // Get content and extract headings
    $content = $post->post_content;
    preg_match_all( '/<h([2-3]).*?>(.*?)<\/h\1>/i', $content, $matches, PREG_SET_ORDER );

    if ( empty( $matches ) ) {
        return;
    }

    echo '<div class="table-of-contents">';
    echo '<h4 class="table-of-contents-title">';
    echo '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/></svg>';
    esc_html_e( 'Table of Contents', 'toolshed-tested' );
    echo '</h4>';
    echo '<ol>';

    foreach ( $matches as $match ) {
        $level = $match[1];
        $text  = wp_strip_all_tags( $match[2] );
        $slug  = sanitize_title( $text );

        $indent = $level === '3' ? ' class="toc-h3"' : '';
        echo '<li' . esc_attr( $indent ) . '><a href="#' . esc_attr( $slug ) . '">' . esc_html( $text ) . '</a></li>';
    }

    echo '</ol>';
    echo '</div>';
}

/**
 * Add IDs to headings for table of contents
 *
 * @param string $content Post content.
 * @return string Modified content with heading IDs.
 */
function tst_add_heading_ids( $content ) {
    if ( ! is_singular() ) {
        return $content;
    }

    $content = preg_replace_callback(
        '/<h([2-3])([^>]*)>(.*?)<\/h\1>/i',
        function ( $matches ) {
            $level = $matches[1];
            $attrs = $matches[2];
            $text  = $matches[3];
            $slug  = sanitize_title( wp_strip_all_tags( $text ) );

            // Check if ID already exists
            if ( strpos( $attrs, 'id=' ) !== false ) {
                return $matches[0];
            }

            return '<h' . $level . ' id="' . esc_attr( $slug ) . '"' . $attrs . '>' . $text . '</h' . $level . '>';
        },
        $content
    );

    return $content;
}
add_filter( 'the_content', 'tst_add_heading_ids', 5 );

/**
 * Display author box
 */
function tst_author_box() {
    $author_id    = get_the_author_meta( 'ID' );
    $author_name  = get_the_author();
    $author_bio   = get_the_author_meta( 'description' );
    $author_url   = get_author_posts_url( $author_id );
    $author_title = get_the_author_meta( 'job_title' );
    $post_count   = count_user_posts( $author_id, array( 'post', 'product_review' ), true );

    if ( empty( $author_bio ) ) {
        return;
    }
    ?>
    <div class="author-box">
        <div class="author-avatar">
            <?php echo get_avatar( $author_id, 96 ); ?>
            <span class="author-verified-badge" title="<?php esc_attr_e( 'Verified Expert', 'toolshed-tested' ); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
                </svg>
            </span>
        </div>
        <div class="author-info">
            <div class="author-header">
                <h4>
                    <a href="<?php echo esc_url( $author_url ); ?>">
                        <?php echo esc_html( $author_name ); ?>
                    </a>
                </h4>
                <?php if ( $author_title ) : ?>
                    <p class="author-title"><?php echo esc_html( $author_title ); ?></p>
                <?php endif; ?>
            </div>
            <?php echo wp_kses_post( tst_author_credentials( $author_id ) ); ?>
            <p class="author-bio"><?php echo esc_html( $author_bio ); ?></p>
            <div class="author-stats">
                <span class="stat">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                    <?php
                    /* translators: %d: number of reviews */
                    printf( esc_html( _n( '%d Review', '%d Reviews', $post_count, 'toolshed-tested' ) ), (int) $post_count );
                    ?>
                </span>
            </div>
            <a href="<?php echo esc_url( $author_url ); ?>" class="author-link tst-btn tst-btn-secondary">
                <?php esc_html_e( 'View all reviews', 'toolshed-tested' ); ?>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
    <?php
}

/**
 * Display related reviews
 */
function tst_related_reviews() {
    if ( ! is_singular( 'product_review' ) ) {
        return;
    }

    $categories = get_the_terms( get_the_ID(), 'product_category' );
    $category_ids = array();

    if ( $categories && ! is_wp_error( $categories ) ) {
        foreach ( $categories as $category ) {
            $category_ids[] = $category->term_id;
        }
    }

    $related = new WP_Query(
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
        )
    );

    if ( ! $related->have_posts() ) {
        return;
    }
    ?>
    <section class="related-reviews">
        <h3><?php esc_html_e( 'Related Reviews', 'toolshed-tested' ); ?></h3>
        <div class="reviews-grid">
            <?php
            while ( $related->have_posts() ) :
                $related->the_post();
                get_template_part( 'template-parts/review/review', 'card' );
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </section>
    <?php
}

/**
 * Default primary menu fallback
 */
function tst_default_menu() {
    echo '<ul id="primary-menu">';
    echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'toolshed-tested' ) . '</a></li>';

    // Reviews with category dropdown
    echo '<li class="menu-item-has-children">';
    echo '<a href="' . esc_url( get_post_type_archive_link( 'product_review' ) ) . '">' . esc_html__( 'Reviews', 'toolshed-tested' ) . '</a>';

    $categories = get_terms(
        array(
            'taxonomy'   => 'product_category',
            'hide_empty' => false,
            'number'     => 8,
            'orderby'    => 'name',
            'order'      => 'ASC',
        )
    );

    if ( $categories && ! is_wp_error( $categories ) ) {
        echo '<ul class="sub-menu">';
        echo '<li><a href="' . esc_url( get_post_type_archive_link( 'product_review' ) ) . '">' . esc_html__( 'All Reviews', 'toolshed-tested' ) . '</a></li>';
        foreach ( $categories as $category ) {
            echo '<li><a href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category->name ) . '</a></li>';
        }
        echo '</ul>';
    }
    echo '</li>';

    // Brands dropdown
    $brands = get_terms(
        array(
            'taxonomy'   => 'product_brand',
            'hide_empty' => false,
            'number'     => 8,
            'orderby'    => 'count',
            'order'      => 'DESC',
        )
    );

    if ( $brands && ! is_wp_error( $brands ) && ! empty( $brands ) ) {
        echo '<li class="menu-item-has-children">';
        echo '<a href="#">' . esc_html__( 'Brands', 'toolshed-tested' ) . '</a>';
        echo '<ul class="sub-menu">';
        foreach ( $brands as $brand ) {
            echo '<li><a href="' . esc_url( get_term_link( $brand ) ) . '">' . esc_html( $brand->name ) . '</a></li>';
        }
        echo '</ul>';
        echo '</li>';
    }

    echo '<li><a href="' . esc_url( home_url( '/about/' ) ) . '">' . esc_html__( 'About', 'toolshed-tested' ) . '</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/contact/' ) ) . '">' . esc_html__( 'Contact', 'toolshed-tested' ) . '</a></li>';
    echo '</ul>';
}

/**
 * Default footer menu fallback
 */
function tst_default_footer_menu() {
    echo '<ul>';
    echo '<li><a href="' . esc_url( home_url( '/about/' ) ) . '">' . esc_html__( 'About Us', 'toolshed-tested' ) . '</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/contact/' ) ) . '">' . esc_html__( 'Contact', 'toolshed-tested' ) . '</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/privacy-policy/' ) ) . '">' . esc_html__( 'Privacy Policy', 'toolshed-tested' ) . '</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/affiliate-disclosure/' ) ) . '">' . esc_html__( 'Affiliate Disclosure', 'toolshed-tested' ) . '</a></li>';
    echo '</ul>';
}

/**
 * Add custom classes to body
 *
 * @param array $classes Existing body classes.
 * @return array Modified body classes.
 */
function tst_custom_body_classes( $classes ) {
    // Add layout class
    if ( is_page_template( 'template-full-width.php' ) ) {
        $classes[] = 'full-width-layout';
    }

    return $classes;
}
add_filter( 'body_class', 'tst_custom_body_classes' );

/**
 * Calculate and display reading time
 *
 * @param int|null $post_id Post ID (optional).
 * @return string Reading time HTML.
 */
function tst_reading_time( $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }

    $content    = get_post_field( 'post_content', $post_id );
    $word_count = str_word_count( wp_strip_all_tags( $content ) );
    $read_time  = ceil( $word_count / 200 ); // 200 words per minute average

    if ( $read_time < 1 ) {
        $read_time = 1;
    }

    $output = '<span class="reading-time">';
    $output .= '<svg class="reading-time-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
    $output .= '<circle cx="12" cy="12" r="10"/>';
    $output .= '<polyline points="12 6 12 12 16 14"/>';
    $output .= '</svg>';
    /* translators: %d: number of minutes */
    $output .= sprintf( esc_html__( '%d min read', 'toolshed-tested' ), $read_time );
    $output .= '</span>';

    return $output;
}

/**
 * Display expert credentials for author
 *
 * @param int $author_id Author user ID.
 * @return string Expert credentials HTML.
 */
function tst_author_credentials( $author_id ) {
    $credentials = get_user_meta( $author_id, 'tst_credentials', true );
    $expertise   = get_user_meta( $author_id, 'tst_expertise', true );
    $years_exp   = get_user_meta( $author_id, 'tst_years_experience', true );

    $output = '';

    if ( $credentials || $expertise || $years_exp ) {
        $output .= '<div class="author-credentials">';

        if ( $years_exp ) {
            $output .= '<span class="credential-badge experience">';
            $output .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>';
            /* translators: %s: number of years */
            $output .= sprintf( esc_html__( '%s+ Years Experience', 'toolshed-tested' ), esc_html( $years_exp ) );
            $output .= '</span>';
        }

        if ( $expertise ) {
            $output .= '<span class="credential-badge expertise">';
            $output .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>';
            $output .= esc_html( $expertise );
            $output .= '</span>';
        }

        if ( $credentials ) {
            $output .= '<span class="credential-badge verified">';
            $output .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>';
            $output .= esc_html( $credentials );
            $output .= '</span>';
        }

        $output .= '</div>';
    }

    return $output;
}

/**
 * Display verdict box for reviews
 *
 * @param int|null $post_id Post ID (optional).
 */
function tst_verdict_box( $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }

    $rating       = get_post_meta( $post_id, '_tst_rating', true );
    $verdict      = get_post_meta( $post_id, '_tst_verdict', true );
    $best_for     = get_post_meta( $post_id, '_tst_best_for', true );
    $affiliate_url = get_post_meta( $post_id, '_tst_affiliate_url', true );
    $price        = get_post_meta( $post_id, '_tst_price', true );

    if ( ! $rating ) {
        return;
    }

    // Generate verdict label based on rating
    if ( ! $verdict ) {
        if ( $rating >= 4.5 ) {
            $verdict = __( 'Excellent Choice', 'toolshed-tested' );
        } elseif ( $rating >= 4.0 ) {
            $verdict = __( 'Highly Recommended', 'toolshed-tested' );
        } elseif ( $rating >= 3.5 ) {
            $verdict = __( 'Good Option', 'toolshed-tested' );
        } elseif ( $rating >= 3.0 ) {
            $verdict = __( 'Decent Choice', 'toolshed-tested' );
        } else {
            $verdict = __( 'Consider Alternatives', 'toolshed-tested' );
        }
    }

    // Determine verdict class
    $verdict_class = 'neutral';
    if ( $rating >= 4.5 ) {
        $verdict_class = 'excellent';
    } elseif ( $rating >= 4.0 ) {
        $verdict_class = 'great';
    } elseif ( $rating >= 3.5 ) {
        $verdict_class = 'good';
    } elseif ( $rating < 3.0 ) {
        $verdict_class = 'poor';
    }
    ?>
    <div class="verdict-box verdict-<?php echo esc_attr( $verdict_class ); ?>">
        <div class="verdict-header">
            <div class="verdict-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div class="verdict-title">
                <span class="verdict-label"><?php esc_html_e( 'Our Verdict', 'toolshed-tested' ); ?></span>
                <strong class="verdict-text"><?php echo esc_html( $verdict ); ?></strong>
            </div>
            <div class="verdict-score">
                <span class="score-number"><?php echo esc_html( $rating ); ?></span>
                <span class="score-max">/5</span>
            </div>
        </div>

        <?php if ( $best_for ) : ?>
            <div class="verdict-best-for">
                <strong><?php esc_html_e( 'Best For:', 'toolshed-tested' ); ?></strong>
                <?php echo esc_html( $best_for ); ?>
            </div>
        <?php endif; ?>

        <div class="verdict-rating-bar">
            <div class="rating-bar-fill" style="width: <?php echo esc_attr( ( floatval( $rating ) / 5 ) * 100 ); ?>%;"></div>
        </div>

        <?php if ( $affiliate_url ) : ?>
            <div class="verdict-cta">
                <a href="<?php echo esc_url( $affiliate_url ); ?>"
                   class="tst-btn tst-btn-amazon affiliate-link"
                   target="_blank"
                   rel="nofollow noopener sponsored"
                   data-product-id="<?php echo esc_attr( $post_id ); ?>">
                    <?php esc_html_e( 'Check Price on Amazon', 'toolshed-tested' ); ?>
                    <?php if ( $price ) : ?>
                        <span class="btn-price"><?php echo esc_html( $price ); ?></span>
                    <?php endif; ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
