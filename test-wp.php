<?php
// Ultra-simple WordPress test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Step 1: PHP is working<br>";

$wp_load = dirname(__FILE__) . '/wp-load.php';
echo "Step 2: Looking for: $wp_load<br>";

if (!file_exists($wp_load)) {
    die("ERROR: wp-load.php not found. This file must be in the WordPress root folder.");
}

echo "Step 3: wp-load.php exists<br>";

require_once($wp_load);

echo "Step 4: WordPress loaded successfully<br>";
echo "Step 5: Logged in? " . (is_user_logged_in() ? "YES" : "NO") . "<br>";
echo "Step 6: Admin? " . (current_user_can('manage_options') ? "YES" : "NO") . "<br>";
echo "<br><strong>All checks passed! WordPress is working.</strong>";
