#!/usr/bin/env python3
"""
Content Analyzer for ToolShedTested
Analyzes post quality, SEO optimization, and affiliate placement.
"""

import os
import re
import sys
import yaml
import json
from pathlib import Path
from datetime import datetime
from collections import Counter

# Configuration
MIN_WORD_COUNT = 2500
RECOMMENDED_WORD_COUNT = 3500
MIN_AFFILIATE_LINKS = 3
MAX_AFFILIATE_LINKS = 15
RECOMMENDED_H2_COUNT = 5
RECOMMENDED_H3_COUNT = 8
POSTS_DIR = Path(__file__).parent.parent / 'posts'

# Target keywords by category
CATEGORY_KEYWORDS = {
    'drills': ['cordless drill', 'drill driver', 'impact drill', 'drill bit', 'torque', 'battery'],
    'saws': ['circular saw', 'miter saw', 'table saw', 'blade', 'cutting', 'bevel'],
    'grinders': ['angle grinder', 'bench grinder', 'disc', 'grinding wheel', 'rpm'],
    'sanders': ['orbital sander', 'belt sander', 'sandpaper', 'grit', 'dust collection'],
    'outdoor': ['lawn mower', 'chainsaw', 'leaf blower', 'trimmer', 'battery powered'],
}


class ContentAnalyzer:
    def __init__(self):
        self.results = []

    def parse_frontmatter(self, content):
        """Extract YAML frontmatter from markdown."""
        if content.startswith('---'):
            parts = content.split('---', 2)
            if len(parts) >= 3:
                try:
                    frontmatter = yaml.safe_load(parts[1])
                    body = parts[2].strip()
                    return frontmatter, body
                except yaml.YAMLError:
                    return {}, content
        return {}, content

    def count_words(self, text):
        """Count words in text, excluding code and URLs."""
        # Remove code blocks
        text = re.sub(r'```[\s\S]*?```', '', text)
        # Remove inline code
        text = re.sub(r'`[^`]+`', '', text)
        # Remove URLs
        text = re.sub(r'https?://\S+', '', text)
        # Remove markdown links keeping text
        text = re.sub(r'\[([^\]]+)\]\([^\)]+\)', r'\1', text)
        # Count words
        words = text.split()
        return len(words)

    def extract_headings(self, content):
        """Extract all headings with their levels."""
        headings = []
        for match in re.finditer(r'^(#{1,6})\s+(.+)$', content, re.MULTILINE):
            level = len(match.group(1))
            text = match.group(2).strip()
            headings.append({'level': level, 'text': text})
        return headings

    def count_affiliate_links(self, content):
        """Count affiliate links in content."""
        amazon_links = len(re.findall(r'amazon\.com', content, re.IGNORECASE))
        cta_buttons = len(re.findall(r'\[Check Price', content, re.IGNORECASE))
        return amazon_links + cta_buttons

    def check_keyword_usage(self, content, category):
        """Check keyword usage for the category."""
        keywords = CATEGORY_KEYWORDS.get(category, [])
        found_keywords = {}

        content_lower = content.lower()
        for keyword in keywords:
            count = content_lower.count(keyword.lower())
            if count > 0:
                found_keywords[keyword] = count

        return found_keywords

    def check_structure(self, content):
        """Check content structure requirements."""
        checks = {
            'has_comparison_table': bool(re.search(r'\|.+\|.+\|', content)),
            'has_pros_cons': bool(re.search(r'\*\*Pros\*\*|\*\*Cons\*\*|### Pros|### Cons', content)),
            'has_faq': bool(re.search(r'FAQ|Frequently Asked', content, re.IGNORECASE)),
            'has_conclusion': bool(re.search(r'## (Final|Conclusion|Verdict|Bottom Line)', content, re.IGNORECASE)),
            'has_image_placeholder': bool(re.search(r'!\[', content)),
            'has_cta_button': bool(re.search(r'\[Check Price|Buy Now|View on Amazon', content, re.IGNORECASE)),
        }
        return checks

    def analyze_file(self, filepath):
        """Analyze a single markdown file."""
        content = filepath.read_text(encoding='utf-8')
        frontmatter, body = self.parse_frontmatter(content)

        # Basic info
        title = frontmatter.get('title', filepath.stem)
        category = frontmatter.get('category', 'uncategorized')
        slug = frontmatter.get('slug', filepath.stem)

        # Analysis
        word_count = self.count_words(body)
        headings = self.extract_headings(body)
        affiliate_links = self.count_affiliate_links(body)
        keywords = self.check_keyword_usage(body, category)
        structure = self.check_structure(body)

        # Heading counts
        h2_count = len([h for h in headings if h['level'] == 2])
        h3_count = len([h for h in headings if h['level'] == 3])

        # Scoring
        score = 0
        issues = []
        suggestions = []

        # Word count scoring
        if word_count >= RECOMMENDED_WORD_COUNT:
            score += 25
        elif word_count >= MIN_WORD_COUNT:
            score += 15
            suggestions.append(f"Consider expanding content to {RECOMMENDED_WORD_COUNT}+ words")
        else:
            issues.append(f"Word count ({word_count}) below minimum ({MIN_WORD_COUNT})")

        # Affiliate link scoring
        if MIN_AFFILIATE_LINKS <= affiliate_links <= MAX_AFFILIATE_LINKS:
            score += 20
        elif affiliate_links < MIN_AFFILIATE_LINKS:
            issues.append(f"Only {affiliate_links} affiliate links (recommend {MIN_AFFILIATE_LINKS}+)")
        else:
            suggestions.append(f"Consider reducing affiliate links ({affiliate_links}) to avoid over-optimization")

        # Structure scoring
        if structure['has_comparison_table']:
            score += 15
        else:
            suggestions.append("Add a comparison table for better user experience")

        if structure['has_pros_cons']:
            score += 10
        else:
            issues.append("Missing pros/cons sections")

        if structure['has_faq']:
            score += 10
        else:
            suggestions.append("Add FAQ section for rich snippets potential")

        if structure['has_conclusion']:
            score += 5
        else:
            suggestions.append("Add a clear conclusion/verdict section")

        if structure['has_cta_button']:
            score += 10
        else:
            issues.append("Missing CTA buttons (Check Price, etc.)")

        # Heading structure scoring
        if h2_count >= RECOMMENDED_H2_COUNT:
            score += 5
        else:
            suggestions.append(f"Consider adding more H2 sections ({h2_count}/{RECOMMENDED_H2_COUNT})")

        # Determine grade
        if score >= 90:
            grade = 'A'
        elif score >= 75:
            grade = 'B'
        elif score >= 60:
            grade = 'C'
        elif score >= 45:
            grade = 'D'
        else:
            grade = 'F'

        return {
            'file': filepath.name,
            'title': title,
            'category': category,
            'slug': slug,
            'word_count': word_count,
            'affiliate_links': affiliate_links,
            'h2_count': h2_count,
            'h3_count': h3_count,
            'structure': structure,
            'keywords_found': keywords,
            'score': score,
            'grade': grade,
            'issues': issues,
            'suggestions': suggestions
        }

    def analyze_all_posts(self):
        """Analyze all posts in the directory."""
        if not POSTS_DIR.exists():
            print(f"Error: Posts directory not found: {POSTS_DIR}")
            sys.exit(1)

        for md_file in sorted(POSTS_DIR.glob('*.md')):
            result = self.analyze_file(md_file)
            self.results.append(result)
            print(f"Analyzed: {md_file.name} [{result['grade']}]")

    def generate_report(self):
        """Generate analysis report."""
        if not self.results:
            return "No posts analyzed."

        report = []
        report.append("=" * 70)
        report.append("CONTENT ANALYSIS REPORT - ToolShedTested")
        report.append(f"Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        report.append("=" * 70)
        report.append("")

        # Summary statistics
        total = len(self.results)
        avg_word_count = sum(r['word_count'] for r in self.results) / total
        avg_score = sum(r['score'] for r in self.results) / total
        grades = Counter(r['grade'] for r in self.results)

        report.append("SUMMARY")
        report.append("-" * 40)
        report.append(f"Total posts analyzed: {total}")
        report.append(f"Average word count: {avg_word_count:.0f}")
        report.append(f"Average score: {avg_score:.1f}/100")
        report.append(f"Grade distribution: {dict(grades)}")
        report.append("")

        # Posts below threshold
        low_quality = [r for r in self.results if r['grade'] in ['D', 'F']]
        if low_quality:
            report.append("POSTS NEEDING ATTENTION")
            report.append("-" * 40)
            for post in low_quality:
                report.append(f"\n{post['title']}")
                report.append(f"  File: {post['file']}")
                report.append(f"  Grade: {post['grade']} (Score: {post['score']})")
                report.append(f"  Word count: {post['word_count']}")
                for issue in post['issues']:
                    report.append(f"  - ISSUE: {issue}")
                for suggestion in post['suggestions'][:3]:  # Top 3 suggestions
                    report.append(f"  - Suggestion: {suggestion}")
            report.append("")

        # High performers
        high_quality = [r for r in self.results if r['grade'] == 'A']
        if high_quality:
            report.append("TOP PERFORMING POSTS")
            report.append("-" * 40)
            for post in high_quality:
                report.append(f"  {post['grade']}: {post['title']} ({post['word_count']} words)")
            report.append("")

        # Category breakdown
        categories = {}
        for result in self.results:
            cat = result['category']
            if cat not in categories:
                categories[cat] = []
            categories[cat].append(result)

        report.append("CATEGORY BREAKDOWN")
        report.append("-" * 40)
        for cat, posts in sorted(categories.items()):
            avg_score_cat = sum(p['score'] for p in posts) / len(posts)
            report.append(f"  {cat}: {len(posts)} posts, avg score: {avg_score_cat:.1f}")
        report.append("")

        # Action items
        all_issues = []
        for result in self.results:
            for issue in result['issues']:
                all_issues.append(f"{result['file']}: {issue}")

        if all_issues:
            report.append("PRIORITIZED ACTION ITEMS")
            report.append("-" * 40)
            for i, issue in enumerate(all_issues[:10], 1):  # Top 10 issues
                report.append(f"  {i}. {issue}")

        return '\n'.join(report)

    def save_report(self):
        """Save report to files."""
        output_dir = Path(__file__).parent

        # Text report
        report = self.generate_report()
        report_path = output_dir / 'content_analysis_report.txt'
        report_path.write_text(report)
        print(f"\nReport saved to: {report_path}")

        # JSON data
        json_path = output_dir / 'content_analysis_data.json'
        with open(json_path, 'w') as f:
            json.dump(self.results, f, indent=2)
        print(f"JSON data saved to: {json_path}")

        return report


def main():
    print("ToolShedTested Content Analyzer")
    print("-" * 40)

    analyzer = ContentAnalyzer()
    analyzer.analyze_all_posts()

    report = analyzer.save_report()
    print("\n" + report)

    # Exit with error if any posts have F grade
    if any(r['grade'] == 'F' for r in analyzer.results):
        sys.exit(1)


if __name__ == "__main__":
    main()
