<?php
/**
 * Product Review Meta Box and Functions
 *
 * @package Toolshed_Tested
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class TST_Product_Review
 *
 * Handles product review custom post type functionality
 */
class TST_Product_Review {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post_product_review', array( $this, 'save_meta_boxes' ) );
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'tst_product_details',
            __( 'Product Details', 'toolshed-tested' ),
            array( $this, 'render_product_details_meta_box' ),
            'product_review',
            'normal',
            'high'
        );

        add_meta_box(
            'tst_product_ratings',
            __( 'Rating & Review', 'toolshed-tested' ),
            array( $this, 'render_ratings_meta_box' ),
            'product_review',
            'side',
            'high'
        );

        add_meta_box(
            'tst_affiliate_links',
            __( 'Affiliate Links', 'toolshed-tested' ),
            array( $this, 'render_affiliate_meta_box' ),
            'product_review',
            'normal',
            'default'
        );

        add_meta_box(
            'tst_specifications',
            __( 'Product Specifications', 'toolshed-tested' ),
            array( $this, 'render_specifications_meta_box' ),
            'product_review',
            'normal',
            'default'
        );
    }

    /**
     * Render product details meta box
     *
     * @param WP_Post $post Current post object.
     */
    public function render_product_details_meta_box( $post ) {
        wp_nonce_field( 'tst_product_meta', 'tst_product_nonce' );

        $price    = get_post_meta( $post->ID, '_tst_price', true );
        $best_for = get_post_meta( $post->ID, '_tst_best_for', true );
        $badge    = get_post_meta( $post->ID, '_tst_badge', true );

        $badges = array(
            ''               => __( 'None', 'toolshed-tested' ),
            'bestseller'     => __( 'Best Seller', 'toolshed-tested' ),
            'editors-choice' => __( 'Editor\'s Choice', 'toolshed-tested' ),
            'budget-pick'    => __( 'Budget Pick', 'toolshed-tested' ),
            'premium-pick'   => __( 'Premium Pick', 'toolshed-tested' ),
        );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="tst_price"><?php esc_html_e( 'Price', 'toolshed-tested' ); ?></label></th>
                <td>
                    <input type="text" id="tst_price" name="tst_price" value="<?php echo esc_attr( $price ); ?>" class="regular-text" placeholder="$299.99">
                    <p class="description"><?php esc_html_e( 'Enter the current price including currency symbol.', 'toolshed-tested' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="tst_best_for"><?php esc_html_e( 'Best For', 'toolshed-tested' ); ?></label></th>
                <td>
                    <input type="text" id="tst_best_for" name="tst_best_for" value="<?php echo esc_attr( $best_for ); ?>" class="regular-text" placeholder="Large yards, professional use">
                    <p class="description"><?php esc_html_e( 'Who is this product best suited for?', 'toolshed-tested' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="tst_badge"><?php esc_html_e( 'Badge', 'toolshed-tested' ); ?></label></th>
                <td>
                    <select id="tst_badge" name="tst_badge">
                        <?php foreach ( $badges as $value => $label ) : ?>
                            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $badge, $value ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render ratings meta box
     *
     * @param WP_Post $post Current post object.
     */
    public function render_ratings_meta_box( $post ) {
        $rating = get_post_meta( $post->ID, '_tst_rating', true );
        $pros   = get_post_meta( $post->ID, '_tst_pros', true );
        $cons   = get_post_meta( $post->ID, '_tst_cons', true );
        ?>
        <p>
            <label for="tst_rating"><strong><?php esc_html_e( 'Overall Rating', 'toolshed-tested' ); ?></strong></label>
            <input type="number" id="tst_rating" name="tst_rating" value="<?php echo esc_attr( $rating ); ?>" min="0" max="5" step="0.1" style="width: 80px;">
            <span>/5</span>
        </p>

        <p>
            <label for="tst_pros"><strong><?php esc_html_e( 'Pros', 'toolshed-tested' ); ?></strong></label>
            <textarea id="tst_pros" name="tst_pros" rows="5" style="width: 100%;"><?php echo esc_textarea( $pros ); ?></textarea>
            <span class="description"><?php esc_html_e( 'One pro per line', 'toolshed-tested' ); ?></span>
        </p>

        <p>
            <label for="tst_cons"><strong><?php esc_html_e( 'Cons', 'toolshed-tested' ); ?></strong></label>
            <textarea id="tst_cons" name="tst_cons" rows="5" style="width: 100%;"><?php echo esc_textarea( $cons ); ?></textarea>
            <span class="description"><?php esc_html_e( 'One con per line', 'toolshed-tested' ); ?></span>
        </p>
        <?php
    }

    /**
     * Render affiliate links meta box
     *
     * @param WP_Post $post Current post object.
     */
    public function render_affiliate_meta_box( $post ) {
        $affiliate_url    = get_post_meta( $post->ID, '_tst_affiliate_url', true );
        $affiliate_url_2  = get_post_meta( $post->ID, '_tst_affiliate_url_2', true );
        $affiliate_name_2 = get_post_meta( $post->ID, '_tst_affiliate_name_2', true );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="tst_affiliate_url"><?php esc_html_e( 'Amazon Link', 'toolshed-tested' ); ?></label></th>
                <td>
                    <input type="url" id="tst_affiliate_url" name="tst_affiliate_url" value="<?php echo esc_url( $affiliate_url ); ?>" class="large-text">
                    <p class="description"><?php esc_html_e( 'Enter your Amazon affiliate link for this product.', 'toolshed-tested' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="tst_affiliate_name_2"><?php esc_html_e( 'Alternative Retailer', 'toolshed-tested' ); ?></label></th>
                <td>
                    <input type="text" id="tst_affiliate_name_2" name="tst_affiliate_name_2" value="<?php echo esc_attr( $affiliate_name_2 ); ?>" class="regular-text" placeholder="Home Depot">
                </td>
            </tr>
            <tr>
                <th><label for="tst_affiliate_url_2"><?php esc_html_e( 'Alternative Link', 'toolshed-tested' ); ?></label></th>
                <td>
                    <input type="url" id="tst_affiliate_url_2" name="tst_affiliate_url_2" value="<?php echo esc_url( $affiliate_url_2 ); ?>" class="large-text">
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render specifications meta box
     *
     * @param WP_Post $post Current post object.
     */
    public function render_specifications_meta_box( $post ) {
        $specs = get_post_meta( $post->ID, '_tst_specifications', true );
        if ( ! is_array( $specs ) ) {
            $specs = array();
        }
        ?>
        <div id="tst-specs-container">
            <table class="widefat" id="tst-specs-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Specification', 'toolshed-tested' ); ?></th>
                        <th><?php esc_html_e( 'Value', 'toolshed-tested' ); ?></th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ( empty( $specs ) ) {
                        $specs[] = array(
                            'label' => '',
                            'value' => '',
                        );
                    }
                    foreach ( $specs as $index => $spec ) :
                        ?>
                        <tr>
                            <td>
                                <input type="text" name="tst_spec_label[]" value="<?php echo esc_attr( $spec['label'] ?? '' ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g., Engine Power', 'toolshed-tested' ); ?>">
                            </td>
                            <td>
                                <input type="text" name="tst_spec_value[]" value="<?php echo esc_attr( $spec['value'] ?? '' ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g., 190cc', 'toolshed-tested' ); ?>">
                            </td>
                            <td>
                                <button type="button" class="button tst-remove-spec">&times;</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p>
                <button type="button" class="button" id="tst-add-spec"><?php esc_html_e( 'Add Specification', 'toolshed-tested' ); ?></button>
            </p>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#tst-add-spec').on('click', function() {
                var row = '<tr>' +
                    '<td><input type="text" name="tst_spec_label[]" class="widefat" placeholder="<?php esc_attr_e( 'e.g., Engine Power', 'toolshed-tested' ); ?>"></td>' +
                    '<td><input type="text" name="tst_spec_value[]" class="widefat" placeholder="<?php esc_attr_e( 'e.g., 190cc', 'toolshed-tested' ); ?>"></td>' +
                    '<td><button type="button" class="button tst-remove-spec">&times;</button></td>' +
                    '</tr>';
                $('#tst-specs-table tbody').append(row);
            });

            $(document).on('click', '.tst-remove-spec', function() {
                if ($('#tst-specs-table tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Save meta boxes
     *
     * @param int $post_id Post ID.
     */
    public function save_meta_boxes( $post_id ) {
        // Check nonce
        if ( ! isset( $_POST['tst_product_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['tst_product_nonce'] ), 'tst_product_meta' ) ) {
            return;
        }

        // Check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save product details
        $fields = array(
            'tst_price'           => '_tst_price',
            'tst_best_for'        => '_tst_best_for',
            'tst_badge'           => '_tst_badge',
            'tst_rating'          => '_tst_rating',
            'tst_pros'            => '_tst_pros',
            'tst_cons'            => '_tst_cons',
            'tst_affiliate_url'   => '_tst_affiliate_url',
            'tst_affiliate_url_2' => '_tst_affiliate_url_2',
            'tst_affiliate_name_2' => '_tst_affiliate_name_2',
        );

        foreach ( $fields as $field => $meta_key ) {
            if ( isset( $_POST[ $field ] ) ) {
                $value = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );

                // Special handling for URLs
                if ( strpos( $field, 'url' ) !== false ) {
                    $value = esc_url_raw( wp_unslash( $_POST[ $field ] ) );
                }

                // Special handling for rating
                if ( 'tst_rating' === $field ) {
                    $value = floatval( $value );
                    $value = max( 0, min( 5, $value ) );
                }

                update_post_meta( $post_id, $meta_key, $value );
            }
        }

        // Save specifications
        if ( isset( $_POST['tst_spec_label'] ) && isset( $_POST['tst_spec_value'] ) ) {
            $labels = array_map( 'sanitize_text_field', wp_unslash( $_POST['tst_spec_label'] ) );
            $values = array_map( 'sanitize_text_field', wp_unslash( $_POST['tst_spec_value'] ) );

            $specs = array();
            foreach ( $labels as $index => $label ) {
                if ( ! empty( $label ) || ! empty( $values[ $index ] ) ) {
                    $specs[] = array(
                        'label' => $label,
                        'value' => $values[ $index ] ?? '',
                    );
                }
            }

            update_post_meta( $post_id, '_tst_specifications', $specs );
        }
    }
}

// Initialize the class
new TST_Product_Review();
