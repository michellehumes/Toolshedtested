#!/usr/bin/env python3
"""
Direct Publishing Script for Toolshed Tested (Hostinger)
Publishes pages directly to the Hostinger WordPress installation
"""

import base64
import requests
import urllib3
import json

# Disable SSL warnings
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# Configuration - targeting Hostinger directly
HOSTINGER_IP = "82.25.87.19"
DOMAIN = "toolshedtested.com"
WP_USER = "michelle.e.humes@gmail.com"
APP_PASS = "KddKwVPv7Vh10ny3MZNv4CsZ"

API_BASE = f"https://{HOSTINGER_IP}/wp-json/wp/v2"

def get_headers():
    """Get headers with auth and host"""
    credentials = f"{WP_USER}:{APP_PASS}"
    token = base64.b64encode(credentials.encode()).decode()
    return {
        "Authorization": f"Basic {token}",
        "Host": DOMAIN,
        "Content-Type": "application/json"
    }

def check_page_exists(slug):
    """Check if page exists and return ID"""
    response = requests.get(
        f"{API_BASE}/pages",
        headers=get_headers(),
        params={"slug": slug, "status": "any"},
        verify=False
    )
    if response.status_code == 200:
        pages = response.json()
        if pages:
            return pages[0]['id']
    return None

def publish_page(title, slug, content, status="publish"):
    """Publish or update a page"""
    page_data = {
        "title": title,
        "slug": slug,
        "content": content,
        "status": status
    }

    existing_id = check_page_exists(slug)

    if existing_id:
        response = requests.post(
            f"{API_BASE}/pages/{existing_id}",
            headers=get_headers(),
            json=page_data,
            verify=False
        )
        action = "Updated"
    else:
        response = requests.post(
            f"{API_BASE}/pages",
            headers=get_headers(),
            json=page_data,
            verify=False
        )
        action = "Created"

    if response.status_code in [200, 201]:
        page = response.json()
        print(f"✅ {action}: {title}")
        print(f"   URL: {page['link']}")
        return True
    else:
        print(f"❌ Failed: {title}")
        print(f"   Status: {response.status_code}")
        print(f"   Error: {response.text[:300]}")
        return False

# Page Contents
PRIVACY_POLICY = """
<h2>Information We Collect</h2>
<p>At Toolshed Tested, we collect information you provide directly, such as your name and email when you subscribe to our newsletter or contact us. We also automatically collect certain information when you visit our site, including your IP address, browser type, and pages visited.</p>

<h2>How We Use Your Information</h2>
<p>We use your information to:</p>
<ul>
<li>Send our newsletter and product recommendations (if you subscribe)</li>
<li>Respond to your questions and comments</li>
<li>Improve our website content and user experience</li>
<li>Analyze site traffic and usage patterns</li>
</ul>

<h2>Cookies and Tracking</h2>
<p>We use cookies to improve your browsing experience and analyze site traffic. Our analytics tools (Google Analytics) may track:</p>
<ul>
<li>Pages you visit on our site</li>
<li>Time spent on pages</li>
<li>Links you click, including affiliate links</li>
<li>Your general geographic location</li>
</ul>
<p>You can disable cookies in your browser settings, though this may affect site functionality.</p>

<h2>Affiliate Links</h2>
<p>Our site contains affiliate links to retailers like Amazon. When you click these links, cookies may be placed by the retailer to track your purchase. This helps us earn commissions at no extra cost to you. See our <a href="/affiliate-disclosure/">Affiliate Disclosure</a> for details.</p>

<h2>Third-Party Services</h2>
<p>We use the following third-party services:</p>
<ul>
<li><strong>Google Analytics</strong> - For website analytics</li>
<li><strong>Amazon Associates</strong> - Affiliate program</li>
<li><strong>LiteSpeed Cache</strong> - Performance optimization</li>
</ul>
<p>Each of these services has their own privacy policy governing how they collect and use data.</p>

<h2>Your Rights</h2>
<p>You have the right to:</p>
<ul>
<li>Request access to your personal data</li>
<li>Request correction of inaccurate data</li>
<li>Request deletion of your data</li>
<li>Unsubscribe from our newsletter at any time</li>
</ul>

<h2>Contact Us</h2>
<p>If you have questions about this Privacy Policy, please <a href="/contact/">contact us</a>.</p>
<p><em>Last updated: December 2025</em></p>
"""

AFFILIATE_DISCLOSURE = """
<div style="background: #e8f5e9; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
<p><strong>Transparency Commitment:</strong> We believe in complete honesty with our readers. This page explains how we make money and how it affects (and doesn't affect) our reviews.</p>
</div>

<h2>How Toolshed Tested Makes Money</h2>
<p>Toolshed Tested is an independently owned website. We earn money through affiliate partnerships with retailers, primarily Amazon. When you click a link on our site and make a purchase, we may earn a small commission at no extra cost to you.</p>

<h2>Our Affiliate Partners</h2>
<ul>
<li><strong>Amazon Associates</strong> - Our primary affiliate partner. Most product links go to Amazon.</li>
<li><strong>Home Depot</strong> - We may link to Home Depot for certain products</li>
<li><strong>Lowe's</strong> - We may link to Lowe's for certain products</li>
</ul>

<h2>Our Promise to You</h2>
<p>Affiliate partnerships do NOT influence our reviews or recommendations. Here's our commitment:</p>
<ol>
<li><strong>We test everything ourselves</strong> - Every tool we recommend has been personally tested in real projects</li>
<li><strong>We recommend what's best, not what pays most</strong> - Our top pick is always what performed best in our testing</li>
<li><strong>We're honest about limitations</strong> - We'll tell you the cons, not just the pros</li>
<li><strong>We update our reviews</strong> - If a product's quality changes, we update our recommendation</li>
</ol>

<h2>How to Identify Affiliate Links</h2>
<p>Links to Amazon and other retailers on our product reviews are affiliate links. When in doubt, assume any link to a store where you can buy something is an affiliate link.</p>

<h2>Questions?</h2>
<p>If you have any questions about our affiliate relationships or review process, please <a href="/contact/">contact us</a>. We're happy to be transparent about everything we do.</p>
<p><em>This disclosure is provided in accordance with FTC guidelines.</em></p>
"""

FAQ_CONTENT = """
<h2>About Our Reviews</h2>

<h3>How do you test power tools?</h3>
<p>We put every tool through real-world testing in actual projects. For drills, this means drilling hundreds of holes in various materials. For saws, it means making cuts in different woods and conditions. We test battery life, ergonomics, durability, and performance over time - not just out of the box.</p>

<h3>Do you get paid to recommend products?</h3>
<p>We earn affiliate commissions when you buy through our links, but this never influences which product we recommend. We always recommend the best performer from our testing, regardless of commission rates. See our <a href="/affiliate-disclosure/">Affiliate Disclosure</a> for full details.</p>

<h3>How often do you update reviews?</h3>
<p>We update our reviews whenever manufacturers release significant updates or when we discover new information through extended testing. We also update prices and availability regularly.</p>

<h2>Buying Advice</h2>

<h3>Should I buy brushless or brushed tools?</h3>
<p>For most users, brushless is worth the extra cost. Brushless motors are more efficient (longer battery life), more powerful, and last longer since there are no brushes to wear out. However, if you're on a tight budget and only need occasional use, brushed tools still work fine.</p>

<h3>Which brand ecosystem should I choose?</h3>
<p>The best brand depends on your needs. DeWalt and Milwaukee lead in professional-grade tools. Makita offers excellent balance of quality and value. Ryobi is great for homeowners with its affordable ONE+ system. Once you choose a battery platform, sticking with it saves money on batteries.</p>

<h3>How do I know if a tool is right for me?</h3>
<p>Think about your typical projects. Homeowners doing occasional repairs don't need contractor-grade tools. DIY enthusiasts benefit from mid-range options. Professionals should invest in premium tools that withstand daily use. Our reviews include "Best For" recommendations to help match tools to users.</p>

<h2>Site Questions</h2>

<h3>Can I suggest a product for review?</h3>
<p>Absolutely! We love hearing what tools our readers want reviewed. <a href="/contact/">Contact us</a> with your suggestions and we'll consider them for future reviews.</p>

<h3>How can I support Toolshed Tested?</h3>
<p>The best way to support us is by using our affiliate links when you buy tools. It costs you nothing extra and helps us continue testing and reviewing products. You can also share our reviews with friends and on social media.</p>
"""

CONTACT_CONTENT = """
<p>Have a question, suggestion, or just want to say hi? We'd love to hear from you!</p>

<h2>Get in Touch</h2>
<p><strong>Email:</strong> <a href="mailto:hello@toolshedtested.com">hello@toolshedtested.com</a></p>
<p>We typically respond within 1-2 business days.</p>

<h2>What We Can Help With</h2>
<ul>
<li><strong>Tool recommendations</strong> - Need help choosing the right tool? Ask away!</li>
<li><strong>Review requests</strong> - Want us to review a specific product?</li>
<li><strong>Feedback</strong> - Found an error or have suggestions to improve our site?</li>
<li><strong>Partnership inquiries</strong> - Brands interested in having products reviewed</li>
</ul>

<h2>Before You Ask</h2>
<p>Check our <a href="/faq/">FAQ page</a> first - your question might already be answered!</p>

<h2>Connect With Us</h2>
<p>Follow us for the latest reviews and tool tips:</p>
<ul>
<li>Subscribe to our newsletter (coming soon)</li>
</ul>
"""

def main():
    print("=" * 50)
    print("Publishing pages to Toolshed Tested (Hostinger)")
    print("=" * 50)
    print()

    # Test connection first
    print("Testing connection...")
    response = requests.get(
        f"{API_BASE}/users/me",
        headers=get_headers(),
        verify=False
    )
    if response.status_code != 200:
        print(f"❌ Authentication failed: {response.status_code}")
        print(response.text[:200])
        return
    print(f"✅ Connected as: {response.json().get('name')}")
    print()

    # Publish pages
    pages = [
        ("Privacy Policy", "privacy-policy", PRIVACY_POLICY),
        ("Affiliate Disclosure", "affiliate-disclosure", AFFILIATE_DISCLOSURE),
        ("FAQ", "faq", FAQ_CONTENT),
        ("Contact", "contact", CONTACT_CONTENT),
    ]

    for title, slug, content in pages:
        publish_page(title, slug, content)
        print()

    print("=" * 50)
    print("Done! All pages published.")
    print("=" * 50)

if __name__ == "__main__":
    main()
