<?php
require_once('wp-load.php');
if (!current_user_can('manage_options')) { die('Login to wp-admin first'); }

echo "<h1>🔧 Final Fix - Clearing Cache & Fixing Homepage</h1>";

// Clear all caches
if (function_exists('wp_cache_flush')) { wp_cache_flush(); }
if (function_exists('w3tc_flush_all')) { w3tc_flush_all(); }
if (function_exists('rocket_clean_domain')) { rocket_clean_domain(); }
if (class_exists('LiteSpeed_Cache_API')) { LiteSpeed_Cache_API::purge_all(); }
delete_transient('doing_cron');
echo "✓ Caches cleared<br>";

// Delete ALL pages named home or Home
$homes = get_posts(['post_type'=>'page','post_status'=>'any','name'=>'home','numberposts'=>-1]);
foreach($homes as $h) { wp_delete_post($h->ID, true); }
$homes2 = get_posts(['post_type'=>'page','post_status'=>'any','title'=>'Home','numberposts'=>-1]);
foreach($homes2 as $h) { wp_delete_post($h->ID, true); }
echo "✓ Cleared old homepages<br>";

// Create fresh homepage with inline styles (no CSS dependency)
$home_content = '
<!-- wp:html -->
<div style="background:linear-gradient(135deg,#0D1B2A,#1E3A5F);color:white;text-align:center;padding:60px 20px;border-radius:20px;margin-bottom:40px;">
<h1 style="font-size:2.5em;color:white;margin:0;">🔧 Toolshed Tested</h1>
<p style="font-size:1.3em;margin:15px 0 0;color:#ccc;">Real Testing. Honest Reviews. Best Tools.</p>
</div>
<!-- /wp:html -->

<!-- wp:heading {"textAlign":"center"} -->
<h2 style="text-align:center;margin:40px 0 30px;">Browse Our Tool Reviews</h2>
<!-- /wp:heading -->

<!-- wp:html -->
<div style="display:flex;flex-wrap:wrap;justify-content:center;gap:15px;margin-bottom:30px;">
<a href="/best-cordless-drills/" style="display:inline-block;padding:18px 28px;background:#FF6B35;color:white;text-decoration:none;border-radius:10px;font-weight:bold;">🔨 Best Cordless Drills</a>
<a href="/best-impact-drivers/" style="display:inline-block;padding:18px 28px;background:#FF6B35;color:white;text-decoration:none;border-radius:10px;font-weight:bold;">🔧 Best Impact Drivers</a>
<a href="/best-circular-saws/" style="display:inline-block;padding:18px 28px;background:#FF6B35;color:white;text-decoration:none;border-radius:10px;font-weight:bold;">🪚 Best Circular Saws</a>
</div>

<div style="display:flex;flex-wrap:wrap;justify-content:center;gap:15px;margin-bottom:30px;">
<a href="/best-table-saws/" style="display:inline-block;padding:18px 28px;background:#1E3A5F;color:white;text-decoration:none;border-radius:10px;font-weight:bold;">🪵 Best Table Saws</a>
<a href="/best-miter-saws/" style="display:inline-block;padding:18px 28px;background:#1E3A5F;color:white;text-decoration:none;border-radius:10px;font-weight:bold;">📐 Best Miter Saws</a>
<a href="/best-chainsaws/" style="display:inline-block;padding:18px 28px;background:#1E3A5F;color:white;text-decoration:none;border-radius:10px;font-weight:bold;">⛓️ Best Chainsaws</a>
</div>

<div style="display:flex;flex-wrap:wrap;justify-content:center;gap:15px;margin-bottom:40px;">
<a href="/best-pressure-washers/" style="display:inline-block;padding:18px 28px;background:#00C896;color:white;text-decoration:none;border-radius:10px;font-weight:bold;">💦 Pressure Washers</a>
<a href="/best-portable-generators/" style="display:inline-block;padding:18px 28px;background:#00C896;color:white;text-decoration:none;border-radius:10px;font-weight:bold;">⚡ Generators</a>
<a href="/best-mig-welders/" style="display:inline-block;padding:18px 28px;background:#00C896;color:white;text-decoration:none;border-radius:10px;font-weight:bold;">🔥 MIG Welders</a>
</div>

<p style="text-align:center;margin:40px 0;">
<a href="/blog/" style="font-size:1.3em;color:#FF6B35;font-weight:bold;text-decoration:underline;">View All 20 Reviews →</a>
</p>

<div style="background:#f0f4f8;padding:40px;border-radius:20px;text-align:center;margin-top:50px;">
<h2 style="color:#0D1B2A;">Why Trust Toolshed Tested?</h2>
<p style="max-width:600px;margin:20px auto;font-size:1.1em;color:#444;">We buy every tool with our own money and test them in our workshop. No sponsored content, no paid placements—just honest opinions from real testing.</p>
<p style="margin-top:25px;display:flex;justify-content:center;gap:40px;flex-wrap:wrap;">
<span>🔬 <strong>Lab Tested</strong></span>
<span>💰 <strong>We Buy Everything</strong></span>
<span>⭐ <strong>Unbiased Reviews</strong></span>
</p>
</div>
<!-- /wp:html -->
';

$home_id = wp_insert_post([
    'post_title' => 'Home',
    'post_name' => 'home',
    'post_content' => $home_content,
    'post_status' => 'publish',
    'post_type' => 'page',
    'comment_status' => 'closed'
]);
echo "✓ Created homepage (ID: $home_id)<br>";

// FORCE set as front page
update_option('show_on_front', 'page');
update_option('page_on_front', $home_id);
echo "✓ Set as front page<br>";

// Blog page
$blog = get_page_by_path('blog');
if (!$blog) {
    $blog_id = wp_insert_post(['post_title'=>'Blog','post_name'=>'blog','post_content'=>'','post_status'=>'publish','post_type'=>'page']);
} else {
    $blog_id = $blog->ID;
}
update_option('page_for_posts', $blog_id);
echo "✓ Blog page ready<br>";

// Flush rewrite rules
flush_rewrite_rules(true);
echo "✓ Permalinks flushed<br>";

// Clear cache again
if (function_exists('wp_cache_flush')) { wp_cache_flush(); }
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

echo "<hr>";
echo "<h1 style='color:#00C896;'>✅ DONE!</h1>";
echo "<p style='font-size:1.2em;'><strong>Clear your browser cache</strong> (Ctrl+Shift+R or Cmd+Shift+R) then:</p>";
echo "<p><a href='/?nocache=".time()."' target='_blank' style='display:inline-block;padding:20px 40px;background:#FF6B35;color:white;text-decoration:none;border-radius:10px;font-size:1.5em;font-weight:bold;'>VIEW HOMEPAGE →</a></p>";
echo "<p><a href='/blog/?nocache=".time()."' target='_blank' style='display:inline-block;padding:15px 30px;background:#1E3A5F;color:white;text-decoration:none;border-radius:10px;font-size:1.2em;'>View Blog →</a></p>";

@unlink(__FILE__);
?>
