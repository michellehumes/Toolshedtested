#!/usr/bin/env python3
"""
Fix Affiliate Tags Script for ToolShedTested
Replaces incorrect affiliate tags with the correct one.
"""

import os
import re
import sys
from pathlib import Path

# Configuration
CORRECT_TAG = os.environ.get('AMAZON_TAG', 'toolshedtested-20')
POSTS_DIR = Path(__file__).parent.parent / 'posts'
PAGES_DIR = Path(__file__).parent.parent / 'pages'

# Known incorrect tags to replace
INCORRECT_TAGS = [
    'SHELZYSDESIGNS-20',
    'shelzysdesigns-20',
    'YOUR-TAG-HERE',
    'your-tag-here',
    'AFFILIATE-TAG',
    'affiliate-tag',
]


def fix_tags_in_file(filepath, dry_run=False):
    """Fix affiliate tags in a single file."""
    content = filepath.read_text(encoding='utf-8')
    original_content = content
    changes = []

    # Replace each incorrect tag
    for wrong_tag in INCORRECT_TAGS:
        if wrong_tag in content:
            changes.append(f"  Replaced: {wrong_tag} -> {CORRECT_TAG}")
            content = content.replace(f'tag={wrong_tag}', f'tag={CORRECT_TAG}')

    # Also fix any missing tags on Amazon URLs
    # Pattern: amazon.com URL without tag parameter
    amazon_pattern = re.compile(
        r'(https?://(?:www\.)?amazon\.com/[^\s\)\]"\']+)(?!\?tag=|&tag=)',
        re.IGNORECASE
    )

    def add_tag(match):
        url = match.group(1)
        # Don't add if already has a tag parameter anywhere
        if 'tag=' in url:
            return url
        separator = '&' if '?' in url else '?'
        return f'{url}{separator}tag={CORRECT_TAG}'

    new_content = amazon_pattern.sub(add_tag, content)
    if new_content != content:
        changes.append(f"  Added missing tag to Amazon URLs")
        content = new_content

    # Save if changes were made
    if content != original_content:
        if not dry_run:
            filepath.write_text(content, encoding='utf-8')
        return changes

    return None


def process_directory(directory, dry_run=False):
    """Process all markdown files in a directory."""
    if not directory.exists():
        return []

    results = []
    for md_file in directory.glob('*.md'):
        changes = fix_tags_in_file(md_file, dry_run)
        if changes:
            results.append({
                'file': md_file.name,
                'changes': changes
            })

    return results


def main():
    dry_run = '--dry-run' in sys.argv

    print("=" * 60)
    print("AFFILIATE TAG FIXER - ToolShedTested")
    print(f"Correct tag: {CORRECT_TAG}")
    print(f"Mode: {'DRY RUN (no changes)' if dry_run else 'LIVE (making changes)'}")
    print("=" * 60)
    print()

    all_results = []

    # Process posts
    print(f"Processing posts directory: {POSTS_DIR}")
    results = process_directory(POSTS_DIR, dry_run)
    all_results.extend(results)

    # Process pages
    print(f"Processing pages directory: {PAGES_DIR}")
    results = process_directory(PAGES_DIR, dry_run)
    all_results.extend(results)

    # Report
    print()
    if all_results:
        print(f"FILES MODIFIED: {len(all_results)}")
        print("-" * 40)
        for result in all_results:
            print(f"\n{result['file']}:")
            for change in result['changes']:
                print(change)

        if dry_run:
            print("\n⚠️  DRY RUN - No files were modified")
            print("   Run without --dry-run to apply changes")
        else:
            print(f"\n✅ Fixed affiliate tags in {len(all_results)} files")
    else:
        print("✅ All affiliate tags are correct - no changes needed")


if __name__ == "__main__":
    main()
