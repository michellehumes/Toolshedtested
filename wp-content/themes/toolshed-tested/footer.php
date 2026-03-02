<?php
/**
 * Footer Template
 *
 * @package Toolshed_Tested
 */

?>

<footer id="colophon" class="site-footer">
    <div class="tst-container">
        <div class="footer-widgets">
            <div class="footer-widget-area">
                <h4 class="footer-widget-title"><?php esc_html_e( 'About Us', 'toolshed-tested' ); ?></h4>
                <p><?php esc_html_e( 'Toolshed Tested provides expert, hands-on reviews of power tools and equipment to help you make informed purchasing decisions.', 'toolshed-tested' ); ?></p>
            </div>

            <?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
                <div class="footer-widget-area">
                    <?php dynamic_sidebar( 'footer-2' ); ?>
                </div>
            <?php else : ?>
                <div class="footer-widget-area">
                    <h4 class="footer-widget-title"><?php esc_html_e( 'Categories', 'toolshed-tested' ); ?></h4>
                    <ul>
                        <?php
                        $footer_categories = get_terms(
                            array(
                                'taxonomy'   => 'category',
                                'hide_empty' => false,
                                'number'     => 6,
                                'orderby'    => 'count',
                                'order'      => 'DESC',
                            )
                        );
                        if ( $footer_categories && ! is_wp_error( $footer_categories ) ) :
                            foreach ( $footer_categories as $category ) :
                                ?>
                                <li><a href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a></li>
                                <?php
                            endforeach;
                        else :
                            // Fallback if no categories exist yet
                            ?>
                            <li><a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'All Reviews', 'toolshed-tested' ); ?></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
                <div class="footer-widget-area">
                    <?php dynamic_sidebar( 'footer-3' ); ?>
                </div>
            <?php else : ?>
                <div class="footer-widget-area">
                    <h4 class="footer-widget-title"><?php esc_html_e( 'Quick Links', 'toolshed-tested' ); ?></h4>
                    <?php
                    wp_nav_menu(
                        array(
                            'theme_location' => 'footer',
                            'container'      => false,
                            'fallback_cb'    => 'tst_default_footer_menu',
                        )
                    );
                    ?>
                </div>
            <?php endif; ?>

            <?php if ( is_active_sidebar( 'footer-4' ) ) : ?>
                <div class="footer-widget-area">
                    <?php dynamic_sidebar( 'footer-4' ); ?>
                </div>
            <?php else : ?>
                <div class="footer-widget-area">
                    <h4 class="footer-widget-title"><?php esc_html_e( 'Newsletter', 'toolshed-tested' ); ?></h4>
                    <p><?php esc_html_e( 'Subscribe to get the latest reviews and deals.', 'toolshed-tested' ); ?></p>
                    <form class="newsletter-form" action="#" method="post">
                        <input type="email" name="email" placeholder="<?php esc_attr_e( 'Your email', 'toolshed-tested' ); ?>" required>
                        <button type="submit"><?php esc_html_e( 'Subscribe', 'toolshed-tested' ); ?></button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer-bottom">
            <p>
                &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. 
                <?php esc_html_e( 'All rights reserved.', 'toolshed-tested' ); ?>
            </p>
            <p class="affiliate-disclosure-footer">
                <?php esc_html_e( 'As an Amazon Associate we earn from qualifying purchases.', 'toolshed-tested' ); ?>
            </p>
            <div class="social-links">
                <?php
                $social = array(
                    'facebook'  => array(
                        'label' => 'Facebook',
                        'icon'  => '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>',
                    ),
                    'twitter'   => array(
                        'label' => 'Twitter',
                        'icon'  => '<path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>',
                    ),
                    'youtube'   => array(
                        'label' => 'YouTube',
                        'icon'  => '<path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>',
                    ),
                    'instagram' => array(
                        'label' => 'Instagram',
                        'icon'  => '<path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.757-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>',
                    ),
                );
                foreach ( $social as $network => $data ) :
                    $url = get_theme_mod( 'tst_social_' . $network, '' );
                    if ( ! $url ) {
                        continue;
                    }
                    ?>
                    <a href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( $data['label'] ); ?>" target="_blank" rel="noopener">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><?php echo wp_kses( $data['icon'], array( 'path' => array( 'd' => array() ) ) ); ?></svg>
                    </a>
                <?php endforeach; ?>
        </div>
    </div>
</footer>

<?php
// Mobile Sticky CTA for product pages
$affiliate_url = tst_get_primary_affiliate_url();
if ( $affiliate_url ) :
    $price = get_post_meta( get_the_ID(), '_tst_price', true );
?>
<div class="mobile-sticky-cta">
    <a href="<?php echo esc_url( $affiliate_url ); ?>" class="affiliate-link" target="_blank" rel="nofollow noopener sponsored">
        <span class="sticky-cta-text"><?php esc_html_e( 'Check Best Price on Amazon', 'toolshed-tested' ); ?></span>
        <?php if ( $price ) : ?>
            <span class="sticky-cta-price"><?php echo esc_html( $price ); ?></span>
        <?php endif; ?>
    </a>
</div>
<?php endif; ?>

<!-- Email Capture Popup -->
<?php if ( get_theme_mod( 'tst_popup_enabled', true ) ) :
    $popup_headline    = get_theme_mod( 'tst_popup_headline', __( 'Get the Free Tool Buying Checklist', 'toolshed-tested' ) );
    $popup_description = get_theme_mod( 'tst_popup_description', __( 'Join 5,000+ DIYers who get our weekly tool deals and buying guides.', 'toolshed-tested' ) );
    $popup_button      = get_theme_mod( 'tst_popup_button_text', __( 'Get Free Checklist', 'toolshed-tested' ) );
    $email_field_name  = get_theme_mod( 'tst_email_field_name', 'email' );
    $form_action       = get_theme_mod( 'tst_newsletter_action', '#' );
    $success_url       = get_theme_mod( 'tst_newsletter_success_url', '' );
?>
<div id="email-popup" class="email-popup" data-success-url="<?php echo esc_url( $success_url ); ?>">
    <div class="popup-overlay"></div>
    <div class="popup-content">
        <button class="popup-close" aria-label="<?php esc_attr_e( 'Close', 'toolshed-tested' ); ?>">&times;</button>
        <div class="popup-icon">&#128295;</div>
        <h3><?php echo esc_html( $popup_headline ); ?></h3>
        <p><?php echo esc_html( $popup_description ); ?></p>
        <form class="popup-form" action="<?php echo esc_url( $form_action ); ?>" method="post">
            <input type="email" name="<?php echo esc_attr( $email_field_name ); ?>" placeholder="<?php esc_attr_e( 'Enter your email', 'toolshed-tested' ); ?>" required>
            <button type="submit" class="tst-btn tst-btn-primary"><?php echo esc_html( $popup_button ); ?></button>
        </form>
        <p class="popup-note"><?php esc_html_e( 'No spam. Unsubscribe anytime.', 'toolshed-tested' ); ?></p>
    </div>
</div>
<?php endif; ?>

<?php wp_footer(); ?>

</body>
</html>
