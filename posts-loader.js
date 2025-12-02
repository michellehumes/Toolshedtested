// Toolshed Tested - Dynamic Post Loading
// This script fetches posts from the GitHub repo and displays them on the homepage

const GITHUB_API = 'https://api.github.com/repos/michellehumes/Toolshedtested/contents/posts';
const RAW_BASE = 'https://raw.githubusercontent.com/michellehumes/Toolshedtested/main/posts/';

// Parse YAML frontmatter from markdown
function parseFrontmatter(content) {
    const match = content.match(/^---\n([\s\S]*?)\n---/);
    if (!match) return { title: 'Untitled', excerpt: '', category: 'general', rating: 4.5 };
    
    const frontmatter = {};
    match[1].split('\n').forEach(line => {
        const [key, ...valueParts] = line.split(':');
        if (key && valueParts.length) {
            let value = valueParts.join(':').trim();
            // Remove quotes
            value = value.replace(/^["']|["']$/g, '');
            frontmatter[key.trim()] = value;
        }
    });
    return frontmatter;
}

// Generate category icon
function getCategoryIcon(category) {
    const icons = {
        'drills': '🔧',
        'saws': '🪚',
        'outdoor': '🌲',
        'generators': '⚡',
        'power-tools': '🔌',
        'hand-tools': '🛠️',
        'welding': '🔥',
        'automotive': '🚗',
        'storage': '📦',
        'safety': '🦺',
        'comparisons': '⚖️',
        'budget': '💰',
        'guides': '📖',
        'gift-guides': '🎁',
        'deals': '🏷️'
    };
    return icons[category] || '🔧';
}

// Format slug to readable title
function formatTitle(slug) {
    return slug
        .replace(/-/g, ' ')
        .replace(/\b\w/g, l => l.toUpperCase())
        .replace(/^Best /, 'Best ');
}

// Create post card HTML
function createPostCard(post, featured = false) {
    const icon = getCategoryIcon(post.category);
    const rating = post.rating || 4.5;
    const stars = '★'.repeat(Math.floor(rating)) + (rating % 1 >= 0.5 ? '½' : '');
    
    if (featured) {
        return `
            <article class="featured-post">
                <div class="featured-badge">Featured Review</div>
                <div class="featured-content">
                    <span class="post-category">${icon} ${post.category || 'Tools'}</span>
                    <h2 class="featured-title">
                        <a href="/posts/${post.slug}.html">${post.title}</a>
                    </h2>
                    <p class="featured-excerpt">${post.excerpt || 'Expert tested and reviewed. Find the best tools for your projects.'}</p>
                    <div class="post-meta">
                        <span class="post-rating">${stars} ${rating}/5</span>
                        <span class="post-date">${post.date || 'December 2025'}</span>
                    </div>
                    <a href="/posts/${post.slug}.html" class="btn btn-primary">Read Full Review →</a>
                </div>
            </article>
        `;
    }
    
    return `
        <article class="post-card">
            <div class="post-card-header">
                <span class="post-category">${icon} ${post.category || 'Tools'}</span>
                <span class="post-rating">${stars}</span>
            </div>
            <h3 class="post-title">
                <a href="/posts/${post.slug}.html">${post.title}</a>
            </h3>
            <p class="post-excerpt">${post.excerpt || 'Expert review with top picks and buying guide.'}</p>
            <div class="post-footer">
                <span class="post-date">${post.date || 'Dec 2025'}</span>
                <a href="/posts/${post.slug}.html" class="read-more">Read More →</a>
            </div>
        </article>
    `;
}

// Fetch and display posts
async function loadPosts() {
    const postsContainer = document.getElementById('posts-grid');
    const featuredContainer = document.getElementById('featured-post');
    
    if (!postsContainer) return;
    
    try {
        // Fetch list of posts from GitHub API
        const response = await fetch(GITHUB_API);
        const files = await response.json();
        
        // Filter for markdown files and sort by name (newest naming convention first)
        const mdFiles = files
            .filter(f => f.name.endsWith('.md'))
            .sort((a, b) => b.size - a.size); // Larger files (more detailed) first
        
        // Fetch content for each post
        const posts = [];
        for (const file of mdFiles.slice(0, 12)) { // Load first 12 posts
            try {
                const contentRes = await fetch(file.download_url);
                const content = await contentRes.text();
                const frontmatter = parseFrontmatter(content);
                
                posts.push({
                    slug: file.name.replace('.md', ''),
                    title: frontmatter.title || formatTitle(file.name.replace('.md', '')),
                    excerpt: frontmatter.excerpt || '',
                    category: frontmatter.category || 'power-tools',
                    rating: parseFloat(frontmatter.rating) || 4.5,
                    date: frontmatter.date || 'December 2025',
                    size: file.size
                });
            } catch (e) {
                console.error('Error loading post:', file.name, e);
            }
        }
        
        // Sort by size (larger = more detailed reviews) and rating
        posts.sort((a, b) => (b.size * b.rating) - (a.size * a.rating));
        
        // Display featured post
        if (featuredContainer && posts.length > 0) {
            featuredContainer.innerHTML = createPostCard(posts[0], true);
        }
        
        // Display remaining posts in grid
        if (posts.length > 1) {
            postsContainer.innerHTML = posts.slice(1, 10).map(p => createPostCard(p)).join('');
        }
        
        // Update post count in stats
        const postCountEl = document.getElementById('post-count');
        if (postCountEl) {
            postCountEl.textContent = files.filter(f => f.name.endsWith('.md')).length;
        }
        
    } catch (error) {
        console.error('Error loading posts:', error);
        postsContainer.innerHTML = `
            <div class="error-message">
                <p>Unable to load posts. Please refresh the page.</p>
            </div>
        `;
    }
}

// Load posts when DOM is ready
document.addEventListener('DOMContentLoaded', loadPosts);

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menu-toggle');
    const mainNav = document.getElementById('main-nav');
    
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function() {
            mainNav.classList.toggle('nav-open');
            this.classList.toggle('menu-open');
        });
    }
    
    // Search toggle
    const searchToggle = document.querySelector('.search-toggle');
    const searchOverlay = document.getElementById('search-overlay');
    
    if (searchToggle && searchOverlay) {
        searchToggle.addEventListener('click', function() {
            searchOverlay.classList.toggle('active');
        });
    }
});
