#!/usr/bin/env python3
"""
Replace Amazon Search Links with Direct Product Links
Converts /s?k= search URLs to /dp/ASIN product URLs in markdown and HTML posts.
Preserves the affiliate tag (toolshedtested-20) on all links.
"""

import os
import re
import sys
from pathlib import Path
from asin_map import ASIN_MAP

# Configuration
AMAZON_TAG = os.environ.get('AMAZON_TAG', 'toolshedtested-20')
POSTS_DIR = Path(__file__).parent.parent / 'posts'

# Pattern to match Amazon search URLs in both markdown and HTML
# Matches: https://www.amazon.com/s?k=Some+Search+Term&tag=toolshedtested-20
SEARCH_LINK_PATTERN = re.compile(
    r'https?://(?:www\.)?amazon\.com/s\?k=([^&\s\)\]"\']+)(?:&tag=[^&\s\)\]"\']*)?(?:&[^&\s\)\]"\']*)*'
)


def build_product_url(asin):
    """Build a direct Amazon product URL with affiliate tag."""
    return f"https://www.amazon.com/dp/{asin}?tag={AMAZON_TAG}"


def replace_search_links_in_content(content, filename=""):
    """Replace all Amazon search links with direct product links."""
    replacements = []
    skipped = []

    def replace_match(match):
        full_url = match.group(0)
        search_term = match.group(1)

        # Look up ASIN in map
        asin = ASIN_MAP.get(search_term)

        if asin and asin != "NOT_ON_AMAZON":
            new_url = build_product_url(asin)
            replacements.append({
                'search_term': search_term.replace('+', ' '),
                'old_url': full_url,
                'new_url': new_url,
                'asin': asin
            })
            return new_url
        elif asin == "NOT_ON_AMAZON":
            skipped.append({
                'search_term': search_term.replace('+', ' '),
                'reason': 'Product not available on Amazon'
            })
            # Keep original URL but ensure it has the affiliate tag
            if f'tag={AMAZON_TAG}' not in full_url:
                separator = '&' if '?' in full_url else '?'
                return f'{full_url}{separator}tag={AMAZON_TAG}'
            return full_url
        else:
            skipped.append({
                'search_term': search_term.replace('+', ' '),
                'reason': 'ASIN not found in map'
            })
            return full_url

    new_content = SEARCH_LINK_PATTERN.sub(replace_match, content)
    return new_content, replacements, skipped


def process_file(filepath, dry_run=False):
    """Process a single file and replace search links."""
    content = filepath.read_text(encoding='utf-8')
    new_content, replacements, skipped = replace_search_links_in_content(
        content, filepath.name
    )

    if replacements:
        if not dry_run:
            filepath.write_text(new_content, encoding='utf-8')

        return {
            'file': filepath.name,
            'replacements': replacements,
            'skipped': skipped,
            'count': len(replacements)
        }

    if skipped:
        return {
            'file': filepath.name,
            'replacements': [],
            'skipped': skipped,
            'count': 0
        }

    return None


def main():
    dry_run = '--dry-run' in sys.argv

    print("=" * 60)
    print("AMAZON SEARCH LINK REPLACER - ToolShedTested")
    print(f"Affiliate tag: {AMAZON_TAG}")
    print(f"ASIN map entries: {len(ASIN_MAP)}")
    print(f"Mode: {'DRY RUN (no changes)' if dry_run else 'LIVE (making changes)'}")
    print("=" * 60)
    print()

    if not POSTS_DIR.exists():
        print(f"Error: Posts directory not found: {POSTS_DIR}")
        sys.exit(1)

    total_replacements = 0
    total_skipped = 0
    files_modified = 0
    all_skipped = []

    # Process both .md and .html files
    for ext in ['*.md', '*.html']:
        for filepath in sorted(POSTS_DIR.glob(ext)):
            result = process_file(filepath, dry_run)
            if result:
                if result['replacements']:
                    files_modified += 1
                    total_replacements += result['count']
                    print(f"\n{result['file']}: {result['count']} links replaced")
                    for r in result['replacements']:
                        print(f"  {r['search_term']}")
                        print(f"    -> /dp/{r['asin']}")

                if result['skipped']:
                    total_skipped += len(result['skipped'])
                    all_skipped.extend([
                        {**s, 'file': result['file']}
                        for s in result['skipped']
                    ])

    # Summary
    print()
    print("=" * 60)
    print("SUMMARY")
    print("-" * 30)
    print(f"Files modified: {files_modified}")
    print(f"Links replaced: {total_replacements}")
    print(f"Links skipped: {total_skipped}")

    if all_skipped:
        print(f"\nSKIPPED LINKS (need manual attention):")
        print("-" * 30)
        for s in all_skipped:
            print(f"  {s['file']}: {s['search_term']}")
            print(f"    Reason: {s['reason']}")

    if dry_run:
        print(f"\nDRY RUN - No files were modified")
        print("Run without --dry-run to apply changes")
    else:
        print(f"\nDone! {total_replacements} links replaced in {files_modified} files.")


if __name__ == "__main__":
    main()
