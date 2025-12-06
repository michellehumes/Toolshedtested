/**
 * Toolshed Tested - Main JavaScript
 * 
 * @package Toolshed_Tested
 */

(function() {
    'use strict';

    /**
     * Mobile Navigation Toggle
     */
    const initMobileNav = () => {
        const menuToggle = document.querySelector('.menu-toggle');
        const navigation = document.querySelector('.main-navigation');

        if (!menuToggle || !navigation) return;

        menuToggle.addEventListener('click', () => {
            navigation.classList.toggle('toggled');
            menuToggle.setAttribute(
                'aria-expanded',
                navigation.classList.contains('toggled') ? 'true' : 'false'
            );
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!navigation.contains(e.target) && !menuToggle.contains(e.target)) {
                navigation.classList.remove('toggled');
                menuToggle.setAttribute('aria-expanded', 'false');
            }
        });

        // Handle mobile dropdown toggles
        const dropdownParents = navigation.querySelectorAll('.menu-item-has-children > a');
        dropdownParents.forEach(link => {
            // Create toggle button for mobile
            const toggleBtn = document.createElement('button');
            toggleBtn.className = 'dropdown-toggle';
            toggleBtn.setAttribute('aria-expanded', 'false');
            toggleBtn.innerHTML = '<span class="screen-reader-text">Toggle submenu</span>';
            link.parentNode.insertBefore(toggleBtn, link.nextSibling);

            toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const parent = toggleBtn.parentNode;
                const isOpen = parent.classList.contains('open');

                // Close other open dropdowns
                navigation.querySelectorAll('.menu-item-has-children.open').forEach(item => {
                    if (item !== parent) {
                        item.classList.remove('open');
                        item.querySelector('.dropdown-toggle')?.setAttribute('aria-expanded', 'false');
                    }
                });

                // Toggle current dropdown
                parent.classList.toggle('open');
                toggleBtn.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
            });
        });
    };

    /**
     * Smooth Scroll for Table of Contents
     */
    const initSmoothScroll = () => {
        const tocLinks = document.querySelectorAll('.table-of-contents a');

        tocLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                if (!href.startsWith('#')) return;

                const target = document.querySelector(href);
                if (!target) return;

                e.preventDefault();

                const headerOffset = 80;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });

                // Update URL without scrolling
                history.pushState(null, null, href);
            });
        });
    };

    /**
     * Sticky Header
     */
    const initStickyHeader = () => {
        const header = document.querySelector('.site-header');
        if (!header) return;

        let lastScroll = 0;

        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;

            if (currentScroll <= 0) {
                header.classList.remove('scroll-up');
                return;
            }

            if (currentScroll > lastScroll && !header.classList.contains('scroll-down')) {
                // Scrolling down
                header.classList.remove('scroll-up');
                header.classList.add('scroll-down');
            } else if (currentScroll < lastScroll && header.classList.contains('scroll-down')) {
                // Scrolling up
                header.classList.remove('scroll-down');
                header.classList.add('scroll-up');
            }

            lastScroll = currentScroll;
        });
    };

    /**
     * Lazy Load Images
     */
    const initLazyLoad = () => {
        if ('loading' in HTMLImageElement.prototype) {
            // Native lazy loading supported
            const images = document.querySelectorAll('img[loading="lazy"]');
            images.forEach(img => {
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                }
            });
        } else {
            // Fallback for older browsers
            const lazyImages = document.querySelectorAll('img[data-src]');

            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        imageObserver.unobserve(img);
                    }
                });
            });

            lazyImages.forEach(img => imageObserver.observe(img));
        }
    };

    /**
     * Search Form Toggle
     */
    const initSearchToggle = () => {
        const searchToggle = document.querySelector('.search-toggle');
        const searchForm = document.querySelector('.header-search');

        if (!searchToggle || !searchForm) return;

        searchToggle.addEventListener('click', () => {
            searchForm.classList.toggle('active');
            const input = searchForm.querySelector('input[type="search"]');
            if (input && searchForm.classList.contains('active')) {
                input.focus();
            }
        });
    };

    /**
     * Copy to Clipboard for Share Links
     */
    const initShareButtons = () => {
        const copyButtons = document.querySelectorAll('[data-copy-url]');

        copyButtons.forEach(button => {
            button.addEventListener('click', async () => {
                const url = button.dataset.copyUrl || window.location.href;

                try {
                    await navigator.clipboard.writeText(url);
                    
                    const originalText = button.textContent;
                    button.textContent = 'Copied!';
                    button.classList.add('copied');

                    setTimeout(() => {
                        button.textContent = originalText;
                        button.classList.remove('copied');
                    }, 2000);
                } catch (err) {
                    console.error('Failed to copy:', err);
                }
            });
        });
    };

    /**
     * Reading Progress Indicator
     */
    const initReadingProgress = () => {
        const progressBar = document.querySelector('.reading-progress');
        if (!progressBar) return;

        const article = document.querySelector('.entry-content');
        if (!article) return;

        window.addEventListener('scroll', () => {
            const articleTop = article.offsetTop;
            const articleHeight = article.offsetHeight;
            const windowHeight = window.innerHeight;
            const scrollTop = window.pageYOffset;

            const progress = Math.min(
                100,
                Math.max(
                    0,
                    ((scrollTop - articleTop + windowHeight / 2) / articleHeight) * 100
                )
            );

            progressBar.style.width = `${progress}%`;
        });
    };

    /**
     * Collapsible FAQ Sections
     */
    const initFAQAccordion = () => {
        const faqItems = document.querySelectorAll('.faq-item');

        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            const answer = item.querySelector('.faq-answer');

            if (!question || !answer) return;

            question.addEventListener('click', () => {
                const isOpen = item.classList.contains('open');

                // Close all other items
                faqItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('open');
                        otherItem.querySelector('.faq-answer').style.maxHeight = null;
                    }
                });

                // Toggle current item
                item.classList.toggle('open');
                
                if (!isOpen) {
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                } else {
                    answer.style.maxHeight = null;
                }
            });
        });
    };

    /**
     * Back to Top Button
     */
    const initBackToTop = () => {
        const button = document.querySelector('.back-to-top');
        if (!button) return;

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                button.classList.add('visible');
            } else {
                button.classList.remove('visible');
            }
        });

        button.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    };

    /**
     * Mobile Sticky CTA
     */
    const initMobileStickyCTA = () => {
        const stickyCTA = document.querySelector('.mobile-sticky-cta');
        if (!stickyCTA) return;

        const showAfter = 300; // pixels from top

        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;

            if (currentScroll > showAfter) {
                stickyCTA.classList.add('visible');
                document.body.classList.add('has-sticky-cta');
            } else {
                stickyCTA.classList.remove('visible');
                document.body.classList.remove('has-sticky-cta');
            }
        });

        // Track CTA clicks
        const ctaLink = stickyCTA.querySelector('a');
        if (ctaLink) {
            ctaLink.addEventListener('click', () => {
                // Track with GA4 if available
                if (typeof gtag === 'function') {
                    gtag('event', 'affiliate_click', {
                        'event_category': 'engagement',
                        'event_label': 'mobile_sticky_cta'
                    });
                }
            });
        }
    };

    /**
     * Table Scroll Detection
     * Hides "scroll for more" indicator once user has scrolled
     */
    const initTableScrollIndicator = () => {
        const tableWrappers = document.querySelectorAll('.comparison-table-wrapper[data-scrollable]');

        tableWrappers.forEach(wrapper => {
            wrapper.addEventListener('scroll', () => {
                if (wrapper.scrollLeft > 10) {
                    wrapper.classList.add('scrolled');
                }
            }, { passive: true });
        });
    };

    /**
     * Initialize all modules
     */
    const init = () => {
        initMobileNav();
        initSmoothScroll();
        initStickyHeader();
        initLazyLoad();
        initSearchToggle();
        initShareButtons();
        initReadingProgress();
        initFAQAccordion();
        initBackToTop();
        initMobileStickyCTA();
        initTableScrollIndicator();
    };

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
