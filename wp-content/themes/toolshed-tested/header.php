<?php
/**
 * Header Template
 *
 * @package Toolshed_Tested
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'toolshed-tested' ); ?></a>

<!-- Trust Bar -->
<div class="trust-bar">
    <div class="tst-container">
        <span class="trust-item">
            <svg class="trust-icon" viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            <?php esc_html_e( '150+ Tools Tested', 'toolshed-tested' ); ?>
        </span>
        <span class="trust-item">
            <svg class="trust-icon" viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
            <?php esc_html_e( '500+ Hours Research', 'toolshed-tested' ); ?>
        </span>
        <span class="trust-item">
            <svg class="trust-icon" viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg>
            <?php esc_html_e( '100% Independent', 'toolshed-tested' ); ?>
        </span>
    </div>
</div>

<header id="masthead" class="site-header">
    <div class="tst-container">
        <div class="header-inner">
            <div class="site-branding">
                <?php if ( has_custom_logo() ) : ?>
                    <div class="site-logo">
                        <?php the_custom_logo(); ?>
                    </div>
                <?php else : ?>
                    <h1 class="site-title">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                            <?php bloginfo( 'name' ); ?>
                        </a>
                    </h1>
                    <?php
                    $tst_description = get_bloginfo( 'description', 'display' );
                    if ( $tst_description || is_customize_preview() ) :
                        ?>
                        <p class="site-description screen-reader-text"><?php echo esc_html( $tst_description ); ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
                <span class="screen-reader-text"><?php esc_html_e( 'Menu', 'toolshed-tested' ); ?></span>
            </button>

            <nav id="site-navigation" class="main-navigation">
                <?php
                wp_nav_menu(
                    array(
                        'theme_location' => 'primary',
                        'menu_id'        => 'primary-menu',
                        'container'      => false,
                        'fallback_cb'    => 'tst_default_menu',
                    )
                );
                ?>
            </nav>

            <div class="header-search">
                <?php get_search_form(); ?>
            </div>
        </div>
    </div>
</header>

<?php if ( is_singular( 'product_review' ) || is_singular( 'post' ) ) : ?>
    <nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'toolshed-tested' ); ?>">
        <div class="tst-container">
            <?php tst_breadcrumbs(); ?>
        </div>
    </nav>
<?php endif; ?>
