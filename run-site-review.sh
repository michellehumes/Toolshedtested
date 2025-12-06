#!/bin/bash
#
# Launch Claude Code with full site review prompt
# This script runs the complete automated site audit and implementation
#

echo "=========================================="
echo "  ToolshedTested Site Review & Implementation"
echo "=========================================="
echo ""
echo "This will:"
echo "  1. Review all pages on toolshedtested.com"
echo "  2. Generate detailed recommendations"
echo "  3. Create automated implementation scripts"
echo "  4. Execute changes with full automation"
echo ""
echo "Starting Claude Code with --dangerously-skip-permissions..."
echo ""

# Run Claude Code with the site review command
claude --dangerously-skip-permissions -p "$(cat <<'EOF'
--dangerously-skip-permissions

## CRITICAL: Use Existing Components First

Before creating ANY new components, CSS classes, shortcodes, or templates, you MUST check and USE existing resources in this codebase:

### Available Shortcodes (inc/shortcodes.php):
- [product_box id="123"] - Product summary with image, rating, pros/cons, CTAs
- [comparison_table ids="1,2,3"] - Side-by-side product comparison
- [star_rating rating="4.5"] - Star display
- [affiliate_button url="..." text="..." style="amazon|cta|primary|secondary"] - CTA buttons
- [pros_cons pros="Pro 1|Pro 2" cons="Con 1|Con 2"] - Pros/cons list
- [disclosure] - Affiliate disclosure notice
- [newsletter title="..." description="..."] - Email signup form

### Available CSS Classes (style.css, components.css):
- Buttons: .tst-btn, .tst-btn-primary, .tst-btn-secondary, .tst-btn-amazon, .tst-btn-cta
- Cards: .review-card, .review-box, .card-rating, .card-price
- Grids: .posts-grid, .reviews-grid
- Badges: .badge, .badge-best-seller, .badge-editors-choice, .badge-budget-pick
- Components: .pros-cons, .faq-section, .newsletter-box, .author-box, .trust-badges

### Existing Scripts (scripts/):
- wp_publish.py, bulk-import.py, publish_to_hostinger.py, analyze_content.py, check_affiliate_links.py, fix_affiliate_tags.py

DO NOT create new components when existing ones can be modified or extended.

---

## TASK: Complete Site Audit of toolshedtested.com

### Step 1: Navigate and Review the Full Site

Go to https://toolshedtested.com and systematically review EVERY page:

1. **Homepage** - Analyze hero, navigation, CTAs, trust signals, mobile experience
2. **Category Pages** (/category/*) - Review filtering, sorting, product displays
3. **Review Posts** (/best-*) - Check CTAs, images, comparison tables, affiliate links
4. **Comparison Posts** (*-vs-*) - Review side-by-side comparisons, winner declarations
5. **About Page** (/about/) - Check author credibility, trust signals
6. **FAQ Page** (/faq/) - Verify FAQPage schema exists
7. **Contact Page** (/contact/) - Check form functionality
8. **Disclosure Page** (/disclosure/) - VERIFY IT EXISTS (was 404 previously)
9. **404 Page** - Check user experience
10. **Search Results** - Test search functionality

### Step 2: For Each Page, Document

```markdown
## [Page Name]
URL: [full URL]

### REMOVE
- [What to remove and why]

### UPDATE (Frontend)
| Element | Current | Recommended | Priority | Revenue Impact |
|---------|---------|-------------|----------|----------------|

### UPDATE (Backend)
| File | Change Needed | Priority |
|------|--------------|----------|

### ADD (Frontend)
1. [Feature] - Priority: X, Impact: +X%

### ADD (Backend)
1. [Feature] - File: X, Priority: X
```

### Step 3: Create Automated Implementation Plan

For every change, provide:
1. **Exact file paths** to modify
2. **Complete code** (not snippets)
3. **Terminal commands** to execute
4. **GitHub Actions workflows** for automation
5. **No manual steps** - everything via Claude Code, CLI, or scripts

### Step 4: Prioritize by Revenue Impact

Focus on changes that increase affiliate income:
1. **CTA Optimization** - More visible, larger buttons with price display
2. **Mobile Sticky CTA** - Floating "Check Price" bar
3. **Comparison Tables** - On every category page
4. **Product Images** - In every review
5. **Email Capture** - Lead magnets and exit intent popups
6. **Schema Markup** - FAQPage, AggregateRating, Product with offers
7. **Trust Signals** - Testing hours, products tested, credentials

### Step 5: Create Automation Scripts

Create/update these scripts with COMPLETE code:
1. Price monitoring (Amazon API)
2. Broken link checker
3. Schema validator
4. Image optimizer (WebP conversion)
5. Internal link suggester
6. Content freshness checker

### Step 6: Create GitHub Actions Workflows

- daily-maintenance.yml - Price checks, link audits
- weekly-audit.yml - Full site analysis, reports
- on-publish.yml - Validate, deploy, ping search engines

---

## OUTPUT REQUIREMENTS

1. **Save full audit** to `SITE_REVIEW_FULL.md`
2. **Create all scripts** in `scripts/` directory
3. **Create all workflows** in `.github/workflows/`
4. **Update theme files** as needed
5. **Provide execution commands** - copy-paste ready

Start by fetching the live site and analyzing each page systematically.
EOF
)"
