<?php
/**
 * Toolshed Tested - Blog Post Import Script
 *
 * Reads posts from /posts/*.md files with YAML front matter
 *
 * Usage with WP-CLI: wp eval-file import-posts.php
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
    // Create categories
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

    // Find all markdown files in posts directory
    $posts_dir = __DIR__ . '/posts';
    if (!is_dir($posts_dir)) {
        echo "Error: posts directory not found at {$posts_dir}\n";
        return;
    }

    $files = glob($posts_dir . '/*.md');
    if (empty($files)) {
        echo "No markdown files found in {$posts_dir}\n";
        return;
    }

    echo "Found " . count($files) . " post files\n\n";

    foreach ($files as $file) {
        $post_data = toolshed_parse_markdown_file($file);
        if (!$post_data) {
            echo "Skipping invalid file: " . basename($file) . "\n";
            continue;
        }

        // Check if post already exists
        $existing = get_page_by_path($post_data['slug'], OBJECT, 'post');
        if ($existing) {
            echo "Skipping (exists): {$post_data['title']}\n";
            continue;
        }

        // Get category
        $category = get_term_by('slug', $post_data['category'], 'category');
        if (!$category) {
            echo "Warning: Category '{$post_data['category']}' not found for {$post_data['title']}\n";
            continue;
        }

        // Create post
        $post_id = wp_insert_post([
            'post_title'    => $post_data['title'],
            'post_name'     => $post_data['slug'],
            'post_content'  => $post_data['content'],
            'post_excerpt'  => $post_data['excerpt'],
            'post_status'   => 'publish',
            'post_type'     => 'post',
            'post_category' => [$category->term_id],
        ]);

        if ($post_id && !is_wp_error($post_id)) {
            echo "Created: {$post_data['title']}\n";
        } else {
            echo "Error creating: {$post_data['title']}\n";
        }
    }

    echo "\nImport complete!\n";
}

/**
 * Parse a markdown file with YAML front matter
 */
function toolshed_parse_markdown_file($file) {
    $content = file_get_contents($file);
    if (!$content) {
        return null;
    }

    // Parse YAML front matter (between --- markers)
    if (!preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $content, $matches)) {
        return null;
    }

    $front_matter = $matches[1];
    $markdown_content = trim($matches[2]);

    // Parse YAML front matter
    $meta = [];
    foreach (explode("\n", $front_matter) as $line) {
        if (preg_match('/^(\w+):\s*["\']?(.+?)["\']?\s*$/', $line, $m)) {
            $meta[$m[1]] = $m[2];
        }
    }

    // Validate required fields
    if (empty($meta['title']) || empty($meta['slug']) || empty($meta['category'])) {
        return null;
    }

    // Convert markdown to HTML
    $html_content = toolshed_markdown_to_html($markdown_content);

    return [
        'title'    => $meta['title'],
        'slug'     => $meta['slug'],
        'category' => $meta['category'],
        'excerpt'  => $meta['excerpt'] ?? '',
        'content'  => $html_content,
    ];
}

/**
 * Convert markdown to WordPress HTML
 */
function toolshed_markdown_to_html($markdown) {
    $html = $markdown;

    // Convert ## headings to <h2>
    $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);

    // Convert **bold** to <strong>
    $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);

    // Convert [text](url) to WordPress button blocks
    $html = preg_replace_callback(
        '/\[([^\]]+)\]\(([^)]+)\)/',
        function($matches) {
            $text = $matches[1];
            $url = $matches[2];
            return '<div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="' . esc_url($url) . '" target="_blank" rel="nofollow noopener sponsored">' . esc_html($text) . '</a></div></div>';
        },
        $html
    );

    // Convert paragraphs (lines separated by blank lines)
    $paragraphs = preg_split('/\n\s*\n/', $html);
    $html = '';
    foreach ($paragraphs as $p) {
        $p = trim($p);
        if (empty($p)) continue;

        // Don't wrap if already wrapped in HTML tags
        if (preg_match('/^<(h[1-6]|div|ul|ol|blockquote)/', $p)) {
            $html .= $p . "\n\n";
        } else {
            // First paragraph gets lead class
            if (empty($html)) {
                $html .= '<p class="lead">' . $p . "</p>\n\n";
            } else {
                $html .= '<p>' . $p . "</p>\n\n";
            }
        }
    }

    return trim($html);
}

if (defined('WP_CLI') && WP_CLI) {
    toolshed_import_posts();
}
