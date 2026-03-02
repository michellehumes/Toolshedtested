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

    // Get content and extract H2 headings only (skip H3s to keep TOC concise)
    $content = $post->post_content;
    preg_match_all( '/<h2[^>]*>(.*?)<\/h2>/i', $content, $matches, PREG_SET_ORDER );

    if ( empty( $matches ) ) {
        return;
    }

    // Cap at 10 items max
    $matches = array_slice( $matches, 0, 10 );

    echo '<div class="table-of-contents">';
    echo '<button class="table-of-contents-title" aria-expanded="true" onclick="this.setAttribute(\'aria-expanded\', this.getAttribute(\'aria-expanded\') === \'true\' ? \'false\' : \'true\'); this.nextElementSibling.style.display = this.getAttribute(\'aria-expanded\') === \'true\' ? \'block\' : \'none\';">';
    echo '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/></svg>';
    esc_html_e( 'Table of Contents', 'toolshed-tested' );
    echo '<svg class="toc-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6z"/></svg>';
    echo '</button>';
    echo '<ol class="toc-list">';

    foreach ( $matches as $match ) {
        $text = wp_strip_all_tags( $match[1] );
        $slug = sanitize_title( $text );
        echo '<li><a href="#' . esc_attr( $slug ) . '">' . esc_html( $text ) . '</a></li>';
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
    $author_id   = get_the_author_meta( 'ID' );
    $author_name = get_the_author();
    $author_bio  = get_the_author_meta( 'description' );
    $author_url  = get_author_posts_url( $author_id );
    $author_title = get_the_author_meta( 'job_title' );

    if ( empty( $author_bio ) ) {
        return;
    }
    ?>
    <div class="author-box">
        <div class="author-avatar">
            <?php echo get_avatar( $author_id, 80 ); ?>
        </div>
        <div class="author-info">
            <h4>
                <a href="<?php echo esc_url( $author_url ); ?>">
                    <?php echo esc_html( $author_name ); ?>
                </a>
            </h4>
            <?php if ( $author_title ) : ?>
                <p class="author-title"><?php echo esc_html( $author_title ); ?></p>
            <?php endif; ?>
            <p class="author-bio"><?php echo esc_html( $author_bio ); ?></p>
            <a href="<?php echo esc_url( $author_url ); ?>" class="author-link">
                <?php esc_html_e( 'View all posts', 'toolshed-tested' ); ?> &rarr;
            </a>
        </div>
    </div>
    <?php
}

/**
 * Display related reviews
 */
function tst_related_reviews() {
    if ( ! is_singular( array( 'post', 'product_review' ) ) ) {
        return;
    }

    // Try custom taxonomy first, fall back to standard categories
    $categories = get_the_terms( get_the_ID(), 'product_category' );
    $taxonomy   = 'product_category';
    if ( ! $categories || is_wp_error( $categories ) ) {
        $categories = get_the_category();
        $taxonomy   = 'category';
    }

    $category_ids = array();
    if ( $categories && ! is_wp_error( $categories ) ) {
        foreach ( $categories as $category ) {
            $category_ids[] = $category->term_id;
        }
    }

    $query_args = array(
        'post_type'      => 'post',
        'posts_per_page' => 3,
        'post__not_in'   => array( get_the_ID() ),
    );

    if ( ! empty( $category_ids ) ) {
        $query_args['tax_query'] = array(
            array(
                'taxonomy' => $taxonomy,
                'field'    => 'term_id',
                'terms'    => $category_ids,
            ),
        );
    }

    $related = new WP_Query( $query_args );

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
    $blog_url = get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' );
    echo '<a href="' . esc_url( $blog_url ) . '">' . esc_html__( 'Reviews', 'toolshed-tested' ) . '</a>';

    $categories = get_terms(
        array(
            'taxonomy'   => 'category',
            'hide_empty' => false,
            'number'     => 8,
            'orderby'    => 'name',
            'order'      => 'ASC',
        )
    );

    if ( $categories && ! is_wp_error( $categories ) ) {
        echo '<ul class="sub-menu">';
        echo '<li><a href="' . esc_url( $blog_url ) . '">' . esc_html__( 'All Reviews', 'toolshed-tested' ) . '</a></li>';
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
