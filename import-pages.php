<?php
/**
 * Toolshed Tested - Page Import Script
 *
 * Reads pages from /pages/*.md files with YAML front matter
 *
 * Usage with WP-CLI: wp eval-file import-pages.php
 * Or visit: yoursite.com/?import_toolshed_pages=1 (requires admin)
 */

// Security check for web access
if (isset($_GET['import_toolshed_pages']) && $_GET['import_toolshed_pages'] === '1') {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    toolshed_import_pages();
    echo '<h1>Pages imported successfully!</h1>';
    exit;
}

function toolshed_import_pages() {
    // Find all markdown files in pages directory
    $pages_dir = __DIR__ . '/pages';
    if (!is_dir($pages_dir)) {
        echo "Error: pages directory not found at {$pages_dir}\n";
        return;
    }

    $files = glob($pages_dir . '/*.md');
    if (empty($files)) {
        echo "No markdown files found in {$pages_dir}\n";
        return;
    }

    echo "Found " . count($files) . " page files\n\n";

    foreach ($files as $file) {
        $page_data = toolshed_parse_page_file($file);
        if (!$page_data) {
            echo "Skipping invalid file: " . basename($file) . "\n";
            continue;
        }

        // Check if page already exists
        $existing = get_page_by_path($page_data['slug'], OBJECT, 'page');
        if ($existing) {
            // Update existing page
            $page_id = wp_update_post([
                'ID'           => $existing->ID,
                'post_title'   => $page_data['title'],
                'post_content' => $page_data['content'],
            ]);
            if ($page_id && !is_wp_error($page_id)) {
                echo "Updated: {$page_data['title']}\n";
            }
            continue;
        }

        // Create new page
        $page_id = wp_insert_post([
            'post_title'   => $page_data['title'],
            'post_name'    => $page_data['slug'],
            'post_content' => $page_data['content'],
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);

        if ($page_id && !is_wp_error($page_id)) {
            echo "Created: {$page_data['title']}\n";

            // Set as front page if template is front-page
            if ($page_data['template'] === 'front-page') {
                update_option('show_on_front', 'page');
                update_option('page_on_front', $page_id);
                echo "  -> Set as front page\n";
            }

            // Update meta description if provided
            if (!empty($page_data['meta_description'])) {
                update_post_meta($page_id, '_yoast_wpseo_metadesc', $page_data['meta_description']);
                update_post_meta($page_id, 'meta_description', $page_data['meta_description']);
            }
        } else {
            echo "Error creating: {$page_data['title']}\n";
        }
    }

    echo "\nImport complete!\n";
}

/**
 * Parse a markdown file with YAML front matter
 */
function toolshed_parse_page_file($file) {
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
    if (empty($meta['title']) || empty($meta['slug'])) {
        return null;
    }

    // Convert markdown to HTML
    $html_content = toolshed_page_markdown_to_html($markdown_content);

    return [
        'title'            => $meta['title'],
        'slug'             => $meta['slug'],
        'template'         => $meta['template'] ?? 'page',
        'meta_description' => $meta['meta_description'] ?? '',
        'content'          => $html_content,
    ];
}

/**
 * Convert markdown to WordPress HTML
 */
function toolshed_page_markdown_to_html($markdown) {
    $html = $markdown;

    // Convert ### headings to <h3>
    $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);

    // Convert ## headings to <h2>
    $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);

    // Convert **bold** to <strong>
    $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);

    // Convert - list items to <ul><li>
    $html = preg_replace_callback(
        '/^(- .+\n?)+/m',
        function($matches) {
            $items = preg_replace('/^- (.+)$/m', '<li>$1</li>', trim($matches[0]));
            return '<ul>' . $items . '</ul>';
        },
        $html
    );

    // Convert [text](url) to links or buttons
    $html = preg_replace_callback(
        '/\[([^\]]+)\]\(([^)]+)\)/',
        function($matches) {
            $text = $matches[1];
            $url = $matches[2];
            // Make internal links buttons, external links regular
            if (strpos($url, '/') === 0) {
                return '<div class="wp-block-buttons"><div class="wp-block-button"><a class="wp-block-button__link" href="' . esc_url($url) . '">' . esc_html($text) . '</a></div></div>';
            }
            return '<a href="' . esc_url($url) . '">' . esc_html($text) . '</a>';
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
            $html .= '<p>' . $p . "</p>\n\n";
        }
    }

    return trim($html);
}

if (defined('WP_CLI') && WP_CLI) {
    toolshed_import_pages();
}
