#!/usr/bin/env python3
"""
Generate weekly site performance report
"""

import json
from datetime import datetime
from pathlib import Path

def load_latest_audit():
    """Load the most recent audit report"""
    reports_dir = Path("reports")
    audit_files = list(reports_dir.glob("site-audit-*.json"))

    if not audit_files:
        return None

    # Sort by modification time, get latest
    latest = max(audit_files, key=lambda p: p.stat().st_mtime)

    with open(latest) as f:
        return json.load(f)

def generate_report():
    """Generate weekly performance report"""
    print(f"\n{'='*60}")
    print(f"Weekly Report Generator - {datetime.now().strftime('%Y-%m-%d')}")
    print(f"{'='*60}\n")

    audit_data = load_latest_audit()

    if not audit_data:
        print("No audit data found. Run full_site_audit.py first.")
        return

    # Analyze data
    total_pages = len(audit_data)
    pages_with_issues = len([p for p in audit_data if p.get('issues')])
    pages_with_warnings = len([p for p in audit_data if p.get('warnings')])

    total_issues = sum(len(p.get('issues', [])) for p in audit_data)
    total_warnings = sum(len(p.get('warnings', [])) for p in audit_data)

    # Stats aggregation
    total_amazon_links = sum(p.get('stats', {}).get('amazon_links', 0) for p in audit_data)
    total_cta_buttons = sum(p.get('stats', {}).get('cta_buttons', 0) for p in audit_data)
    total_images = sum(p.get('stats', {}).get('total_images', 0) for p in audit_data)
    images_without_alt = sum(p.get('stats', {}).get('images_without_alt', 0) for p in audit_data)

    # Generate markdown report
    report = f"""# Weekly Site Report
## Week of {datetime.now().strftime('%B %d, %Y')}

---

## Site Health Overview

| Metric | Value | Status |
|--------|-------|--------|
| Total Pages | {total_pages} | ✓ |
| Pages with Issues | {pages_with_issues} | {'⚠️' if pages_with_issues > 0 else '✓'} |
| Critical Issues | {total_issues} | {'❌' if total_issues > 0 else '✓'} |
| Warnings | {total_warnings} | {'⚠️' if total_warnings > 5 else '✓'} |

---

## Content Metrics

| Metric | Value |
|--------|-------|
| Amazon Affiliate Links | {total_amazon_links} |
| CTA Buttons | {total_cta_buttons} |
| Total Images | {total_images} |
| Images Missing Alt | {images_without_alt} |

---

## Issues to Address

"""

    # Group issues by type
    issue_types = {}
    for page in audit_data:
        for issue in page.get('issues', []):
            if issue not in issue_types:
                issue_types[issue] = []
            issue_types[issue].append(page['url'])

    if issue_types:
        for issue, pages in issue_types.items():
            report += f"### {issue}\n"
            report += f"Affects {len(pages)} page(s):\n"
            for page in pages[:5]:  # Show first 5
                report += f"- {page}\n"
            if len(pages) > 5:
                report += f"- ... and {len(pages) - 5} more\n"
            report += "\n"
    else:
        report += "No critical issues found! 🎉\n\n"

    report += """---

## Recommendations

1. **Fix Critical Issues First** - Address any missing affiliate tags or broken links
2. **Add Alt Text to Images** - Improves SEO and accessibility
3. **Increase CTA Density** - Add more prominent buttons on review pages
4. **Monitor Schema Markup** - Ensure all pages have proper structured data

---

## Next Steps

- [ ] Review and fix critical issues
- [ ] Update oldest content
- [ ] Add new reviews to content calendar
- [ ] Check competitor activity

---

*Report generated automatically by ToolshedTested audit system*
"""

    # Save report
    reports_dir = Path("reports")
    reports_dir.mkdir(exist_ok=True)

    report_file = reports_dir / f"weekly-report-{datetime.now().strftime('%Y%m%d')}.md"
    with open(report_file, 'w') as f:
        f.write(report)

    print(f"Report saved to: {report_file}")
    print("\n" + "="*60)
    print("SUMMARY")
    print("="*60)
    print(f"Total Pages: {total_pages}")
    print(f"Critical Issues: {total_issues}")
    print(f"Warnings: {total_warnings}")

    return report_file

if __name__ == "__main__":
    generate_report()
