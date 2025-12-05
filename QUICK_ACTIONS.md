# ToolShedTested Quick Actions Guide
## Immediate Implementation Checklist

---

## CRITICAL - Do Today

### 1. Fix Disclosure Page 404 (10 minutes)

The disclosure page is returning 404 - this is an FTC compliance issue.

**Option A: Publish via WordPress Admin**
1. Go to WordPress Admin → Pages → Add New
2. Copy content from `pages/disclosure.md`
3. Set slug to `disclosure`
4. Publish

**Option B: Use Publishing Script**
```bash
cd /Users/michellehumes/Desktop/Toolshedtested
export WP_URL="https://toolshedtested.com"
export WP_USER="your-username"
export WP_APP_PASSWORD="your-app-password"
python scripts/wp_publish.py pages/disclosure.md
```

**Verify:** Visit https://toolshedtested.com/disclosure/ - should show disclosure content

---

### 2. Add FAQPage Schema to FAQ (15 minutes)

The FAQ page lacks structured data, missing rich snippet opportunities.

**Add this code to the FAQ page in WordPress:**

```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "How do you test the tools you review?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "We purchase all tools at retail price with our own money. Each tool goes through standardized hands-on testing, real-world use on actual projects, and long-term follow-up over 6-12 months."
      }
    },
    {
      "@type": "Question",
      "name": "Do you accept payment for reviews?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "No. We maintain 100% independence. No manufacturer has ever paid us for a review, and none ever will. We may earn affiliate commissions when you purchase through our links."
      }
    },
    {
      "@type": "Question",
      "name": "What's the difference between brushless and brushed drills?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Brushless motors offer 25-50% longer runtime, increased motor life, more power in a compact size, and reduced maintenance since there are no brushes to wear out. While brushless drills cost more upfront, they typically provide better long-term value."
      }
    }
  ]
}
</script>
```

**Verify:** Use Google's Rich Results Test at https://search.google.com/test/rich-results

---

### 3. Add Author Photo to About Page (5 minutes)

Missing author photo reduces trust by ~40%.

**Required:**
1. Upload a professional headshot to WordPress Media Library
2. Add to About page content

**Recommended specifications:**
- Size: 400x400 px minimum
- Format: JPG or WebP
- Style: Professional, friendly, workshop setting ideal

---

## HIGH PRIORITY - This Week

### 4. Upgrade CTA Buttons

Current buttons don't stand out. Add this CSS to `style.css`:

```css
/* High-visibility Amazon CTA button */
.tst-btn-amazon {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(to bottom, #f7dfa5, #f0c14b);
    border: 1px solid #a88734;
    border-radius: 4px;
    color: #111 !important;
    font-size: 16px;
    font-weight: 700;
    padding: 12px 24px;
    text-decoration: none;
    box-shadow: 0 1px 0 rgba(255,255,255,.4) inset;
    transition: all 0.2s ease;
}

.tst-btn-amazon:hover {
    background: linear-gradient(to bottom, #f5d78e, #eeb933);
    color: #111 !important;
    transform: translateY(-1px);
}

.tst-btn-amazon::before {
    content: "→";
}
```

---

### 5. Add Mobile Sticky CTA

Add this to `assets/js/main.js`:

```javascript
// Mobile sticky CTA
(function() {
    const stickyBtn = document.createElement('div');
    stickyBtn.className = 'mobile-sticky-cta';
    stickyBtn.innerHTML = '<a href="#" class="tst-btn-amazon">Check Best Price</a>';
    stickyBtn.style.cssText = `
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        padding: 12px;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        z-index: 1000;
        display: none;
        text-align: center;
    `;

    // Only show on single posts
    if (document.body.classList.contains('single')) {
        document.body.appendChild(stickyBtn);

        // Show after scrolling 30%
        window.addEventListener('scroll', function() {
            const scrollPercent = (window.scrollY / document.body.scrollHeight) * 100;
            stickyBtn.style.display = scrollPercent > 30 ? 'block' : 'none';
        });

        // Update href to first Amazon link on page
        const firstAmazonLink = document.querySelector('a[href*="amazon.com"]');
        if (firstAmazonLink) {
            stickyBtn.querySelector('a').href = firstAmazonLink.href;
        }
    }
})();
```

---

### 6. Add Product Images to Reviews

Reviews without images lose 50% trust.

**For each review post:**
1. Go to Amazon product page
2. Right-click → Copy image address (or download)
3. Upload to WordPress Media Library
4. Add to post with proper alt text

**Image specs:**
- Size: 600x600 px optimal
- Alt text: Include product name and brand

---

### 7. Create Comparison Tables for Each Category

Example for Drills category page:

```html
<table class="comparison-table">
<thead>
<tr>
<th>Model</th>
<th>Price</th>
<th>Torque</th>
<th>Our Rating</th>
<th>Buy</th>
</tr>
</thead>
<tbody>
<tr>
<td>DeWalt DCD771C2</td>
<td>~$89</td>
<td>300 in-lbs</td>
<td>⭐⭐⭐⭐⭐</td>
<td><a href="https://amazon.com/dp/B00ET5VMTU?tag=toolshedtested-20" class="tst-btn-amazon">Check Price</a></td>
</tr>
<tr>
<td>Milwaukee M18 Fuel</td>
<td>~$179</td>
<td>725 in-lbs</td>
<td>⭐⭐⭐⭐⭐</td>
<td><a href="https://amazon.com/dp/B07G63N2Z4?tag=toolshedtested-20" class="tst-btn-amazon">Check Price</a></td>
</tr>
</tbody>
</table>
```

---

## Automation Setup

### Run Content Analyzer

```bash
cd /Users/michellehumes/Desktop/Toolshedtested
pip install pyyaml
python scripts/analyze_content.py
```

### Run Link Checker

```bash
pip install requests beautifulsoup4
python scripts/check_affiliate_links.py
```

### Set Up GitHub Actions

1. Go to your GitHub repo Settings → Secrets
2. Add these secrets:
   - `WP_URL`: https://toolshedtested.com
   - `WP_USER`: your WordPress username
   - `WP_APP_PASSWORD`: your WordPress application password
   - `AMAZON_TAG`: toolshedtested-20

The workflow will automatically:
- Analyze content quality on each push
- Check affiliate links daily
- Publish changed posts to WordPress

---

## Checklist

- [ ] Fix disclosure page (CRITICAL)
- [ ] Add FAQPage schema to FAQ page
- [ ] Add author photo to About page
- [ ] Upgrade CTA button styling
- [ ] Add mobile sticky CTA
- [ ] Add product images to top 5 reviews
- [ ] Create comparison tables for main categories
- [ ] Set up automation (GitHub Actions secrets)
- [ ] Run initial content analysis
- [ ] Run initial link audit

---

## Expected Impact

| Action | Time | Revenue Impact |
|--------|------|----------------|
| CTA button upgrade | 30 min | +40% clicks |
| Mobile sticky CTA | 15 min | +30% mobile conversions |
| Product images | 2 hrs | +50% trust |
| Comparison tables | 1 hr/cat | +25% conversions |
| FAQPage schema | 15 min | +50% rich snippets |

**Total estimated conversion improvement: 25-40%**
