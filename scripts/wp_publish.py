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
            
            # Add nofollow, sponsored, and noopener for affiliate links
            link['rel'] = 'nofollow noopener sponsored'
            link['target'] = '_blank'
    
    return str(soup)

def enhance_product_sections(html_content):
    """Transform plain product sections into rich affiliate-optimized HTML.

    Detects product review patterns (heading + specs + pros/cons + single Amazon link)
    and wraps them in styled product boxes with multiple CTAs.
    """
    soup = BeautifulSoup(html_content, 'html.parser')

    # Find all h2 headings that look like numbered product entries (e.g. "1. DeWalt 20V MAX")
    product_headings = []
    for h2 in soup.find_all('h2'):
        text = h2.get_text()
        if re.match(r'^\d+\.\s+', text):
            product_headings.append(h2)

    for heading in product_headings:
        product_name = re.sub(r'^\d+\.\s+', '', heading.get_text()).strip()

        # Collect all sibling elements until the next h2
        section_elements = []
        amazon_link = None
        sibling = heading.find_next_sibling()

        while sibling and sibling.name != 'h2':
            section_elements.append(sibling)
            # Find the Amazon affiliate link in this section
            if sibling.name == 'p':
                link = sibling.find('a', href=lambda h: h and 'amazon.com' in h)
                if link:
                    amazon_link = link['href']
            sibling = sibling.find_next_sibling()

        if not amazon_link:
            continue

        # Build the enhanced product box wrapper
        product_box = soup.new_tag('div', attrs={'class': 'review-box product-section'})

        # Add a styled header with the product name
        box_header = soup.new_tag('div', attrs={'class': 'review-box-header'})
        product_box.append(box_header)

        # Move the heading into the box
        heading_copy = heading.extract()
        heading_copy['class'] = heading_copy.get('class', [])
        if isinstance(heading_copy['class'], str):
            heading_copy['class'] = [heading_copy['class']]
        heading_copy['class'].append('review-box-title')
        box_header.append(heading_copy)

        # Add a top CTA button right after the heading
        top_cta = soup.new_tag('div', attrs={'class': 'review-box-cta top-cta'})
        top_btn = soup.new_tag('a', href=amazon_link, attrs={
            'class': 'tst-btn tst-btn-amazon affiliate-link',
            'target': '_blank',
            'rel': 'nofollow noopener sponsored'
        })
        top_btn.string = f'Check Price on Amazon'
        top_cta.append(top_btn)
        box_header.append(top_cta)

        # Add the content section
        content_div = soup.new_tag('div', attrs={'class': 'review-box-content'})
        product_box.append(content_div)

        for elem in section_elements:
            extracted = elem.extract()
            # Replace the bare "Check Price on Amazon" paragraph with a styled CTA block
            if extracted.name == 'p' and extracted.find('a', href=lambda h: h and 'amazon.com' in h):
                cta_block = soup.new_tag('div', attrs={'class': 'product-cta-block'})

                primary_btn = soup.new_tag('a', href=amazon_link, attrs={
                    'class': 'tst-btn tst-btn-amazon affiliate-link',
                    'target': '_blank',
                    'rel': 'nofollow noopener sponsored'
                })
                primary_btn.string = 'Check Price on Amazon'
                cta_block.append(primary_btn)

                content_div.append(cta_block)
            else:
                content_div.append(extracted)

        # Add a bottom verdict CTA
        bottom_cta = soup.new_tag('div', attrs={'class': 'product-verdict-cta'})
        verdict_btn = soup.new_tag('a', href=amazon_link, attrs={
            'class': 'tst-btn tst-btn-cta affiliate-link',
            'target': '_blank',
            'rel': 'nofollow noopener sponsored'
        })
        verdict_btn.string = f'See {product_name} on Amazon →'
        bottom_cta.append(verdict_btn)
        product_box.append(bottom_cta)

        # Insert the product box where the heading was
        # Find the right insertion point
        if product_box.parent is None:
            # Insert before the next h2 or at the end
            next_h2 = None
            for h2 in soup.find_all('h2'):
                if h2.get_text() != heading_copy.get_text():
                    next_h2 = h2
                    break
            if next_h2:
                next_h2.insert_before(product_box)
            else:
                soup.append(product_box)

    return str(soup)


def add_disclosure_and_footer(html_content, title, category):
    """Add FTC disclosure at top and a summary CTA section at bottom."""

    # FTC disclosure banner at the top
    disclosure = (
        '<div class="affiliate-disclosure">'
        '<p><strong>Affiliate Disclosure:</strong> Toolshed Tested is reader-supported. '
        'When you buy through links on our site, we may earn an affiliate commission at '
        'no extra cost to you. This helps us continue testing and reviewing tools.</p>'
        '</div>\n\n'
    )

    # Bottom CTA section
    category_label = category.replace('-', ' ').title() if category else 'Tools'
    bottom_section = (
        f'\n\n<div class="post-footer-cta">'
        f'<h3>Ready to Buy?</h3>'
        f'<p>All of the {category_label.lower()} reviewed above have been hands-on tested by our team. '
        f'Click any "Check Price on Amazon" button above to see current pricing and availability.</p>'
        f'<p class="disclosure-reminder"><em>As an Amazon Associate, Toolshed Tested earns from qualifying purchases.</em></p>'
        f'</div>'
    )

    return disclosure + html_content + bottom_section


def add_inline_affiliate_mentions(html_content):
    """Add contextual affiliate text links within product description paragraphs.

    Finds paragraphs that mention a product by name near an Amazon link section
    and adds subtle inline links like "available on Amazon" where natural.
    """
    soup = BeautifulSoup(html_content, 'html.parser')

    # Find all product CTA blocks and get their Amazon URLs
    cta_blocks = soup.find_all('div', class_='product-cta-block')

    for cta in cta_blocks:
        link = cta.find('a', href=lambda h: h and 'amazon.com' in h)
        if not link:
            continue

        amazon_url = link['href']

        # Find the parent product section
        product_section = cta.find_parent('div', class_='review-box')
        if not product_section:
            continue

        # Find description paragraphs (not in specs or pros/cons)
        paragraphs = product_section.find_all('p')
        description_paras = []
        for p in paragraphs:
            text = p.get_text()
            # Skip very short paragraphs and ones already containing links
            if len(text) > 100 and not p.find('a'):
                description_paras.append(p)

        # Add an inline link to the first description paragraph
        if description_paras:
            p = description_paras[0]
            # Add a subtle inline mention at the end
            inline_link = soup.new_tag('a', href=amazon_url, attrs={
                'class': 'affiliate-link inline-affiliate',
                'target': '_blank',
                'rel': 'nofollow noopener sponsored'
            })
            inline_link.string = ' Check current pricing.'
            p.append(inline_link)

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

def get_or_create_product_category(category_name):
    """Get product_category taxonomy term ID, or create if it doesn't exist.

    Uses the custom 'product_category' taxonomy registered by the theme,
    accessible at /wp-json/wp/v2/product_category.
    """
    headers = get_auth_header()

    # Search for existing product category term
    response = requests.get(
        f"{API_BASE}/product_category",
        headers=headers,
        params={"search": category_name, "per_page": 100}
    )

    if response.status_code == 200:
        categories = response.json()
        for cat in categories:
            if cat['name'].lower() == category_name.lower():
                return cat['id']

    # Create new product category term
    response = requests.post(
        f"{API_BASE}/product_category",
        headers=headers,
        json={"name": category_name.title(), "slug": category_name.lower().replace(' ', '-')}
    )

    if response.status_code == 201:
        return response.json()['id']

    # Fallback: try standard categories if product_category endpoint not available
    print(f"Note: product_category endpoint failed, trying standard categories for '{category_name}'")
    return get_or_create_standard_category(category_name)


def get_or_create_standard_category(category_name):
    """Fallback: get standard category ID by name, or create if it doesn't exist"""
    headers = get_auth_header()

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


def check_post_exists(slug, post_type='product_review'):
    """Check if a post with this slug already exists"""
    headers = get_auth_header()

    # Determine the correct endpoint based on post type
    endpoint = f"{API_BASE}/product_review" if post_type == 'product_review' else f"{API_BASE}/posts"

    response = requests.get(
        endpoint,
        headers=headers,
        params={"slug": slug, "status": "any"}
    )

    if response.status_code == 200:
        posts = response.json()
        if posts:
            return posts[0]['id']

    # Also check the other post type in case it was published there before
    if post_type == 'product_review':
        response = requests.get(
            f"{API_BASE}/posts",
            headers=headers,
            params={"slug": slug, "status": "any"}
        )
        if response.status_code == 200:
            posts = response.json()
            if posts:
                print(f"Note: Found existing post as standard 'post' type (ID: {posts[0]['id']})")
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
    post_type = frontmatter.get('post_type', 'product_review')  # default to product_review CPT

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
    html_content = enhance_product_sections(html_content)
    html_content = process_affiliate_links(html_content)
    html_content = add_inline_affiliate_mentions(html_content)

    # Inject comparison table shortcode if compare_ids provided in frontmatter
    # Usage in frontmatter: compare_ids: "101,102,103" (product_review post IDs)
    compare_ids = frontmatter.get('compare_ids', '')
    if compare_ids:
        if isinstance(compare_ids, list):
            compare_ids = ','.join(str(i) for i in compare_ids)
        comparison_shortcode = f'\n[comparison_table ids="{compare_ids}"]\n'
        # Insert after the first heading (h1 or first h2)
        soup = BeautifulSoup(html_content, 'html.parser')
        first_heading = soup.find(['h1', 'h2'])
        if first_heading:
            # Insert after the first paragraph following the heading
            insert_point = first_heading.find_next_sibling('p')
            if insert_point:
                shortcode_tag = soup.new_tag('div')
                shortcode_tag.string = comparison_shortcode
                insert_point.insert_after(shortcode_tag)
                html_content = str(soup)
            else:
                html_content = comparison_shortcode + html_content
        else:
            html_content = comparison_shortcode + html_content

    html_content = add_disclosure_and_footer(html_content, title, category)

    # Get/create category and tags
    category_id = get_or_create_product_category(category)
    tag_ids = get_or_create_tags(tags)

    # Prepare post data
    post_data = {
        "title": title,
        "slug": slug,
        "content": html_content,
        "status": status,
        "date": date_str,
    }

    # Set taxonomy based on post type
    if post_type == 'product_review':
        if category_id:
            post_data["product_category"] = [category_id]
    else:
        if category_id:
            post_data["categories"] = [category_id]

    if tag_ids:
        post_data["tags"] = tag_ids

    # Build meta fields for product_review CPT
    meta = {}

    # Yoast/RankMath SEO meta
    if meta_description:
        meta["_yoast_wpseo_metadesc"] = meta_description
        meta["rank_math_description"] = meta_description

    # Product review meta fields (used by theme templates, top-picks table, review cards)
    if post_type == 'product_review':
        # Rating
        rating = frontmatter.get('rating', '')
        if rating:
            meta["_tst_rating"] = str(float(rating))

        # Price
        price = frontmatter.get('price', '')
        if price:
            meta["_tst_price"] = str(price)

        # Best For
        best_for = frontmatter.get('best_for', frontmatter.get('best-for', ''))
        if best_for:
            meta["_tst_best_for"] = best_for

        # Badge (bestseller, editors-choice, budget-pick, premium-pick)
        badge = frontmatter.get('badge', '')
        if badge:
            meta["_tst_badge"] = badge

        # Affiliate URL - extract from frontmatter or find the first Amazon link in content
        affiliate_url = frontmatter.get('affiliate_url', frontmatter.get('affiliate-url', ''))
        if not affiliate_url:
            # Try to find the first Amazon link in the content
            soup = BeautifulSoup(html_content, 'html.parser')
            amazon_link = soup.find('a', href=lambda h: h and 'amazon.com' in h)
            if amazon_link:
                affiliate_url = amazon_link['href']
        if affiliate_url:
            # Ensure tag is applied
            affiliate_url = re.sub(r'[?&]tag=[^&]*', '', affiliate_url)
            separator = '&' if '?' in affiliate_url else '?'
            affiliate_url = f"{affiliate_url}{separator}tag={AMAZON_TAG}"
            meta["_tst_affiliate_url"] = affiliate_url

        # Pros/Cons
        pros = frontmatter.get('pros', '')
        if isinstance(pros, list):
            pros = '\n'.join(pros)
        if pros:
            meta["_tst_pros"] = pros

        cons = frontmatter.get('cons', '')
        if isinstance(cons, list):
            cons = '\n'.join(cons)
        if cons:
            meta["_tst_cons"] = cons

    if meta:
        post_data["meta"] = meta

    headers = get_auth_header()

    # Determine the correct API endpoint
    endpoint = f"{API_BASE}/product_review" if post_type == 'product_review' else f"{API_BASE}/posts"

    # Check if post exists
    existing_id = check_post_exists(slug, post_type)

    if existing_id:
        # Update existing post (use the same endpoint regardless)
        # WordPress REST API updates at the posts endpoint work for any post type by ID
        update_endpoint = f"{API_BASE}/product_review/{existing_id}" if post_type == 'product_review' else f"{API_BASE}/posts/{existing_id}"
        response = requests.post(
            update_endpoint,
            headers=headers,
            json=post_data
        )
        action = "Updated"
    else:
        # Create new post
        response = requests.post(
            endpoint,
            headers=headers,
            json=post_data
        )
        action = "Created"

    if response.status_code in [200, 201]:
        post = response.json()
        print(f"✅ {action}: {title}")
        print(f"   URL: {post['link']}")
        print(f"   ID: {post['id']}")
        print(f"   Type: {post_type}")
        return post['id']
    else:
        print(f"❌ Failed to publish as {post_type}: {title}")
        print(f"   Status: {response.status_code}")
        print(f"   Error: {response.text[:500]}")

        # If product_review endpoint fails, fall back to standard posts
        if post_type == 'product_review':
            print(f"   Falling back to standard 'post' type...")
            post_data.pop("product_category", None)
            if category_id:
                post_data["categories"] = [category_id]

            response = requests.post(
                f"{API_BASE}/posts",
                headers=headers,
                json=post_data
            )
            if response.status_code in [200, 201]:
                post = response.json()
                print(f"✅ {action} (as standard post): {title}")
                print(f"   URL: {post['link']}")
                print(f"   ID: {post['id']}")
                return post['id']

        print(f"❌ Final failure. Could not publish: {title}")
        sys.exit(1)

def main():
    if len(sys.argv) < 2:
        print("Usage: python wp_publish.py <path-to-markdown-file>")
        print("       python wp_publish.py posts/best-cordless-drills.md")
        print("")
        print("Posts are published as 'product_review' custom post type by default.")
        print("Add 'post_type: post' to frontmatter to publish as standard post.")
        print("")
        print("Required environment variables:")
        print("  WP_URL          - WordPress site URL")
        print("  WP_USER         - WordPress username")
        print("  WP_APP_PASSWORD - WordPress application password")
        print("  AMAZON_TAG      - Amazon Associates tag (default: toolshedtested-20)")
        sys.exit(1)

    filepath = sys.argv[1]
    publish_post(filepath)

if __name__ == "__main__":
    main()
