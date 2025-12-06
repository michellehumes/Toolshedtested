# Complete Site Review and Automated Implementation Plan

--dangerously-skip-permissions

## CRITICAL: Use Existing Components First

Before creating ANY new components, CSS classes, shortcodes, or templates, you MUST:

1. **Check existing shortcodes in `inc/shortcodes.php`:**
   - `[product_box id="123"]` - Product summary with image, rating, pros/cons, CTAs
   - `[comparison_table ids="1,2,3"]` - Side-by-side product comparison
   - `[star_rating rating="4.5"]` - Star display
   - `[affiliate_button url="..." text="..." style="amazon|cta|primary|secondary"]` - CTA buttons
   - `[pros_cons pros="Pro 1|Pro 2" cons="Con 1|Con 2"]` - Pros/cons list
   - `[disclosure]` - Affiliate disclosure notice
   - `[newsletter title="..." description="..."]` - Email signup form

2. **Check existing CSS classes in `assets/css/components.css` and `style.css`:**
   - `.review-card`, `.review-box`, `.review-box-header`, `.review-box-image`
   - `.posts-grid`, `.reviews-grid`, `.reviews-grid.small`
   - `.tst-btn`, `.tst-btn-primary`, `.tst-btn-secondary`, `.tst-btn-amazon`, `.tst-btn-cta`
   - `.pros-cons`, `.pros-list`, `.cons-list`
   - `.badge`, `.badge-best-seller`, `.badge-editors-choice`, `.badge-budget-pick`
   - `.comparison-table`, `.spec-table`
   - `.faq-section`, `.faq-item`, `.faq-question`, `.faq-answer`
   - `.newsletter-box`, `.affiliate-disclosure`
   - `.table-of-contents`, `.author-box`, `.trust-badges`
   - `.final-cta`, `.related-reviews`, `.specifications-section`

3. **Check existing template parts:**
   - `template-parts/content/content.php`, `content-single.php`, `content-search.php`, `content-none.php`
   - `template-parts/review/review-card.php`
   - `template-parts/product/comparison-table.php`

4. **Check existing automation scripts in `/scripts/`:**
   - `wp_publish.py` - Markdown to WordPress publishing
   - `bulk-import.py` - Batch content import
   - `publish_to_hostinger.py` - Hostinger deployment
   - `publish_pages.py` - Single page publishing
   - `analyze_content.py` - Content quality analysis
   - `check_affiliate_links.py` - Affiliate link validation
   - `fix_affiliate_tags.py` - Fix Amazon affiliate tags

**DO NOT create new components when existing ones can be modified or extended.**

---

## TASK: Complete Site Audit and Implementation

### Phase 1: Live Site Review

Navigate to https://toolshedtested.com and systematically review EVERY page:

#### 1.1 Homepage Analysis
- [ ] Capture current layout and above-the-fold content
- [ ] Document hero section, CTAs, and navigation
- [ ] Note any missing trust signals or conversion elements
- [ ] Check mobile responsiveness
- [ ] Identify income optimization opportunities

#### 1.2 Review Each Content Type
For each page type, document:
- Current URL structure
- Page load performance issues
- Missing schema markup
- Broken links or 404s
- Missing affiliate links or CTAs
- SEO issues (meta, headings, internal links)
- User experience problems
- Mobile issues

**Pages to Review:**
- Homepage (/)
- All category pages (/category/*)
- All product review posts (/best-*)
- All comparison posts (*-vs-*)
- About page (/about/)
- FAQ page (/faq/)
- Contact page (/contact/)
- Disclosure page (/disclosure/) - CHECK IF EXISTS
- Search results
- 404 page

#### 1.3 Backend/Code Review
- [ ] Review `functions.php` for optimization opportunities
- [ ] Check `inc/class-tst-schema.php` for missing schema types
- [ ] Audit `inc/class-tst-affiliate.php` for tracking improvements
- [ ] Review all template files for consistency
- [ ] Check for unused CSS/JS
- [ ] Identify missing accessibility features

### Phase 2: Detailed Recommendations

For EACH page, provide specific recommendations in this format:

```
## [Page Name]
URL: [full URL]

### REMOVE
- [Item 1: reason]
- [Item 2: reason]

### UPDATE (Frontend)
| Element | Current State | Recommended Change | Priority | Revenue Impact |
|---------|---------------|-------------------|----------|----------------|
| [element] | [current] | [change] | High/Med/Low | +X% |

### UPDATE (Backend)
| File | Current Code | Recommended Change | Priority |
|------|--------------|-------------------|----------|
| [file.php] | [current] | [change] | High/Med/Low |

### ADD (Frontend)
1. **[Feature Name]** - Priority: X, Revenue Impact: +X%
   - Description
   - Implementation notes (use existing component if possible)

### ADD (Backend)
1. **[Feature Name]** - Priority: X
   - File location
   - Code changes needed
```

### Phase 3: Automated Implementation Plan

Create a FULLY AUTOMATED implementation plan that requires ZERO manual work. Everything must be executable via:
- Claude Code
- Terminal/Bash commands
- GitHub Actions
- VS Code extensions
- Python/Node scripts

#### 3.1 Create GitHub Actions Workflows

**Daily Automation (`/.github/workflows/daily-maintenance.yml`):**
- Price monitoring via Amazon API
- Broken link checking
- Schema validation
- SEO score tracking

**Weekly Automation (`/.github/workflows/weekly-audit.yml`):**
- Full site crawl and analysis
- Performance benchmarking
- Content freshness checks
- Generate automated reports

**On-Push Automation (`/.github/workflows/on-publish.yml`):**
- Validate markdown content
- Check affiliate tag presence
- Generate schema markup
- Deploy to WordPress
- Ping search engines
- Post to social media

#### 3.2 Create/Update Automation Scripts

For each script, provide:
- Full file path
- Complete code
- Requirements/dependencies
- Cron schedule or trigger
- Expected output

**Required Scripts:**
1. `scripts/price_monitor.py` - Track Amazon prices, update product meta
2. `scripts/mobile_sticky_cta.js` - Add floating CTA for mobile
3. `scripts/schema_generator.py` - Generate complete schema for all page types
4. `scripts/image_optimizer.py` - Compress and convert images to WebP
5. `scripts/internal_linker.py` - Auto-suggest and add internal links
6. `scripts/email_capture_popup.js` - Exit intent and scroll-triggered popups
7. `scripts/social_share.py` - Auto-post to social platforms

#### 3.3 WordPress/Theme Updates

For each theme file change:
- Provide exact file path
- Show current code block
- Show replacement code block
- Explain the change

**Priority Updates:**
1. Enhanced CTA buttons (larger, colored, with price)
2. Mobile sticky "Check Price" bar
3. FAQPage schema for FAQ page
4. Product images in comparison tables
5. Author photo on About page
6. Trust badges on all pages
7. Email capture lead magnets

#### 3.4 Content Automation

Create templates and scripts for:
1. Auto-generating new review posts from product data
2. Updating prices across all posts
3. Adding missing affiliate links
4. Generating comparison tables automatically
5. Creating seasonal content (holiday deals, etc.)

### Phase 4: Implementation Execution Order

Provide a numbered execution order with exact commands:

```bash
# Step 1: [Description]
[exact terminal command]

# Step 2: [Description]
[exact terminal command]

# etc...
```

### Phase 5: Monitoring and Optimization

Create dashboards/reports for:
1. Affiliate click tracking
2. Conversion rates by page
3. Revenue by product category
4. SEO ranking changes
5. Page speed metrics

---

## OUTPUT FORMAT

Provide your complete analysis in a structured markdown file that can be saved and executed step-by-step.

Include:
1. **Executive Summary** - Top 5 quick wins for immediate revenue boost
2. **Complete Page-by-Page Analysis** - Every page audited
3. **Full Implementation Code** - All scripts, workflows, and theme updates
4. **Execution Commands** - Copy-paste ready bash/terminal commands
5. **Success Metrics** - How to measure improvement

---

## REMEMBER

- Use EXISTING components before creating new ones
- All changes must be automatable
- Focus on REVENUE IMPACT
- Mobile-first approach
- Prioritize quick wins that increase income
- Every recommendation must include exact implementation code
- No manual steps - everything via CLI, scripts, or GitHub Actions
