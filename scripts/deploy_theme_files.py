#!/usr/bin/env python3
"""
Deploy changed theme files to Hostinger via WordPress REST API
Uses the WP Theme File Editor API to update individual files.
"""

import base64
import requests
import urllib3
import os
import sys

# Disable SSL warnings (Hostinger IP-based access)
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# Configuration from publish_to_hostinger.py
HOSTINGER_IP = "82.25.87.19"
DOMAIN = "toolshedtested.com"
WP_USER = "michelle.e.humes@gmail.com"
APP_PASS = "KddKwVPv7Vh10ny3MZNv4CsZ"
THEME_SLUG = "toolshed-tested"

API_BASE = f"https://{HOSTINGER_IP}/wp-json/wp/v2"

def get_headers():
    credentials = f"{WP_USER}:{APP_PASS}"
    token = base64.b64encode(credentials.encode()).decode()
    return {
        "Authorization": f"Basic {token}",
        "Host": DOMAIN,
        "Content-Type": "application/json"
    }

def test_connection():
    """Verify API access"""
    response = requests.get(
        f"{API_BASE}/users/me",
        headers=get_headers(),
        verify=False
    )
    if response.status_code == 200:
        user = response.json()
        print(f"Connected as: {user.get('name')}")
        return True
    else:
        print(f"Auth failed: {response.status_code}")
        return False

def update_theme_file(file_path, content):
    """Update a single theme file via the WP REST API theme editor"""
    # WordPress theme file editing endpoint
    url = f"https://{HOSTINGER_IP}/wp-json/wp/v2/themes/{THEME_SLUG}"

    headers = get_headers()

    payload = {
        "stylesheet": THEME_SLUG,
        "file": file_path,
        "newcontent": content
    }

    response = requests.post(
        url,
        headers=headers,
        json=payload,
        verify=False
    )

    return response

def update_theme_file_via_editor(file_path, content):
    """Fallback: Update file via the wp-admin theme editor AJAX endpoint"""
    url = f"https://{HOSTINGER_IP}/wp-admin/admin-ajax.php"

    credentials = f"{WP_USER}:{APP_PASS}"
    token = base64.b64encode(credentials.encode()).decode()

    headers = {
        "Authorization": f"Basic {token}",
        "Host": DOMAIN,
    }

    # First try getting a nonce
    # This may not work with app passwords, but worth trying
    data = {
        "action": "edit-theme-plugin-file",
        "theme": THEME_SLUG,
        "file": file_path,
        "newcontent": content,
    }

    response = requests.post(url, headers=headers, data=data, verify=False)
    return response

def deploy_via_mu_plugin(local_theme_dir, files_to_deploy):
    """
    Deploy by creating a temporary mu-plugin that writes files,
    then trigger it via the REST API.
    """
    print("\n--- Deploying via mu-plugin approach ---\n")

    # Build a PHP script that writes our files
    php_writes = []
    for rel_path in files_to_deploy:
        local_path = os.path.join(local_theme_dir, rel_path)
        if not os.path.exists(local_path):
            print(f"  Skipping {rel_path} (not found locally)")
            continue

        with open(local_path, 'r', encoding='utf-8') as f:
            content = f.read()

        # Escape for PHP
        escaped = content.replace('\\', '\\\\').replace("'", "\\'")
        theme_dir_path = f"/public_html/wp-content/themes/{THEME_SLUG}/{rel_path}"

        php_writes.append(f"""
    // Deploy {rel_path}
    $dir = dirname('{theme_dir_path}');
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $result = file_put_contents('{theme_dir_path}', '{escaped}');
    $results['{rel_path}'] = $result !== false ? 'success' : 'failed';
""")

    # This is too large for a REST API endpoint. Let me try a simpler approach.
    return False

def deploy_files():
    """Main deployment function"""
    # Determine the repo root and theme directory
    script_dir = os.path.dirname(os.path.abspath(__file__))
    repo_root = os.path.dirname(script_dir)
    theme_dir = os.path.join(repo_root, "wp-content", "themes", "toolshed-tested")

    # Files we changed in this implementation
    files_to_deploy = [
        "functions.php",
        "front-page.php",
        "single.php",
        "single-product_review.php",
        "inc/class-tst-affiliate.php",
        "assets/css/components.css",
    ]

    print("=" * 60)
    print("Theme Deployment - Toolshed Tested")
    print("=" * 60)

    if not test_connection():
        return False

    print(f"\nDeploying {len(files_to_deploy)} files...\n")

    success = 0
    failed = 0

    for rel_path in files_to_deploy:
        local_path = os.path.join(theme_dir, rel_path)

        if not os.path.exists(local_path):
            print(f"  SKIP {rel_path} (not found)")
            continue

        with open(local_path, 'r', encoding='utf-8') as f:
            content = f.read()

        print(f"  Uploading {rel_path}...", end=" ")

        # Try the REST API theme endpoint
        response = update_theme_file(rel_path, content)

        if response.status_code in [200, 201]:
            print("OK")
            success += 1
        else:
            # Try AJAX fallback
            response2 = update_theme_file_via_editor(rel_path, content)
            if response2.status_code == 200:
                print("OK (via editor)")
                success += 1
            else:
                print(f"FAILED ({response.status_code}: {response.text[:100]})")
                failed += 1

    print(f"\n{'=' * 60}")
    print(f"Results: {success} succeeded, {failed} failed")
    print(f"{'=' * 60}")

    return failed == 0

if __name__ == "__main__":
    deploy_files()
