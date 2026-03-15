#!/usr/bin/env python3
"""
Category Consolidation Script for ToolShed Tested

This script consolidates overlapping WordPress categories by:
1. Merging posts from source categories into target categories
2. Setting up redirect rules for old category URLs
3. Optionally deleting empty source categories

Usage:
    python scripts/consolidate_categories.py --dry-run
    python scripts/consolidate_categories.py --execute

Requires: WP REST API access configured in .env
"""

import os
import sys
import json
import argparse
import requests
from dotenv import load_dotenv

load_dotenv()

WP_URL = os.getenv("WP_URL", "https://toolshedtested.com")
WP_USER = os.getenv("WP_USER", "")
WP_APP_PASSWORD = os.getenv("WP_APP_PASSWORD", "")

# Category merge map: source categories -> target category
# Format: { "source_slug": "target_slug" }
MERGE_MAP = {
    # Merge overlapping outdoor categories
    "outdoor": "outdoor-tools",
    "lawn-and-garden": "outdoor-tools",

    # Merge guide categories
    "guides": "buying-guides",
    "home-improvement": "buying-guides",

    # Move leaf-related into outdoor
    "leaf-vacuums": "outdoor-tools",
    "wood-chippers": "outdoor-tools",
    "log-splitters": "outdoor-tools",
    "tillers": "outdoor-tools",
    "pole-saws": "outdoor-tools",

    # Consolidate saws subcategories are fine as children
    # (table-saws, miter-saws already under saws/)

    # Merge shop-related
    "shop-vacs": "workshop",
}

# Categories to delete after merging (empty ones)
CATEGORIES_TO_DELETE = [
    "uncategorized",
]


def get_categories():
    """Fetch all categories from WordPress."""
    categories = []
    page = 1
    while True:
        resp = requests.get(
            f"{WP_URL}/wp-json/wp/v2/categories",
            params={"per_page": 100, "page": page},
            auth=(WP_USER, WP_APP_PASSWORD) if WP_USER else None,
        )
        if resp.status_code != 200:
            break
        data = resp.json()
        if not data:
            break
        categories.extend(data)
        page += 1
    return categories


def get_posts_in_category(category_id):
    """Get all posts in a category."""
    posts = []
    page = 1
    while True:
        resp = requests.get(
            f"{WP_URL}/wp-json/wp/v2/posts",
            params={"categories": category_id, "per_page": 100, "page": page},
            auth=(WP_USER, WP_APP_PASSWORD) if WP_USER else None,
        )
        if resp.status_code != 200:
            break
        data = resp.json()
        if not data:
            break
        posts.extend(data)
        page += 1
    return posts


def move_posts(post_ids, source_cat_id, target_cat_id, dry_run=True):
    """Move posts from source category to target category."""
    for post_id in post_ids:
        # Get current categories
        resp = requests.get(
            f"{WP_URL}/wp-json/wp/v2/posts/{post_id}",
            auth=(WP_USER, WP_APP_PASSWORD) if WP_USER else None,
        )
        if resp.status_code != 200:
            print(f"  [ERROR] Could not fetch post {post_id}")
            continue

        post = resp.json()
        current_cats = post.get("categories", [])

        # Remove source, add target
        new_cats = [c for c in current_cats if c != source_cat_id]
        if target_cat_id not in new_cats:
            new_cats.append(target_cat_id)

        if dry_run:
            print(f"  [DRY RUN] Post {post_id}: {current_cats} -> {new_cats}")
        else:
            resp = requests.post(
                f"{WP_URL}/wp-json/wp/v2/posts/{post_id}",
                json={"categories": new_cats},
                auth=(WP_USER, WP_APP_PASSWORD),
            )
            if resp.status_code == 200:
                print(f"  [OK] Post {post_id} moved")
            else:
                print(f"  [ERROR] Post {post_id}: {resp.status_code}")


def main():
    parser = argparse.ArgumentParser(description="Consolidate WordPress categories")
    parser.add_argument("--dry-run", action="store_true", default=True, help="Preview changes without executing")
    parser.add_argument("--execute", action="store_true", help="Execute the merge")
    args = parser.parse_args()

    dry_run = not args.execute

    if dry_run:
        print("=== DRY RUN MODE (use --execute to apply changes) ===\n")
    else:
        print("=== EXECUTING CATEGORY CONSOLIDATION ===\n")

    # Fetch all categories
    categories = get_categories()
    cat_by_slug = {c["slug"]: c for c in categories}

    print(f"Found {len(categories)} categories\n")

    # Show current category stats
    print("Current category post counts:")
    for cat in sorted(categories, key=lambda c: c["count"], reverse=True):
        print(f"  {cat['slug']}: {cat['count']} posts")

    print(f"\n--- Merge Operations ---\n")

    redirect_rules = []

    for source_slug, target_slug in MERGE_MAP.items():
        source = cat_by_slug.get(source_slug)
        target = cat_by_slug.get(target_slug)

        if not source:
            print(f"[SKIP] Source '{source_slug}' not found")
            continue
        if not target:
            print(f"[SKIP] Target '{target_slug}' not found")
            continue

        posts = get_posts_in_category(source["id"])
        print(f"\nMerge: {source_slug} ({source['count']} posts) -> {target_slug}")

        if posts:
            post_ids = [p["id"] for p in posts]
            move_posts(post_ids, source["id"], target["id"], dry_run)

        # Generate redirect rule
        redirect_rules.append({
            "from": f"/category/{source_slug}/",
            "to": f"/category/{target_slug}/",
        })

    # Output redirect rules for .htaccess or functions.php
    print(f"\n--- Category Redirect Rules ---\n")
    print("Add to .htaccess or functions.php:\n")
    for rule in redirect_rules:
        print(f"  '{rule['from'].strip('/')}' => '{rule['to'].strip('/')}',")

    # Categories to delete
    print(f"\n--- Categories to Delete ---\n")
    for slug in CATEGORIES_TO_DELETE:
        cat = cat_by_slug.get(slug)
        if cat:
            print(f"  {slug} (ID: {cat['id']}, posts: {cat['count']})")
            if cat["count"] > 0:
                print(f"    WARNING: Category has {cat['count']} posts - reassign first!")

    print("\n=== Done ===")


if __name__ == "__main__":
    main()
