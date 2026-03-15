<?php
/**
 * Front Page Template
 *
 * Displays the homepage with hero, trust bar, category showcase,
 * top picks, and latest reviews.
 *
 * @package Toolshed_Tested
 */

get_header();
?>

<main id="primary" class="site-main homepage">
	<!-- Hero Section -->
	<section class="hero-section">
		<div class="hero-overlay"></div>
		<div class="tst-container hero-inner">
			<span class="hero-eyebrow"><?php esc_html_e( 'Independent. Hands-On. No BS.', 'toolshed-tested' ); ?></span>
			<h1><?php esc_html_e( 'Power Tool Reviews Built on Real Testing', 'toolshed-tested' ); ?></h1>
			<p><?php esc_html_e( 'We tear down, test, and compare drills, saws, grinders, and more — so you buy the right tool the first time.', 'toolshed-tested' ); ?></p>
			<div class="hero-ctas">
				<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) ); ?>" class="tst-btn tst-btn-cta hero-btn-primary">
					<?php esc_html_e( 'Browse All Reviews', 'toolshed-tested' ); ?>
				</a>
				<a href="#top-picks" class="tst-btn tst-btn-outline hero-btn-secondary">
					<?php esc_html_e( 'See Top Picks', 'toolshed-tested' ); ?>
				</a>
			</div>
		</div>
	</section>

	<!-- Trust Bar -->
	<section class="trust-bar-home">
		<div class="tst-container">
			<div class="trust-stats">
				<div class="trust-stat">
					<span class="trust-stat-icon">
						<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
					</span>
					<div class="trust-stat-text">
						<strong><?php esc_html_e( '80+', 'toolshed-tested' ); ?></strong>
						<span><?php esc_html_e( 'Tools Reviewed', 'toolshed-tested' ); ?></span>
					</div>
				</div>
				<div class="trust-stat">
					<span class="trust-stat-icon">
						<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
					</span>
					<div class="trust-stat-text">
						<strong><?php esc_html_e( '200+', 'toolshed-tested' ); ?></strong>
						<span><?php esc_html_e( 'Hours Tested', 'toolshed-tested' ); ?></span>
					</div>
				</div>
				<div class="trust-stat">
					<span class="trust-stat-icon">
						<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
					</span>
					<div class="trust-stat-text">
						<strong><?php esc_html_e( '100%', 'toolshed-tested' ); ?></strong>
						<span><?php esc_html_e( 'Independent', 'toolshed-tested' ); ?></span>
					</div>
				</div>
				<div class="trust-stat">
					<span class="trust-stat-icon">
						<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
					</span>
					<div class="trust-stat-text">
						<strong><?php esc_html_e( 'Expert', 'toolshed-tested' ); ?></strong>
						<span><?php esc_html_e( 'Tested & Rated', 'toolshed-tested' ); ?></span>
					</div>
				</div>
			</div>
		</div>
	</section>

	<div class="tst-container">
		<!-- Category Showcase -->
		<?php
		$showcase_categories = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => true,
				'number'     => 8,
				'orderby'    => 'count',
				'order'      => 'DESC',
			)
		);

		// Tool category icons (SVG paths mapped to common category slugs)
		$category_icons = array(
			'drills'        => '<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>',
			'saws'          => '<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>',
			'grinders'      => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
			'sanders'       => '<rect x="2" y="7" width="20" height="15" rx="2" ry="2"/><path d="M17 2l-5 5-5-5"/>',
			'outdoor-tools' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
			'power-tools'   => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
			'hand-tools'    => '<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>',
			'workshop'      => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
			'buying-guides' => '<path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/>',
			'default'       => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
		);

		if ( ! empty( $showcase_categories ) && ! is_wp_error( $showcase_categories ) ) :
			?>
			<section class="category-showcase">
				<div class="section-header-pro">
					<h2><?php esc_html_e( 'Shop by Category', 'toolshed-tested' ); ?></h2>
					<div class="section-header-line"></div>
				</div>
				<div class="category-grid">
					<?php foreach ( $showcase_categories as $cat ) :
						$slug = $cat->slug;
						$icon_svg = isset( $category_icons[ $slug ] ) ? $category_icons[ $slug ] : $category_icons['default'];
						?>
						<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="category-card">
							<div class="category-card-icon">
								<svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
									<?php echo wp_kses( $icon_svg, array(
										'path'     => array( 'd' => array() ),
										'circle'   => array( 'cx' => array(), 'cy' => array(), 'r' => array() ),
										'rect'     => array( 'x' => array(), 'y' => array(), 'width' => array(), 'height' => array(), 'rx' => array(), 'ry' => array() ),
										'polygon'  => array( 'points' => array() ),
										'polyline' => array( 'points' => array() ),
									) ); ?>
								</svg>
							</div>
							<span class="category-card-name"><?php echo esc_html( $cat->name ); ?></span>
							<span class="category-card-count"><?php echo esc_html( $cat->count ); ?> <?php esc_html_e( 'reviews', 'toolshed-tested' ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php
		// Top Picks Section
		$top_picks = get_posts(
			array(
				'post_type'      => 'post',
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

		if ( empty( $top_picks ) ) {
			$top_picks = get_posts(
				array(
					'post_type'      => 'post',
					'posts_per_page' => 3,
					'orderby'        => 'meta_value_num',
					'meta_key'       => '_tst_rating',
					'order'          => 'DESC',
				)
			);
		}

		if ( empty( $top_picks ) ) {
			$guides_cat   = get_term_by( 'slug', 'guides', 'category' );
			$exclude_cats = $guides_cat ? array( $guides_cat->term_id ) : array();
			$top_picks    = get_posts(
				array(
					'post_type'        => 'post',
					'posts_per_page'   => 3,
					'orderby'          => 'date',
					'order'            => 'DESC',
					'category__not_in' => $exclude_cats,
				)
			);
		}

		$top_pick_ids = ! empty( $top_picks ) ? wp_list_pluck( $top_picks, 'ID' ) : array();

		if ( ! empty( $top_picks ) ) :
			$rank_labels = array( '1', '2', '3' );
			?>
			<section id="top-picks" class="homepage-top-picks">
				<div class="section-header-pro">
					<h2><?php esc_html_e( 'Editor\'s Top Picks', 'toolshed-tested' ); ?></h2>
					<div class="section-header-line"></div>
					<p class="section-subtitle"><?php esc_html_e( 'Our highest-rated tools, tested head-to-head in our workshop.', 'toolshed-tested' ); ?></p>
				</div>

				<div class="top-picks-grid">
					<?php foreach ( $top_picks as $index => $pick ) :
						$rating        = floatval( get_post_meta( $pick->ID, '_tst_rating', true ) );
						$price         = get_post_meta( $pick->ID, '_tst_price', true );
						$affiliate_url = get_post_meta( $pick->ID, '_tst_affiliate_url', true );
						$best_for      = get_post_meta( $pick->ID, '_tst_best_for', true );
						$badge_text    = get_post_meta( $pick->ID, '_tst_badge', true );
						$excerpt       = has_excerpt( $pick ) ? get_the_excerpt( $pick ) : wp_trim_words( $pick->post_content, 15 );

						if ( empty( $badge_text ) ) {
							$default_badges = array( 'Best Overall', 'Runner Up', 'Budget Pick' );
							$badge_text     = isset( $default_badges[ $index ] ) ? $default_badges[ $index ] : '';
						}

						$has_affiliate = ! empty( $affiliate_url );
						$product_url   = $has_affiliate ? tst_get_affiliate_url( $affiliate_url ) : get_permalink( $pick );
						?>
						<div class="top-pick-card<?php echo 0 === $index ? ' top-pick-featured' : ''; ?>">
							<div class="top-pick-rank"><?php echo esc_html( $rank_labels[ $index ] ); ?></div>

							<?php if ( ! empty( $badge_text ) ) : ?>
								<span class="top-pick-badge badge-<?php echo esc_attr( sanitize_title( $badge_text ) ); ?>">
									<?php echo esc_html( ucwords( str_replace( '-', ' ', $badge_text ) ) ); ?>
								</span>
							<?php endif; ?>

							<?php if ( has_post_thumbnail( $pick ) ) : ?>
								<div class="top-pick-image">
									<a href="<?php echo esc_url( get_permalink( $pick ) ); ?>">
										<?php echo get_the_post_thumbnail( $pick, 'tst-product-thumb' ); ?>
									</a>
								</div>
							<?php else : ?>
								<div class="top-pick-image top-pick-placeholder">
									<svg width="64" height="64" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" opacity="0.3">
										<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
									</svg>
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
										<div class="rating-bar">
											<div class="rating-bar-fill" style="width: <?php echo esc_attr( ( $rating / 5 ) * 100 ); ?>%"></div>
										</div>
										<span class="rating-score"><?php echo esc_html( $rating ); ?>/5</span>
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
									<div class="top-pick-price-row">
										<span class="top-pick-price"><?php echo esc_html( $price ); ?></span>
									</div>
								<?php endif; ?>

								<div class="top-pick-actions">
									<?php if ( $has_affiliate ) : ?>
										<a href="<?php echo esc_url( $product_url ); ?>"
										   class="tst-btn tst-btn-amazon affiliate-link"
										   target="_blank"
										   rel="nofollow noopener sponsored">
											<?php esc_html_e( 'Check Price on Amazon', 'toolshed-tested' ); ?>
										</a>
									<?php endif; ?>
									<a href="<?php echo esc_url( get_permalink( $pick ) ); ?>" class="tst-btn tst-btn-secondary">
										<?php esc_html_e( 'Full Review', 'toolshed-tested' ); ?>
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
				'post_type'      => 'post',
				'posts_per_page' => 6,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'post__not_in'   => $top_pick_ids,
			)
		);

		if ( ! empty( $latest_reviews ) ) :
			?>
			<section class="homepage-latest-reviews">
				<div class="section-header-pro">
					<h2><?php esc_html_e( 'Latest Reviews', 'toolshed-tested' ); ?></h2>
					<div class="section-header-line"></div>
					<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) ); ?>" class="section-link">
						<?php esc_html_e( 'View All', 'toolshed-tested' ); ?> &rarr;
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

		<!-- Bottom CTA -->
		<section class="homepage-bottom-cta">
			<div class="bottom-cta-inner">
				<div class="bottom-cta-icon">
					<svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
						<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
					</svg>
				</div>
				<h2><?php esc_html_e( 'Not Sure Which Tool to Buy?', 'toolshed-tested' ); ?></h2>
				<p><?php esc_html_e( 'Browse our complete library of hands-on reviews, comparison guides, and buyer\'s guides to find the perfect tool for your project.', 'toolshed-tested' ); ?></p>
				<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) ); ?>" class="tst-btn tst-btn-cta">
					<?php esc_html_e( 'Explore All Reviews', 'toolshed-tested' ); ?>
				</a>
			</div>
		</section>
	</div>
</main>

<?php
get_footer();
