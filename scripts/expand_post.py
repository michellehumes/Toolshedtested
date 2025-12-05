#!/usr/bin/env python3
"""
Content Expansion Helper for ToolShedTested
Provides structure and prompts for expanding stub articles.
"""

import os
import re
import sys
import yaml
from pathlib import Path

POSTS_DIR = Path(__file__).parent.parent / 'posts'
TEMPLATE_DIR = Path(__file__).parent.parent / 'templates'

# Target structure for a complete review
REQUIRED_SECTIONS = {
    'quick_answer': 'Quick answer/summary at top with #1 pick',
    'comparison_table': 'Side-by-side comparison table',
    'individual_reviews': 'At least 3-5 individual product reviews',
    'buying_guide': 'What to look for section',
    'faq': 'Frequently Asked Questions (5+ questions)',
    'final_verdict': 'Conclusion/recommendation summary',
}

REQUIRED_ELEMENTS = {
    'pros_cons': 'Pros and Cons for each product',
    'specs': 'Key specifications for each product',
    'cta_buttons': 'Check Price buttons with affiliate links',
    'images': 'Product images (recommended)',
}


def parse_frontmatter(content):
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


def count_words(text):
    """Count words in text."""
    text = re.sub(r'```[\s\S]*?```', '', text)
    text = re.sub(r'`[^`]+`', '', text)
    text = re.sub(r'https?://\S+', '', text)
    text = re.sub(r'\[([^\]]+)\]\([^\)]+\)', r'\1', text)
    return len(text.split())


def analyze_post(filepath):
    """Analyze what's missing from a post."""
    content = filepath.read_text(encoding='utf-8')
    frontmatter, body = parse_frontmatter(content)

    word_count = count_words(body)

    # Check for sections
    missing_sections = []
    present_sections = []

    if not re.search(r'(top pick|our pick|best overall|#1|winner)', body, re.IGNORECASE):
        missing_sections.append('quick_answer')
    else:
        present_sections.append('quick_answer')

    if not re.search(r'\|.+\|.+\|', body):
        missing_sections.append('comparison_table')
    else:
        present_sections.append('comparison_table')

    # Count product sections (## headers followed by product content)
    product_sections = len(re.findall(r'^##[^#]', body, re.MULTILINE))
    if product_sections < 3:
        missing_sections.append('individual_reviews (need more products)')
    else:
        present_sections.append(f'individual_reviews ({product_sections} found)')

    if not re.search(r'(what to look|buying guide|how to choose|factors)', body, re.IGNORECASE):
        missing_sections.append('buying_guide')
    else:
        present_sections.append('buying_guide')

    if not re.search(r'(FAQ|frequently asked|common questions)', body, re.IGNORECASE):
        missing_sections.append('faq')
    else:
        present_sections.append('faq')

    if not re.search(r'(verdict|conclusion|bottom line|final thoughts)', body, re.IGNORECASE):
        missing_sections.append('final_verdict')
    else:
        present_sections.append('final_verdict')

    # Check for elements
    missing_elements = []
    present_elements = []

    if not re.search(r'\*\*Pros\*\*|\*\*Cons\*\*|### Pros|### Cons', body):
        missing_elements.append('pros_cons')
    else:
        present_elements.append('pros_cons')

    if not re.search(r'(specs|specifications|voltage|torque|rpm|weight)', body, re.IGNORECASE):
        missing_elements.append('specs')
    else:
        present_elements.append('specs')

    cta_count = len(re.findall(r'\[Check Price', body, re.IGNORECASE))
    if cta_count < 3:
        missing_elements.append(f'cta_buttons (only {cta_count} found)')
    else:
        present_elements.append(f'cta_buttons ({cta_count} found)')

    if not re.search(r'!\[', body):
        missing_elements.append('images')
    else:
        present_elements.append('images')

    return {
        'file': filepath.name,
        'title': frontmatter.get('title', filepath.stem),
        'category': frontmatter.get('category', 'uncategorized'),
        'word_count': word_count,
        'target_word_count': 2500,
        'words_needed': max(0, 2500 - word_count),
        'present_sections': present_sections,
        'missing_sections': missing_sections,
        'present_elements': present_elements,
        'missing_elements': missing_elements,
    }


def generate_expansion_guide(analysis):
    """Generate a guide for expanding the post."""
    guide = []
    guide.append("=" * 70)
    guide.append(f"EXPANSION GUIDE: {analysis['title']}")
    guide.append("=" * 70)
    guide.append("")

    # Word count status
    guide.append(f"📊 WORD COUNT STATUS")
    guide.append(f"   Current: {analysis['word_count']} words")
    guide.append(f"   Target: {analysis['target_word_count']} words")
    guide.append(f"   Needed: {analysis['words_needed']} words")
    guide.append("")

    # What's present
    if analysis['present_sections'] or analysis['present_elements']:
        guide.append("✅ PRESENT (keep these):")
        for item in analysis['present_sections'] + analysis['present_elements']:
            guide.append(f"   • {item}")
        guide.append("")

    # What's missing
    if analysis['missing_sections'] or analysis['missing_elements']:
        guide.append("❌ MISSING (add these):")
        for item in analysis['missing_sections']:
            desc = REQUIRED_SECTIONS.get(item, item)
            guide.append(f"   • {item}: {desc}")
        for item in analysis['missing_elements']:
            base_item = item.split(' ')[0]
            desc = REQUIRED_ELEMENTS.get(base_item, item)
            guide.append(f"   • {item}: {desc}")
        guide.append("")

    # Specific recommendations
    guide.append("📝 RECOMMENDED ADDITIONS:")
    guide.append("")

    if 'comparison_table' in analysis['missing_sections']:
        guide.append("1. ADD COMPARISON TABLE at top:")
        guide.append("   ```markdown")
        guide.append("   | Model | Key Spec | Price | Rating | Buy |")
        guide.append("   |-------|----------|-------|--------|-----|")
        guide.append("   | Product 1 | Spec | $XXX | ⭐⭐⭐⭐⭐ | [Check Price](...) |")
        guide.append("   ```")
        guide.append("")

    if 'pros_cons' in analysis['missing_elements']:
        guide.append("2. ADD PROS/CONS for each product:")
        guide.append("   ```markdown")
        guide.append("   **Pros:**")
        guide.append("   - Specific benefit with context")
        guide.append("   - Another benefit")
        guide.append("")
        guide.append("   **Cons:**")
        guide.append("   - Honest limitation")
        guide.append("   ```")
        guide.append("")

    if 'faq' in analysis['missing_sections']:
        guide.append("3. ADD FAQ SECTION:")
        guide.append("   ```markdown")
        guide.append("   ## Frequently Asked Questions")
        guide.append("")
        guide.append("   ### What is the best [category] for home use?")
        guide.append("   [Answer with recommendation]")
        guide.append("")
        guide.append("   ### How much should I spend?")
        guide.append("   [Budget breakdown]")
        guide.append("   ```")
        guide.append("")

    if 'buying_guide' in analysis['missing_sections']:
        guide.append("4. ADD BUYING GUIDE:")
        guide.append("   ```markdown")
        guide.append("   ## What to Look for When Buying")
        guide.append("")
        guide.append("   ### Power/Voltage")
        guide.append("   [Explain what to look for]")
        guide.append("")
        guide.append("   ### Build Quality")
        guide.append("   [Explain what to look for]")
        guide.append("   ```")
        guide.append("")

    guide.append("-" * 70)
    guide.append(f"Template available at: templates/review-template.md")
    guide.append("")

    return '\n'.join(guide)


def main():
    if len(sys.argv) < 2:
        # Analyze all posts and show summary
        print("CONTENT EXPANSION ANALYSIS")
        print("=" * 70)
        print("")

        posts = []
        for md_file in sorted(POSTS_DIR.glob('*.md')):
            analysis = analyze_post(md_file)
            posts.append(analysis)

        # Sort by words needed (most expansion needed first)
        posts.sort(key=lambda x: x['words_needed'], reverse=True)

        print(f"{'File':<45} {'Words':<8} {'Needed':<8} {'Missing'}")
        print("-" * 70)

        for post in posts:
            missing_count = len(post['missing_sections']) + len(post['missing_elements'])
            status = "🔴" if post['words_needed'] > 2000 else "🟡" if post['words_needed'] > 0 else "🟢"
            print(f"{status} {post['file']:<42} {post['word_count']:<8} {post['words_needed']:<8} {missing_count} items")

        print("")
        print("Run with a specific file for detailed guide:")
        print("  python expand_post.py posts/angle-grinders.md")

    else:
        # Analyze specific post
        filepath = Path(sys.argv[1])
        if not filepath.exists():
            filepath = POSTS_DIR / sys.argv[1]

        if not filepath.exists():
            print(f"Error: File not found: {filepath}")
            sys.exit(1)

        analysis = analyze_post(filepath)
        guide = generate_expansion_guide(analysis)
        print(guide)


if __name__ == "__main__":
    main()
