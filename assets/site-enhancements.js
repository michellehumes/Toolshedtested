/**
 * SITE ENHANCEMENTS FOR TOOLSHED TESTED
 * =====================================
 * Loaded via CDN: cdn.jsdelivr.net/gh/michellehumes/Toolshedtested@main/assets/site-enhancements.js
 *
 * Features:
 * - Affiliate link tracking (GA4)
 * - Mobile sticky CTA bar
 * - Link enhancement (nofollow, target)
 * - Click event tracking
 */

(function() {
  'use strict';

  // Configuration
  const CONFIG = {
    affiliateTag: 'toolshedtested-20',
    amazonSelectors: 'a[href*="amazon.com"], a[href*="amzn.to"]',
    homeDepotSelectors: 'a[href*="homedepot.com"]',
    lowesSelectors: 'a[href*="lowes.com"]',
    enableStickyCTA: true,
    stickyScrollThreshold: 500,
    enableExitIntent: false,
    debug: false
  };

  /**
   * Log helper for debugging
   */
  function log(...args) {
    if (CONFIG.debug) {
      console.log('[TST]', ...args);
    }
  }

  /**
   * Initialize all enhancements when DOM is ready
   */
  function init() {
    log('Initializing site enhancements...');

    enhanceAffiliateLinks();
    setupClickTracking();

    if (CONFIG.enableStickyCTA && isMobile() && isReviewPage()) {
      setupStickyCTA();
    }

    setupTableOfContentsHighlight();

    log('Site enhancements initialized');
  }

  /**
   * Check if DOM is ready
   */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  /**
   * Enhance all affiliate links with proper attributes
   */
  function enhanceAffiliateLinks() {
    const affiliateLinks = document.querySelectorAll(
      CONFIG.amazonSelectors + ',' +
      CONFIG.homeDepotSelectors + ',' +
      CONFIG.lowesSelectors
    );

    log(`Found ${affiliateLinks.length} affiliate links`);

    affiliateLinks.forEach(function(link) {
      // Ensure links open in new tab
      link.setAttribute('target', '_blank');

      // Set proper rel attributes for affiliate links
      const currentRel = link.getAttribute('rel') || '';
      if (!currentRel.includes('nofollow')) {
        link.setAttribute('rel', 'nofollow noopener sponsored');
      }

      // Add visual class for styling
      if (!link.classList.contains('affiliate-link')) {
        link.classList.add('affiliate-link');
      }

      // Ensure Amazon links have affiliate tag
      if (link.href.includes('amazon.com') && !link.href.includes('tag=')) {
        try {
          const url = new URL(link.href);
          url.searchParams.set('tag', CONFIG.affiliateTag);
          link.href = url.toString();
          log('Added affiliate tag to:', link.href);
        } catch (e) {
          log('Error processing URL:', e);
        }
      }
    });
  }

  /**
   * Set up click tracking for analytics
   */
  function setupClickTracking() {
    // Track Amazon clicks
    document.querySelectorAll(CONFIG.amazonSelectors).forEach(function(link) {
      link.addEventListener('click', function(e) {
        trackAffiliateClick('Amazon', this.href, this.textContent);
      });
    });

    // Track Home Depot clicks
    document.querySelectorAll(CONFIG.homeDepotSelectors).forEach(function(link) {
      link.addEventListener('click', function(e) {
        trackAffiliateClick('Home Depot', this.href, this.textContent);
      });
    });

    // Track Lowe's clicks
    document.querySelectorAll(CONFIG.lowesSelectors).forEach(function(link) {
      link.addEventListener('click', function(e) {
        trackAffiliateClick('Lowes', this.href, this.textContent);
      });
    });

    log('Click tracking setup complete');
  }

  /**
   * Send click event to Google Analytics 4
   */
  function trackAffiliateClick(retailer, url, linkText) {
    // GA4 tracking
    if (typeof gtag === 'function') {
      gtag('event', 'affiliate_click', {
        'event_category': 'Affiliate',
        'event_label': retailer,
        'link_url': url,
        'link_text': linkText ? linkText.trim().substring(0, 100) : '',
        'page_title': document.title,
        'page_path': window.location.pathname
      });
      log('Tracked GA4 event:', retailer, url);
    }

    // Legacy Universal Analytics (if still in use)
    if (typeof ga === 'function') {
      ga('send', 'event', 'Affiliate', 'Click', retailer);
    }
  }

  /**
   * Set up sticky CTA bar for mobile on review pages
   */
  function setupStickyCTA() {
    // Find the first product recommendation
    const productCard = document.querySelector(
      '.product-cta-card, ' +
      '[class*="best-overall"], ' +
      '[class*="top-pick"], ' +
      'div[style*="border"][style*="green"]'
    );

    if (!productCard) {
      log('No product card found for sticky CTA');
      return;
    }

    // Try to get product info
    const productName = productCard.querySelector('h3, .product-name, strong')?.textContent || 'Top Pick';
    const productLink = productCard.querySelector('a[href*="amazon.com"]')?.href || '#';

    if (productLink === '#') {
      log('No Amazon link found for sticky CTA');
      return;
    }

    // Create sticky bar
    const stickyBar = document.createElement('div');
    stickyBar.id = 'tst-sticky-cta';
    stickyBar.innerHTML = `
      <div class="tst-sticky-product">
        <div class="tst-sticky-name">${escapeHtml(productName.substring(0, 30))}</div>
        <div class="tst-sticky-label">Our Top Pick</div>
      </div>
      <a href="${productLink}" class="tst-sticky-btn" target="_blank" rel="nofollow noopener sponsored">
        Check Price
      </a>
    `;

    // Add styles
    const styles = document.createElement('style');
    styles.textContent = `
      #tst-sticky-cta {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        padding: 12px 16px;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
        z-index: 9999;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
      }
      #tst-sticky-cta.active {
        display: flex;
      }
      .tst-sticky-product {
        flex: 1;
        min-width: 0;
      }
      .tst-sticky-name {
        font-weight: 600;
        font-size: 14px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #1a1a1a;
      }
      .tst-sticky-label {
        font-size: 12px;
        color: #666;
      }
      .tst-sticky-btn {
        background: linear-gradient(135deg, #ff9800, #f57c00);
        color: #1a1a1a !important;
        padding: 12px 20px;
        border-radius: 6px;
        text-decoration: none !important;
        font-weight: 700;
        font-size: 14px;
        flex-shrink: 0;
        white-space: nowrap;
      }
      body.has-sticky-cta {
        padding-bottom: 70px;
      }
      @media (min-width: 769px) {
        #tst-sticky-cta { display: none !important; }
        body.has-sticky-cta { padding-bottom: 0; }
      }
    `;

    document.head.appendChild(styles);
    document.body.appendChild(stickyBar);
    document.body.classList.add('has-sticky-cta');

    // Show/hide based on scroll
    let ticking = false;

    window.addEventListener('scroll', function() {
      if (!ticking) {
        window.requestAnimationFrame(function() {
          if (window.scrollY > CONFIG.stickyScrollThreshold) {
            stickyBar.classList.add('active');
          } else {
            stickyBar.classList.remove('active');
          }
          ticking = false;
        });
        ticking = true;
      }
    });

    // Track clicks on sticky CTA
    stickyBar.querySelector('.tst-sticky-btn').addEventListener('click', function() {
      trackAffiliateClick('Amazon', this.href, 'Sticky CTA');
    });

    log('Sticky CTA initialized');
  }

  /**
   * Setup Table of Contents scroll highlighting
   */
  function setupTableOfContentsHighlight() {
    const toc = document.querySelector('.table-of-contents');
    if (!toc) return;

    const headings = document.querySelectorAll('h2[id], h3[id]');
    const tocLinks = toc.querySelectorAll('a');

    if (!headings.length || !tocLinks.length) return;

    function updateActiveLink() {
      const scrollPos = window.scrollY + 120;

      headings.forEach(function(heading, index) {
        const nextHeading = headings[index + 1];
        const sectionTop = heading.offsetTop;
        const sectionBottom = nextHeading ? nextHeading.offsetTop : document.body.scrollHeight;

        if (scrollPos >= sectionTop && scrollPos < sectionBottom) {
          tocLinks.forEach(link => link.classList.remove('active'));
          const activeLink = toc.querySelector(`a[href="#${heading.id}"]`);
          if (activeLink) {
            activeLink.classList.add('active');
          }
        }
      });
    }

    // Add active styles
    const tocStyles = document.createElement('style');
    tocStyles.textContent = `
      .table-of-contents a.active {
        color: #2d5a27;
        font-weight: 600;
      }
    `;
    document.head.appendChild(tocStyles);

    window.addEventListener('scroll', debounce(updateActiveLink, 100));
    log('Table of Contents highlighting initialized');
  }

  /**
   * Check if user is on mobile device
   */
  function isMobile() {
    return window.innerWidth <= 768;
  }

  /**
   * Check if current page is a review/post page
   */
  function isReviewPage() {
    return document.body.classList.contains('single-post') ||
           document.body.classList.contains('single') ||
           document.querySelector('article.post') !== null;
  }

  /**
   * Debounce function for scroll events
   */
  function debounce(func, wait) {
    let timeout;
    return function(...args) {
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(this, args), wait);
    };
  }

  /**
   * Escape HTML to prevent XSS
   */
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

})();
