<?php
/**
 * Toolshed Tested Theme Functions
 *
 * @package Toolshed_Tested
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define theme constants
define( 'TST_VERSION', '1.0.0' );
define( 'TST_THEME_DIR', get_template_directory() );
define( 'TST_THEME_URI', get_template_directory_uri() );

/**
 * Theme Setup
 */
function tst_theme_setup() {
    // Add default posts and comments RSS feed links to head
    add_theme_support( 'automatic-feed-links' );

    // Let WordPress manage the document title
    add_theme_support( 'title-tag' );

    // Enable support for Post Thumbnails on posts and pages
    add_theme_support( 'post-thumbnails' );

    // Add custom image sizes for product reviews
    add_image_size( 'tst-review-featured', 1200, 630, true );
    add_image_size( 'tst-product-thumb', 300, 300, true );
    add_image_size( 'tst-product-large', 600, 600, true );
    add_image_size( 'tst-card-image', 400, 225, true );

    // Register navigation menus
    register_nav_menus(
        array(
            'primary'   => esc_html__( 'Primary Menu', 'toolshed-tested' ),
            'footer'    => esc_html__( 'Footer Menu', 'toolshed-tested' ),
            'category'  => esc_html__( 'Category Menu', 'toolshed-tested' ),
        )
    );

    // Switch default core markup to output valid HTML5
    add_theme_support(
        'html5',
        array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        )
    );

    // Add theme support for selective refresh for widgets
    add_theme_support( 'customize-selective-refresh-widgets' );

    // Add support for Block Styles
    add_theme_support( 'wp-block-styles' );

    // Add support for full and wide align images
    add_theme_support( 'align-wide' );

    // Add support for responsive embedded content
    add_theme_support( 'responsive-embeds' );

    // Add support for custom logo
    add_theme_support(
        'custom-logo',
        array(
            'height'      => 100,
            'width'       => 300,
            'flex-width'  => true,
            'flex-height' => true,
        )
    );

    // Add editor styles
    add_theme_support( 'editor-styles' );
    add_editor_style( 'assets/css/editor-style.css' );

    // Add custom color palette for blocks
    add_theme_support(
        'editor-color-palette',
        array(
            array(
                'name'  => esc_html__( 'Primary Green', 'toolshed-tested' ),
                'slug'  => 'primary',
                'color' => '#2d5a27',
            ),
            array(
                'name'  => esc_html__( 'Secondary Yellow', 'toolshed-tested' ),
                'slug'  => 'secondary',
                'color' => '#f4a524',
            ),
            array(
                'name'  => esc_html__( 'Accent Red', 'toolshed-tested' ),
                'slug'  => 'accent',
                'color' => '#e63946',
            ),
            array(
                'name'  => esc_html__( 'Dark', 'toolshed-tested' ),
                'slug'  => 'dark',
                'color' => '#1a1a1a',
            ),
            array(
                'name'  => esc_html__( 'Light Gray', 'toolshed-tested' ),
                'slug'  => 'light-gray',
                'color' => '#f8f9fa',
            ),
        )
    );

    // Set content width
    if ( ! isset( $content_width ) ) {
        $content_width = 800;
    }
}
add_action( 'after_setup_theme', 'tst_theme_setup' );

/**
 * Enqueue Scripts and Styles
 */
function tst_enqueue_assets() {
    // Google Fonts - Preconnect
    wp_enqueue_style(
        'tst-google-fonts-preconnect',
        'https://fonts.googleapis.com',
        array(),
        null
    );

    // Enqueue Google Fonts
    wp_enqueue_style(
        'tst-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800&display=swap',
        array(),
        null
    );

    // Main stylesheet
    wp_enqueue_style(
        'tst-style',
        get_stylesheet_uri(),
        array(),
        TST_VERSION
    );

    // Additional component styles
    wp_enqueue_style(
        'tst-components',
        TST_THEME_URI . '/assets/css/components.css',
        array( 'tst-style' ),
        TST_VERSION
    );

    // Main JavaScript
    wp_enqueue_script(
        'tst-main',
        TST_THEME_URI . '/assets/js/main.js',
        array(),
        TST_VERSION,
        true
    );

    // Affiliate link tracking
    wp_enqueue_script(
        'tst-affiliate',
        TST_THEME_URI . '/assets/js/affiliate.js',
        array(),
        TST_VERSION,
        true
    );

    // Localize script for AJAX
    wp_localize_script(
        'tst-main',
        'tstData',
        array(
            'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
            'nonce'         => wp_create_nonce( 'tst_nonce' ),
            'siteUrl'       => home_url(),
            'affiliateNote' => esc_html__( 'As an Amazon Associate, we earn from qualifying purchases.', 'toolshed-tested' ),
        )
    );

    // Comment reply script
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'tst_enqueue_assets' );

/**
 * Register Widget Areas
 */
function tst_register_sidebars() {
    register_sidebar(
        array(
            'name'          => esc_html__( 'Primary Sidebar', 'toolshed-tested' ),
            'id'            => 'sidebar-1',
            'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'toolshed-tested' ),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        )
    );

    register_sidebar(
        array(
            'name'          => esc_html__( 'Footer 1', 'toolshed-tested' ),
            'id'            => 'footer-1',
            'description'   => esc_html__( 'First footer widget area.', 'toolshed-tested' ),
            'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="footer-widget-title">',
            'after_title'   => '</h4>',
        )
    );

    register_sidebar(
        array(
            'name'          => esc_html__( 'Footer 2', 'toolshed-tested' ),
            'id'            => 'footer-2',
            'description'   => esc_html__( 'Second footer widget area.', 'toolshed-tested' ),
            'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="footer-widget-title">',
            'after_title'   => '</h4>',
        )
    );

    register_sidebar(
        array(
            'name'          => esc_html__( 'Footer 3', 'toolshed-tested' ),
            'id'            => 'footer-3',
            'description'   => esc_html__( 'Third footer widget area.', 'toolshed-tested' ),
            'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="footer-widget-title">',
            'after_title'   => '</h4>',
        )
    );

    register_sidebar(
        array(
            'name'          => esc_html__( 'Footer 4', 'toolshed-tested' ),
            'id'            => 'footer-4',
            'description'   => esc_html__( 'Fourth footer widget area.', 'toolshed-tested' ),
            'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="footer-widget-title">',
            'after_title'   => '</h4>',
        )
    );
}
add_action( 'widgets_init', 'tst_register_sidebars' );

/**
 * Include additional theme files
 */
require_once TST_THEME_DIR . '/inc/template-functions.php';
require_once TST_THEME_DIR . '/inc/template-tags.php';
require_once TST_THEME_DIR . '/inc/customizer.php';
require_once TST_THEME_DIR . '/inc/class-tst-product-review.php';
require_once TST_THEME_DIR . '/inc/class-tst-schema.php';
require_once TST_THEME_DIR . '/inc/class-tst-affiliate.php';
require_once TST_THEME_DIR . '/inc/shortcodes.php';

/**
 * Register Custom Post Type: Product Review
 */
function tst_register_post_types() {
    $labels = array(
        'name'                  => _x( 'Product Reviews', 'Post type general name', 'toolshed-tested' ),
        'singular_name'         => _x( 'Product Review', 'Post type singular name', 'toolshed-tested' ),
        'menu_name'             => _x( 'Product Reviews', 'Admin Menu text', 'toolshed-tested' ),
        'add_new'               => __( 'Add New', 'toolshed-tested' ),
        'add_new_item'          => __( 'Add New Product Review', 'toolshed-tested' ),
        'edit_item'             => __( 'Edit Product Review', 'toolshed-tested' ),
        'new_item'              => __( 'New Product Review', 'toolshed-tested' ),
        'view_item'             => __( 'View Product Review', 'toolshed-tested' ),
        'search_items'          => __( 'Search Product Reviews', 'toolshed-tested' ),
        'not_found'             => __( 'No product reviews found', 'toolshed-tested' ),
        'not_found_in_trash'    => __( 'No product reviews found in Trash', 'toolshed-tested' ),
        'all_items'             => __( 'All Product Reviews', 'toolshed-tested' ),
        'featured_image'        => __( 'Product Image', 'toolshed-tested' ),
        'set_featured_image'    => __( 'Set product image', 'toolshed-tested' ),
        'remove_featured_image' => __( 'Remove product image', 'toolshed-tested' ),
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array( 'slug' => 'reviews' ),
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-star-filled',
        'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'custom-fields' ),
        'show_in_rest'        => true,
    );

    register_post_type( 'product_review', $args );
}
add_action( 'init', 'tst_register_post_types' );

/**
 * Register Custom Taxonomies
 */
function tst_register_taxonomies() {
    // Product Category
    register_taxonomy(
        'product_category',
        'product_review',
        array(
            'labels'            => array(
                'name'              => _x( 'Product Categories', 'taxonomy general name', 'toolshed-tested' ),
                'singular_name'     => _x( 'Product Category', 'taxonomy singular name', 'toolshed-tested' ),
                'search_items'      => __( 'Search Product Categories', 'toolshed-tested' ),
                'all_items'         => __( 'All Product Categories', 'toolshed-tested' ),
                'parent_item'       => __( 'Parent Product Category', 'toolshed-tested' ),
                'parent_item_colon' => __( 'Parent Product Category:', 'toolshed-tested' ),
                'edit_item'         => __( 'Edit Product Category', 'toolshed-tested' ),
                'update_item'       => __( 'Update Product Category', 'toolshed-tested' ),
                'add_new_item'      => __( 'Add New Product Category', 'toolshed-tested' ),
                'new_item_name'     => __( 'New Product Category Name', 'toolshed-tested' ),
                'menu_name'         => __( 'Product Categories', 'toolshed-tested' ),
            ),
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'category' ),
            'show_in_rest'      => true,
        )
    );

    // Brand
    register_taxonomy(
        'product_brand',
        'product_review',
        array(
            'labels'            => array(
                'name'              => _x( 'Brands', 'taxonomy general name', 'toolshed-tested' ),
                'singular_name'     => _x( 'Brand', 'taxonomy singular name', 'toolshed-tested' ),
                'search_items'      => __( 'Search Brands', 'toolshed-tested' ),
                'all_items'         => __( 'All Brands', 'toolshed-tested' ),
                'edit_item'         => __( 'Edit Brand', 'toolshed-tested' ),
                'update_item'       => __( 'Update Brand', 'toolshed-tested' ),
                'add_new_item'      => __( 'Add New Brand', 'toolshed-tested' ),
                'new_item_name'     => __( 'New Brand Name', 'toolshed-tested' ),
                'menu_name'         => __( 'Brands', 'toolshed-tested' ),
            ),
            'hierarchical'      => false,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'brand' ),
            'show_in_rest'      => true,
        )
    );
}
add_action( 'init', 'tst_register_taxonomies' );

/**
 * Add Preload for Critical Resources
 */
function tst_add_resource_hints( $urls, $relation_type ) {
    if ( 'preconnect' === $relation_type ) {
        $urls[] = array(
            'href' => 'https://fonts.googleapis.com',
            'crossorigin',
        );
        $urls[] = array(
            'href' => 'https://fonts.gstatic.com',
            'crossorigin',
        );
    }
    return $urls;
}
add_filter( 'wp_resource_hints', 'tst_add_resource_hints', 10, 2 );

/**
 * Add async/defer to scripts for performance
 */
function tst_script_loader_tag( $tag, $handle, $src ) {
    $async_scripts = array( 'tst-affiliate' );
    $defer_scripts = array( 'tst-main' );

    if ( in_array( $handle, $async_scripts, true ) ) {
        return str_replace( ' src', ' async src', $tag );
    }

    if ( in_array( $handle, $defer_scripts, true ) ) {
        return str_replace( ' src', ' defer src', $tag );
    }

    return $tag;
}
add_filter( 'script_loader_tag', 'tst_script_loader_tag', 10, 3 );

/**
 * Optimize images for lazy loading
 */
function tst_add_lazy_loading_attribute( $content ) {
    // Only apply to post content
    if ( ! is_singular() ) {
        return $content;
    }

    // Add loading="lazy" to images that don't have it
    $content = preg_replace(
        '/<img(?![^>]*loading=)([^>]*)>/i',
        '<img loading="lazy"$1>',
        $content
    );

    return $content;
}
add_filter( 'the_content', 'tst_add_lazy_loading_attribute' );

/**
 * Add SEO meta tags
 */
function tst_add_meta_tags() {
    global $post;

    // Default meta description
    $description = get_bloginfo( 'description' );
    $title       = get_bloginfo( 'name' );
    $image       = '';

    if ( is_singular() && isset( $post ) ) {
        $description = has_excerpt( $post->ID ) ? get_the_excerpt( $post ) : wp_trim_words( $post->post_content, 30 );
        $title       = get_the_title( $post );

        if ( has_post_thumbnail( $post->ID ) ) {
            $image = get_the_post_thumbnail_url( $post->ID, 'large' );
        }
    }

    // Clean up description
    $description = wp_strip_all_tags( $description );
    $description = esc_attr( $description );

    echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";

    // Open Graph meta tags
    echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
    echo '<meta property="og:type" content="' . ( is_singular() ? 'article' : 'website' ) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url( is_singular() ? get_permalink() : home_url() ) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";

    if ( $image ) {
        echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
    }

    // Twitter Card meta tags
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";

    if ( $image ) {
        echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
    }
}
add_action( 'wp_head', 'tst_add_meta_tags', 1 );

/**
 * Custom Excerpt Length
 */
function tst_excerpt_length( $length ) {
    return 30;
}
add_filter( 'excerpt_length', 'tst_excerpt_length' );

/**
 * Custom Excerpt More Link
 */
function tst_excerpt_more( $more ) {
    return '...';
}
add_filter( 'excerpt_more', 'tst_excerpt_more' );

/**
 * Exclude Uncategorized from category widgets.
 *
 * @param array $args Widget args.
 * @return array
 */
function tst_exclude_uncategorized_from_widgets( $args ) {
    $default_category = absint( get_option( 'default_category' ) );
    if ( ! $default_category ) {
        return $args;
    }

    if ( empty( $args['exclude'] ) ) {
        $args['exclude'] = $default_category;
    } else {
        $excluded = array_filter( array_map( 'absint', explode( ',', $args['exclude'] ) ) );
        if ( ! in_array( $default_category, $excluded, true ) ) {
            $excluded[] = $default_category;
        }
        $args['exclude'] = implode( ',', $excluded );
    }

    return $args;
}
add_filter( 'widget_categories_args', 'tst_exclude_uncategorized_from_widgets' );
add_filter( 'widget_categories_dropdown_args', 'tst_exclude_uncategorized_from_widgets' );

/**
 * Add custom body classes
 */
function tst_body_classes( $classes ) {
    // Adds a class of hfeed to non-singular pages
    if ( ! is_singular() ) {
        $classes[] = 'hfeed';
    }

    // Add class for sidebar
    if ( is_active_sidebar( 'sidebar-1' ) && ! is_page_template( 'template-full-width.php' ) ) {
        $classes[] = 'has-sidebar';
    }

    // Add class for product review pages
    if ( is_singular( 'product_review' ) ) {
        $classes[] = 'single-review';
    }

    return $classes;
}
add_filter( 'body_class', 'tst_body_classes' );

/**
 * Disable XML-RPC for security
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Remove WordPress version from header for security
 */
remove_action( 'wp_head', 'wp_generator' );

/**
 * Add security headers
 */
function tst_add_security_headers() {
    header( 'X-Content-Type-Options: nosniff' );
    header( 'X-Frame-Options: SAMEORIGIN' );
    header( 'X-XSS-Protection: 1; mode=block' );
    header( 'Referrer-Policy: strict-origin-when-cross-origin' );
}
add_action( 'send_headers', 'tst_add_security_headers' );

/**
 * AJAX handler for affiliate click tracking
 */
function tst_track_affiliate_click() {
    check_ajax_referer( 'tst_nonce', 'nonce' );

    $product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
    $affiliate_id = isset( $_POST['affiliate_id'] ) ? sanitize_text_field( wp_unslash( $_POST['affiliate_id'] ) ) : '';

    if ( $product_id && $affiliate_id ) {
        // Log the click (you can customize this to save to database or analytics)
        do_action( 'tst_affiliate_click', $product_id, $affiliate_id );

        wp_send_json_success( array( 'message' => 'Click tracked' ) );
    }

    wp_send_json_error( array( 'message' => 'Invalid data' ) );
}
add_action( 'wp_ajax_tst_track_click', 'tst_track_affiliate_click' );
add_action( 'wp_ajax_nopriv_tst_track_click', 'tst_track_affiliate_click' );

/**
 * Redirect /disclosure/ to /affiliate-disclosure/
 */
function tst_disclosure_redirect() {
    if ( is_404() ) {
        global $wp;
        if ( $wp->request === 'disclosure' ) {
            wp_redirect( home_url( '/affiliate-disclosure/' ), 301 );
            exit;
        }
    }
}
add_action( 'template_redirect', 'tst_disclosure_redirect' );

/**
 * Enqueue email popup script
 */
function tst_enqueue_popup_script() {
    if ( ! is_admin() ) {
        wp_enqueue_script(
            'tst-email-popup',
            TST_THEME_URI . '/assets/js/email-popup.js',
            array(),
            TST_VERSION,
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'tst_enqueue_popup_script' );

/**
 * Get the primary affiliate URL for mobile sticky CTA
 */
function tst_get_affiliate_url( $url ) {
    $url = trim( (string) $url );
    if ( '' === $url ) {
        return '';
    }

    if ( class_exists( 'TST_Affiliate' ) ) {
        $affiliate = TST_Affiliate::get_instance();
        if ( $affiliate && method_exists( $affiliate, 'append_amazon_tag' ) ) {
            return $affiliate->append_amazon_tag( $url );
        }
    }

    return $url;
}

/**
 * Get affiliate disclosure text.
 *
 * @return string
 */
function tst_get_affiliate_disclosure_text() {
    return get_theme_mod(
        'tst_affiliate_disclosure',
        __( 'As an Amazon Associate, we earn from qualifying purchases. This post may contain affiliate links.', 'toolshed-tested' )
    );
}

/**
 * Determine if affiliate disclosure should be shown for a post.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function tst_should_show_affiliate_disclosure( $post_id ) {
    $post_id = absint( $post_id );
    if ( ! $post_id ) {
        return false;
    }

    if ( 'product_review' === get_post_type( $post_id ) ) {
        return true;
    }

    if ( has_tag( 'affiliate', $post_id ) || has_category( 'reviews', $post_id ) ) {
        return true;
    }

    $affiliate_url   = get_post_meta( $post_id, '_tst_affiliate_url', true );
    $affiliate_url_2 = get_post_meta( $post_id, '_tst_affiliate_url_2', true );
    if ( $affiliate_url || $affiliate_url_2 ) {
        return true;
    }

    $post = get_post( $post_id );
    if ( $post && preg_match( '/href=["\']([^"\']*amazon\.[^"\']*)["\']/', $post->post_content ) ) {
        return true;
    }

    return false;
}

function tst_get_primary_affiliate_url() {
    if ( is_singular( 'post' ) || is_singular( 'product_review' ) ) {
        $affiliate_url = get_post_meta( get_the_ID(), '_tst_affiliate_url', true );
        if ( $affiliate_url ) {
            return tst_get_affiliate_url( $affiliate_url );
        }

        $affiliate_url_2 = get_post_meta( get_the_ID(), '_tst_affiliate_url_2', true );
        if ( $affiliate_url_2 ) {
            return tst_get_affiliate_url( $affiliate_url_2 );
        }

        // Try to find first Amazon link in content
        global $post;
        if ( $post && preg_match( '/href=["\']([^"\']*amazon\.com[^"\']*)["\']/', $post->post_content, $matches ) ) {
            return tst_get_affiliate_url( $matches[1] );
        }
    }
    return '';
}
