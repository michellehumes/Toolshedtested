/**
 * Email Capture Popup - Exit Intent & Scroll Trigger
 *
 * @package Toolshed_Tested
 */

(function() {
    'use strict';

    var POPUP_COOKIE = 'tst_popup_shown';
    var POPUP_DELAY_MOBILE = 60000; // 60 seconds
    var SCROLL_TRIGGER_MOBILE = 70; // 70% scroll
    var scrollTriggered = false;

    /**
     * Check if popup was already shown
     */
    function hasSeenPopup() {
        return document.cookie.indexOf(POPUP_COOKIE) !== -1;
    }

    /**
     * Set cookie to remember popup was shown
     */
    function setPopupCookie() {
        var date = new Date();
        date.setTime(date.getTime() + (7 * 24 * 60 * 60 * 1000)); // 7 days
        document.cookie = POPUP_COOKIE + '=1; expires=' + date.toUTCString() + '; path=/';
    }

    /**
     * Show the popup
     */
    function showPopup() {
        if (hasSeenPopup()) {
            return;
        }

        var popup = document.getElementById('email-popup');
        if (!popup) {
            return;
        }

        popup.classList.add('visible');
        document.body.style.overflow = 'hidden';
        setPopupCookie();

        // Focus on email input for accessibility
        var emailInput = popup.querySelector('input[type="email"]');
        if (emailInput) {
            setTimeout(function() {
                emailInput.focus();
            }, 300);
        }
    }

    /**
     * Hide the popup
     */
    function hidePopup() {
        var popup = document.getElementById('email-popup');
        if (!popup) {
            return;
        }

        popup.classList.remove('visible');
        document.body.style.overflow = '';
    }

    /**
     * Initialize popup triggers
     */
    function init() {
        // Don't run on admin pages
        if (document.body.classList.contains('wp-admin')) {
            return;
        }

        // Exit intent detection (desktop only)
        if (window.innerWidth > 768) {
            document.addEventListener('mouseout', function(e) {
                if (e.clientY < 10 && !hasSeenPopup()) {
                    showPopup();
                }
            });
        } else {
            // Mobile scroll trigger
            window.addEventListener('scroll', function() {
                if (scrollTriggered || hasSeenPopup()) {
                    return;
                }

                var scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
                var scrollPercent = (window.scrollY / scrollHeight) * 100;

                if (scrollPercent > SCROLL_TRIGGER_MOBILE) {
                    scrollTriggered = true;
                    setTimeout(showPopup, 1500);
                }
            });

            // Mobile time-based fallback
            setTimeout(function() {
                if (!hasSeenPopup()) {
                    showPopup();
                }
            }, POPUP_DELAY_MOBILE);
        }

        // Close button handler
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('popup-close') ||
                e.target.classList.contains('popup-overlay')) {
                hidePopup();
            }
        });

        // ESC key handler
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                hidePopup();
            }
        });

        // Form submission tracking
        var popup = document.getElementById('email-popup');
        if (popup) {
            var form = popup.querySelector('form');
            if (form) {
                form.addEventListener('submit', function() {
                    // Track conversion if analytics available
                    if (typeof gtag === 'function') {
                        gtag('event', 'email_signup', {
                            'event_category': 'engagement',
                            'event_label': 'popup'
                        });
                    }
                });
            }
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
