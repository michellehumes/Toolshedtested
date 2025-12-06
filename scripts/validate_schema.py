#!/usr/bin/env python3
"""
Validate schema markup on all site pages
"""

import requests
import json
import re
from datetime import datetime

SITE_URL = "https://toolshedtested.com"
PAGES_TO_CHECK = [
    "/",
    "/about/",
    "/faq/",
    "/affiliate-disclosure/",
    "/contact/",
    "/category/drills/",
    "/category/saws/",
    "/category/grinders/",
    "/category/sanders/",
    "/category/outdoor-power/",
    "/best-cordless-drills/",
    "/best-table-saws/",
    "/makita-vs-milwaukee/",
]

REQUIRED_SCHEMAS = {
    "/": ["Organization", "WebSite", "WebPage"],
    "/faq/": ["FAQPage"],
    "/about/": ["Person", "WebPage"],
    "/category/": ["CollectionPage"],
    "/best-": ["BlogPosting", "Review", "Product"],
}

def extract_json_ld(html):
    """Extract JSON-LD schema from HTML"""
    pattern = r'<script type="application/ld\+json">(.*?)</script>'
    matches = re.findall(pattern, html, re.DOTALL)
    schemas = []
    for match in matches:
        try:
            schemas.append(json.loads(match))
        except json.JSONDecodeError:
            pass
    return schemas

def get_schema_types(schemas):
    """Extract all @type values from schemas"""
    types = set()
    for schema in schemas:
        if isinstance(schema, dict):
            if "@type" in schema:
                t = schema["@type"]
                if isinstance(t, list):
                    types.update(t)
                else:
                    types.add(t)
            # Check @graph
            if "@graph" in schema:
                for item in schema["@graph"]:
                    if isinstance(item, dict) and "@type" in item:
                        t = item["@type"]
                        if isinstance(t, list):
                            types.update(t)
                        else:
                            types.add(t)
        elif isinstance(schema, list):
            for item in schema:
                if isinstance(item, dict) and "@type" in item:
                    types.add(item["@type"])
    return types

def validate_page(url):
    """Validate schema on a single page"""
    try:
        response = requests.get(url, timeout=30, headers={
            'User-Agent': 'Mozilla/5.0 (compatible; SchemaValidator/1.0)'
        })
        if response.status_code != 200:
            return {"url": url, "status": "error", "message": f"HTTP {response.status_code}"}

        schemas = extract_json_ld(response.text)
        schema_types = get_schema_types(schemas)

        # Check for missing required schemas
        missing = []
        for pattern, required in REQUIRED_SCHEMAS.items():
            if pattern in url:
                for req in required:
                    if req not in schema_types:
                        missing.append(req)

        return {
            "url": url,
            "status": "success",
            "schema_types": list(schema_types),
            "schema_count": len(schemas),
            "missing_schemas": missing
        }
    except Exception as e:
        return {"url": url, "status": "error", "message": str(e)}

def main():
    print(f"\n{'='*60}")
    print(f"Schema Validation Report - {datetime.now().strftime('%Y-%m-%d %H:%M')}")
    print(f"{'='*60}\n")

    results = []
    issues_found = 0

    for page in PAGES_TO_CHECK:
        url = f"{SITE_URL}{page}"
        result = validate_page(url)
        results.append(result)

        if result["status"] == "success":
            status_icon = "✓" if not result["missing_schemas"] else "⚠"
            print(f"{status_icon} {page}")
            print(f"  Found: {', '.join(result['schema_types']) or 'None'}")
            if result["missing_schemas"]:
                issues_found += 1
                print(f"  Missing: {', '.join(result['missing_schemas'])}")
        else:
            issues_found += 1
            print(f"✗ {page}")
            print(f"  Error: {result.get('message', 'Unknown')}")
        print()

    # Summary
    print(f"\n{'='*60}")
    print("SUMMARY")
    print(f"{'='*60}")
    print(f"Pages checked: {len(results)}")
    print(f"Issues found: {issues_found}")

    if issues_found > 0:
        print("\nRecommended Actions:")
        for result in results:
            if result.get("missing_schemas"):
                print(f"  • Add {', '.join(result['missing_schemas'])} to {result['url']}")

    return issues_found

if __name__ == "__main__":
    exit(main())
