<?php
/**
 * Front Page Template
 *
 * Displays the homepage with hero, top picks, and latest reviews.
 *
 * @package Toolshed_Tested
 */

get_header();
?>

<main id="primary" class="site-main">
	<div class="tst-container">
		<section class="hero-section">
			<h1><?php esc_html_e( 'Hands-On Power Tool Reviews You Can Trust', 'toolshed-tested' ); ?></h1>
			<p><?php esc_html_e( 'Independent testing and straight talk on drills, saws, grinders, sanders, and more so you can buy with confidence.', 'toolshed-tested' ); ?></p>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'product_review' ) ); ?>" class="tst-btn tst-btn-cta">
				<?php esc_html_e( 'Browse Reviews', 'toolshed-tested' ); ?>
			</a>

			<?php
			$hero_terms = get_terms(
				array(
					'taxonomy'   => 'product_category',
					'hide_empty' => true,
					'number'     => 6,
				)
			);
			if ( ! empty( $hero_terms ) && ! is_wp_error( $hero_terms ) ) :
				?>
				<div class="hero-categories">
					<span class="hero-categories-label"><?php esc_html_e( 'Popular categories:', 'toolshed-tested' ); ?></span>
					<div class="hero-categories-list">
						<?php foreach ( $hero_terms as $term ) : ?>
							<a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</section>

		<?php
		// Top Picks Section - pulls highest-rated product_review posts with affiliate links
		$top_picks = get_posts(
			array(
				'post_type'      => 'product_review',
				'posts_per_page' => 3,
				'orderby'        => 'meta_value_num',
				'meta_key'       => '_tst_rating',
				'order'          => 'DESC',
				'meta_query'     => array(
					array(
						'key'     => '_tst_affiliate_url',
						'value'   => '',
						'compare' => '!=',
					),
				),
			)
		);

		// Fallback: if no rated posts with affiliate URLs, get latest product reviews
		if ( empty( $top_picks ) ) {
			$top_picks = get_posts(
				array(
					'post_type'      => 'product_review',
					'posts_per_page' => 3,
					'orderby'        => 'date',
					'order'          => 'DESC',
				)
			);
		}

		if ( ! empty( $top_picks ) ) :
			$badges = array( 'Best Overall', 'Best Value', 'Budget Pick' );
			?>
			<section class="homepage-top-picks">
				<div class="section-header">
					<h2><?php esc_html_e( '#1 Picks by Category', 'toolshed-tested' ); ?></h2>
					<p><?php esc_html_e( 'Our top-rated tools, tested and reviewed by our team.', 'toolshed-tested' ); ?></p>
				</div>

				<div class="top-picks-grid">
					<?php foreach ( $top_picks as $index => $pick ) :
						$rating        = floatval( get_post_meta( $pick->ID, '_tst_rating', true ) );
						$price         = get_post_meta( $pick->ID, '_tst_price', true );
						$affiliate_url = get_post_meta( $pick->ID, '_tst_affiliate_url', true );
						$best_for      = get_post_meta( $pick->ID, '_tst_best_for', true );
						$badge_text    = get_post_meta( $pick->ID, '_tst_badge', true );
						$excerpt       = has_excerpt( $pick ) ? get_the_excerpt( $pick ) : wp_trim_words( $pick->post_content, 15 );

						// Use custom badge or fallback to positional badge
						if ( empty( $badge_text ) && isset( $badges[ $index ] ) ) {
							$badge_text = $badges[ $index ];
						}

						$has_affiliate = ! empty( $affiliate_url );
						$product_url   = $has_affiliate ? tst_get_affiliate_url( $affiliate_url ) : get_permalink( $pick );
						?>
						<div class="top-pick-card">
							<?php if ( ! empty( $badge_text ) ) : ?>
								<span class="badge badge-<?php echo esc_attr( sanitize_title( $badge_text ) ); ?>">
									<?php echo esc_html( ucwords( str_replace( '-', ' ', $badge_text ) ) ); ?>
								</span>
							<?php endif; ?>

							<?php if ( has_post_thumbnail( $pick ) ) : ?>
								<div class="top-pick-image">
									<a href="<?php echo esc_url( get_permalink( $pick ) ); ?>">
										<?php echo get_the_post_thumbnail( $pick, 'tst-product-thumb' ); ?>
									</a>
								</div>
							<?php endif; ?>

							<div class="top-pick-content">
								<h3 class="top-pick-title">
									<a href="<?php echo esc_url( get_permalink( $pick ) ); ?>">
										<?php echo esc_html( get_the_title( $pick ) ); ?>
									</a>
								</h3>

								<?php if ( $rating > 0 ) : ?>
									<div class="top-pick-rating">
										<?php
										$full_stars  = floor( $rating );
										$half_star   = ( $rating - $full_stars ) >= 0.5;
										$empty_stars = 5 - $full_stars - ( $half_star ? 1 : 0 );

										for ( $i = 0; $i < $full_stars; $i++ ) {
											echo '<span class="star full">&#9733;</span>';
										}
										if ( $half_star ) {
											echo '<span class="star half">&#9733;</span>';
										}
										for ( $i = 0; $i < $empty_stars; $i++ ) {
											echo '<span class="star empty">&#9734;</span>';
										}
										?>
										<span class="rating-number"><?php echo esc_html( $rating ); ?>/5</span>
									</div>
								<?php endif; ?>

								<?php if ( $best_for ) : ?>
									<p class="top-pick-best-for">
										<strong><?php esc_html_e( 'Best for:', 'toolshed-tested' ); ?></strong>
										<?php echo esc_html( $best_for ); ?>
									</p>
								<?php elseif ( $excerpt ) : ?>
									<p class="top-pick-excerpt"><?php echo esc_html( $excerpt ); ?></p>
								<?php endif; ?>

								<?php if ( $price ) : ?>
									<span class="top-pick-price"><?php echo esc_html( $price ); ?></span>
								<?php endif; ?>

								<div class="top-pick-actions">
									<?php if ( $has_affiliate ) : ?>
										<a href="<?php echo esc_url( $product_url ); ?>"
										   class="tst-btn tst-btn-amazon affiliate-link"
										   target="_blank"
										   rel="nofollow noopener sponsored">
											<?php esc_html_e( 'Check Price', 'toolshed-tested' ); ?>
										</a>
									<?php endif; ?>
									<a href="<?php echo esc_url( get_permalink( $pick ) ); ?>" class="tst-btn tst-btn-secondary">
										<?php esc_html_e( 'Read Review', 'toolshed-tested' ); ?>
									</a>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php
		// Latest Reviews Section
		$latest_reviews = get_posts(
			array(
				'post_type'      => 'product_review',
				'posts_per_page' => 6,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( ! empty( $latest_reviews ) ) :
			?>
			<section class="homepage-latest-reviews">
				<div class="section-header">
					<h2><?php esc_html_e( 'Latest Reviews', 'toolshed-tested' ); ?></h2>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'product_review' ) ); ?>" class="section-link">
						<?php esc_html_e( 'View All Reviews →', 'toolshed-tested' ); ?>
					</a>
				</div>

				<div class="reviews-grid">
					<?php
					foreach ( $latest_reviews as $review ) :
						$GLOBALS['post'] = $review;
						setup_postdata( $review );
						get_template_part( 'template-parts/review/review-card' );
					endforeach;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>

		<?php
		// Recent Blog Posts
		$blog_posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 4,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( ! empty( $blog_posts ) ) :
			?>
			<section class="homepage-blog-posts">
				<div class="section-header">
					<h2><?php esc_html_e( 'Guides & Tips', 'toolshed-tested' ); ?></h2>
				</div>

				<div class="posts-grid">
					<?php
					foreach ( $blog_posts as $blog_post ) :
						$GLOBALS['post'] = $blog_post;
						setup_postdata( $blog_post );
						get_template_part( 'template-parts/content/content', 'post' );
					endforeach;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
