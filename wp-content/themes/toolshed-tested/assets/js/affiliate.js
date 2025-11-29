/**
 * Toolshed Tested - Affiliate Link Tracking
 * 
 * @package Toolshed_Tested
 */

(function() {
    'use strict';

    /**
     * Track affiliate link clicks
     */
    const trackAffiliateClick = (productId, affiliateUrl) => {
        // Check if we have the required data
        if (typeof tstData === 'undefined') return;

        // Send tracking data via AJAX
        const formData = new FormData();
        formData.append('action', 'tst_track_click');
        formData.append('nonce', tstData.nonce);
        formData.append('product_id', productId);
        formData.append('affiliate_id', affiliateUrl);

        // Use sendBeacon for reliability
        if (navigator.sendBeacon) {
            navigator.sendBeacon(tstData.ajaxUrl, formData);
        } else {
            // Fallback to fetch
            fetch(tstData.ajaxUrl, {
                method: 'POST',
                body: formData,
                keepalive: true
            }).catch(() => {
                // Silently fail - don't block the user
            });
        }
    };

    /**
     * Initialize affiliate link tracking
     */
    const initAffiliateTracking = () => {
        const affiliateLinks = document.querySelectorAll('.affiliate-link, a[rel*="sponsored"]');

        affiliateLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                const productId = link.dataset.productId || '0';
                const affiliateUrl = link.href;

                // Track the click
                trackAffiliateClick(productId, affiliateUrl);

                // Show affiliate disclosure tooltip (optional)
                showAffiliateNotice();
            });
        });
    };

    /**
     * Show affiliate disclosure notice
     */
    const showAffiliateNotice = () => {
        if (typeof tstData === 'undefined' || !tstData.affiliateNote) return;

        // Check if notice already exists
        let notice = document.querySelector('.tst-affiliate-notice');
        
        if (notice) {
            // Reset animation
            notice.classList.remove('show');
            void notice.offsetWidth; // Trigger reflow
            notice.classList.add('show');
        } else {
            // Create notice
            notice = document.createElement('div');
            notice.className = 'tst-affiliate-notice';
            notice.innerHTML = `<p>${tstData.affiliateNote}</p>`;
            document.body.appendChild(notice);

            // Show after a brief delay
            setTimeout(() => notice.classList.add('show'), 10);
        }

        // Hide after 3 seconds
        setTimeout(() => {
            notice.classList.remove('show');
        }, 3000);
    };

    /**
     * Add affiliate disclosure on hover (optional enhancement)
     */
    const initAffiliateHover = () => {
        const affiliateLinks = document.querySelectorAll('.affiliate-link');

        affiliateLinks.forEach(link => {
            // Add title if not present
            if (!link.title) {
                link.title = 'Affiliate link - we may earn a commission';
            }
        });
    };

    /**
     * Track outbound links
     */
    const initOutboundTracking = () => {
        const links = document.querySelectorAll('a[href^="http"]');
        const siteHost = window.location.hostname;

        links.forEach(link => {
            const linkHost = new URL(link.href).hostname;

            // Skip internal links
            if (linkHost === siteHost) return;

            link.addEventListener('click', () => {
                // Track outbound link (can be used with Google Analytics)
                if (typeof gtag === 'function') {
                    gtag('event', 'click', {
                        event_category: 'outbound',
                        event_label: link.href,
                        transport_type: 'beacon'
                    });
                }
            });
        });
    };

    /**
     * Add CSS for affiliate notice
     */
    const addNoticeStyles = () => {
        const style = document.createElement('style');
        style.textContent = `
            .tst-affiliate-notice {
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: rgba(0, 0, 0, 0.8);
                color: #fff;
                padding: 12px 20px;
                border-radius: 8px;
                font-size: 14px;
                max-width: 300px;
                opacity: 0;
                transform: translateY(20px);
                transition: all 0.3s ease;
                z-index: 9999;
            }

            .tst-affiliate-notice.show {
                opacity: 1;
                transform: translateY(0);
            }

            .tst-affiliate-notice p {
                margin: 0;
            }

            @media (max-width: 576px) {
                .tst-affiliate-notice {
                    left: 20px;
                    right: 20px;
                    max-width: none;
                }
            }
        `;
        document.head.appendChild(style);
    };

    /**
     * Initialize
     */
    const init = () => {
        addNoticeStyles();
        initAffiliateTracking();
        initAffiliateHover();
        initOutboundTracking();
    };

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
