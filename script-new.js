/**
 * Toolshed Tested - Main JavaScript
 * Handles navigation, search, forms, and interactions
 */

(function() {
    'use strict';

    // DOM Elements
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mainNav = document.getElementById('main-nav');
    const searchToggle = document.querySelector('.search-toggle');
    const searchOverlay = document.getElementById('search-overlay');
    const searchClose = document.querySelector('.search-close');
    const header = document.querySelector('.site-header');
    const dropdownItems = document.querySelectorAll('.nav-item.has-dropdown');

    // Mobile Menu
    if (mobileMenuBtn && mainNav) {
        mobileMenuBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            mainNav.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        });

        document.addEventListener('click', function(e) {
            if (!mainNav.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                mobileMenuBtn.classList.remove('active');
                mainNav.classList.remove('active');
                document.body.classList.remove('menu-open');
            }
        });

        dropdownItems.forEach(item => {
            const link = item.querySelector('.nav-link');
            link.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    item.classList.toggle('active');
                }
            });
        });
    }

    // Search Overlay
    if (searchToggle && searchOverlay) {
        searchToggle.addEventListener('click', function() {
            searchOverlay.classList.add('active');
            const input = searchOverlay.querySelector('input');
            if (input) setTimeout(() => input.focus(), 100);
        });

        if (searchClose) {
            searchClose.addEventListener('click', function() {
                searchOverlay.classList.remove('active');
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
                searchOverlay.classList.remove('active');
            }
        });
    }

    // Sticky Header
    if (header) {
        let lastScroll = 0;
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            header.classList.toggle('scrolled', currentScroll > 100);
            if (currentScroll > lastScroll && currentScroll > 300) {
                header.classList.add('header-hidden');
            } else {
                header.classList.remove('header-hidden');
            }
            lastScroll = currentScroll;
        }, { passive: true });
    }

    // Form Handling
    const forms = document.querySelectorAll('form[action*="formspree"]');
    forms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            const emailInput = form.querySelector('input[type="email"]');
            
            if (!emailInput || !emailInput.value || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
                showFormMessage(form, 'Please enter a valid email address.', 'error');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'Accept': 'application/json' }
                });

                if (response.ok) {
                    showFormMessage(form, '🎉 Success! Check your email for the free guide.', 'success');
                    form.reset();
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'sign_up', { method: 'email' });
                    }
                } else {
                    throw new Error('Failed');
                }
            } catch (error) {
                showFormMessage(form, 'Something went wrong. Please try again.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    });

    function showFormMessage(form, message, type) {
        const existing = form.querySelector('.form-message');
        if (existing) existing.remove();

        const messageEl = document.createElement('div');
        messageEl.className = `form-message form-message-${type}`;
        messageEl.textContent = message;
        messageEl.style.cssText = `
            padding: 12px 16px; margin-top: 12px; border-radius: 8px; font-size: 14px; font-weight: 500;
            ${type === 'success' ? 'background:#d4edda;color:#155724;' : 'background:#f8d7da;color:#721c24;'}
        `;
        form.appendChild(messageEl);
        setTimeout(() => messageEl.remove(), 5000);
    }

    // Smooth Scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                const offset = header ? header.offsetHeight + 20 : 20;
                window.scrollTo({
                    top: target.offsetTop - offset,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Affiliate Link Tracking
    document.querySelectorAll('a[href*="amazon.com"], a[href*="amzn.to"]').forEach(link => {
        link.addEventListener('click', function() {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'affiliate_click', {
                    event_category: 'affiliate',
                    event_label: this.href,
                    transport_type: 'beacon'
                });
            }
        });
    });

    // Announcement Bar Dismiss
    const announcementBar = document.querySelector('.announcement-bar');
    if (announcementBar) {
        if (sessionStorage.getItem('announcement-dismissed')) {
            announcementBar.style.display = 'none';
        } else {
            const closeBtn = document.createElement('button');
            closeBtn.innerHTML = '×';
            closeBtn.style.cssText = 'position:absolute;right:20px;top:50%;transform:translateY(-50%);background:none;border:none;color:white;font-size:20px;cursor:pointer;';
            closeBtn.onclick = () => {
                announcementBar.style.display = 'none';
                sessionStorage.setItem('announcement-dismissed', 'true');
            };
            const container = announcementBar.querySelector('.container');
            container.style.position = 'relative';
            container.appendChild(closeBtn);
        }
    }

    console.log('🔧 Toolshed Tested initialized');
})();
