#!/usr/bin/env python3
"""
Bulk Import Script for Toolshed Tested
Imports all markdown posts from /posts/ directory to WordPress
"""

import os
import sys
import time
from pathlib import Path

# Add scripts directory to path
sys.path.insert(0, str(Path(__file__).parent))

from wp_publish import publish_post, WP_URL, WP_USER, WP_APP_PASSWORD

def find_all_posts(posts_dir='posts'):
    """Find all markdown files in the posts directory"""
    posts_path = Path(posts_dir)
    
    if not posts_path.exists():
        print(f"Error: Posts directory not found: {posts_dir}")
        print("Make sure you're running from the repository root")
        sys.exit(1)
    
    markdown_files = list(posts_path.glob('**/*.md'))
    return sorted(markdown_files)

def bulk_import(posts_dir='posts', dry_run=False, delay=2):
    """Import all posts to WordPress"""
    
    # Validate environment
    if not all([WP_URL, WP_USER, WP_APP_PASSWORD]):
        print("Error: Missing WordPress credentials")
        print("")
        print("Set these environment variables:")
        print("  export WP_URL='https://toolshedtested.com'")
        print("  export WP_USER='your-username'")
        print("  export WP_APP_PASSWORD='your-app-password'")
        sys.exit(1)
    
    posts = find_all_posts(posts_dir)
    
    if not posts:
        print(f"No markdown files found in {posts_dir}/")
        sys.exit(0)
    
    print(f"Found {len(posts)} posts to import")
    print(f"Target: {WP_URL}")
    print("")
    
    if dry_run:
        print("DRY RUN - No posts will be published")
        print("")
        for post in posts:
            print(f"  Would import: {post}")
        return
    
    # Confirm before bulk import
    print("Posts to import:")
    for post in posts:
        print(f"  - {post.name}")
    print("")
    
    response = input("Proceed with import? (yes/no): ")
    if response.lower() not in ['yes', 'y']:
        print("Import cancelled")
        sys.exit(0)
    
    print("")
    print("=" * 50)
    print("Starting bulk import...")
    print("=" * 50)
    print("")
    
    successful = 0
    failed = 0
    failed_posts = []
    
    for i, post in enumerate(posts, 1):
        print(f"[{i}/{len(posts)}] Processing: {post.name}")
        
        try:
            publish_post(str(post))
            successful += 1
        except Exception as e:
            print(f"   ❌ Error: {e}")
            failed += 1
            failed_posts.append(post.name)
        
        # Rate limit to avoid overwhelming the server
        if i < len(posts):
            time.sleep(delay)
        
        print("")
    
    # Summary
    print("=" * 50)
    print("IMPORT COMPLETE")
    print("=" * 50)
    print(f"  Successful: {successful}")
    print(f"  Failed: {failed}")
    
    if failed_posts:
        print("")
        print("Failed posts:")
        for name in failed_posts:
            print(f"  - {name}")
    
    print("")
    print(f"View your posts at: {WP_URL}/blog/")

def main():
    import argparse
    
    parser = argparse.ArgumentParser(
        description='Bulk import markdown posts to WordPress'
    )
    parser.add_argument(
        '--dir', '-d',
        default='posts',
        help='Directory containing markdown files (default: posts)'
    )
    parser.add_argument(
        '--dry-run', '-n',
        action='store_true',
        help='Show what would be imported without actually importing'
    )
    parser.add_argument(
        '--delay',
        type=int,
        default=2,
        help='Seconds to wait between posts (default: 2)'
    )
    
    args = parser.parse_args()
    bulk_import(args.dir, args.dry_run, args.delay)

if __name__ == "__main__":
    main()
