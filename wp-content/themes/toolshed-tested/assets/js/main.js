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
     * Mobile Search Toggle
     */
    const initMobileSearchToggle = () => {
        const mobileSearchToggle = document.querySelector('.mobile-search-toggle');
        const mobileSearchDrawer = document.querySelector('.mobile-search-drawer');

        if (!mobileSearchToggle || !mobileSearchDrawer) return;

        mobileSearchToggle.addEventListener('click', () => {
            const isExpanded = mobileSearchToggle.getAttribute('aria-expanded') === 'true';

            mobileSearchToggle.setAttribute('aria-expanded', !isExpanded);
            mobileSearchDrawer.classList.toggle('visible');
            mobileSearchDrawer.setAttribute('aria-hidden', isExpanded);

            // Focus search input when opening
            if (!isExpanded) {
                const input = mobileSearchDrawer.querySelector('input[type="search"]');
                if (input) {
                    setTimeout(() => input.focus(), 100);
                }
            }
        });

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mobileSearchDrawer.classList.contains('visible')) {
                mobileSearchDrawer.classList.remove('visible');
                mobileSearchToggle.setAttribute('aria-expanded', 'false');
                mobileSearchDrawer.setAttribute('aria-hidden', 'true');
            }
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!mobileSearchDrawer.contains(e.target) &&
                !mobileSearchToggle.contains(e.target) &&
                mobileSearchDrawer.classList.contains('visible')) {
                mobileSearchDrawer.classList.remove('visible');
                mobileSearchToggle.setAttribute('aria-expanded', 'false');
                mobileSearchDrawer.setAttribute('aria-hidden', 'true');
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
     * Table Scroll Indicator
     */
    const initTableScrollIndicator = () => {
        const tableWrappers = document.querySelectorAll('.comparison-table-wrapper');

        tableWrappers.forEach(wrapper => {
            const checkScroll = () => {
                const isScrolledEnd = wrapper.scrollLeft + wrapper.clientWidth >= wrapper.scrollWidth - 10;
                wrapper.classList.toggle('scrolled-end', isScrolledEnd);
            };

            wrapper.addEventListener('scroll', checkScroll);
            // Initial check
            checkScroll();
        });
    };

    /**
     * Dark Mode Toggle
     */
    const initDarkMode = () => {
        const toggle = document.querySelector('.dark-mode-toggle');
        if (!toggle) return;

        // Check for saved preference or system preference
        const savedTheme = localStorage.getItem('tst-theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            document.documentElement.classList.add('dark-mode');
        }

        toggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark-mode');

            const isDark = document.documentElement.classList.contains('dark-mode');
            localStorage.setItem('tst-theme', isDark ? 'dark' : 'light');

            // Announce change to screen readers
            const announcement = isDark ? 'Dark mode enabled' : 'Light mode enabled';
            toggle.setAttribute('aria-label', announcement);
        });

        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('tst-theme')) {
                document.documentElement.classList.toggle('dark-mode', e.matches);
            }
        });
    };

    /**
     * Scroll-triggered Fade-in Animations
     */
    const initScrollAnimations = () => {
        const animatedElements = document.querySelectorAll(
            '.review-box, .pros-cons, .specifications-section, .verdict-box, ' +
            '.compare-alternatives, .author-box, .related-reviews, .faq-section, ' +
            '.review-card, .posts-grid article'
        );

        if (!animatedElements.length) return;

        // Add initial state class
        animatedElements.forEach(el => {
            el.classList.add('animate-on-scroll');
        });

        const observerOptions = {
            root: null,
            rootMargin: '0px 0px -50px 0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        animatedElements.forEach(el => observer.observe(el));
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
     * Initialize all modules
     */
    const init = () => {
        initDarkMode();
        initMobileNav();
        initSmoothScroll();
        initStickyHeader();
        initLazyLoad();
        initSearchToggle();
        initMobileSearchToggle();
        initShareButtons();
        initReadingProgress();
        initFAQAccordion();
        initBackToTop();
        initTableScrollIndicator();
        initMobileStickyCTA();
        initScrollAnimations();
    };

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
