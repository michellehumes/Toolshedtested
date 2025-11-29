<?php
/**
 * Affiliate Link Handler
 *
 * @package Toolshed_Tested
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class TST_Affiliate
 *
 * Handles affiliate link functionality
 */
class TST_Affiliate {

    /**
     * Amazon Associate Tag
     *
     * @var string
     */
    private $amazon_tag;

    /**
     * Constructor
     */
    public function __construct() {
        $this->amazon_tag = get_theme_mod( 'tst_amazon_associate_id', '' );

        add_filter( 'the_content', array( $this, 'process_affiliate_links' ) );
        add_action( 'tst_affiliate_click', array( $this, 'log_click' ), 10, 2 );
    }

    /**
     * Process affiliate links in content
     *
     * @param string $content Post content.
     * @return string Modified content.
     */
    public function process_affiliate_links( $content ) {
        // Add nofollow to Amazon links
        $content = preg_replace_callback(
            '/<a([^>]*href=["\']https?:\/\/(www\.)?amazon\.[^"\']+["\'][^>]*)>/i',
            array( $this, 'add_affiliate_attributes' ),
            $content
        );

        return $content;
    }

    /**
     * Add affiliate attributes to links
     *
     * @param array $matches Regex matches.
     * @return string Modified link tag.
     */
    public function add_affiliate_attributes( $matches ) {
        $link = $matches[0];

        // Add rel attributes if not present
        if ( strpos( $link, 'rel=' ) === false ) {
            $link = str_replace( '<a', '<a rel="nofollow noopener sponsored"', $link );
        } else {
            // Ensure sponsored is in the rel attribute
            $link = preg_replace( '/rel=["\']([^"\']*)["\']/', 'rel="$1 sponsored nofollow noopener"', $link );
        }

        // Add target blank if not present
        if ( strpos( $link, 'target=' ) === false ) {
            $link = str_replace( '<a', '<a target="_blank"', $link );
        }

        // Add affiliate link class if not present
        if ( strpos( $link, 'affiliate-link' ) === false ) {
            if ( strpos( $link, 'class=' ) !== false ) {
                $link = preg_replace( '/class=["\']([^"\']*)["\']/', 'class="$1 affiliate-link"', $link );
            } else {
                $link = str_replace( '<a', '<a class="affiliate-link"', $link );
            }
        }

        return $link;
    }

    /**
     * Append Amazon tag to URL
     *
     * @param string $url Amazon URL.
     * @return string URL with tag.
     */
    public function append_amazon_tag( $url ) {
        if ( empty( $this->amazon_tag ) ) {
            return $url;
        }

        // Check if it's an Amazon URL
        if ( strpos( $url, 'amazon.' ) === false ) {
            return $url;
        }

        // Parse URL
        $parsed = wp_parse_url( $url );

        if ( ! $parsed ) {
            return $url;
        }

        // Parse query string
        $query = array();
        if ( isset( $parsed['query'] ) ) {
            parse_str( $parsed['query'], $query );
        }

        // Add tag
        $query['tag'] = $this->amazon_tag;

        // Rebuild URL
        $new_url = $parsed['scheme'] . '://' . $parsed['host'];

        if ( isset( $parsed['path'] ) ) {
            $new_url .= $parsed['path'];
        }

        $new_url .= '?' . http_build_query( $query );

        if ( isset( $parsed['fragment'] ) ) {
            $new_url .= '#' . $parsed['fragment'];
        }

        return $new_url;
    }

    /**
     * Log affiliate click
     *
     * @param int    $product_id Product/Post ID.
     * @param string $affiliate_id Affiliate identifier.
     */
    public function log_click( $product_id, $affiliate_id ) {
        // Get current click count
        $clicks = get_post_meta( $product_id, '_tst_affiliate_clicks', true );
        $clicks = $clicks ? intval( $clicks ) : 0;

        // Increment
        $clicks++;

        // Save
        update_post_meta( $product_id, '_tst_affiliate_clicks', $clicks );

        // Log to transient for rate limiting (optional)
        $log_key    = 'tst_click_log_' . gmdate( 'Y-m-d' );
        $daily_log  = get_transient( $log_key );

        if ( ! is_array( $daily_log ) ) {
            $daily_log = array();
        }

        $daily_log[] = array(
            'product_id'   => $product_id,
            'affiliate_id' => $affiliate_id,
            'timestamp'    => current_time( 'mysql' ),
            'ip'           => $this->get_client_ip(),
        );

        set_transient( $log_key, $daily_log, DAY_IN_SECONDS );
    }

    /**
     * Get client IP address
     *
     * @return string IP address.
     */
    private function get_client_ip() {
        $ip = '';

        if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
        } elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
        } elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }

        // Hash the IP for privacy
        return wp_hash( $ip );
    }

    /**
     * Get click statistics for a product
     *
     * @param int $product_id Product/Post ID.
     * @return array Click statistics.
     */
    public static function get_click_stats( $product_id ) {
        return array(
            'total_clicks' => get_post_meta( $product_id, '_tst_affiliate_clicks', true ) ?: 0,
        );
    }
}

// Initialize
new TST_Affiliate();
