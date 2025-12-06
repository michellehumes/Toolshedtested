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
        add_action( 'wp_head', array( $this, 'output_faq_schema' ) );
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

    /**
     * Output FAQ Page Schema
     *
     * Adds FAQPage structured data to FAQ pages for rich snippets
     */
    public function output_faq_schema() {
        // Check if this is the FAQ page
        if ( ! is_page( 'faq' ) && ! is_page( 'frequently-asked-questions' ) ) {
            return;
        }

        // Define FAQ items - these match the actual FAQ page content
        $faq_items = array(
            // About Our Reviews
            array(
                'question' => 'How do you test the tools?',
                'answer'   => 'We purchase all tools at full retail price with our own money and test them in real workshop conditions. Each tool undergoes 20+ hours of hands-on testing, including drilling through various materials (pine, oak, steel), extended runtime tests, and durability assessments over 6-12 months.',
            ),
            array(
                'question' => 'Do you accept free products from manufacturers?',
                'answer'   => 'No. We buy every tool we test at full retail price. This ensures our reviews remain completely independent and unbiased. We never accept manufacturer samples or sponsored products.',
            ),
            array(
                'question' => 'How do you make money?',
                'answer'   => 'We earn affiliate commissions when you purchase through our links, primarily through Amazon Associates and other retailers like Home Depot and Lowe\'s. This doesn\'t affect the price you pay or our recommendations - we recommend the best tools regardless of commission rates.',
            ),
            array(
                'question' => 'How often do you update reviews?',
                'answer'   => 'We update our reviews whenever new models are released or when we discover significant changes in product quality, pricing, or availability. Major roundup articles are refreshed at least annually, and we conduct long-term follow-up testing at 6-12 months.',
            ),
            // Buying Advice
            array(
                'question' => 'What\'s the difference between brushless and brushed motors?',
                'answer'   => 'Brushless motors are more efficient, generate less heat, last longer, and deliver more power than brushed motors. They cost more upfront but provide better value for frequent users due to longer lifespan and better performance. Brushed motors are fine for occasional DIY use and light-duty tasks.',
            ),
            array(
                'question' => 'Should I stick with one battery platform?',
                'answer'   => 'Yes, sticking with one battery platform (like DeWalt 20V MAX, Milwaukee M18, or Makita 18V LXT) saves money and reduces clutter. You can share batteries across all tools in the system. Consider the tool selection, battery availability, and your specific needs when choosing a platform.',
            ),
            array(
                'question' => 'How do I choose the right tool for my needs?',
                'answer'   => 'Consider your primary use case (DIY vs professional), frequency of use, budget, and whether you\'re already invested in a battery platform. Our buying guides break down recommendations by user type: Budget Picks for occasional users, Best Value for regular DIYers, and Professional Grade for daily use.',
            ),
            // Site Questions
            array(
                'question' => 'Can I suggest a product for review?',
                'answer'   => 'Absolutely! We welcome suggestions from our readers. Send your product review requests to hello@toolshedtested.com. While we can\'t review everything suggested, we prioritize requests based on reader interest and market relevance.',
            ),
            array(
                'question' => 'How can I support ToolShed Tested?',
                'answer'   => 'The best way to support us is by using our affiliate links when making purchases - it costs you nothing extra but helps fund our testing. You can also subscribe to our newsletter, share our reviews with friends, and follow us on social media.',
            ),
            array(
                'question' => 'Do you have a warranty or return policy?',
                'answer'   => 'We don\'t sell products directly - we provide reviews and recommendations. For warranty and return questions, please contact the retailer where you made your purchase (Amazon, Home Depot, Lowe\'s, etc.) or the tool manufacturer directly.',
            ),
        );

        // Build the schema
        $main_entity = array();
        foreach ( $faq_items as $item ) {
            $main_entity[] = array(
                '@type'          => 'Question',
                'name'           => $item['question'],
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text'  => $item['answer'],
                ),
            );
        }

        $schema = array(
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $main_entity,
        );

        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
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
