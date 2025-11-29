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
