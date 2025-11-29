<?php
/**
 * Schema.org Markup Generator
 *
 * @package Toolshed_Tested
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class TST_Schema
 *
 * Generates structured data for SEO
 */
class TST_Schema {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_head', array( $this, 'output_organization_schema' ) );
        add_action( 'wp_head', array( $this, 'output_website_schema' ) );
        add_action( 'wp_head', array( $this, 'output_breadcrumb_schema' ) );
    }

    /**
     * Output Organization Schema
     */
    public function output_organization_schema() {
        if ( ! is_front_page() ) {
            return;
        }

        $schema = array(
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => get_bloginfo( 'name' ),
            'url'      => home_url(),
            'logo'     => array(
                '@type' => 'ImageObject',
                'url'   => has_custom_logo() ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) : '',
            ),
        );

        // Add social profiles
        $social_profiles = array();
        $networks        = array( 'facebook', 'twitter', 'instagram', 'youtube', 'pinterest' );

        foreach ( $networks as $network ) {
            $url = get_theme_mod( 'tst_social_' . $network );
            if ( ! empty( $url ) ) {
                $social_profiles[] = $url;
            }
        }

        if ( ! empty( $social_profiles ) ) {
            $schema['sameAs'] = $social_profiles;
        }

        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    }

    /**
     * Output Website Schema
     */
    public function output_website_schema() {
        if ( ! is_front_page() ) {
            return;
        }

        $schema = array(
            '@context'        => 'https://schema.org',
            '@type'           => 'WebSite',
            'name'            => get_bloginfo( 'name' ),
            'url'             => home_url(),
            'potentialAction' => array(
                '@type'       => 'SearchAction',
                'target'      => home_url( '/?s={search_term_string}' ),
                'query-input' => 'required name=search_term_string',
            ),
        );

        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
    }

    /**
     * Output Breadcrumb Schema
     */
    public function output_breadcrumb_schema() {
        if ( is_front_page() ) {
            return;
        }

        $items = array();
        $position = 1;

        // Home
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position,
            'name'     => __( 'Home', 'toolshed-tested' ),
            'item'     => home_url(),
        );

        if ( is_singular( 'product_review' ) ) {
            $position++;

            // Reviews archive
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position,
                'name'     => __( 'Reviews', 'toolshed-tested' ),
                'item'     => get_post_type_archive_link( 'product_review' ),
            );

            // Category
            $categories = get_the_terms( get_the_ID(), 'product_category' );
            if ( $categories && ! is_wp_error( $categories ) ) {
                $position++;
                $items[] = array(
                    '@type'    => 'ListItem',
                    'position' => $position,
                    'name'     => $categories[0]->name,
                    'item'     => get_term_link( $categories[0] ),
                );
            }

            // Current post
            $position++;
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position,
                'name'     => get_the_title(),
                'item'     => get_permalink(),
            );
        } elseif ( is_singular( 'post' ) ) {
            $position++;

            $categories = get_the_category();
            if ( ! empty( $categories ) ) {
                $items[] = array(
                    '@type'    => 'ListItem',
                    'position' => $position,
                    'name'     => $categories[0]->name,
                    'item'     => get_category_link( $categories[0]->term_id ),
                );
                $position++;
            }

            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position,
                'name'     => get_the_title(),
                'item'     => get_permalink(),
            );
        }

        if ( count( $items ) > 1 ) {
            $schema = array(
                '@context'        => 'https://schema.org',
                '@type'           => 'BreadcrumbList',
                'itemListElement' => $items,
            );

            echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
        }
    }
}

// Initialize
new TST_Schema();

/**
 * Generate Product Review Schema
 *
 * @return string JSON-LD schema markup.
 */
function tst_product_review_schema() {
    if ( ! is_singular( 'product_review' ) ) {
        return '';
    }

    $post_id      = get_the_ID();
    $rating       = get_post_meta( $post_id, '_tst_rating', true );
    $price        = get_post_meta( $post_id, '_tst_price', true );
    $pros         = get_post_meta( $post_id, '_tst_pros', true );
    $cons         = get_post_meta( $post_id, '_tst_cons', true );

    // Clean price
    $price_value = preg_replace( '/[^0-9.]/', '', $price );

    // Get brand
    $brands     = get_the_terms( $post_id, 'product_brand' );
    $brand_name = $brands && ! is_wp_error( $brands ) ? $brands[0]->name : '';

    // Get image
    $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url( $post_id, 'large' ) : '';

    // Build Review schema
    $review_schema = array(
        '@context'      => 'https://schema.org',
        '@type'         => 'Review',
        'itemReviewed'  => array(
            '@type'       => 'Product',
            'name'        => get_the_title(),
            'image'       => $image_url,
            'description' => get_the_excerpt(),
        ),
        'reviewRating'  => array(
            '@type'       => 'Rating',
            'ratingValue' => floatval( $rating ),
            'bestRating'  => 5,
            'worstRating' => 1,
        ),
        'author'        => array(
            '@type' => 'Person',
            'name'  => get_the_author(),
        ),
        'publisher'     => array(
            '@type' => 'Organization',
            'name'  => get_bloginfo( 'name' ),
        ),
        'datePublished' => get_the_date( 'c' ),
        'dateModified'  => get_the_modified_date( 'c' ),
    );

    // Add brand
    if ( $brand_name ) {
        $review_schema['itemReviewed']['brand'] = array(
            '@type' => 'Brand',
            'name'  => $brand_name,
        );
    }

    // Add price
    if ( $price_value ) {
        $review_schema['itemReviewed']['offers'] = array(
            '@type'         => 'Offer',
            'price'         => $price_value,
            'priceCurrency' => 'USD',
            'availability'  => 'https://schema.org/InStock',
        );
    }

    // Add pros and cons
    if ( $pros || $cons ) {
        $review_body = '';
        if ( $pros ) {
            $review_body .= __( 'Pros: ', 'toolshed-tested' ) . implode( ', ', array_filter( explode( "\n", $pros ) ) ) . '. ';
        }
        if ( $cons ) {
            $review_body .= __( 'Cons: ', 'toolshed-tested' ) . implode( ', ', array_filter( explode( "\n", $cons ) ) ) . '.';
        }
        $review_schema['reviewBody'] = trim( $review_body );
    }

    return '<script type="application/ld+json">' . wp_json_encode( $review_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
