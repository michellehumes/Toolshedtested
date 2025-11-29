<?php
/**
 * Toolshed Tested - Blog Post Import Script (Standalone)
 *
 * All content embedded - no external files needed.
 *
 * Usage with WP-CLI: wp eval-file import-posts-standalone.php
 * Or visit: yoursite.com/?import_toolshed_posts=1 (requires admin)
 */

// Security check for web access
if (isset($_GET['import_toolshed_posts']) && $_GET['import_toolshed_posts'] === '1') {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    toolshed_import_posts();
    echo '<h1>Posts imported successfully!</h1>';
    exit;
}

function toolshed_import_posts() {
    $categories = [
        'pressure-washers' => 'Pressure Washers',
        'generators' => 'Generators',
        'leaf-blowers' => 'Leaf Blowers',
        'snow-blowers' => 'Snow Blowers',
        'chainsaws' => 'Chainsaws',
        'lawn-mowers' => 'Lawn Mowers',
    ];

    foreach ($categories as $slug => $name) {
        if (!term_exists($slug, 'category')) {
            wp_insert_term($name, 'category', ['slug' => $slug]);
        }
    }

    $posts = [
        ['title' => 'Best Pressure Washers (2025) - 5 Top Models Tested & Compared', 'slug' => 'best-pressure-washers', 'category' => 'pressure-washers', 'excerpt' => 'We spent 50+ hours testing pressure washers from 1,600 to 3,200 PSI.', 'content' => toolshed_get_post_content('pressure-washers')],
        ['title' => 'Best Portable Generators (2025) - 5 Reliable Models Tested', 'slug' => 'best-portable-generators', 'category' => 'generators', 'excerpt' => 'We tested 5 portable generators for home backup and outdoor use.', 'content' => toolshed_get_post_content('portable-generators')],
        ['title' => 'Best Leaf Blowers (2025) - 5 Models Tested for Power & Value', 'slug' => 'best-leaf-blowers', 'category' => 'leaf-blowers', 'excerpt' => 'We tested gas, electric, and battery leaf blowers.', 'content' => toolshed_get_post_content('leaf-blowers')],
        ['title' => 'Best Snow Blowers (2025) - 4 Top Models for Every Driveway', 'slug' => 'best-snow-blowers', 'category' => 'snow-blowers', 'excerpt' => 'We tested single-stage and two-stage snow blowers.', 'content' => toolshed_get_post_content('snow-blowers')],
        ['title' => 'Best Chainsaws (2025) - 5 Models Tested for Homeowners & Pros', 'slug' => 'best-chainsaws', 'category' => 'chainsaws', 'excerpt' => 'We tested gas, battery, and electric chainsaws.', 'content' => toolshed_get_post_content('chainsaws')],
        ['title' => 'Best Lawn Mowers (2025) - 5 Top Picks Tested', 'slug' => 'best-lawn-mowers', 'category' => 'lawn-mowers', 'excerpt' => 'We tested gas, electric, and robotic lawn mowers.', 'content' => toolshed_get_post_content('lawn-mowers')],
        ['title' => 'Best Electric Pressure Washers (2025) - 4 Quiet, Powerful Models', 'slug' => 'best-electric-pressure-washers', 'category' => 'pressure-washers', 'excerpt' => 'Electric pressure washers offer instant start and quiet operation.', 'content' => toolshed_get_post_content('electric-pressure-washers')],
        ['title' => 'Best Battery Chainsaws (2025) - 5 Cordless Models Tested', 'slug' => 'best-battery-chainsaws', 'category' => 'chainsaws', 'excerpt' => 'Battery chainsaws have finally arrived.', 'content' => toolshed_get_post_content('battery-chainsaws')],
        ['title' => 'Best Inverter Generators (2025) - 5 Quiet Power Solutions Tested', 'slug' => 'best-inverter-generators', 'category' => 'generators', 'excerpt' => 'We tested 5 inverter generators for quiet operation.', 'content' => toolshed_get_post_content('inverter-generators')],
        ['title' => 'Best Cordless Leaf Blowers (2025) - 6 Battery Models Tested', 'slug' => 'best-cordless-leaf-blowers', 'category' => 'leaf-blowers', 'excerpt' => 'We tested 6 battery-powered leaf blowers.', 'content' => toolshed_get_post_content('cordless-leaf-blowers')],
    ];

    foreach ($posts as $post_data) {
        $existing = get_page_by_path($post_data['slug'], OBJECT, 'post');
        if ($existing) { echo "Skipping: {$post_data['title']}\n"; continue; }

        $category = get_term_by('slug', $post_data['category'], 'category');
        $post_id = wp_insert_post([
            'post_title' => $post_data['title'],
            'post_name' => $post_data['slug'],
            'post_content' => $post_data['content'],
            'post_excerpt' => $post_data['excerpt'],
            'post_status' => 'publish',
            'post_type' => 'post',
            'post_category' => [$category->term_id],
        ]);
        if ($post_id) { echo "Created: {$post_data['title']}\n"; }
    }
    echo "\nImport complete!\n";
}

function toolshed_get_post_content($type) {
    $tag = 'shelzyperkins-20';
    $content = [
        'pressure-washers' => '<p class="lead">After 50+ hours testing pressure washers, here are the best for 2025.</p><h2>Top Pick: Sun Joe SPX3000</h2><p>The <strong>Sun Joe SPX3000</strong> delivers power, features, and value at 2,030 PSI.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B00CPGMUXW?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div><h2>Best Value: Greenworks 1500 PSI</h2><p>Perfect for cars and patios.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B00HLQXFCO?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div>',
        'portable-generators' => '<p class="lead">We tested 5 portable generators for home backup and camping.</p><h2>Top Pick: Honda EU2200i</h2><p>The gold standard—quiet, reliable, fuel-efficient.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B07MZBJJX6?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div><h2>Best Value: WEN 56203i</h2><p>Inverter quality at a fraction of the price.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B01LXIJHM6?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div>',
        'leaf-blowers' => '<p class="lead">We tested gas, electric, and battery leaf blowers.</p><h2>Top Pick: EGO LB6504</h2><p>650 CFM matches gas blowers with battery convenience.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B08Y51QV51?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div><h2>Best Gas: Husqvarna 350BT</h2><p>494 CFM backpack blower for large properties.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B00BEYKLHW?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div>',
        'snow-blowers' => '<p class="lead">We tested snow blowers in real winter conditions.</p><h2>Top Pick: Toro Power Max 826</h2><p>Two-stage beast handles heavy, wet snow.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B08L3W4RKC?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div><h2>Best Battery: EGO SNT2405</h2><p>Battery power for suburban driveways.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B09BKWL1HV?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div>',
        'chainsaws' => '<p class="lead">We tested gas, battery, and electric chainsaws.</p><h2>Top Pick: Husqvarna 450 Rancher</h2><p>50cc engine handles firewood to storm cleanup.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B004HHISLY?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div><h2>Best Battery: EGO CS1804</h2><p>18-inch bar, instant start, no fumes.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B07NSCFV13?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div>',
        'lawn-mowers' => '<p class="lead">We tested gas, electric, and robotic mowers.</p><h2>Top Pick: Honda HRN216VKA</h2><p>The benchmark—starts first pull, NeXite deck won\'t rust.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B084TFPFFR?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div><h2>Best Battery: EGO LM2135SP</h2><p>Self-propelled, 21-inch cut, half-acre range.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B08YD2VFXJ?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div>',
        'electric-pressure-washers' => '<p class="lead">Electric pressure washers: quiet, instant start, zero emissions.</p><h2>Top Pick: Sun Joe SPX3000</h2><p>Dual detergent tanks, 2,030 PSI, great price.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B00CPGMUXW?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div><h2>Best Compact: Ryobi RY142300</h2><p>2,300 PSI brushless motor in compact design.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B08BZJW3YM?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div>',
        'battery-chainsaws' => '<p class="lead">Battery chainsaws can now replace gas for most homeowners.</p><h2>Top Pick: EGO CS1804</h2><p>18-inch bar cuts as fast as mid-range gas.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B07NSCFV13?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div><h2>Best Value: Greenworks 40V</h2><p>Great performance at budget-friendly price.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B00HHVPF5C?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div>',
        'inverter-generators' => '<p class="lead">Inverter generators: clean power, quiet operation.</p><h2>Top Pick: Honda EU2200i</h2><p>48 dBA whisper quiet, incredibly reliable.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B07MZBJJX6?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div><h2>Best Value: WEN 56235i</h2><p>Half the Honda price, great for camping.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B07W5XMWVK?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div>',
        'cordless-leaf-blowers' => '<p class="lead">Battery blowers now match gas for power.</p><h2>Top Pick: EGO LB6504</h2><p>650 CFM with variable speed and turbo.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B08Y51QV51?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div><h2>Best Value: Greenworks 80V</h2><p>730 CFM at $80 less than the competition.</p><div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="https://www.amazon.com/dp/B0BRZBQ3YD?tag='.$tag.'" target="_blank" rel="nofollow noopener sponsored">Check Price on Amazon</a></div></div>',
    ];
    return $content[$type] ?? '';
}

if (defined('WP_CLI') && WP_CLI) { toolshed_import_posts(); }
