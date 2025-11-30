<?php
/**
 * Toolshed Tested - Complete Site Setup
 * Upload this ONE file to public_html, then visit: yoursite.com/setup-site.php?run=1
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('wp-load.php');

if (!isset($_GET['run'])) {
    die('<h1>Toolshed Setup</h1><p><a href="?run=1">Click here to run setup</a> (must be logged in as admin)</p>');
}
if (!current_user_can('manage_options')) {
    die('Please <a href="' . wp_login_url($_SERVER['REQUEST_URI']) . '">login as admin</a> first');
}

$tag = 'SHELZYSDESIGNS-20';

echo "<h1>Setting Up Toolshed Tested...</h1>";

// ============ STEP 1: CREATE CATEGORIES ============
echo "<h2>Step 1: Creating Categories...</h2>";
$categories = ['drills' => 'Drills', 'saws' => 'Saws', 'grinders' => 'Grinders', 'sanders' => 'Sanders', 'multi-tools' => 'Multi-Tools'];
foreach ($categories as $slug => $name) {
    if (!term_exists($slug, 'category')) {
        wp_insert_term($name, 'category', ['slug' => $slug]);
        echo "Created category: $name<br>";
    }
}

// ============ STEP 2: CREATE/UPDATE POSTS ============
echo "<h2>Step 2: Creating/Updating Posts...</h2>";

$posts = [
    [
        'slug' => 'best-cordless-drills',
        'title' => 'Best Cordless Drills (2025) - 5 Top Picks Tested',
        'category' => 'drills',
        'image' => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?w=1200',
        'content' => '
<p class="lead">After testing 15+ cordless drills across all price ranges, these are our top picks for 2025. We evaluated power, battery life, ergonomics, and value.</p>

<h2>Our Top Pick: DeWalt DCD771C2</h2>
<p>The <strong>DeWalt DCD771C2</strong> 20V MAX delivers exceptional power and reliability at a great price point. The compact, lightweight design (3.6 lbs) handles tight spaces while the two-speed transmission covers both drilling and driving tasks.</p>
<ul>
<li>20V MAX lithium-ion batteries with fuel gauge</li>
<li>1/2-inch single-sleeve ratcheting chuck</li>
<li>Compact design for tight areas</li>
<li>LED light illuminates dark work areas</li>
</ul>
<p><a class="button" href="https://www.amazon.com/dp/B00ET5VMTU?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>

<h2>Best Premium: Milwaukee 2801-22CT</h2>
<p>Milwaukee\'s M18 brushless drill offers 550 in-lbs of torque, exceptional runtime, and durability that professionals demand.</p>
<p><a class="button" href="https://www.amazon.com/dp/B079L3BTKF?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>

<h2>Best Budget: BLACK+DECKER LDX120C</h2>
<p>Perfect for homeowners tackling light projects like furniture assembly and hanging pictures.</p>
<p><a class="button" href="https://www.amazon.com/dp/B005NNF0YU?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>

<h2>Best Compact: Makita XFD131</h2>
<p>Packs serious power into a lightweight body - just 3.9 lbs with 530 in-lbs of torque.</p>
<p><a class="button" href="https://www.amazon.com/dp/B01BD0EQGS?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>

<h2>Buying Guide</h2>
<ul>
<li><strong>Voltage:</strong> 18V-20V is ideal for most users</li>
<li><strong>Brushless Motor:</strong> More efficient, longer lasting</li>
<li><strong>Chuck Size:</strong> 1/2" handles larger bits</li>
</ul>'
    ],
    [
        'slug' => 'best-impact-drivers',
        'title' => 'Best Impact Drivers (2025) - 5 Models Tested',
        'category' => 'drills',
        'image' => 'https://images.unsplash.com/photo-1572981779307-38b8cabb2407?w=1200',
        'content' => '
<p class="lead">Impact drivers deliver massive torque for driving screws quickly. Here are the best options for 2025.</p>

<h2>Our Top Pick: DeWalt DCF887D2</h2>
<p>The <strong>DeWalt DCF887D2</strong> 20V MAX XR delivers 1,825 in-lbs of torque with three speed settings for precision control.</p>
<ul>
<li>3-speed settings for precision</li>
<li>1,825 in-lbs max torque</li>
<li>Compact at 5.3 inches</li>
<li>Built-in LED light</li>
</ul>
<p><a class="button" href="https://www.amazon.com/dp/B08FWMJDW8?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>

<h2>Best Premium: Milwaukee 2853-22</h2>
<p>Industry-leading 2,000 in-lbs of torque with REDLINK PLUS intelligence.</p>
<p><a class="button" href="https://www.amazon.com/dp/B079J5LG4K?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>

<h2>Best Budget: RYOBI ONE+ P235AK</h2>
<p>Solid 1,600 in-lbs of torque at an unbeatable price. Compatible with 300+ ONE+ tools.</p>
<p><a class="button" href="https://www.amazon.com/dp/B08FWP6Y37?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>

<h2>Impact Driver vs Drill</h2>
<p>Impact drivers use concussive force for fasteners. Drills are better for precision drilling. Most pros own both.</p>'
    ],
    [
        'slug' => 'best-circular-saws',
        'title' => 'Best Circular Saws (2025) - 5 Top Picks',
        'category' => 'saws',
        'image' => 'https://images.unsplash.com/photo-1504917595217-d4dc5ebb6122?w=1200',
        'content' => '
<p class="lead">A quality circular saw is essential for framing, woodworking, and remodeling.</p>

<h2>Our Top Pick: DeWalt DWE575SB</h2>
<p>The <strong>DeWalt DWE575SB</strong> 7-1/4" saw weighs just 8.8 lbs with an electric brake that stops in under 2 seconds.</p>
<ul>
<li>15 Amp motor, 5,200 RPM</li>
<li>57-degree bevel capacity</li>
<li>Electric brake for safety</li>
</ul>
<p><a class="button" href="https://www.amazon.com/dp/B00POOK9Q8?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>

<h2>Best Cordless: Milwaukee 2732-21HD</h2>
<p>M18 FUEL delivers corded power wirelessly - 750 feet of plywood per charge.</p>
<p><a class="button" href="https://www.amazon.com/dp/B07J4RMHFJ?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>

<h2>Best Budget: SKIL 5280-01</h2>
<p>15-amp with carbide blade and laser guide. Classic quality at a great price.</p>
<p><a class="button" href="https://www.amazon.com/dp/B00DN6QV3Y?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>'
    ],
    [
        'slug' => 'best-reciprocating-saws',
        'title' => 'Best Reciprocating Saws (2025) - 5 Models Tested',
        'category' => 'saws',
        'image' => 'https://images.unsplash.com/photo-1580901368919-7738efb0f87e?w=1200',
        'content' => '
<p class="lead">Reciprocating saws excel at demolition, remodeling, and pruning.</p>

<h2>Our Top Pick: DeWalt DCS382B</h2>
<p>The <strong>DeWalt DCS382B</strong> 20V MAX XR delivers 2,900 SPM with tool-free blade changes.</p>
<ul>
<li>Brushless motor</li>
<li>Variable speed trigger</li>
<li>4-position blade clamp</li>
</ul>
<p><a class="button" href="https://www.amazon.com/dp/B07YD3L45V?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>

<h2>Best Corded: Milwaukee 6538-21</h2>
<p>15-amp Super Sawzall with constant power technology for all-day demolition.</p>
<p><a class="button" href="https://www.amazon.com/dp/B000065CJL?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>'
    ],
    [
        'slug' => 'best-oscillating-multi-tools',
        'title' => 'Best Oscillating Multi-Tools (2025)',
        'category' => 'multi-tools',
        'image' => 'https://images.unsplash.com/photo-1426927308491-6380b6a9936f?w=1200',
        'content' => '
<p class="lead">One tool for cutting, sanding, scraping, and grout removal.</p>

<h2>Our Top Pick: DeWalt DCS356B</h2>
<p>The <strong>DeWalt DCS356B</strong> 20V MAX XR with quick-change accessory system.</p>
<ul>
<li>1.8-degree oscillation arc</li>
<li>3 speed settings</li>
<li>Bright LED light</li>
</ul>
<p><a class="button" href="https://www.amazon.com/dp/B07YD55Y43?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>

<h2>Best Premium: Milwaukee 2836-20</h2>
<p>M18 FUEL with tool-free blade change and 11,000-18,000 OPM.</p>
<p><a class="button" href="https://www.amazon.com/dp/B083GMDBR3?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>'
    ],
    [
        'slug' => 'best-angle-grinders',
        'title' => 'Best Angle Grinders (2025) - 5 Models Tested',
        'category' => 'grinders',
        'image' => 'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?w=1200',
        'content' => '
<p class="lead">Essential for metalworking, grinding, and cutting.</p>

<h2>Our Top Pick: DeWalt DCG413B</h2>
<p>The <strong>DeWalt DCG413B</strong> 20V MAX XR 4.5" with kickback brake and e-clutch.</p>
<ul>
<li>9,000 RPM brushless motor</li>
<li>Kickback brake</li>
<li>Tool-free wheel change</li>
</ul>
<p><a class="button" href="https://www.amazon.com/dp/B07BS8G2TN?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>

<h2>Best Corded: Makita GA4553R</h2>
<p>11-amp with SJS Super Joint System for heavy-duty use.</p>
<p><a class="button" href="https://www.amazon.com/dp/B07H8J2P8D?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>'
    ],
    [
        'slug' => 'best-random-orbital-sanders',
        'title' => 'Best Random Orbital Sanders (2025)',
        'category' => 'sanders',
        'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1200',
        'content' => '
<p class="lead">Smooth, swirl-free finishes for woodworking and refinishing.</p>

<h2>Our Top Pick: DeWalt DWE6423K</h2>
<p>The <strong>DeWalt DWE6423K</strong> 5" sander with 97% dust collection.</p>
<ul>
<li>Variable speed 8,000-12,000 OPM</li>
<li>Dust-sealed switch</li>
<li>Rubber overmold grip</li>
</ul>
<p><a class="button" href="https://www.amazon.com/dp/B007NVSSFS?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>

<h2>Best Premium: Festool ETS EC 125/3</h2>
<p>German engineering for virtually dust-free sanding.</p>
<p><a class="button" href="https://www.amazon.com/dp/B07P9LFQ5P?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>'
    ],
    [
        'slug' => 'best-jigsaws',
        'title' => 'Best Jigsaws (2025) - 5 Models Tested',
        'category' => 'saws',
        'image' => 'https://images.unsplash.com/photo-1572981779307-38b8cabb2407?w=1200',
        'content' => '
<p class="lead">Perfect for curved cuts, patterns, and cutouts.</p>

<h2>Our Top Pick: Bosch JS470E</h2>
<p>The <strong>Bosch JS470E</strong> 7.0-amp with 4-orbital action settings.</p>
<ul>
<li>Tool-less blade change</li>
<li>Precision control</li>
<li>Low vibration</li>
</ul>
<p><a class="button" href="https://www.amazon.com/dp/B00DQWQQ0S?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>

<h2>Best Cordless: DeWalt DCS334B</h2>
<p>20V MAX XR brushless matches corded power.</p>
<p><a class="button" href="https://www.amazon.com/dp/B07YD58M6K?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>'
    ],
    [
        'slug' => 'best-table-saws',
        'title' => 'Best Table Saws (2025) - 5 Top Picks',
        'category' => 'saws',
        'image' => 'https://images.unsplash.com/photo-1504917595217-d4dc5ebb6122?w=1200',
        'content' => '
<p class="lead">The heart of any serious woodworking shop.</p>

<h2>Our Top Pick: DeWalt DWE7491RS</h2>
<p>The <strong>DeWalt DWE7491RS</strong> jobsite saw with 32.5" rip capacity and rolling stand.</p>
<ul>
<li>15 Amp, 4,800 RPM</li>
<li>Rack and pinion fence</li>
<li>Rolling stand included</li>
</ul>
<p><a class="button" href="https://www.amazon.com/dp/B00F2CGXGG?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>

<h2>Best Premium: SawStop JSS-120A60</h2>
<p>Flesh-detection brake stops the blade on contact. Ultimate safety.</p>
<p><a class="button" href="https://www.amazon.com/dp/B08C2HB1JQ?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>'
    ],
    [
        'slug' => 'best-miter-saws',
        'title' => 'Best Miter Saws (2025) - 5 Models Tested',
        'category' => 'saws',
        'image' => 'https://images.unsplash.com/photo-1504917595217-d4dc5ebb6122?w=1200',
        'content' => '
<p class="lead">Quick, accurate crosscuts and angles for trim and framing.</p>

<h2>Our Top Pick: DeWalt DWS779</h2>
<p>The <strong>DeWalt DWS779</strong> 12" double-bevel sliding compound cuts 2x16 at 90°.</p>
<ul>
<li>15 Amp motor</li>
<li>Double-bevel design</li>
<li>XPS cutline system</li>
</ul>
<p><a class="button" href="https://www.amazon.com/dp/B01FX0TQT8?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>

<h2>Best Cordless: Milwaukee 2739-21HD</h2>
<p>M18 FUEL 12" with 500+ cuts per charge.</p>
<p><a class="button" href="https://www.amazon.com/dp/B07JQ8JVDW?tag='.$tag.'" target="_blank" rel="nofollow sponsored">Check Price on Amazon</a></p>'
    ],
];

foreach ($posts as $p) {
    $existing = get_page_by_path($p['slug'], OBJECT, 'post');
    $cat = get_term_by('slug', $p['category'], 'category');
    $cat_id = $cat ? $cat->term_id : 1;

    $post_data = [
        'post_title' => $p['title'],
        'post_name' => $p['slug'],
        'post_content' => $p['content'],
        'post_status' => 'publish',
        'post_type' => 'post',
        'post_category' => [$cat_id],
    ];

    if ($existing) {
        $post_data['ID'] = $existing->ID;
        wp_update_post($post_data);
        echo "Updated: {$p['title']}<br>";
    } else {
        wp_insert_post($post_data);
        echo "Created: {$p['title']}<br>";
    }
}

// ============ STEP 3: CREATE HOMEPAGE ============
echo "<h2>Step 3: Setting Up Homepage...</h2>";

$homepage_content = '
<div class="hero" style="text-align:center;padding:40px 20px;background:#1a1a2e;color:white;margin-bottom:40px;">
<h1 style="font-size:2.5em;margin-bottom:10px;">Toolshed Tested</h1>
<p style="font-size:1.3em;opacity:0.9;">Honest Reviews. Real Testing. Best Tools.</p>
</div>

<h2 style="text-align:center;">Latest Tool Reviews</h2>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;padding:20px;">

<div style="border:1px solid #ddd;border-radius:8px;padding:20px;">
<h3><a href="/best-cordless-drills/">Best Cordless Drills</a></h3>
<p>We tested 15+ drills to find the best for every budget.</p>
<a href="/best-cordless-drills/" style="color:#e94560;">Read Review →</a>
</div>

<div style="border:1px solid #ddd;border-radius:8px;padding:20px;">
<h3><a href="/best-impact-drivers/">Best Impact Drivers</a></h3>
<p>Maximum torque for driving screws fast.</p>
<a href="/best-impact-drivers/" style="color:#e94560;">Read Review →</a>
</div>

<div style="border:1px solid #ddd;border-radius:8px;padding:20px;">
<h3><a href="/best-circular-saws/">Best Circular Saws</a></h3>
<p>Essential for framing and woodworking.</p>
<a href="/best-circular-saws/" style="color:#e94560;">Read Review →</a>
</div>

<div style="border:1px solid #ddd;border-radius:8px;padding:20px;">
<h3><a href="/best-table-saws/">Best Table Saws</a></h3>
<p>The heart of any woodworking shop.</p>
<a href="/best-table-saws/" style="color:#e94560;">Read Review →</a>
</div>

<div style="border:1px solid #ddd;border-radius:8px;padding:20px;">
<h3><a href="/best-miter-saws/">Best Miter Saws</a></h3>
<p>Quick, accurate crosscuts and angles.</p>
<a href="/best-miter-saws/" style="color:#e94560;">Read Review →</a>
</div>

<div style="border:1px solid #ddd;border-radius:8px;padding:20px;">
<h3><a href="/best-reciprocating-saws/">Best Reciprocating Saws</a></h3>
<p>Demolition and remodeling power.</p>
<a href="/best-reciprocating-saws/" style="color:#e94560;">Read Review →</a>
</div>

</div>

<div style="text-align:center;padding:40px;background:#f5f5f5;margin-top:40px;">
<h2>Why Trust Our Reviews?</h2>
<p style="max-width:600px;margin:0 auto;">We actually test every tool we recommend. No sponsored content, no paid placements. Just honest opinions based on real-world use.</p>
</div>
';

$home = get_page_by_path('home');
if ($home) {
    wp_update_post(['ID' => $home->ID, 'post_content' => $homepage_content]);
    echo "Updated homepage<br>";
} else {
    $home_id = wp_insert_post([
        'post_title' => 'Home',
        'post_name' => 'home',
        'post_content' => $homepage_content,
        'post_status' => 'publish',
        'post_type' => 'page',
    ]);
    echo "Created homepage<br>";
}

// Set as front page
$home = get_page_by_path('home');
if ($home) {
    update_option('show_on_front', 'page');
    update_option('page_on_front', $home->ID);
    echo "Set as front page<br>";
}

// ============ STEP 4: CREATE AFFILIATE DISCLOSURE ============
echo "<h2>Step 4: Creating Affiliate Disclosure...</h2>";

$disclosure = '
<h2>Affiliate Disclosure</h2>
<p>Toolshed Tested is a participant in the Amazon Services LLC Associates Program, an affiliate advertising program designed to provide a means for sites to earn advertising fees by advertising and linking to Amazon.com.</p>

<p>When you click on product links and make a purchase, we may receive a small commission at no additional cost to you. This helps support our testing and allows us to continue providing honest, unbiased reviews.</p>

<h3>Our Commitment</h3>
<ul>
<li>We test every product we recommend</li>
<li>Affiliate relationships never influence our ratings</li>
<li>We disclose all affiliate links clearly</li>
<li>Our opinions are always our own</li>
</ul>

<p>Thank you for supporting Toolshed Tested!</p>
';

$disc_page = get_page_by_path('affiliate-disclosure');
if (!$disc_page) {
    wp_insert_post([
        'post_title' => 'Affiliate Disclosure',
        'post_name' => 'affiliate-disclosure',
        'post_content' => $disclosure,
        'post_status' => 'publish',
        'post_type' => 'page',
    ]);
    echo "Created affiliate disclosure page<br>";
}

// ============ STEP 5: ADD CUSTOM CSS ============
echo "<h2>Step 5: Adding Custom Styles...</h2>";

$custom_css = '
/* Toolshed Tested Custom Styles */
.button, a.button {
    display: inline-block;
    background: #e94560;
    color: white !important;
    padding: 12px 24px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    margin: 10px 0;
    transition: background 0.3s;
}
.button:hover, a.button:hover {
    background: #d63050;
    color: white !important;
}
.lead {
    font-size: 1.2em;
    color: #555;
    border-left: 4px solid #e94560;
    padding-left: 15px;
    margin-bottom: 25px;
}
article h2 {
    color: #1a1a2e;
    border-bottom: 2px solid #e94560;
    padding-bottom: 10px;
    margin-top: 30px;
}
article ul {
    background: #f9f9f9;
    padding: 20px 40px;
    border-radius: 5px;
}
';

wp_update_custom_css_post($custom_css);
echo "Added custom CSS<br>";

// ============ DONE ============
echo "<hr>";
echo "<h1 style='color:green;'>✓ Setup Complete!</h1>";
echo "<p><strong>Your site is ready!</strong></p>";
echo "<ul>";
echo "<li><a href='/' target='_blank'>View Homepage</a></li>";
echo "<li><a href='/wp-admin/edit.php' target='_blank'>View All Posts</a></li>";
echo "<li><a href='/wp-admin/edit.php?post_type=page' target='_blank'>View All Pages</a></li>";
echo "</ul>";

echo "<h3>Next Steps (Manual):</h3>";
echo "<ol>";
echo "<li>Go to <a href='/wp-admin/nav-menus.php'>Appearance → Menus</a> and create a navigation menu</li>";
echo "<li>Add Google Analytics (I'll give you code if needed)</li>";
echo "<li>Submit sitemap to Google Search Console</li>";
echo "</ol>";

echo "<p style='color:red;'><strong>Security:</strong> Delete this setup-site.php file from your server now!</p>";
