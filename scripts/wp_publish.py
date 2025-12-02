#!/usr/bin/env python3
"""
WordPress Publishing Script for Toolshed Tested
Converts markdown posts to WordPress via REST API
"""

import os
import sys
import re
import json
import base64
import requests
import yaml
import markdown
from pathlib import Path
from bs4 import BeautifulSoup
from datetime import datetime

# Configuration from environment variables
WP_URL = os.environ.get('WP_URL', '').rstrip('/')
WP_USER = os.environ.get('WP_USER', '')
WP_APP_PASSWORD = os.environ.get('WP_APP_PASSWORD', '')
AMAZON_TAG = os.environ.get('AMAZON_TAG', 'toolshedtested-20')

# WordPress REST API endpoints
API_BASE = f"{WP_URL}/wp-json/wp/v2"

def get_auth_header():
    """Create Basic Auth header for WordPress REST API"""
    credentials = f"{WP_USER}:{WP_APP_PASSWORD}"
    token = base64.b64encode(credentials.encode()).decode()
    return {"Authorization": f"Basic {token}"}

def parse_frontmatter(content):
    """Extract YAML frontmatter and markdown content from file"""
    if content.startswith('---'):
        parts = content.split('---', 2)
        if len(parts) >= 3:
            frontmatter = yaml.safe_load(parts[1])
            body = parts[2].strip()
            return frontmatter, body
    return {}, content

def process_affiliate_links(html_content):
    """Ensure all Amazon links have the correct affiliate tag"""
    soup = BeautifulSoup(html_content, 'html.parser')
    
    for link in soup.find_all('a', href=True):
        href = link['href']
        
        # Process Amazon links
        if 'amazon.com' in href:
            # Remove any existing tag parameter
            href = re.sub(r'[?&]tag=[^&]*', '', href)
            # Add our affiliate tag
            separator = '&' if '?' in href else '?'
            link['href'] = f"{href}{separator}tag={AMAZON_TAG}"
            
            # Add nofollow and target blank for affiliate links
            link['rel'] = 'nofollow sponsored'
            link['target'] = '_blank'
    
    return str(soup)

def convert_markdown_to_html(markdown_content):
    """Convert markdown to HTML with extensions for tables, code, etc."""
    md = markdown.Markdown(extensions=[
        'tables',
        'fenced_code',
        'codehilite',
        'toc',
        'attr_list',
        'md_in_html'
    ])
    return md.convert(markdown_content)

def get_or_create_category(category_name):
    """Get category ID by name, or create if it doesn't exist"""
    headers = get_auth_header()
    
    # Search for existing category
    response = requests.get(
        f"{API_BASE}/categories",
        headers=headers,
        params={"search": category_name, "per_page": 100}
    )
    
    if response.status_code == 200:
        categories = response.json()
        for cat in categories:
            if cat['name'].lower() == category_name.lower():
                return cat['id']
    
    # Create new category
    response = requests.post(
        f"{API_BASE}/categories",
        headers=headers,
        json={"name": category_name.title()}
    )
    
    if response.status_code == 201:
        return response.json()['id']
    
    print(f"Warning: Could not create category '{category_name}'")
    return None

def get_or_create_tags(tag_names):
    """Get tag IDs by names, creating any that don't exist"""
    if not tag_names:
        return []
    
    headers = get_auth_header()
    tag_ids = []
    
    for tag_name in tag_names:
        # Search for existing tag
        response = requests.get(
            f"{API_BASE}/tags",
            headers=headers,
            params={"search": tag_name, "per_page": 100}
        )
        
        found = False
        if response.status_code == 200:
            tags = response.json()
            for tag in tags:
                if tag['name'].lower() == tag_name.lower():
                    tag_ids.append(tag['id'])
                    found = True
                    break
        
        if not found:
            # Create new tag
            response = requests.post(
                f"{API_BASE}/tags",
                headers=headers,
                json={"name": tag_name}
            )
            if response.status_code == 201:
                tag_ids.append(response.json()['id'])
    
    return tag_ids

def check_post_exists(slug):
    """Check if a post with this slug already exists"""
    headers = get_auth_header()
    response = requests.get(
        f"{API_BASE}/posts",
        headers=headers,
        params={"slug": slug, "status": "any"}
    )
    
    if response.status_code == 200:
        posts = response.json()
        if posts:
            return posts[0]['id']
    return None

def publish_post(filepath):
    """Publish a markdown file to WordPress"""
    
    # Validate environment
    if not all([WP_URL, WP_USER, WP_APP_PASSWORD]):
        print("Error: Missing WordPress credentials in environment variables")
        print("Required: WP_URL, WP_USER, WP_APP_PASSWORD")
        sys.exit(1)
    
    # Read and parse the file
    filepath = Path(filepath)
    if not filepath.exists():
        print(f"Error: File not found: {filepath}")
        sys.exit(1)
    
    content = filepath.read_text(encoding='utf-8')
    frontmatter, markdown_body = parse_frontmatter(content)
    
    # Extract metadata
    title = frontmatter.get('title', filepath.stem.replace('-', ' ').title())
    slug = frontmatter.get('slug', filepath.stem)
    category = frontmatter.get('category', 'uncategorized')
    tags = frontmatter.get('tags', [])
    meta_description = frontmatter.get('meta_description', '')
    featured_image = frontmatter.get('featured_image', '')
    status = frontmatter.get('status', 'publish')  # publish, draft, pending
    
    # Handle date
    date = frontmatter.get('date')
    if isinstance(date, datetime):
        date_str = date.isoformat()
    elif date:
        date_str = str(date)
    else:
        date_str = datetime.now().isoformat()
    
    # Convert content
    html_content = convert_markdown_to_html(markdown_body)
    html_content = process_affiliate_links(html_content)
    
    # Get/create category and tags
    category_id = get_or_create_category(category)
    tag_ids = get_or_create_tags(tags)
    
    # Prepare post data
    post_data = {
        "title": title,
        "slug": slug,
        "content": html_content,
        "status": status,
        "date": date_str,
    }
    
    if category_id:
        post_data["categories"] = [category_id]
    
    if tag_ids:
        post_data["tags"] = tag_ids
    
    # Add Yoast/RankMath meta if available
    if meta_description:
        post_data["meta"] = {
            "_yoast_wpseo_metadesc": meta_description,
            "rank_math_description": meta_description
        }
    
    headers = get_auth_header()
    
    # Check if post exists
    existing_id = check_post_exists(slug)
    
    if existing_id:
        # Update existing post
        response = requests.post(
            f"{API_BASE}/posts/{existing_id}",
            headers=headers,
            json=post_data
        )
        action = "Updated"
    else:
        # Create new post
        response = requests.post(
            f"{API_BASE}/posts",
            headers=headers,
            json=post_data
        )
        action = "Created"
    
    if response.status_code in [200, 201]:
        post = response.json()
        print(f"✅ {action}: {title}")
        print(f"   URL: {post['link']}")
        print(f"   ID: {post['id']}")
        return post['id']
    else:
        print(f"❌ Failed to publish: {title}")
        print(f"   Status: {response.status_code}")
        print(f"   Error: {response.text[:500]}")
        sys.exit(1)

def main():
    if len(sys.argv) < 2:
        print("Usage: python wp-publish.py <path-to-markdown-file>")
        print("       python wp-publish.py posts/best-cordless-drills.md")
        sys.exit(1)
    
    filepath = sys.argv[1]
    publish_post(filepath)

if __name__ == "__main__":
    main()
