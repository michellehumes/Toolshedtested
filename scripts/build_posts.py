#!/usr/bin/env python3
"""
Build script to convert markdown posts to HTML pages.
Reads markdown files from /posts/ and generates HTML in /posts/ directory.
"""

import os
import re
from pathlib import Path

# Paths
SCRIPT_DIR = Path(__file__).parent
PROJECT_ROOT = SCRIPT_DIR.parent
POSTS_DIR = PROJECT_ROOT / "posts"
OUTPUT_DIR = PROJECT_ROOT / "posts"

# HTML template
HTML_TEMPLATE = '''<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{excerpt}">
    <title>{title} - Toolshed Tested</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles-new.css">
    <style>
        .post-content {{ max-width: 800px; margin: 0 auto; padding: 2rem 1rem; }}
        .post-content h1 {{ font-family: var(--font-heading); font-size: 2.25rem; margin-bottom: 1rem; color: var(--gray-900); }}
        .post-content h2 {{ font-family: var(--font-heading); font-size: 1.5rem; margin: 2rem 0 1rem; color: var(--gray-800); border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem; }}
        .post-content h3 {{ font-size: 1.25rem; margin: 1.5rem 0 0.75rem; color: var(--gray-800); }}
        .post-content p {{ margin-bottom: 1rem; color: var(--gray-700); line-height: 1.7; }}
        .post-content ul, .post-content ol {{ margin: 1rem 0; padding-left: 1.5rem; color: var(--gray-700); }}
        .post-content li {{ margin-bottom: 0.5rem; }}
        .post-content a {{ color: var(--primary); text-decoration: none; font-weight: 500; }}
        .post-content a:hover {{ text-decoration: underline; }}
        .post-content strong {{ color: var(--gray-900); }}
        .amazon-btn {{ display: inline-block; background: #FF9900; color: #111 !important; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; margin: 1rem 0; text-decoration: none !important; }}
        .amazon-btn:hover {{ background: #E88B00; text-decoration: none !important; }}
        .post-meta {{ color: var(--gray-500); font-size: 0.9rem; margin-bottom: 2rem; }}
        .back-link {{ display: inline-block; margin-bottom: 1rem; color: var(--primary); text-decoration: none; }}
        .back-link:hover {{ text-decoration: underline; }}
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="/" class="logo">
                <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
                <span class="logo-text">Toolshed <span class="logo-accent">Tested</span></span>
            </a>
            <nav class="main-nav" id="main-nav">
                <ul class="nav-menu">
                    <li class="nav-item"><a href="/" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="/about.html" class="nav-link">About</a></li>
                    <li class="nav-item"><a href="/disclosure.html" class="nav-link">Disclosure</a></li>
                    <li class="nav-item"><a href="/contact.html" class="nav-link">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="post-content">
            <a href="/" class="back-link">&larr; Back to Reviews</a>
            <article>
                {content}
            </article>
            <div style="margin-top: 3rem; padding: 1.5rem; background: var(--gray-50); border-radius: 8px; border-left: 4px solid var(--primary);">
                <p style="margin: 0; font-size: 0.9rem; color: var(--gray-600);"><strong>Affiliate Disclosure:</strong> As an Amazon Associate, we earn from qualifying purchases. This helps support our testing and keeps the site running.</p>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-bottom" style="padding-top: 1rem; border: none;">
                <p>&copy; 2025 Toolshed Tested. All rights reserved.</p>
                <p class="affiliate-disclosure">As an Amazon Associate, we earn from qualifying purchases.</p>
            </div>
        </div>
    </footer>
</body>
</html>
'''

def parse_frontmatter(content):
    """Extract YAML frontmatter from markdown content."""
    # Handle code-fenced frontmatter
    content = content.replace('```yaml\n---', '---').replace('---\n```', '---')

    match = re.match(r'^---\n(.*?)\n---\n(.*)$', content, re.DOTALL)
    if not match:
        return {}, content

    frontmatter_str = match.group(1)
    body = match.group(2)

    frontmatter = {}
    for line in frontmatter_str.split('\n'):
        if ':' in line:
            key, value = line.split(':', 1)
            value = value.strip().strip('"').strip("'")
            frontmatter[key.strip()] = value

    return frontmatter, body


def markdown_to_html(md_content):
    """Convert markdown to HTML (simple implementation)."""
    html = md_content

    # Convert headers
    html = re.sub(r'^### (.+)$', r'<h3>\1</h3>', html, flags=re.MULTILINE)
    html = re.sub(r'^## (.+)$', r'<h2>\1</h2>', html, flags=re.MULTILINE)
    html = re.sub(r'^# (.+)$', r'<h1>\1</h1>', html, flags=re.MULTILINE)

    # Convert bold
    html = re.sub(r'\*\*(.+?)\*\*', r'<strong>\1</strong>', html)

    # Convert italic
    html = re.sub(r'\*(.+?)\*', r'<em>\1</em>', html)

    # Convert Amazon links to buttons
    html = re.sub(
        r'\[Check Price on Amazon\]\((https://www\.amazon\.com[^)]+)\)',
        r'<a href="\1" class="amazon-btn" rel="nofollow noopener sponsored" target="_blank">Check Price on Amazon</a>',
        html
    )
    html = re.sub(
        r'\[Buy on Amazon\]\((https://www\.amazon\.com[^)]+)\)',
        r'<a href="\1" class="amazon-btn" rel="nofollow noopener sponsored" target="_blank">Buy on Amazon</a>',
        html
    )

    # Convert other links
    html = re.sub(r'\[([^\]]+)\]\(([^)]+)\)', r'<a href="\2">\1</a>', html)

    # Convert lists
    lines = html.split('\n')
    in_list = False
    new_lines = []
    for line in lines:
        if line.strip().startswith('- '):
            if not in_list:
                new_lines.append('<ul>')
                in_list = True
            new_lines.append(f'<li>{line.strip()[2:]}</li>')
        else:
            if in_list:
                new_lines.append('</ul>')
                in_list = False
            new_lines.append(line)
    if in_list:
        new_lines.append('</ul>')
    html = '\n'.join(new_lines)

    # Convert paragraphs (lines that don't start with HTML tags)
    lines = html.split('\n')
    new_lines = []
    for line in lines:
        stripped = line.strip()
        if stripped and not stripped.startswith('<') and not stripped.startswith('|'):
            new_lines.append(f'<p>{stripped}</p>')
        else:
            new_lines.append(line)

    return '\n'.join(new_lines)


def build_post(md_file):
    """Build a single post from markdown to HTML."""
    with open(md_file, 'r', encoding='utf-8') as f:
        content = f.read()

    frontmatter, body = parse_frontmatter(content)

    title = frontmatter.get('title', md_file.stem.replace('-', ' ').title())
    excerpt = frontmatter.get('excerpt', '')

    html_content = markdown_to_html(body)

    # Add title as H1 if not already present
    if not html_content.strip().startswith('<h1>'):
        html_content = f'<h1>{title}</h1>\n{html_content}'

    full_html = HTML_TEMPLATE.format(
        title=title,
        excerpt=excerpt,
        content=html_content
    )

    output_file = OUTPUT_DIR / f"{md_file.stem}.html"
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write(full_html)

    print(f"Built: {output_file.name}")
    return output_file


def main():
    """Build all posts."""
    if not POSTS_DIR.exists():
        print(f"Error: Posts directory not found: {POSTS_DIR}")
        return

    md_files = list(POSTS_DIR.glob("*.md"))
    print(f"Found {len(md_files)} markdown files")

    for md_file in md_files:
        try:
            build_post(md_file)
        except Exception as e:
            print(f"Error building {md_file.name}: {e}")

    print(f"\nBuilt {len(md_files)} HTML files in {OUTPUT_DIR}")


if __name__ == "__main__":
    main()
