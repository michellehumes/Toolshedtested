<?php
/**
 * Fix Homepage & Navigation
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('wp-load.php');
if (!current_user_can('manage_options')) { die('Login as admin first'); }

echo "<h1>🔧 Fixing Site Settings</h1>";

// 1. Create/update homepage
$home = get_page_by_path('home');
if (!$home) {
    $home_id = wp_insert_post([
        'post_title' => 'Home',
        'post_name' => 'home',
        'post_content' => '<h2>Welcome to Toolshed Tested</h2><p>Real tool reviews from real testing.</p>',
        'post_status' => 'publish',
        'post_type' => 'page'
    ]);
    echo "✓ Created homepage<br>";
} else {
    $home_id = $home->ID;
    echo "✓ Homepage exists (ID: $home_id)<br>";
}

// 2. Create blog page
$blog = get_page_by_path('blog');
if (!$blog) {
    $blog_id = wp_insert_post([
        'post_title' => 'Blog',
        'post_name' => 'blog',
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'page'
    ]);
    echo "✓ Created blog page<br>";
} else {
    $blog_id = $blog->ID;
    echo "✓ Blog page exists (ID: $blog_id)<br>";
}

// 3. Set homepage and blog page
update_option('show_on_front', 'page');
update_option('page_on_front', $home_id);
update_option('page_for_posts', $blog_id);
echo "✓ Set static homepage and blog page<br>";

// 4. Create navigation menu
$menu_name = 'Main Menu';
$menu_exists = wp_get_nav_menu_object($menu_name);

if (!$menu_exists) {
    $menu_id = wp_create_nav_menu($menu_name);
} else {
    $menu_id = $menu_exists->term_id;
    // Clear existing items
    $items = wp_get_nav_menu_items($menu_id);
    if ($items) {
        foreach ($items as $item) {
            wp_delete_post($item->ID, true);
        }
    }
}

// Add menu items
wp_update_nav_menu_item($menu_id, 0, [
    'menu-item-title' => 'Home',
    'menu-item-url' => home_url('/'),
    'menu-item-status' => 'publish',
    'menu-item-type' => 'custom',
]);

wp_update_nav_menu_item($menu_id, 0, [
    'menu-item-title' => 'Blog',
    'menu-item-url' => home_url('/blog/'),
    'menu-item-status' => 'publish',
    'menu-item-type' => 'custom',
]);

wp_update_nav_menu_item($menu_id, 0, [
    'menu-item-title' => 'Drills',
    'menu-item-url' => home_url('/category/drills/'),
    'menu-item-status' => 'publish',
    'menu-item-type' => 'custom',
]);

wp_update_nav_menu_item($menu_id, 0, [
    'menu-item-title' => 'Saws',
    'menu-item-url' => home_url('/category/saws/'),
    'menu-item-status' => 'publish',
    'menu-item-type' => 'custom',
]);

wp_update_nav_menu_item($menu_id, 0, [
    'menu-item-title' => 'Outdoor Power',
    'menu-item-url' => home_url('/category/outdoor-power/'),
    'menu-item-status' => 'publish',
    'menu-item-type' => 'custom',
]);

echo "✓ Created navigation menu<br>";

// 5. Assign menu to theme location
$locations = get_theme_mod('nav_menu_locations');
$locations['primary'] = $menu_id;
$locations['menu-1'] = $menu_id;
set_theme_mod('nav_menu_locations', $locations);
echo "✓ Assigned menu to theme<br>";

// 6. Update permalinks
update_option('permalink_structure', '/%postname%/');
flush_rewrite_rules();
echo "✓ Updated permalinks to pretty URLs<br>";

// 7. Show all posts
$posts = get_posts(['numberposts' => -1, 'post_status' => 'publish']);
echo "<h2>Your Published Posts (" . count($posts) . " total):</h2>";
echo "<ul>";
foreach ($posts as $p) {
    $url = get_permalink($p->ID);
    echo "<li><a href='$url' target='_blank'>{$p->post_title}</a></li>";
}
echo "</ul>";

echo "<hr>";
echo "<h1 style='color:green;'>✅ Site Fixed!</h1>";
echo "<p><a href='/' target='_blank' style='font-size:1.2em;'>View Homepage →</a></p>";
echo "<p><a href='/blog/' target='_blank' style='font-size:1.2em;'>View Blog →</a></p>";

// Self-delete
@unlink(__FILE__);
?>
