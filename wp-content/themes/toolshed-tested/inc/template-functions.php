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

    echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'toolshed-tested' ) . '">';
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

    echo '</nav>';
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
 * Displays categories and brands for better buyer-intent navigation
 */
function tst_default_menu() {
    echo '<ul id="primary-menu">';

    // Tool Reviews with categories dropdown
    echo '<li class="menu-item-has-children">';
    echo '<a href="' . esc_url( get_post_type_archive_link( 'product_review' ) ) . '">' . esc_html__( 'Tool Reviews', 'toolshed-tested' ) . '</a>';
    echo '<ul class="sub-menu">';

    $categories = get_terms( array(
        'taxonomy'   => 'product_category',
        'hide_empty' => false,
        'number'     => 6,
    ) );

    if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
        foreach ( $categories as $category ) {
            echo '<li><a href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category->name ) . '</a></li>';
        }
    } else {
        // Fallback categories if none exist yet
        $default_categories = array( 'Chainsaws', 'Leaf Blowers', 'Lawn Mowers', 'String Trimmers', 'Pressure Washers' );
        foreach ( $default_categories as $cat_name ) {
            echo '<li><a href="' . esc_url( get_post_type_archive_link( 'product_review' ) ) . '">' . esc_html( $cat_name ) . '</a></li>';
        }
    }

    echo '<li class="view-all"><a href="' . esc_url( get_post_type_archive_link( 'product_review' ) ) . '">' . esc_html__( 'View All Reviews', 'toolshed-tested' ) . '</a></li>';
    echo '</ul></li>';

    // Brands dropdown
    echo '<li class="menu-item-has-children">';
    echo '<a href="' . esc_url( home_url( '/brand/' ) ) . '">' . esc_html__( 'Brands', 'toolshed-tested' ) . '</a>';
    echo '<ul class="sub-menu">';

    $brands = get_terms( array(
        'taxonomy'   => 'product_brand',
        'hide_empty' => false,
        'number'     => 6,
    ) );

    if ( ! is_wp_error( $brands ) && ! empty( $brands ) ) {
        foreach ( $brands as $brand ) {
            echo '<li><a href="' . esc_url( get_term_link( $brand ) ) . '">' . esc_html( $brand->name ) . '</a></li>';
        }
    } else {
        // Fallback brands if none exist yet
        $default_brands = array( 'DeWalt', 'Milwaukee', 'Ryobi', 'Stihl', 'Husqvarna', 'Makita' );
        foreach ( $default_brands as $brand_name ) {
            echo '<li><a href="' . esc_url( get_post_type_archive_link( 'product_review' ) ) . '">' . esc_html( $brand_name ) . '</a></li>';
        }
    }

    echo '<li class="view-all"><a href="' . esc_url( home_url( '/brand/' ) ) . '">' . esc_html__( 'All Brands', 'toolshed-tested' ) . '</a></li>';
    echo '</ul></li>';

    // Buying Guides
    echo '<li class="menu-item-has-children">';
    echo '<a href="' . esc_url( home_url( '/guides/' ) ) . '">' . esc_html__( 'Buying Guides', 'toolshed-tested' ) . '</a>';
    echo '<ul class="sub-menu">';
    echo '<li><a href="' . esc_url( home_url( '/guides/beginners/' ) ) . '">' . esc_html__( "Beginner's Guide", 'toolshed-tested' ) . '</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/guides/budget-picks/' ) ) . '">' . esc_html__( 'Best Budget Picks', 'toolshed-tested' ) . '</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/guides/professional/' ) ) . '">' . esc_html__( 'Professional Grade', 'toolshed-tested' ) . '</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/comparisons/' ) ) . '">' . esc_html__( 'Comparison Charts', 'toolshed-tested' ) . '</a></li>';
    echo '</ul></li>';

    // About
    echo '<li><a href="' . esc_url( home_url( '/about/' ) ) . '">' . esc_html__( 'About', 'toolshed-tested' ) . '</a></li>';

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
