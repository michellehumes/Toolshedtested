<?php
/**
 * Debug Import Script - Upload to WordPress root folder (same as wp-load.php)
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Info</h1>";
echo "<p>Current file: " . __FILE__ . "</p>";
echo "<p>Looking for wp-load.php in: " . dirname(__FILE__) . "/wp-load.php</p>";

if (file_exists(dirname(__FILE__) . '/wp-load.php')) {
    echo "<p style='color:green'>✓ wp-load.php found!</p>";

    require_once(dirname(__FILE__) . '/wp-load.php');

    echo "<p style='color:green'>✓ WordPress loaded!</p>";

    if (function_exists('current_user_can')) {
        echo "<p style='color:green'>✓ WordPress functions available</p>";

        if (is_user_logged_in()) {
            echo "<p style='color:green'>✓ User is logged in</p>";
            if (current_user_can('manage_options')) {
                echo "<p style='color:green'>✓ User is admin - Ready to import!</p>";
                echo "<p><a href='import-posts-standalone.php?import_toolshed_posts=1'>Run Full Import</a></p>";
            } else {
                echo "<p style='color:red'>✗ User is NOT admin</p>";
            }
        } else {
            echo "<p style='color:red'>✗ User is NOT logged in. <a href='" . wp_login_url() . "'>Log in first</a></p>";
        }
    }
} else {
    echo "<p style='color:red'>✗ wp-load.php NOT found!</p>";
    echo "<p>Make sure this file is in your WordPress root folder (public_html)</p>";

    // List files in current directory
    echo "<h2>Files in current directory:</h2><ul>";
    foreach (scandir(dirname(__FILE__)) as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
}
