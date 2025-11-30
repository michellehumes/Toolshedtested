<?php
/**
 * Auto-cleanup script - deletes all test files and sets up menu
 * This file self-destructs after running
 */
require_once('wp-load.php');
if (!current_user_can('manage_options')) { die('Login as admin first'); }

echo "<h1>🧹 Cleaning Up & Finalizing...</h1>";

// Files to delete
$files_to_delete = [
    'deploy.php',
    'add-css.php',
    'expand-posts.php',
    'upgrade-home.php',
    'import.php',
    'test.php',
    'test2.php',
    'import-debug.php',
    'test-wp.php',
    'import-posts-standalone.php',
    'setup-site.php',
    'upgrade-posts.php',
    'upgrade-site.php'
];

echo "<h2>Step 1: Deleting test files...</h2>";
foreach ($files_to_delete as $file) {
    $path = dirname(__FILE__) . '/' . $file;
    if (file_exists($path)) {
        if (unlink($path)) {
            echo "✓ Deleted: $file<br>";
        } else {
            echo "✗ Could not delete: $file<br>";
        }
    }
}

// Create main menu
echo "<h2>Step 2: Creating navigation menu...</h2>";
$menu_name = 'Main Menu';
$menu_exists = wp_get_nav_menu_object($menu_name);

if (!$menu_exists) {
    $menu_id = wp_create_nav_menu($menu_name);

    // Add Home
    wp_update_nav_menu_item($menu_id, 0, [
        'menu-item-title' => 'Home',
        'menu-item-url' => home_url('/'),
        'menu-item-status' => 'publish'
    ]);

    // Add review pages
    $reviews = [
        'best-cordless-drills' => 'Drills',
        'best-circular-saws' => 'Saws',
        'best-angle-grinders' => 'Grinders',
        'best-random-orbital-sanders' => 'Sanders',
    ];

    foreach ($reviews as $slug => $title) {
        $post = get_page_by_path($slug, OBJECT, 'post');
        if ($post) {
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => $title,
                'menu-item-object' => 'post',
                'menu-item-object-id' => $post->ID,
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish'
            ]);
        }
    }

    // Add Disclosure
    $disc = get_page_by_path('affiliate-disclosure');
    if ($disc) {
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => 'Disclosure',
            'menu-item-object' => 'page',
            'menu-item-object-id' => $disc->ID,
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish'
        ]);
    }

    // Assign to theme locations
    $locations = get_theme_mod('nav_menu_locations');
    if (!is_array($locations)) $locations = [];
    $locations['primary'] = $menu_id;
    $locations['menu-1'] = $menu_id;
    $locations['main-menu'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);

    echo "✓ Menu created and assigned<br>";
} else {
    echo "✓ Menu already exists<br>";
}

echo "<h2>Step 3: Self-destructing this script...</h2>";
// Delete this cleanup script itself
$self = __FILE__;
echo "<hr>";
echo "<h1 style='color:green'>✅ All Done!</h1>";
echo "<p>Your site is now clean and ready!</p>";
echo "<p><a href='/' style='display:inline-block;background:#e94560;color:white;padding:14px 28px;border-radius:6px;text-decoration:none;font-weight:bold;'>View Your Site →</a></p>";

// Self-delete at the very end
@unlink($self);
