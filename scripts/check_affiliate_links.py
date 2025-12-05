#!/usr/bin/env python3
"""
Affiliate Link Checker for ToolShedTested
Validates all affiliate links across the site and reports issues.
"""

import os
import re
import sys
import json
import requests
from pathlib import Path
from datetime import datetime
from concurrent.futures import ThreadPoolExecutor, as_completed

# Configuration
AMAZON_TAG = os.environ.get('AMAZON_TAG', 'toolshedtested-20')
POSTS_DIR = Path(__file__).parent.parent / 'posts'
TIMEOUT = 10
MAX_WORKERS = 5

# Link patterns
AMAZON_PATTERN = re.compile(r'https?://(?:www\.)?amazon\.com[^\s\)"\'\]]+', re.IGNORECASE)
AFFILIATE_LINK_PATTERN = re.compile(r'\[([^\]]+)\]\((https?://[^\)]+)\)')

class LinkChecker:
    def __init__(self):
        self.results = {
            'checked': 0,
            'valid': 0,
            'broken': [],
            'missing_tag': [],
            'redirects': [],
            'errors': []
        }
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (compatible; LinkChecker/1.0)'
        })

    def extract_links_from_file(self, filepath):
        """Extract all URLs from a markdown file."""
        content = filepath.read_text(encoding='utf-8')
        links = []

        # Extract markdown links
        for match in AFFILIATE_LINK_PATTERN.finditer(content):
            text, url = match.groups()
            links.append({
                'text': text,
                'url': url,
                'file': str(filepath.name),
                'type': 'markdown'
            })

        # Extract raw Amazon URLs
        for match in AMAZON_PATTERN.finditer(content):
            url = match.group()
            # Skip if already captured as markdown link
            if not any(l['url'] == url for l in links):
                links.append({
                    'text': 'raw_link',
                    'url': url,
                    'file': str(filepath.name),
                    'type': 'raw'
                })

        return links

    def check_amazon_tag(self, url):
        """Check if Amazon URL has the correct affiliate tag."""
        if 'amazon.com' not in url.lower():
            return True  # Not an Amazon link

        if f'tag={AMAZON_TAG}' in url:
            return True

        return False

    def check_link_status(self, link_info):
        """Check if a link is accessible."""
        url = link_info['url']

        try:
            response = self.session.head(
                url,
                timeout=TIMEOUT,
                allow_redirects=True
            )

            # If HEAD fails, try GET
            if response.status_code >= 400:
                response = self.session.get(
                    url,
                    timeout=TIMEOUT,
                    allow_redirects=True
                )

            return {
                'status_code': response.status_code,
                'final_url': response.url,
                'redirected': response.url != url
            }

        except requests.exceptions.Timeout:
            return {'status_code': 'timeout', 'error': 'Request timed out'}
        except requests.exceptions.ConnectionError:
            return {'status_code': 'connection_error', 'error': 'Connection failed'}
        except Exception as e:
            return {'status_code': 'error', 'error': str(e)}

    def check_link(self, link_info):
        """Full check of a single link."""
        url = link_info['url']
        result = {
            **link_info,
            'has_correct_tag': self.check_amazon_tag(url)
        }

        # Check link status
        status = self.check_link_status(link_info)
        result.update(status)

        return result

    def process_results(self, result):
        """Categorize a link check result."""
        self.results['checked'] += 1

        # Check for missing affiliate tag
        if 'amazon.com' in result['url'].lower() and not result['has_correct_tag']:
            self.results['missing_tag'].append(result)

        # Check for broken links
        status = result.get('status_code')
        if isinstance(status, int):
            if status >= 400:
                self.results['broken'].append(result)
            elif result.get('redirected'):
                self.results['redirects'].append(result)
            else:
                self.results['valid'] += 1
        else:
            self.results['errors'].append(result)

    def scan_all_posts(self):
        """Scan all markdown posts for links."""
        if not POSTS_DIR.exists():
            print(f"Error: Posts directory not found: {POSTS_DIR}")
            sys.exit(1)

        all_links = []

        for md_file in POSTS_DIR.glob('*.md'):
            links = self.extract_links_from_file(md_file)
            all_links.extend(links)

        print(f"Found {len(all_links)} links in {len(list(POSTS_DIR.glob('*.md')))} files")

        # Check links in parallel
        with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
            futures = {executor.submit(self.check_link, link): link for link in all_links}

            for i, future in enumerate(as_completed(futures), 1):
                result = future.result()
                self.process_results(result)
                print(f"\rChecking links: {i}/{len(all_links)}", end='', flush=True)

        print()  # New line after progress

    def generate_report(self):
        """Generate a summary report."""
        report = []
        report.append("=" * 60)
        report.append("AFFILIATE LINK AUDIT REPORT")
        report.append(f"Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        report.append("=" * 60)
        report.append("")

        # Summary
        report.append("SUMMARY")
        report.append("-" * 30)
        report.append(f"Total links checked: {self.results['checked']}")
        report.append(f"Valid links: {self.results['valid']}")
        report.append(f"Broken links: {len(self.results['broken'])}")
        report.append(f"Missing affiliate tag: {len(self.results['missing_tag'])}")
        report.append(f"Redirects: {len(self.results['redirects'])}")
        report.append(f"Errors: {len(self.results['errors'])}")
        report.append("")

        # Broken links
        if self.results['broken']:
            report.append("BROKEN LINKS (Need immediate attention)")
            report.append("-" * 30)
            for link in self.results['broken']:
                report.append(f"  File: {link['file']}")
                report.append(f"  URL: {link['url'][:80]}...")
                report.append(f"  Status: {link['status_code']}")
                report.append("")

        # Missing tags
        if self.results['missing_tag']:
            report.append(f"AMAZON LINKS MISSING '{AMAZON_TAG}' TAG")
            report.append("-" * 30)
            for link in self.results['missing_tag']:
                report.append(f"  File: {link['file']}")
                report.append(f"  URL: {link['url'][:80]}...")
                report.append("")

        # Errors
        if self.results['errors']:
            report.append("ERRORS (Could not check)")
            report.append("-" * 30)
            for link in self.results['errors']:
                report.append(f"  File: {link['file']}")
                report.append(f"  URL: {link['url'][:80]}...")
                report.append(f"  Error: {link.get('error', 'Unknown')}")
                report.append("")

        return '\n'.join(report)

    def save_report(self, output_path=None):
        """Save report to file."""
        if output_path is None:
            output_path = Path(__file__).parent / 'link_audit_report.txt'

        report = self.generate_report()
        Path(output_path).write_text(report)
        print(f"\nReport saved to: {output_path}")

        # Also save JSON for programmatic access
        json_path = Path(output_path).with_suffix('.json')
        with open(json_path, 'w') as f:
            json.dump(self.results, f, indent=2, default=str)
        print(f"JSON data saved to: {json_path}")


def main():
    print("ToolShedTested Affiliate Link Checker")
    print("-" * 40)

    checker = LinkChecker()
    checker.scan_all_posts()

    report = checker.generate_report()
    print(report)

    checker.save_report()

    # Exit with error code if issues found
    if checker.results['broken'] or checker.results['missing_tag']:
        sys.exit(1)


if __name__ == "__main__":
    main()
