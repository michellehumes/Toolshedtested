# ToolShed Tested — Complete Site Audit
**Date:** March 15, 2026
**URL:** https://toolshedtested.com
**Scope:** Full homepage, content, code, SEO, UX, and revenue audit

---

## Executive Summary — Top 10 Quick Wins

| # | Action | Effort | Revenue Impact | Priority |
|---|--------|--------|----------------|----------|
| 1 | **Add real product images** — placeholders are killing trust & conversions | Medium | +25-40% CTR | CRITICAL |
| 2 | **Add author byline + photo on every post** — E-E-A-T is a ranking factor | Low | +10-15% organic | HIGH |
| 3 | **Add `rel="nofollow sponsored"` to ALL affiliate links** — FTC compliance gap | Low | Risk mitigation | CRITICAL |
| 4 | **Add Product schema to review posts** — missing rich snippets in SERPs | Low | +15-25% CTR | HIGH |
| 5 | **Consolidate duplicate posts** (3+ cordless drill URLs, 2+ lawn mower URLs) | Medium | Fix cannibalization | HIGH |
| 6 | **Remove "Uncategorized" category** — 1 of 40 categories, looks unprofessional | Low | Trust improvement | MEDIUM |
| 7 | **Add per-article affiliate disclosure near first Amazon link** | Low | FTC compliance | HIGH |
| 8 | **Add breadcrumb nav to all pages** (only on posts currently) | Low | +SEO + UX | MEDIUM |
| 9 | **Delete duplicate /disclosure/ page** (keep /affiliate-disclosure/) | Low | Fix confusion | LOW |
| 10 | **Reduce 40 categories to ~15-20** — too many thin category pages | Medium | Fix thin content | MEDIUM |

---

## Site Inventory

### Content Volume
| Type | Count | Notes |
|------|-------|-------|
| Posts | 81 | Mix of reviews, comparisons, guides, industry news |
| Pages | 17 | Includes 6 "ultimate guide" pages that look like posts |
| Categories | 40 | Many overlapping (e.g., "Outdoor" vs "Outdoor Tools", "Saws" vs "Table Saws" vs "Miter Saws") |
| Total URLs | 138+ | Including category archives |

### Content Types Identified
- **Product roundups** (best-X-2026): ~35 posts — PRIMARY revenue driver
- **Brand comparisons** (X-vs-Y): 4 posts — HIGH intent traffic
- **How-to/troubleshooting**: 5 posts — informational traffic
- **Industry news/trends**: 4 posts — authority building
- **Buying guides**: 3 hub pages
- **Static pages**: About, FAQ, Contact, Disclosure, How We Test, Privacy, Reviews

---

## Page-by-Page Analysis

### 1. HOMEPAGE (/)

**Current State:** Hero + 3 top picks + 6 latest reviews grid. Clean layout with trust bar.

#### ISSUES
| Element | Problem | Fix | Priority |
|---------|---------|-----|----------|
| Product images | Placeholder gradients instead of real images | Add actual product photography or Amazon product images | CRITICAL |
| Top Picks query | Falls back to "highest rated" but all posts may have same rating | Add manual top-picks selection via Customizer | HIGH |
| "Browse Reviews" CTA | Points to `/blog/` not `/reviews/` — inconsistent | Point to `/reviews/` | LOW |
| Trust bar numbers | "150+ Tools Tested" — is this accurate with 81 posts? | Verify or update claim | MEDIUM |
| No testimonials | Zero social proof from actual users | Add 2-3 testimonials or link to comments | MEDIUM |
| Category links | Only 6 shown, but 40 exist — confusing IA | Show top 8-10 most important categories | LOW |

#### WHAT'S WORKING
- Clean hero with clear value proposition
- Trust bar with specific metrics
- Star ratings on cards
- Mobile-responsive grid layout
- Email popup with 30s delay + 7-day cooldown

---

### 2. REVIEW POSTS (e.g., /best-cordless-drills-2026/)

**Current State:** Well-structured roundups with comparison tables, individual product reviews, pros/cons, and FAQ sections.

#### ISSUES
| Element | Problem | Fix | Priority |
|---------|---------|-----|----------|
| Affiliate link `rel` attrs | Missing `rel="nofollow sponsored"` on rendered links | Fix `class-tst-affiliate.php` regex to catch all links | CRITICAL |
| Author attribution | No byline, no author box, no credentials | Add author box with photo + bio using existing `.author-box` CSS | HIGH |
| Product images | Placeholder gradients, no real photos | Source product images from Amazon or original photography | CRITICAL |
| Publication date | Not visible in article header | Add visible "Published/Updated" date | HIGH |
| Sample disclosure | No mention if products were purchased vs. provided | Add "How we obtained these products" note | MEDIUM |
| Internal links | "Explore More" section has 19 links — too many | Limit to 5-7 most relevant | LOW |
| Methodology link | References testing but no linked methodology page | Link to `/how-we-test/` from every review | MEDIUM |

#### WHAT'S WORKING
- Proper H1 > H2 > H3 heading hierarchy
- Quick Picks comparison table at top
- Badge system (Best Overall, Best Value, Budget Pick)
- FAQ section with FAQPage schema
- Newsletter signup in content
- Mobile-responsive tables

---

### 3. COMPARISON POSTS (e.g., /dewalt-vs-milwaukee/)

**Same issues as review posts, plus:**

| Element | Problem | Fix | Priority |
|---------|---------|-----|----------|
| No comparison table shortcode used | Posts may be plain text comparisons | Use `[comparison_table]` shortcode | MEDIUM |
| No winner declaration | Users want a clear "which to buy" answer | Add verdict section with CTA | HIGH |
| Missing "vs" schema | No ComparisonPage or structured data for comparisons | Custom comparison schema | LOW |

---

### 4. DUPLICATE / CANNIBALIZED CONTENT

**CRITICAL ISSUE:** Multiple posts target the same keywords, competing against each other in search.

| Keyword Target | Duplicate URLs | Action |
|----------------|---------------|--------|
| Best cordless drills 2026 | `/best-cordless-drills-2026/`, `/cordless-drills-26/`, `/best-cordless-drills-2026-7-top-picks-tested-for-power-precision/`, `/best-cordless-drills-2026-7-top-picks-tested-for-power-speed-and-runtime/`, `/best-cordless-drills-of-2026-hands-on-roundup-and-buyers-guide/` | Keep ONE canonical, redirect others |
| Best impact drivers | `/best-impact-drivers-2026/` (page), `/best-impact-drivers-2026-top-6-picks-for-torque-speed-value/` (post), `/impact-drivers-50/` | Keep ONE, redirect others |
| Best lawn mowers | `/best-battery-powered-lawn-mowers-2026-49/`, `/best-battery-powered-lawn-mowers-50/`, `/lawn-mowers-50/` | Keep ONE, redirect others |
| Best angle grinders | `/angle-grinders-50/`, `/best-cordless-angle-grinders-2026-5-tested-for-power-safety-runtime/`, `/best-angle-grinders-2026-7-models-tested...` | Keep ONE, redirect others |
| Best chainsaws | `/chainsaws-50/`, `/best-battery-chainsaws-50/`, `/best-chainsaws-2026-7-models-tested...` | Keep ONE, redirect others |
| Best circular saws | `/circular-saws-50/`, `/best-circular-saws-2026-6-models-tested...` | Keep ONE, redirect others |
| Best cordless drills (page) | `/best-cordless-drills-2026/` (page) vs posts above | Redirect page to best post |
| Best impact wrenches | `/best-cordless-impact-wrenches-2026/`, `/best-impact-wrenches-2026-7-tested...` | Keep ONE, redirect other |
| Best combo kits | `/best-power-tool-combo-kits-2026/`, `/best-cordless-tool-combo-kits-2026-7-kits-tested...` | Keep ONE, redirect other |

**Impact:** This is likely your #1 SEO problem. Google is splitting authority across 2-5 URLs per keyword instead of consolidating it into one strong page. This alone could be costing 30-50% of potential organic traffic.

---

### 5. CATEGORY TAXONOMY ISSUES

**40 categories is excessive for 81 posts.** Many categories likely have only 1-2 posts, creating thin index pages that hurt SEO.

#### Overlapping Categories
| Overlap | Categories | Fix |
|---------|-----------|-----|
| Outdoor | "Outdoor", "Outdoor Tools", "Lawn and Garden" | Merge into "Outdoor & Lawn" |
| Saws | "Saws", "Table Saws", "Miter Saws", "Reciprocating Saws" | Use "Saws" as parent, others as subcategories |
| Drills | "Drills", "Drill Presses" | Keep separate (different tools) |
| General | "Power Tools", "Uncategorized" | Remove "Uncategorized", use "Power Tools" sparingly |
| Guides | "Guides", "Buying Guides", "Home Improvement" | Merge into "Buying Guides" |
| Comparisons | "Comparisons" | Keep — good content type differentiator |

#### Recommended Category Structure (15 categories)
1. Drills & Drivers
2. Saws (with subcategories: Table, Miter, Circular, Reciprocating)
3. Grinders
4. Sanders
5. Multi-Tools
6. Routers & Planers
7. Nail Guns
8. Welding
9. Compressors & Shop Vacs
10. Lawn Mowers (with subcategory: Riding Mowers)
11. Chainsaws & Pole Saws
12. Outdoor Power (Leaf Blowers, String Trimmers, Pressure Washers, Snow Blowers, Tillers, Log Splitters, Wood Chippers)
13. Generators
14. Buying Guides
15. Comparisons

---

### 6. STATIC PAGES

#### /about/
| Issue | Fix | Priority |
|-------|-----|----------|
| No author photo | Add headshot — critical for E-E-A-T | HIGH |
| No credentials listed | Add tool testing experience, certifications | HIGH |
| No social links | Add personal/professional social profiles | LOW |

#### /faq/
| Issue | Fix | Priority |
|-------|-----|----------|
| FAQPage schema has 11 hardcoded Q&As in PHP | Move to CMS or make dynamic | LOW |
| Schema is working | Keep as-is | N/A |

#### /affiliate-disclosure/ vs /disclosure/
| Issue | Fix | Priority |
|-------|-----|----------|
| Two separate disclosure pages exist | Delete `/disclosure/`, redirect to `/affiliate-disclosure/` | MEDIUM |
| Missing per-link disclosures in articles | Add inline disclosure near first affiliate link | HIGH |
| Vague "clearly marked" claim | Actually mark affiliate links visibly | MEDIUM |

#### /how-we-test/
| Issue | Fix | Priority |
|-------|-----|----------|
| Not linked from review posts | Add link in every review post intro | MEDIUM |
| Needs specific test protocols | Add testing criteria, equipment used, scoring methodology | MEDIUM |

#### /contact/
No major issues identified.

#### /privacy-policy/
No major issues identified.

---

### 7. 404 PAGE

Exists at `404.php` — should include search form and popular post links. Need to verify live behavior.

---

## Technical / Code Audit

### Schema Markup (`class-tst-schema.php`)

| Schema Type | Status | Issue | Fix |
|-------------|--------|-------|-----|
| Organization | Implemented | Homepage only — correct | N/A |
| WebSite + SearchAction | Implemented | Good for sitelinks search | N/A |
| BreadcrumbList | Implemented | Only on singular pages | Add to archives/categories |
| Article | Implemented | Missing author object | Add author with name, url, image |
| Review | Implemented | Good — includes rating | N/A |
| Product | **MISSING** | No Product schema on review pages | Add itemReviewed Product entities |
| FAQPage | Implemented | Hardcoded 11 Q&As | Make dynamic per-page |
| HowTo | **MISSING** | Useful for how-to posts | Add for how-to content |
| ItemList | **MISSING** | Roundup posts should have this | Add for "best of" lists |

### Affiliate Link Handler (`class-tst-affiliate.php`)

| Issue | Details | Fix |
|-------|---------|-----|
| Regex may miss links | Only processes `the_content` filter — misses shortcode output? | Verify shortcode links also get processed |
| Click tracking stores in post meta | No aggregate dashboard, just raw meta | Add admin dashboard page |
| Amazon tag append | Works via URL parsing — good approach | N/A |
| `rel` attributes | Code adds `nofollow noopener sponsored` — verify it's actually running on all links | Test with live page source |

### Performance

| Issue | Details | Fix |
|-------|---------|-----|
| Google Fonts external request | Loading Inter + Montserrat from Google | Self-host fonts for speed |
| No image optimization | No WebP conversion, no lazy loading beyond native | Add WebP with fallback |
| Large inline CSS | ~3,200 lines of CSS across 2 files | Acceptable for now, consider critical CSS extraction later |
| No caching headers | Server-side caching depends on Hostinger config | Add cache-control headers in `.htaccess` |

### Security (`functions.php`)

- Security headers are implemented — good
- Input sanitization on meta fields — good
- AJAX handlers use nonce verification — good
- No obvious vulnerabilities found

---

## SEO Audit

### On-Page SEO

| Factor | Status | Notes |
|--------|--------|-------|
| Title tags | Using Rank Math | Verify each page has unique, keyword-rich title |
| Meta descriptions | Using Rank Math | Verify all pages have descriptions |
| H1 tags | One per page | Correct usage |
| Internal linking | Moderate | Could be stronger between related reviews |
| Image alt text | Unknown | Need to verify on live posts |
| URL structure | Inconsistent | Some have `-50` suffix, some have long descriptive slugs |
| Canonical tags | Using Rank Math | Critical for duplicate content issue above |
| XML sitemap | Present | Generated by Rank Math |

### Technical SEO

| Factor | Status | Fix |
|--------|--------|-----|
| Mobile-friendly | Yes | Responsive design with breakpoints |
| HTTPS | Yes | SSL active |
| Page speed | Unknown | Run PageSpeed Insights |
| Core Web Vitals | Unknown | Check in Search Console |
| Robots.txt | Unknown | Verify exists and is correct |
| Redirect chains | Likely | Given duplicate content issue |
| Orphan pages | Possible | Some `-50` suffix pages may not be linked from nav |

### Content SEO Issues

1. **Keyword cannibalization** — Multiple pages targeting same keywords (see Section 4)
2. **Thin category pages** — 40 categories for 81 posts = ~2 posts per category average
3. **No internal linking strategy** — Reviews don't consistently link to related comparisons or guides
4. **Missing "last updated" dates** — Google favors fresh content signals
5. **No FAQ schema on review posts** — Only on `/faq/` page (some reviews have FAQ sections but need schema)

---

## UX / Conversion Audit

### Conversion Funnel Issues

| Stage | Issue | Fix | Revenue Impact |
|-------|-------|-----|----------------|
| **Awareness** | No real product images — users can't see what they're buying | Add images | +25-40% |
| **Trust** | No author face/credentials — who wrote this? | Add author box | +10-15% |
| **Decision** | Comparison tables exist but lack images | Add product thumbnails to tables | +10% |
| **Action** | Amazon buttons work but lack price display on some posts | Show price on every CTA | +5-10% |
| **Return** | Email popup works but no lead magnet delivery system | Set up actual email automation | +long-term |

### Mobile UX

| Element | Status | Notes |
|---------|--------|-------|
| Mobile sticky CTA | Implemented in footer.php | Shows "Check Best Price on Amazon" — good |
| Hamburger nav | Working | Standard toggle |
| Touch targets | Adequate | Buttons are full-width on mobile |
| Font sizing | Good | Uses `clamp()` for responsive typography |
| Table scrolling | Enabled | Horizontal scroll on narrow screens |

### Accessibility

| Issue | Fix | Priority |
|-------|-----|----------|
| Orange button contrast may fail WCAG AA | Test contrast ratio, darken if needed | MEDIUM |
| Form labels may not be explicit | Add `<label>` elements to all form inputs | MEDIUM |
| Skip navigation link missing | Add skip-to-content link in header | LOW |
| Image alt text unverified | Audit all images for descriptive alt text | MEDIUM |

---

## Recommended Implementation Priorities

### Phase 1: Critical Fixes (Week 1) — Compliance & Trust

1. **Fix affiliate link `rel` attributes** — Verify `class-tst-affiliate.php` processes ALL affiliate links including shortcode output
2. **Consolidate duplicate content** — Pick canonical URLs, set up 301 redirects for ~15-20 duplicate posts
3. **Add author byline + photo** — Update `single.php` and `content-single.php` to show author box
4. **Add inline affiliate disclosure** — Insert `[disclosure]` shortcode or auto-insert near first affiliate link
5. **Delete `/disclosure/` page** — Redirect to `/affiliate-disclosure/`
6. **Remove "Uncategorized" category** — Reassign any posts, delete category

### Phase 2: Revenue Optimization (Weeks 2-3) — Images & Schema

7. **Add product images to all review posts** — Source from Amazon Product API or manual screenshots
8. **Add Product schema** — Extend `class-tst-schema.php` with Product + AggregateRating for review posts
9. **Add ItemList schema** — For roundup/best-of posts
10. **Add visible "Updated" dates** — Show last modified date on all review posts
11. **Link to /how-we-test/ from every review** — Auto-insert via template or shortcode
12. **Add comparison table images** — Extend `[comparison_table]` shortcode to include product thumbnails

### Phase 3: SEO Cleanup (Weeks 3-4) — Categories & Structure

13. **Consolidate 40 categories to ~15** — Merge overlapping, redirect old category URLs
14. **Fix URL inconsistencies** — Redirect `-50` and `-26` suffix URLs to clean versions
15. **Build internal linking map** — Every review links to 2-3 related reviews/comparisons
16. **Add breadcrumbs to all page types** — Extend schema breadcrumb to archives and pages
17. **Self-host Google Fonts** — Download Inter + Montserrat, serve locally

### Phase 4: Growth (Month 2+) — Content & Automation

18. **Fill category gaps** — Write reviews for categories with <3 posts
19. **Add HowTo schema** — For troubleshooting/how-to posts
20. **Build affiliate click dashboard** — Admin page showing clicks by post/product
21. **Set up email automation** — Welcome sequence for newsletter signups
22. **Add social sharing buttons** — On every review post
23. **Create seasonal content calendar** — Holiday deals, spring/summer tool guides

---

## Automation Scripts Needed

### Already Existing (17 scripts)
Your `/scripts/` directory is well-stocked. Key scripts to leverage:
- `check_affiliate_links.py` — Run weekly to catch broken links
- `analyze_content.py` — Run on all posts to score quality
- `fix_affiliate_tags.py` — Run to ensure all Amazon links have correct tag
- `validate_schema.py` — Run to verify schema on all pages
- `full_site_audit.py` — Run monthly

### New Scripts Recommended

1. **`scripts/consolidate_duplicates.py`** — Identify duplicate posts by keyword overlap, generate redirect map
2. **`scripts/add_product_images.py`** (exists but verify it works) — Batch add featured images from Amazon
3. **`scripts/internal_link_audit.py`** — Map internal links, find orphan pages, suggest cross-links
4. **`scripts/category_cleanup.py`** — Audit category assignments, merge categories, generate redirects

### GitHub Actions Already Existing (10 workflows)
You already have `daily-maintenance.yml`, `weekly-audit.yml`, `on-publish.yml`, and `deploy.yml`. These cover most automation needs. Verify they're all running successfully.

---

## Success Metrics

| Metric | Current (Estimated) | Target (90 days) | How to Measure |
|--------|---------------------|-------------------|----------------|
| Organic traffic | Baseline | +40-60% | Google Search Console |
| Affiliate click rate | Unknown | 3-5% of pageviews | Affiliate click tracking in post meta |
| Pages with real images | ~0% | 100% | Manual audit |
| Duplicate content pages | ~20 | 0 | Sitemap count |
| Category pages | 40 | 15-20 | WordPress admin |
| Schema rich results | Partial | All reviews show rich snippets | Search Console Enhancement report |
| FTC compliance | Partial | Full compliance | Manual audit |
| E-E-A-T signals | Weak | Author box on all posts | Template check |
| Page speed (mobile) | Unknown | >80 PageSpeed score | PageSpeed Insights |
| Email subscribers | Unknown | Track weekly growth | Newsletter platform |

---

## Summary

The site has a **solid technical foundation** — clean theme code, good shortcodes, working affiliate tracking, responsive design, and strong automation scripts. The main issues are:

1. **Content duplication is severe** — 15-20 posts competing against themselves in search. This is the single biggest SEO problem and should be fixed first.
2. **No product images** — Placeholder gradients destroy trust and conversions. This is the single biggest conversion problem.
3. **E-E-A-T signals are weak** — No author attribution, no credentials, no methodology transparency. Google cares about this for YMYL-adjacent content.
4. **Category bloat** — 40 categories for 81 posts creates thin index pages that dilute crawl budget.
5. **FTC compliance gaps** — The affiliate handler adds `rel` attributes but this needs verification on live pages, and per-article disclosures should be more prominent.

Fix these 5 things and you'll likely see a significant improvement in both organic traffic and affiliate revenue.
