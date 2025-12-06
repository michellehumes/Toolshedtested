<?php
/**
 * Theme Customizer
 *
 * @package Toolshed_Tested
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add theme customizer options
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function tst_customize_register( $wp_customize ) {
    // Site Identity - Add tagline toggle
    $wp_customize->add_setting(
        'tst_show_tagline',
        array(
            'default'           => true,
            'sanitize_callback' => 'tst_sanitize_checkbox',
        )
    );

    $wp_customize->add_control(
        'tst_show_tagline',
        array(
            'type'     => 'checkbox',
            'section'  => 'title_tagline',
            'label'    => esc_html__( 'Display Tagline', 'toolshed-tested' ),
        )
    );

    // Affiliate Settings Section
    $wp_customize->add_section(
        'tst_affiliate_settings',
        array(
            'title'       => esc_html__( 'Affiliate Settings', 'toolshed-tested' ),
            'priority'    => 120,
            'description' => esc_html__( 'Configure your affiliate link settings.', 'toolshed-tested' ),
        )
    );

    // Amazon Associate ID
    $wp_customize->add_setting(
        'tst_amazon_associate_id',
        array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );

    $wp_customize->add_control(
        'tst_amazon_associate_id',
        array(
            'type'        => 'text',
            'section'     => 'tst_affiliate_settings',
            'label'       => esc_html__( 'Amazon Associate ID', 'toolshed-tested' ),
            'description' => esc_html__( 'Enter your Amazon Associates tracking ID.', 'toolshed-tested' ),
        )
    );

    // Affiliate Disclosure Text
    $wp_customize->add_setting(
        'tst_affiliate_disclosure',
        array(
            'default'           => esc_html__( 'As an Amazon Associate, we earn from qualifying purchases. This post may contain affiliate links.', 'toolshed-tested' ),
            'sanitize_callback' => 'wp_kses_post',
        )
    );

    $wp_customize->add_control(
        'tst_affiliate_disclosure',
        array(
            'type'        => 'textarea',
            'section'     => 'tst_affiliate_settings',
            'label'       => esc_html__( 'Affiliate Disclosure', 'toolshed-tested' ),
            'description' => esc_html__( 'This text appears at the top of review posts.', 'toolshed-tested' ),
        )
    );

    // Colors Section - Custom colors
    $wp_customize->add_setting(
        'tst_primary_color',
        array(
            'default'           => '#2d5a27',
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );

    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'tst_primary_color',
            array(
                'label'   => esc_html__( 'Primary Color', 'toolshed-tested' ),
                'section' => 'colors',
            )
        )
    );

    $wp_customize->add_setting(
        'tst_secondary_color',
        array(
            'default'           => '#f4a524',
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );

    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'tst_secondary_color',
            array(
                'label'   => esc_html__( 'Secondary Color', 'toolshed-tested' ),
                'section' => 'colors',
            )
        )
    );

    $wp_customize->add_setting(
        'tst_accent_color',
        array(
            'default'           => '#e63946',
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );

    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'tst_accent_color',
            array(
                'label'   => esc_html__( 'CTA Button Color', 'toolshed-tested' ),
                'section' => 'colors',
            )
        )
    );

    // Social Media Section
    $wp_customize->add_section(
        'tst_social_media',
        array(
            'title'    => esc_html__( 'Social Media', 'toolshed-tested' ),
            'priority' => 130,
        )
    );

    $social_networks = array(
        'facebook'  => 'Facebook',
        'twitter'   => 'Twitter',
        'instagram' => 'Instagram',
        'youtube'   => 'YouTube',
        'pinterest' => 'Pinterest',
    );

    foreach ( $social_networks as $network => $label ) {
        $wp_customize->add_setting(
            'tst_social_' . $network,
            array(
                'default'           => '',
                'sanitize_callback' => 'esc_url_raw',
            )
        );

        $wp_customize->add_control(
            'tst_social_' . $network,
            array(
                'type'    => 'url',
                'section' => 'tst_social_media',
                'label'   => $label . ' ' . esc_html__( 'URL', 'toolshed-tested' ),
            )
        );
    }

    // SEO Settings Section
    $wp_customize->add_section(
        'tst_seo_settings',
        array(
            'title'    => esc_html__( 'SEO Settings', 'toolshed-tested' ),
            'priority' => 140,
        )
    );

    // Google Analytics ID
    $wp_customize->add_setting(
        'tst_google_analytics_id',
        array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );

    $wp_customize->add_control(
        'tst_google_analytics_id',
        array(
            'type'        => 'text',
            'section'     => 'tst_seo_settings',
            'label'       => esc_html__( 'Google Analytics ID', 'toolshed-tested' ),
            'description' => esc_html__( 'Enter your Google Analytics measurement ID (G-XXXXXXXXXX).', 'toolshed-tested' ),
        )
    );

    // Newsletter Section
    $wp_customize->add_section(
        'tst_newsletter',
        array(
            'title'       => esc_html__( 'Newsletter & Email', 'toolshed-tested' ),
            'priority'    => 150,
            'description' => esc_html__( 'Configure your email capture and newsletter settings.', 'toolshed-tested' ),
        )
    );

    // Email Service Provider
    $wp_customize->add_setting(
        'tst_email_provider',
        array(
            'default'           => 'custom',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );

    $wp_customize->add_control(
        'tst_email_provider',
        array(
            'type'        => 'select',
            'section'     => 'tst_newsletter',
            'label'       => esc_html__( 'Email Service Provider', 'toolshed-tested' ),
            'description' => esc_html__( 'Select your email marketing platform.', 'toolshed-tested' ),
            'choices'     => array(
                'custom'      => esc_html__( 'Custom Form Action', 'toolshed-tested' ),
                'mailchimp'   => esc_html__( 'Mailchimp', 'toolshed-tested' ),
                'convertkit'  => esc_html__( 'ConvertKit', 'toolshed-tested' ),
                'beehiiv'     => esc_html__( 'Beehiiv', 'toolshed-tested' ),
                'buttondown'  => esc_html__( 'Buttondown', 'toolshed-tested' ),
            ),
        )
    );

    // Newsletter Form Action URL
    $wp_customize->add_setting(
        'tst_newsletter_action',
        array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        )
    );

    $wp_customize->add_control(
        'tst_newsletter_action',
        array(
            'type'        => 'url',
            'section'     => 'tst_newsletter',
            'label'       => esc_html__( 'Form Action URL', 'toolshed-tested' ),
            'description' => esc_html__( 'Enter the form action URL from your email provider. For Mailchimp, use your list signup URL. For ConvertKit, use your form action URL.', 'toolshed-tested' ),
        )
    );

    // Email Field Name (for different providers)
    $wp_customize->add_setting(
        'tst_email_field_name',
        array(
            'default'           => 'email',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );

    $wp_customize->add_control(
        'tst_email_field_name',
        array(
            'type'        => 'text',
            'section'     => 'tst_newsletter',
            'label'       => esc_html__( 'Email Field Name', 'toolshed-tested' ),
            'description' => esc_html__( 'The name attribute for the email input. Mailchimp uses "EMAIL", ConvertKit uses "email_address".', 'toolshed-tested' ),
        )
    );

    // Popup Settings
    $wp_customize->add_setting(
        'tst_popup_enabled',
        array(
            'default'           => true,
            'sanitize_callback' => 'tst_sanitize_checkbox',
        )
    );

    $wp_customize->add_control(
        'tst_popup_enabled',
        array(
            'type'    => 'checkbox',
            'section' => 'tst_newsletter',
            'label'   => esc_html__( 'Enable Email Popup', 'toolshed-tested' ),
        )
    );

    // Popup Headline
    $wp_customize->add_setting(
        'tst_popup_headline',
        array(
            'default'           => esc_html__( 'Get the Free Tool Buying Checklist', 'toolshed-tested' ),
            'sanitize_callback' => 'sanitize_text_field',
        )
    );

    $wp_customize->add_control(
        'tst_popup_headline',
        array(
            'type'    => 'text',
            'section' => 'tst_newsletter',
            'label'   => esc_html__( 'Popup Headline', 'toolshed-tested' ),
        )
    );

    // Popup Description
    $wp_customize->add_setting(
        'tst_popup_description',
        array(
            'default'           => esc_html__( 'Join 5,000+ DIYers who get our weekly tool deals and buying guides.', 'toolshed-tested' ),
            'sanitize_callback' => 'sanitize_text_field',
        )
    );

    $wp_customize->add_control(
        'tst_popup_description',
        array(
            'type'    => 'text',
            'section' => 'tst_newsletter',
            'label'   => esc_html__( 'Popup Description', 'toolshed-tested' ),
        )
    );

    // Popup Button Text
    $wp_customize->add_setting(
        'tst_popup_button_text',
        array(
            'default'           => esc_html__( 'Get Free Checklist', 'toolshed-tested' ),
            'sanitize_callback' => 'sanitize_text_field',
        )
    );

    $wp_customize->add_control(
        'tst_popup_button_text',
        array(
            'type'    => 'text',
            'section' => 'tst_newsletter',
            'label'   => esc_html__( 'Popup Button Text', 'toolshed-tested' ),
        )
    );

    // Success Redirect URL
    $wp_customize->add_setting(
        'tst_newsletter_success_url',
        array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        )
    );

    $wp_customize->add_control(
        'tst_newsletter_success_url',
        array(
            'type'        => 'url',
            'section'     => 'tst_newsletter',
            'label'       => esc_html__( 'Success Redirect URL', 'toolshed-tested' ),
            'description' => esc_html__( 'Optional: Redirect users to this URL after signup (e.g., a thank you page with your lead magnet).', 'toolshed-tested' ),
        )
    );
}
add_action( 'customize_register', 'tst_customize_register' );

/**
 * Sanitize checkbox
 *
 * @param bool $checked Whether the checkbox is checked.
 * @return bool
 */
function tst_sanitize_checkbox( $checked ) {
    return ( ( isset( $checked ) && true === $checked ) ? true : false );
}

/**
 * Output custom CSS from customizer settings
 */
function tst_customizer_css() {
    $primary   = get_theme_mod( 'tst_primary_color', '#2d5a27' );
    $secondary = get_theme_mod( 'tst_secondary_color', '#f4a524' );
    $accent    = get_theme_mod( 'tst_accent_color', '#e63946' );

    $css = '';

    if ( '#2d5a27' !== $primary ) {
        $css .= '--tst-primary: ' . esc_html( $primary ) . ';';
    }

    if ( '#f4a524' !== $secondary ) {
        $css .= '--tst-secondary: ' . esc_html( $secondary ) . ';';
    }

    if ( '#e63946' !== $accent ) {
        $css .= '--tst-accent: ' . esc_html( $accent ) . ';';
    }

    if ( ! empty( $css ) ) {
        echo '<style type="text/css">:root{' . $css . '}</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
add_action( 'wp_head', 'tst_customizer_css', 100 );

/**
 * Output Google Analytics tracking code
 */
function tst_google_analytics() {
    $ga_id = get_theme_mod( 'tst_google_analytics_id' );

    if ( empty( $ga_id ) ) {
        return;
    }
    ?>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga_id ); ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo esc_js( $ga_id ); ?>');
    </script>
    <?php
}
add_action( 'wp_head', 'tst_google_analytics', 1 );
