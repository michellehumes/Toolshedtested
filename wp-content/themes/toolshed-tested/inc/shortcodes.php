<?php
/**
 * Theme Shortcodes
 *
 * @package Toolshed_Tested
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Product Box Shortcode
 *
 * [product_box id="123"]
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output.
 */
function tst_product_box_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'id' => 0,
        ),
        $atts,
        'product_box'
    );

    $post_id = absint( $atts['id'] );

    if ( ! $post_id ) {
        return '';
    }

    $post = get_post( $post_id );

    if ( ! $post || 'product_review' !== $post->post_type ) {
        return '';
    }

    $rating       = get_post_meta( $post_id, '_tst_rating', true );
    $price        = get_post_meta( $post_id, '_tst_price', true );
    $affiliate_url = tst_get_affiliate_url( get_post_meta( $post_id, '_tst_affiliate_url', true ) );
    $pros         = get_post_meta( $post_id, '_tst_pros', true );
    $cons         = get_post_meta( $post_id, '_tst_cons', true );

    ob_start();
    ?>
    <div class="review-box shortcode-box">
        <div class="review-box-header">
            <?php if ( has_post_thumbnail( $post_id ) ) : ?>
                <div class="review-box-image">
                    <?php echo get_the_post_thumbnail( $post_id, 'tst-product-thumb' ); ?>
                </div>
            <?php endif; ?>

            <div class="review-box-info">
                <h3 class="review-box-title">
                    <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
                        <?php echo esc_html( get_the_title( $post_id ) ); ?>
                    </a>
                </h3>

                <?php if ( $rating ) : ?>
                    <div class="review-box-rating">
                        <?php echo wp_kses_post( tst_star_rating( $rating ) ); ?>
                        <span class="rating-score"><?php echo esc_html( $rating ); ?>/5</span>
                    </div>
                <?php endif; ?>

                <?php if ( $price ) : ?>
                    <div class="review-box-price"><?php echo esc_html( $price ); ?></div>
                <?php endif; ?>

                <div class="review-box-cta">
                    <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="tst-btn tst-btn-primary">
                        <?php esc_html_e( 'Read Review', 'toolshed-tested' ); ?>
                    </a>
                    <?php if ( $affiliate_url ) : ?>
                        <a href="<?php echo esc_url( $affiliate_url ); ?>" 
                           class="tst-btn tst-btn-amazon affiliate-link" 
                           target="_blank" 
                           rel="nofollow noopener sponsored">
                            <?php esc_html_e( 'Check Price', 'toolshed-tested' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ( $pros || $cons ) : ?>
            <div class="pros-cons">
                <?php if ( $pros ) : ?>
                    <div class="pros-list">
                        <h4><?php esc_html_e( 'Pros', 'toolshed-tested' ); ?></h4>
                        <ul>
                            <?php
                            $pros_array = array_filter( explode( "\n", $pros ) );
                            foreach ( $pros_array as $pro ) :
                                $pro = trim( $pro );
                                if ( ! empty( $pro ) ) :
                                    ?>
                                    <li><?php echo esc_html( $pro ); ?></li>
                                    <?php
                                endif;
                            endforeach;
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ( $cons ) : ?>
                    <div class="cons-list">
                        <h4><?php esc_html_e( 'Cons', 'toolshed-tested' ); ?></h4>
                        <ul>
                            <?php
                            $cons_array = array_filter( explode( "\n", $cons ) );
                            foreach ( $cons_array as $con ) :
                                $con = trim( $con );
                                if ( ! empty( $con ) ) :
                                    ?>
                                    <li><?php echo esc_html( $con ); ?></li>
                                    <?php
                                endif;
                            endforeach;
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'product_box', 'tst_product_box_shortcode' );

/**
 * Comparison Table Shortcode
 *
 * [comparison_table ids="123,456,789"]
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output.
 */
function tst_comparison_table_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'ids' => '',
        ),
        $atts,
        'comparison_table'
    );

    if ( empty( $atts['ids'] ) ) {
        return '';
    }

    $ids = array_map( 'absint', explode( ',', $atts['ids'] ) );
    $ids = array_filter( $ids );

    if ( empty( $ids ) ) {
        return '';
    }

    ob_start();
    get_template_part(
        'template-parts/product/comparison-table',
        null,
        array( 'products' => $ids )
    );
    return ob_get_clean();
}
add_shortcode( 'comparison_table', 'tst_comparison_table_shortcode' );

/**
 * Star Rating Shortcode
 *
 * [star_rating rating="4.5"]
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output.
 */
function tst_star_rating_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'rating' => 0,
        ),
        $atts,
        'star_rating'
    );

    $rating = floatval( $atts['rating'] );

    if ( $rating <= 0 || $rating > 5 ) {
        return '';
    }

    return tst_star_rating( $rating );
}
add_shortcode( 'star_rating', 'tst_star_rating_shortcode' );

/**
 * Affiliate Button Shortcode
 *
 * [affiliate_button url="https://..." text="Buy on Amazon"]
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output.
 */
function tst_affiliate_button_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'url'   => '',
            'text'  => __( 'Check Price on Amazon', 'toolshed-tested' ),
            'style' => 'amazon',
        ),
        $atts,
        'affiliate_button'
    );

    if ( empty( $atts['url'] ) ) {
        return '';
    }

    $class = 'tst-btn';

    switch ( $atts['style'] ) {
        case 'amazon':
            $class .= ' tst-btn-amazon';
            break;
        case 'cta':
            $class .= ' tst-btn-cta';
            break;
        case 'primary':
            $class .= ' tst-btn-primary';
            break;
        case 'secondary':
            $class .= ' tst-btn-secondary';
            break;
    }

    return sprintf(
        '<a href="%s" class="%s affiliate-link" target="_blank" rel="nofollow noopener sponsored">%s</a>',
        esc_url( tst_get_affiliate_url( $atts['url'] ) ),
        esc_attr( $class ),
        esc_html( $atts['text'] )
    );
}
add_shortcode( 'affiliate_button', 'tst_affiliate_button_shortcode' );

/**
 * Disclosure Box Shortcode
 *
 * [disclosure]
 *
 * @return string HTML output.
 */
function tst_disclosure_shortcode() {
    $text = get_theme_mod(
        'tst_affiliate_disclosure',
        __( 'As an Amazon Associate, we earn from qualifying purchases. This post may contain affiliate links.', 'toolshed-tested' )
    );

    return '<div class="affiliate-disclosure">' . wp_kses_post( $text ) . '</div>';
}
add_shortcode( 'disclosure', 'tst_disclosure_shortcode' );

/**
 * Pros Cons Shortcode
 *
 * [pros_cons pros="Pro 1|Pro 2|Pro 3" cons="Con 1|Con 2"]
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output.
 */
function tst_pros_cons_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'pros' => '',
            'cons' => '',
        ),
        $atts,
        'pros_cons'
    );

    if ( empty( $atts['pros'] ) && empty( $atts['cons'] ) ) {
        return '';
    }

    $pros_array = array_filter( explode( '|', $atts['pros'] ) );
    $cons_array = array_filter( explode( '|', $atts['cons'] ) );

    ob_start();
    ?>
    <div class="pros-cons">
        <?php if ( ! empty( $pros_array ) ) : ?>
            <div class="pros-list">
                <h4><?php esc_html_e( 'Pros', 'toolshed-tested' ); ?></h4>
                <ul>
                    <?php foreach ( $pros_array as $pro ) : ?>
                        <li><?php echo esc_html( trim( $pro ) ); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $cons_array ) ) : ?>
            <div class="cons-list">
                <h4><?php esc_html_e( 'Cons', 'toolshed-tested' ); ?></h4>
                <ul>
                    <?php foreach ( $cons_array as $con ) : ?>
                        <li><?php echo esc_html( trim( $con ) ); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'pros_cons', 'tst_pros_cons_shortcode' );

/**
 * Newsletter Box Shortcode
 *
 * [newsletter title="Subscribe" description="Get the latest reviews"]
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output.
 */
function tst_newsletter_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'title'       => __( 'Subscribe to Our Newsletter', 'toolshed-tested' ),
            'description' => __( 'Get the latest product reviews and buying guides delivered to your inbox.', 'toolshed-tested' ),
        ),
        $atts,
        'newsletter'
    );

    $action = get_theme_mod( 'tst_newsletter_action', '' );

    ob_start();
    ?>
    <div class="newsletter-box">
        <h3><?php echo esc_html( $atts['title'] ); ?></h3>
        <p><?php echo esc_html( $atts['description'] ); ?></p>
        <form class="newsletter-form" action="<?php echo esc_url( $action ); ?>" method="post">
            <input type="email" name="email" placeholder="<?php esc_attr_e( 'Enter your email', 'toolshed-tested' ); ?>" required>
            <button type="submit"><?php esc_html_e( 'Subscribe', 'toolshed-tested' ); ?></button>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'newsletter', 'tst_newsletter_shortcode' );
