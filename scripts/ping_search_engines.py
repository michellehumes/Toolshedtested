#!/usr/bin/env python3
"""
Ping search engines after publishing new content
"""

import requests
from urllib.parse import quote
from datetime import datetime

SITEMAP_URL = "https://toolshedtested.com/sitemap.xml"

PING_ENDPOINTS = [
    {
        "name": "Google",
        "url": f"https://www.google.com/ping?sitemap={quote(SITEMAP_URL)}"
    },
    {
        "name": "Bing",
        "url": f"https://www.bing.com/ping?sitemap={quote(SITEMAP_URL)}"
    },
    {
        "name": "IndexNow (Bing)",
        "url": "https://www.bing.com/indexnow",
        "method": "POST",
        "payload": {
            "host": "toolshedtested.com",
            "urlList": [SITEMAP_URL]
        }
    }
]

def ping_search_engines():
    """Ping all search engines with sitemap URL"""
    print(f"\n{'='*50}")
    print(f"Pinging Search Engines - {datetime.now().strftime('%Y-%m-%d %H:%M')}")
    print(f"{'='*50}\n")
    print(f"Sitemap: {SITEMAP_URL}\n")

    results = []

    for endpoint in PING_ENDPOINTS:
        name = endpoint["name"]
        url = endpoint["url"]
        method = endpoint.get("method", "GET")

        try:
            if method == "POST":
                response = requests.post(
                    url,
                    json=endpoint.get("payload"),
                    timeout=30,
                    headers={'Content-Type': 'application/json'}
                )
            else:
                response = requests.get(url, timeout=30)

            success = response.status_code in [200, 202]
            status = "✓" if success else "✗"

            results.append({
                "engine": name,
                "status_code": response.status_code,
                "success": success
            })

            print(f"{status} {name}: HTTP {response.status_code}")

        except requests.exceptions.Timeout:
            print(f"✗ {name}: Timeout")
            results.append({
                "engine": name,
                "status_code": 0,
                "success": False,
                "error": "Timeout"
            })
        except Exception as e:
            print(f"✗ {name}: {str(e)}")
            results.append({
                "engine": name,
                "status_code": 0,
                "success": False,
                "error": str(e)
            })

    # Summary
    successful = sum(1 for r in results if r["success"])
    print(f"\nPinged {successful}/{len(results)} search engines successfully")

    return results

def ping_specific_urls(urls):
    """Ping specific URLs to IndexNow"""
    if not urls:
        print("No URLs provided")
        return

    print(f"\nSubmitting {len(urls)} URLs to IndexNow...")

    try:
        response = requests.post(
            "https://www.bing.com/indexnow",
            json={
                "host": "toolshedtested.com",
                "urlList": urls
            },
            headers={'Content-Type': 'application/json'},
            timeout=30
        )

        if response.status_code in [200, 202]:
            print(f"✓ Submitted {len(urls)} URLs successfully")
        else:
            print(f"✗ Failed: HTTP {response.status_code}")

    except Exception as e:
        print(f"✗ Error: {e}")

if __name__ == "__main__":
    import sys

    if len(sys.argv) > 1:
        # Ping specific URLs
        urls = sys.argv[1:]
        ping_specific_urls(urls)
    else:
        # Ping sitemap
        ping_search_engines()

    print("\nDone!")
