#!/usr/bin/env python3
"""
Comprehensive site audit script for ToolshedTested
"""

import requests
from bs4 import BeautifulSoup
import json
import re
from datetime import datetime
from pathlib import Path
from urllib.parse import urljoin, urlparse

SITE_URL = "https://toolshedtested.com"
MAX_PAGES = 100

def check_page(url):
    """Check a single page for SEO and conversion issues"""
    issues = []
    warnings = []
    stats = {}

    try:
        response = requests.get(url, timeout=30, headers={
            'User-Agent': 'Mozilla/5.0 (compatible; SiteAudit/1.0)'
        })

        if response.status_code != 200:
            return {
                "url": url,
                "status_code": response.status_code,
                "issues": [f"HTTP {response.status_code}"],
                "warnings": [],
                "stats": {}
            }

        soup = BeautifulSoup(response.text, 'html.parser')

        # Check title
        title = soup.find('title')
        if not title:
            issues.append("Missing title tag")
        elif len(title.text.strip()) < 30:
            warnings.append(f"Title too short ({len(title.text.strip())} chars)")
        elif len(title.text.strip()) > 60:
            warnings.append(f"Title too long ({len(title.text.strip())} chars)")
        stats['title_length'] = len(title.text.strip()) if title else 0

        # Check meta description
        meta_desc = soup.find('meta', attrs={'name': 'description'})
        if not meta_desc or not meta_desc.get('content'):
            issues.append("Missing meta description")
        else:
            desc_len = len(meta_desc.get('content', ''))
            if desc_len < 120:
                warnings.append(f"Meta description too short ({desc_len} chars)")
            elif desc_len > 160:
                warnings.append(f"Meta description too long ({desc_len} chars)")
            stats['meta_desc_length'] = desc_len

        # Check H1
        h1_tags = soup.find_all('h1')
        if not h1_tags:
            issues.append("Missing H1 tag")
        elif len(h1_tags) > 1:
            warnings.append(f"Multiple H1 tags ({len(h1_tags)})")
        stats['h1_count'] = len(h1_tags)

        # Check heading hierarchy
        h2_tags = soup.find_all('h2')
        h3_tags = soup.find_all('h3')
        stats['h2_count'] = len(h2_tags)
        stats['h3_count'] = len(h3_tags)

        # Check images
        images = soup.find_all('img')
        imgs_without_alt = [img for img in images if not img.get('alt')]
        if imgs_without_alt:
            warnings.append(f"{len(imgs_without_alt)}/{len(images)} images missing alt text")
        stats['total_images'] = len(images)
        stats['images_without_alt'] = len(imgs_without_alt)

        # Check affiliate links
        amazon_links = soup.find_all('a', href=re.compile(r'amazon\.com'))
        tagged_links = [a for a in amazon_links if 'tag=' in a.get('href', '')]
        untagged_links = len(amazon_links) - len(tagged_links)
        if untagged_links > 0:
            issues.append(f"{untagged_links} Amazon links missing affiliate tag")
        stats['amazon_links'] = len(amazon_links)
        stats['tagged_links'] = len(tagged_links)

        # Check for CTA buttons
        cta_buttons = soup.find_all('a', class_=re.compile(r'tst-btn|btn-amazon|affiliate'))
        stats['cta_buttons'] = len(cta_buttons)

        # Review page specific checks
        if '/best-' in url or '/category/' in url:
            if len(tagged_links) < 3:
                warnings.append("Low affiliate link count for review/category page")
            if len(cta_buttons) < 2:
                warnings.append("Few CTA buttons")

        # Check for schema
        schemas = soup.find_all('script', type='application/ld+json')
        stats['schema_count'] = len(schemas)
        if not schemas:
            warnings.append("No schema markup found")

        # Check internal links
        internal_links = soup.find_all('a', href=re.compile(f'^{SITE_URL}|^/'))
        stats['internal_links'] = len(internal_links)

        # Check external links
        all_links = soup.find_all('a', href=True)
        external_links = [a for a in all_links if not a['href'].startswith('/')
                         and SITE_URL not in a['href']
                         and a['href'].startswith('http')]
        stats['external_links'] = len(external_links)

        # Check for nofollow on affiliate links
        affiliate_links = soup.find_all('a', class_=re.compile(r'affiliate'))
        for link in affiliate_links:
            rel = link.get('rel', [])
            if isinstance(rel, str):
                rel = rel.split()
            if 'nofollow' not in rel and 'sponsored' not in rel:
                warnings.append("Affiliate link missing nofollow/sponsored")
                break

        return {
            "url": url,
            "status_code": response.status_code,
            "issues": issues,
            "warnings": warnings,
            "stats": stats
        }

    except Exception as e:
        return {
            "url": url,
            "status_code": 0,
            "issues": [str(e)],
            "warnings": [],
            "stats": {}
        }

def crawl_site():
    """Crawl site and collect all internal pages"""
    pages = set()
    to_visit = [SITE_URL]
    visited = set()

    print("Crawling site...")

    while to_visit and len(pages) < MAX_PAGES:
        url = to_visit.pop(0)

        # Normalize URL
        parsed = urlparse(url)
        normalized = f"{parsed.scheme}://{parsed.netloc}{parsed.path.rstrip('/')}"

        if normalized in visited:
            continue
        visited.add(normalized)

        try:
            response = requests.get(url, timeout=30, headers={
                'User-Agent': 'Mozilla/5.0 (compatible; SiteAudit/1.0)'
            })

            if response.status_code != 200:
                continue

            soup = BeautifulSoup(response.text, 'html.parser')
            pages.add(url)

            for link in soup.find_all('a', href=True):
                href = link['href']

                # Skip anchors, javascript, mailto, etc
                if href.startswith('#') or href.startswith('javascript:') or href.startswith('mailto:'):
                    continue

                # Build absolute URL
                if href.startswith('/'):
                    full_url = urljoin(SITE_URL, href)
                elif href.startswith(SITE_URL):
                    full_url = href
                else:
                    continue

                # Only internal links
                if SITE_URL in full_url and full_url not in visited:
                    to_visit.append(full_url)

        except Exception as e:
            print(f"  Error crawling {url}: {e}")

    print(f"Found {len(pages)} pages")
    return list(pages)

def generate_report(results):
    """Generate audit report"""
    report_dir = Path("reports")
    report_dir.mkdir(exist_ok=True)

    timestamp = datetime.now().strftime('%Y%m%d_%H%M')

    # JSON report
    json_file = report_dir / f"site-audit-{timestamp}.json"
    with open(json_file, 'w') as f:
        json.dump(results, f, indent=2)

    # Markdown report
    md_file = report_dir / f"site-audit-{timestamp}.md"

    total_issues = sum(len(r['issues']) for r in results)
    total_warnings = sum(len(r['warnings']) for r in results)
    pages_with_issues = len([r for r in results if r['issues']])

    md_content = f"""# Site Audit Report
## {datetime.now().strftime('%Y-%m-%d %H:%M')}

---

## Summary

| Metric | Value |
|--------|-------|
| Pages Audited | {len(results)} |
| Pages with Issues | {pages_with_issues} |
| Total Issues | {total_issues} |
| Total Warnings | {total_warnings} |

---

## Critical Issues

"""

    for result in results:
        if result['issues']:
            md_content += f"### {result['url']}\n"
            for issue in result['issues']:
                md_content += f"- ❌ {issue}\n"
            md_content += "\n"

    md_content += "---\n\n## Warnings\n\n"

    for result in results:
        if result['warnings']:
            md_content += f"### {result['url']}\n"
            for warning in result['warnings']:
                md_content += f"- ⚠️ {warning}\n"
            md_content += "\n"

    with open(md_file, 'w') as f:
        f.write(md_content)

    print(f"\nReports saved:")
    print(f"  • {json_file}")
    print(f"  • {md_file}")

    return json_file, md_file

def main():
    print(f"\n{'='*70}")
    print(f"Full Site Audit - {datetime.now().strftime('%Y-%m-%d %H:%M')}")
    print(f"{'='*70}\n")

    # Crawl site
    pages = crawl_site()

    # Audit each page
    print("\nAuditing pages...")
    results = []
    for i, page in enumerate(pages):
        print(f"  [{i+1}/{len(pages)}] {page}")
        result = check_page(page)
        results.append(result)

    # Generate reports
    generate_report(results)

    # Print summary
    print(f"\n{'='*70}")
    print("SUMMARY")
    print(f"{'='*70}")

    total_issues = sum(len(r['issues']) for r in results)
    total_warnings = sum(len(r['warnings']) for r in results)

    print(f"Pages audited: {len(results)}")
    print(f"Critical issues: {total_issues}")
    print(f"Warnings: {total_warnings}")

    if total_issues > 0:
        print("\nPages with critical issues:")
        for result in results:
            if result['issues']:
                print(f"\n  {result['url']}")
                for issue in result['issues']:
                    print(f"    ❌ {issue}")

    return total_issues

if __name__ == "__main__":
    exit(main())
