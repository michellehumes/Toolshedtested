/**
 * Toolshed Tested - Site Enhancement JavaScript
 * Improves UX and conversions
 */

(function() {
  'use strict';

  // Wait for DOM
  document.addEventListener('DOMContentLoaded', function() {
    initFaqAccordion();
    initReadingProgress();
    initStickyMobileCta();
    initBackToTop();
    initSmoothScroll();
    initTableOfContents();
    trackAffiliateClicks();
  });

  /**
   * FAQ Accordion
   */
  function initFaqAccordion() {
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(function(item) {
      const question = item.querySelector('.faq-question');
      const answer = item.querySelector('.faq-answer');

      if (question && answer) {
        question.addEventListener('click', function() {
          const isOpen = item.classList.contains('open');

          // Close all others
          faqItems.forEach(function(other) {
            other.classList.remove('open');
            const otherAnswer = other.querySelector('.faq-answer');
            if (otherAnswer) otherAnswer.style.maxHeight = '0';
          });

          // Toggle current
          if (!isOpen) {
            item.classList.add('open');
            answer.style.maxHeight = answer.scrollHeight + 'px';
          }
        });
      }
    });
  }

  /**
   * Reading Progress Bar
   */
  function initReadingProgress() {
    const progressBar = document.createElement('div');
    progressBar.className = 'reading-progress';
    document.body.prepend(progressBar);

    window.addEventListener('scroll', function() {
      const scrollTop = window.pageYOffset;
      const docHeight = document.documentElement.scrollHeight - window.innerHeight;
      const progress = (scrollTop / docHeight) * 100;
      progressBar.style.width = progress + '%';
    });
  }

  /**
   * Sticky Mobile CTA
   */
  function initStickyMobileCta() {
    if (window.innerWidth > 768) return;

    const amazonBtn = document.querySelector('.review-box-cta .tst-btn-amazon');
    if (!amazonBtn) return;

    const stickyBar = document.createElement('div');
    stickyBar.className = 'sticky-cta-bar';
    stickyBar.style.cssText = 'position:fixed;bottom:0;left:0;right:0;background:#fff;padding:12px 16px;box-shadow:0 -4px 20px rgba(0,0,0,0.15);z-index:1000;display:none;';

    const stickyBtn = amazonBtn.cloneNode(true);
    stickyBtn.style.cssText = 'width:100%;text-align:center;';
    stickyBar.appendChild(stickyBtn);
    document.body.appendChild(stickyBar);

    window.addEventListener('scroll', function() {
      const btnRect = amazonBtn.getBoundingClientRect();
      stickyBar.style.display = btnRect.bottom < 0 ? 'flex' : 'none';
    });
  }

  /**
   * Back to Top Button
   */
  function initBackToTop() {
    const btn = document.createElement('button');
    btn.className = 'back-to-top';
    btn.innerHTML = '↑';
    btn.setAttribute('aria-label', 'Back to top');
    btn.style.cssText = 'position:fixed;bottom:30px;right:30px;width:50px;height:50px;background:#2d5a27;color:#fff;border:none;border-radius:50%;cursor:pointer;opacity:0;visibility:hidden;transition:all 0.3s;z-index:999;font-size:1.5rem;';
    document.body.appendChild(btn);

    window.addEventListener('scroll', function() {
      if (window.pageYOffset > 500) {
        btn.style.opacity = '1';
        btn.style.visibility = 'visible';
      } else {
        btn.style.opacity = '0';
        btn.style.visibility = 'hidden';
      }
    });

    btn.addEventListener('click', function() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  /**
   * Smooth Scroll for Anchor Links
   */
  function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
      anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href === '#') return;

        const target = document.querySelector(href);
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });
  }

  /**
   * Auto-generate Table of Contents
   */
  function initTableOfContents() {
    const content = document.querySelector('.entry-content');
    if (!content) return;

    const headings = content.querySelectorAll('h2, h3');
    if (headings.length < 3) return;

    // Check if TOC already exists
    if (document.querySelector('.table-of-contents')) return;

    const toc = document.createElement('div');
    toc.className = 'table-of-contents';
    toc.innerHTML = '<h4 class="table-of-contents-title">📑 Table of Contents</h4>';

    const list = document.createElement('ol');
    let h2Count = 0;

    headings.forEach(function(heading, index) {
      const id = 'section-' + index;
      heading.id = id;

      const li = document.createElement('li');
      li.className = heading.tagName === 'H3' ? 'toc-h3' : '';

      const link = document.createElement('a');
      link.href = '#' + id;
      link.textContent = heading.textContent;

      li.appendChild(link);
      list.appendChild(li);

      if (heading.tagName === 'H2') h2Count++;
    });

    if (h2Count >= 3) {
      toc.appendChild(list);
      const firstH2 = content.querySelector('h2');
      if (firstH2) {
        firstH2.parentNode.insertBefore(toc, firstH2);
      }
    }
  }

  /**
   * Track Affiliate Link Clicks (for analytics)
   */
  function trackAffiliateClicks() {
    document.querySelectorAll('a[href*="amazon.com"], a[href*="tag=toolshedtested"]').forEach(function(link) {
      link.addEventListener('click', function() {
        // Google Analytics 4 event
        if (typeof gtag === 'function') {
          gtag('event', 'affiliate_click', {
            'event_category': 'Affiliate',
            'event_label': this.href,
            'value': 1
          });
        }

        // Console log for debugging
        console.log('Affiliate click:', this.href);
      });
    });
  }

  /**
   * Lazy Load Images
   */
  function initLazyLoad() {
    if ('loading' in HTMLImageElement.prototype) {
      document.querySelectorAll('img[data-src]').forEach(function(img) {
        img.src = img.dataset.src;
      });
    } else {
      // Fallback for older browsers
      const script = document.createElement('script');
      script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
      document.body.appendChild(script);
    }
  }

  /**
   * Add hover effects to product boxes
   */
  document.querySelectorAll('.review-box').forEach(function(box) {
    box.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-4px)';
      this.style.boxShadow = '0 8px 30px rgba(45, 90, 39, 0.15)';
    });
    box.addEventListener('mouseleave', function() {
      this.style.transform = '';
      this.style.boxShadow = '';
    });
  });

})();
