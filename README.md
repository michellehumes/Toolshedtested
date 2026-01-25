# Toolshed Tested

A production-ready WordPress theme for affiliate marketing blogs focused on outdoor power equipment reviews. Optimized for conversions, SEO, and speed.

![WordPress Version](https://img.shields.io/badge/WordPress-6.0%2B-blue)
![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-purple)
![License](https://img.shields.io/badge/License-GPL--2.0%2B-green)

## 🚀 Features

### Conversion Optimization
- **Review Cards** - Conversion-focused review summaries
- **Comparison Tables** - Easy-to-scan product comparisons
- **Pros & Cons Lists** - Clear, visual product evaluation
- **Prominent CTA Buttons** - Amazon and alternate retailer links
- **Affiliate Link Helpers** - Consistent tagging and disclosures
- **Trust Badges** - Best Seller, Editor's Choice, Budget Pick labels

### SEO Optimization
- **Schema.org Markup** - Rich snippets for articles and reviews
- **Breadcrumbs** - Structured navigation for better indexing
- **Meta Tags** - Auto-generated Open Graph and Twitter Cards
- **Clean URLs** - SEO-friendly permalink structure
- **Table of Contents** - Auto-generated from headings
- **Fast Loading** - Performance-focused code

### Speed & Performance
- **Minimal HTTP Requests** - Optimized asset loading
- **Lazy Loading** - Native browser lazy loading for images
- **Async/Defer Scripts** - Non-blocking JavaScript
- **Preconnect Hints** - Faster external resource loading
- **Clean Code** - No bloated frameworks

### Content Features
- **Review Sections** - Standardized layouts for top picks
- **Author Boxes** - Build trust with expert profiles
- **Related Reviews** - Keep readers engaged
- **Newsletter Integration** - Grow your email list

## 📦 Installation

### Requirements
- WordPress 6.0 or higher
- PHP 8.0 or higher

### Installation Steps

1. **Download the theme**
   ```bash
   git clone https://github.com/michellehumes/Toolshedtested.git
   ```

2. **Upload to WordPress**
   - Go to `Appearance > Themes > Add New > Upload Theme`
   - Upload the `toolshed-tested` folder from `wp-content/themes/`
   - Or FTP upload to `/wp-content/themes/toolshed-tested/`

3. **Activate the theme**
   - Go to `Appearance > Themes`
   - Click "Activate" on Toolshed Tested

4. **Configure settings**
   - Go to `Appearance > Customize`
   - Set up your logo, colors, and affiliate settings

## 🎨 Customization

### Theme Customizer Options

Navigate to `Appearance > Customize` to access:

- **Site Identity** - Logo, site title, tagline
- **Colors** - Primary, secondary, and accent colors
- **Affiliate Settings** - Amazon Associate ID, disclosure text
- **Social Media** - Social profile URLs
- **SEO Settings** - Google Analytics ID
- **Newsletter** - Form action URL

### Creating Reviews

Use standard posts with the review sections already built into the theme:
1. Create a new post
2. Add the comparison table, top picks, and buying guide sections
3. Add product images and affiliate links
4. Publish

### Shortcodes

```
[product_box id="123"]
Display a product review summary box

[comparison_table ids="123,456,789"]
Display a comparison table of multiple products

[star_rating rating="4.5"]
Display a star rating

[affiliate_button url="https://..." text="Buy Now"]
Display an affiliate CTA button

[pros_cons pros="Pro 1|Pro 2" cons="Con 1|Con 2"]
Display a pros and cons list

[disclosure]
Display the affiliate disclosure notice

[newsletter title="Subscribe" description="Get updates"]
Display a newsletter signup form
```

## 📁 Theme Structure

```
toolshed-tested/
├── assets/
│   ├── css/
│   │   ├── components.css    # Component styles
│   │   └── editor-style.css  # Block editor styles
│   ├── js/
│   │   ├── main.js          # Main JavaScript
│   │   └── affiliate.js     # Affiliate tracking
│   ├── images/              # Theme images
│   └── fonts/               # Custom fonts
├── inc/
│   ├── class-tst-affiliate.php    # Affiliate handling
│   ├── class-tst-product-review.php # Review meta boxes
│   ├── class-tst-schema.php       # Schema markup
│   ├── customizer.php             # Theme customizer
│   ├── shortcodes.php             # Theme shortcodes
│   ├── template-functions.php     # Helper functions
│   └── template-tags.php          # Template tags
├── template-parts/
│   ├── content/             # Post content templates
│   ├── review/              # Review templates
│   └── product/             # Product components
├── style.css                # Main stylesheet
├── functions.php            # Theme functions
├── header.php               # Header template
├── footer.php               # Footer template
├── index.php                # Main template
├── single.php               # Single post
├── single-product_review.php # Single review
├── archive.php              # Archive template
├── archive-product_review.php # Reviews archive
├── page.php                 # Page template
├── search.php               # Search results
├── 404.php                  # 404 page
├── sidebar.php              # Sidebar
├── comments.php             # Comments template
└── searchform.php           # Search form
```

## 🔧 Development

### Local Development

1. Set up a local WordPress environment
2. Clone the theme to your themes directory
3. Activate the theme
4. Make your changes

### Coding Standards

This theme follows:
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [WordPress Theme Handbook](https://developer.wordpress.org/themes/)

### Performance Tips

- Use the built-in lazy loading
- Optimize images before upload
- Enable browser caching via `.htaccess`
- Use a caching plugin for production

## 🧪 Content Workflow

This repo includes a markdown-first content pipeline in `posts/` plus helper scripts in `scripts/`.

### Typical Flow
1. Write or expand content in `posts/*.md`
2. Run content analysis and fix gaps
3. Publish to WordPress via REST API

### Publish (REST API)
Set environment variables:
```bash
export WP_URL="https://toolshedtested.com"
export WP_USER="you@example.com"
export WP_APP_PASSWORD="your-app-password"
export AMAZON_TAG="toolshedtested-20"
```

Then publish:
```bash
python3 scripts/wp_publish.py posts/angle-grinders.md
```

### Content Analysis
```bash
python3 scripts/analyze_content.py
```
Output: `scripts/content_analysis_report.txt`

### Image Inserts
To insert top-pick images into each post:
```bash
python3 scripts/add_post_images.py
```

## 📊 Analytics & Tracking

### Built-in Tracking

The theme includes built-in affiliate click tracking:
- Clicks are logged to post meta
- Daily logs stored in transients
- Privacy-respecting IP hashing

### Google Analytics

Add your GA4 measurement ID in:
`Appearance > Customize > SEO Settings`

## 🔒 Security

- XML-RPC disabled by default
- Security headers enabled
- Nonce verification on AJAX
- Proper input sanitization
- Output escaping throughout

## 📄 License

This theme is licensed under [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.html).

## 🙏 Credits
Font and icon sources are defined in theme assets.

## 🤝 Support

For support, please [open an issue](https://github.com/michellehumes/Toolshedtested/issues) on GitHub.

---

**Toolshed Tested** - Expert Reviews for Outdoor Power Equipment 🔧
