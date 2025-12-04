#!/usr/bin/env python3
"""
WordPress Page Publishing Script for Toolshed Tested
Publishes/updates critical pages: Privacy Policy, Affiliate Disclosure, FAQ, Contact
"""

import os
import sys
import base64
import requests
import urllib3

# Disable SSL warnings for local development
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# Configuration from environment variables
WP_URL = os.environ.get('WP_URL', 'https://toolshedtested.com').rstrip('/')
WP_USER = os.environ.get('WP_USER', '')
WP_APP_PASSWORD = os.environ.get('WP_APP_PASSWORD', '')

API_BASE = f"{WP_URL}/wp-json/wp/v2"

def get_auth_header():
    """Create Basic Auth header for WordPress REST API"""
    credentials = f"{WP_USER}:{WP_APP_PASSWORD}"
    token = base64.b64encode(credentials.encode()).decode()
    return {
        "Authorization": f"Basic {token}",
        "Content-Type": "application/json"
    }

def check_page_exists(slug):
    """Check if a page with this slug already exists"""
    headers = get_auth_header()
    response = requests.get(
        f"{API_BASE}/pages",
        headers=headers,
        params={"slug": slug, "status": "any"},
        verify=False  # Skip SSL verification
    )

    if response.status_code == 200:
        pages = response.json()
        if pages:
            return pages[0]['id']
    return None

def publish_page(title, slug, content, meta_description=""):
    """Publish or update a WordPress page"""
    headers = get_auth_header()

    page_data = {
        "title": title,
        "slug": slug,
        "content": content,
        "status": "publish",
    }

    if meta_description:
        page_data["meta"] = {
            "rank_math_description": meta_description,
            "_yoast_wpseo_metadesc": meta_description
        }

    existing_id = check_page_exists(slug)

    if existing_id:
        response = requests.post(
            f"{API_BASE}/pages/{existing_id}",
            headers=headers,
            json=page_data,
            verify=False  # Skip SSL verification
        )
        action = "Updated"
    else:
        response = requests.post(
            f"{API_BASE}/pages",
            headers=headers,
            json=page_data,
            verify=False  # Skip SSL verification
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


# ===========================================
# PAGE CONTENT
# ===========================================

PRIVACY_POLICY_CONTENT = '''
<p><em>Last Updated: December 3, 2025</em></p>

<p>ToolShed Tested ("we," "us," or "our") operates the website toolshedtested.com (the "Site"). This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our Site.</p>

<h2 id="information-we-collect">Information We Collect</h2>

<h3>Information You Provide</h3>
<p>We may collect information you voluntarily provide, including:</p>
<ul>
<li><strong>Contact Information:</strong> Name and email address when you subscribe to our newsletter or contact us</li>
<li><strong>Communications:</strong> Any messages or feedback you send us</li>
</ul>

<h3>Information Collected Automatically</h3>
<p>When you visit our Site, we may automatically collect:</p>
<ul>
<li><strong>Device Information:</strong> Browser type, operating system, device type</li>
<li><strong>Usage Data:</strong> Pages visited, time spent on pages, links clicked</li>
<li><strong>Location Data:</strong> General geographic location based on IP address</li>
<li><strong>Referral Data:</strong> How you arrived at our Site</li>
</ul>

<h2 id="how-we-use-your-information">How We Use Your Information</h2>

<p>We use collected information to:</p>
<ul>
<li>Provide, maintain, and improve our Site</li>
<li>Send newsletters and updates (with your consent)</li>
<li>Respond to your inquiries and requests</li>
<li>Analyze Site usage to improve content and user experience</li>
<li>Comply with legal obligations</li>
</ul>

<h2 id="cookies-and-tracking">Cookies and Tracking Technologies</h2>

<p>We use cookies and similar technologies to:</p>
<ul>
<li>Remember your preferences</li>
<li>Analyze Site traffic and usage patterns</li>
<li>Enable certain Site features</li>
</ul>

<h3>Types of Cookies We Use</h3>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
<thead>
<tr style="background: #f8f9fa;">
<th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Cookie Type</th>
<th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Purpose</th>
</tr>
</thead>
<tbody>
<tr>
<td style="padding: 12px; border: 1px solid #ddd;"><strong>Essential</strong></td>
<td style="padding: 12px; border: 1px solid #ddd;">Required for Site functionality</td>
</tr>
<tr>
<td style="padding: 12px; border: 1px solid #ddd;"><strong>Analytics</strong></td>
<td style="padding: 12px; border: 1px solid #ddd;">Help us understand how visitors use our Site (Google Analytics)</td>
</tr>
<tr>
<td style="padding: 12px; border: 1px solid #ddd;"><strong>Affiliate</strong></td>
<td style="padding: 12px; border: 1px solid #ddd;">Track referrals to retail partners (Amazon Associates)</td>
</tr>
</tbody>
</table>

<h2 id="third-party-services">Third-Party Services</h2>

<h3>Google Analytics</h3>
<p>We use Google Analytics to analyze Site traffic. Google may collect and process data about your visit. Learn more at <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Google's Privacy Policy</a>.</p>

<h3>Amazon Associates Program</h3>
<p>As an Amazon Associate, we earn from qualifying purchases. When you click affiliate links, Amazon may place cookies to track your purchase. See <a href="https://www.amazon.com/gp/help/customer/display.html?nodeId=468496" target="_blank" rel="noopener">Amazon's Privacy Notice</a>.</p>

<h2 id="data-sharing">Data Sharing and Disclosure</h2>

<p>We do not sell your personal information. We may share information:</p>
<ul>
<li><strong>With Service Providers:</strong> Third parties who help us operate our Site</li>
<li><strong>For Legal Reasons:</strong> If required by law or to protect our rights</li>
<li><strong>With Your Consent:</strong> When you explicitly agree to sharing</li>
</ul>

<h2 id="your-rights">Your Privacy Rights</h2>

<h3>All Users</h3>
<p>You have the right to:</p>
<ul>
<li>Access information we hold about you</li>
<li>Request correction of inaccurate information</li>
<li>Request deletion of your information</li>
<li>Opt out of marketing communications</li>
</ul>

<h3>California Residents (CCPA)</h3>
<p>California residents have additional rights under the California Consumer Privacy Act. <strong>We do not sell personal information.</strong></p>

<h3>European Residents (GDPR)</h3>
<p>If you are in the European Economic Area, you have rights under GDPR including access, rectification, erasure, and data portability.</p>

<h2 id="data-security">Data Security</h2>

<p>We implement appropriate technical and organizational measures to protect your information, including SSL/TLS encryption and secure hosting infrastructure.</p>

<h2 id="childrens-privacy">Children's Privacy</h2>

<p>Our Site is not intended for children under 13. We do not knowingly collect information from children under 13.</p>

<h2 id="changes-to-policy">Changes to This Privacy Policy</h2>

<p>We may update this Privacy Policy periodically. We will notify you of significant changes by posting the new policy on this page.</p>

<h2 id="contact-us">Contact Us</h2>

<p>If you have questions about this Privacy Policy or wish to exercise your privacy rights, contact us at:</p>

<p><strong>Email:</strong> <a href="mailto:hello@toolshedtested.com">hello@toolshedtested.com</a></p>
'''


AFFILIATE_DISCLOSURE_CONTENT = '''
<div style="background: linear-gradient(135deg, #2d5a27, #3d7a37); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; text-align: center;">
<h2 style="color: white; margin-bottom: 10px; font-size: 1.5em;">Transparency You Can Trust</h2>
<p style="margin: 0; opacity: 0.9;">We believe in complete honesty about how we fund our independent testing.</p>
</div>

<h2 id="how-we-make-money">How We Make Money</h2>

<p>ToolShed Tested is an independently operated power tool review website. We earn money through <strong>affiliate marketing</strong>, which means when you click a link on our site and make a purchase, we may earn a small commission at <strong>no extra cost to you</strong>.</p>

<p>This income allows us to:</p>
<ul>
<li>Purchase tools for hands-on testing (we buy every tool ourselves)</li>
<li>Maintain our workshop and testing equipment</li>
<li>Pay for website hosting and development</li>
<li>Dedicate time to thorough, unbiased reviews</li>
</ul>

<h2 id="how-affiliate-links-work">How Affiliate Links Work</h2>

<div style="background: #f8f9fa; padding: 25px; border-radius: 8px; margin: 25px 0;">
<p><strong>Here's what happens when you use our links:</strong></p>
<ol style="margin-bottom: 0;">
<li>You click a "Check Price on Amazon" button (or similar)</li>
<li>You're taken to the retailer's website</li>
<li>A small cookie tracks that you came from our site</li>
<li>If you make a purchase, we receive a small commission (typically 1-8%)</li>
<li><strong>You pay the same price</strong> as if you went directly to the retailer</li>
</ol>
</div>

<h2 id="affiliate-programs">Affiliate Programs We Participate In</h2>

<h3>Amazon Associates Program</h3>
<p><strong>ToolShed Tested is a participant in the Amazon Services LLC Associates Program</strong>, an affiliate advertising program designed to provide a means for sites to earn advertising fees by advertising and linking to Amazon.com.</p>

<p>As an Amazon Associate, we earn from qualifying purchases. Amazon and the Amazon logo are trademarks of Amazon.com, Inc. or its affiliates.</p>

<h2 id="editorial-independence">Our Editorial Independence</h2>

<div style="background: #e8f5e9; border-left: 4px solid #2d5a27; padding: 25px; margin: 25px 0;">
<h3 style="margin-top: 0; color: #2d5a27;">Our Promise to You</h3>
<p><strong>Affiliate relationships NEVER influence our recommendations.</strong></p>
<ul style="margin-bottom: 0;">
<li>We purchase all tools with our own money before testing</li>
<li>We recommend products based on performance, not commission rates</li>
<li>We will recommend against products even from affiliate partners if they don't perform well</li>
<li>If a non-affiliate product is better, we'll tell you</li>
<li>We clearly label our top picks based on testing, not revenue potential</li>
</ul>
</div>

<h2 id="identifying-affiliate-links">How to Identify Affiliate Links</h2>

<p>On our site, affiliate links are typically:</p>
<ul>
<li>Buttons labeled "Check Price on Amazon" or similar</li>
<li>Product name links that go to retailer websites</li>
<li>Links in comparison tables to purchase pages</li>
<li>Any link to Amazon, Home Depot, Lowe's, or other retailers</li>
</ul>

<p><strong>When in doubt, assume any link to a product purchase page is an affiliate link.</strong></p>

<h2 id="what-we-dont-do">What We Don't Do</h2>

<div style="background: #fff3e0; border-left: 4px solid #f4a524; padding: 25px; margin: 25px 0;">
<ul style="margin-bottom: 0;">
<li><strong>We don't accept payment for reviews.</strong> No manufacturer has ever paid us to review a product.</li>
<li><strong>We don't accept free products in exchange for positive reviews.</strong> We buy everything ourselves.</li>
<li><strong>We don't let affiliate commissions influence our rankings.</strong> Performance determines our picks.</li>
<li><strong>We don't hide our affiliate relationships.</strong> Complete transparency, always.</li>
</ul>
</div>

<h2 id="questions">Questions?</h2>

<p>If you have any questions about our affiliate relationships, please contact us:</p>

<p><strong>Email:</strong> <a href="mailto:hello@toolshedtested.com">hello@toolshedtested.com</a></p>

<hr style="margin: 40px 0; border: none; border-top: 1px solid #ddd;">

<p style="font-size: 0.9em; color: #666;"><em>This disclosure was last updated on December 3, 2025.</em></p>
'''


FAQ_CONTENT = '''
<div style="background: linear-gradient(135deg, #1a1a1a, #2d2d2d); color: white; padding: 40px; border-radius: 8px; margin-bottom: 40px; text-align: center;">
<p style="margin: 0; opacity: 0.9;">Everything you need to know about our reviews and recommendations.</p>
</div>

<h2 id="about-reviews">About Our Reviews</h2>

<h3 style="color: #2d5a27;">How do you test the tools you review?</h3>
<p>Every tool we review goes through hands-on testing in our workshop. We run standardized tests specific to each tool category (drilling through various materials, cutting tests, runtime tests, etc.) as well as real-world project use. We test both performance and durability over extended periods.</p>

<h3 style="color: #2d5a27;">Do you buy all the tools you review?</h3>
<p><strong>Yes.</strong> We purchase every tool with our own money at retail prices. We don't accept free samples from manufacturers in exchange for reviews. This ensures our reviews remain completely independent and unbiased.</p>

<h3 style="color: #2d5a27;">How often do you update your reviews?</h3>
<p>We update our reviews whenever manufacturers release significant updates, new models, or when prices change substantially. All reviews show a "Last Updated" date so you know how current the information is.</p>

<h3 style="color: #2d5a27;">What makes a tool your "Top Pick"?</h3>
<p>Our top picks are determined by overall performance across our testing criteria: power/performance, build quality, ergonomics, battery life (for cordless), value for money, and warranty/support.</p>

<h2 id="affiliate-questions">Affiliate Questions</h2>

<h3 style="color: #2d5a27;">Do you make money from the links on your site?</h3>
<p>Yes. We're part of the Amazon Associates program and other affiliate programs. When you click a link and make a purchase, we may earn a small commission (typically 1-8%) at no extra cost to you. See our full <a href="/affiliate-disclosure/">Affiliate Disclosure</a> for details.</p>

<h3 style="color: #2d5a27;">Do affiliate commissions affect your recommendations?</h3>
<p><strong>Absolutely not.</strong> Our recommendations are based solely on testing results. Higher commissions don't make a tool rank higher.</p>

<h3 style="color: #2d5a27;">Does it cost me more to use your links?</h3>
<p><strong>No.</strong> You pay exactly the same price whether you use our link or go directly to Amazon/retailer.</p>

<h2 id="tool-questions">Tool Questions</h2>

<h3 style="color: #2d5a27;">What's the difference between brushless and brushed motors?</h3>
<p>Brushless motors are more efficient, produce more power, run cooler, and last significantly longer than brushed motors. While brushless tools cost more upfront, they're worth it for anyone who uses their tools regularly.</p>

<h3 style="color: #2d5a27;">What voltage drill should I get?</h3>
<p><strong>For most users: 20V (18V).</strong> This is the sweet spot of power and portability. 12V is lighter and fine for light-duty work. 20V handles serious drilling and driving.</p>

<h3 style="color: #2d5a27;">DeWalt vs Milwaukee vs Makita - which brand is best?</h3>
<p>All three are excellent professional-grade brands. The "best" depends on your specific needs, existing battery platform, and local service availability. See our brand comparisons for detailed breakdowns.</p>

<h2 id="contact-questions">Contact & Support</h2>

<h3 style="color: #2d5a27;">How can I contact you?</h3>
<p>Email us at <a href="mailto:hello@toolshedtested.com">hello@toolshedtested.com</a>. We read every message and try to respond within 48 hours.</p>

<h3 style="color: #2d5a27;">Can you review a specific tool I'm interested in?</h3>
<p>We love reader suggestions! Email us with the tool you'd like reviewed. Reader requests help us prioritize our testing queue.</p>

<div style="background: #2d5a27; color: white; padding: 30px; border-radius: 8px; margin-top: 40px; text-align: center;">
<h3 style="color: white; margin-bottom: 10px;">Still Have Questions?</h3>
<p style="margin-bottom: 20px;">We're here to help you find the right tools for your projects.</p>
<a href="/contact/" style="background: #f4a524; color: #1a1a1a; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Contact Us</a>
</div>
'''


CONTACT_CONTENT = '''
<div style="background: linear-gradient(135deg, #2d5a27, #3d7a37); color: white; padding: 40px; border-radius: 8px; margin-bottom: 40px; text-align: center;">
<p style="margin: 0; opacity: 0.9;">Questions about tools? Want to suggest a product for review? We'd love to hear from you.</p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; margin-bottom: 40px;">

<div>
<h2 style="margin-top: 0;">Contact Information</h2>

<div style="background: #f8f9fa; padding: 25px; border-radius: 8px; margin-bottom: 20px;">
<h3 style="margin-top: 0; color: #2d5a27;">Email Us</h3>
<p style="font-size: 1.2em; margin-bottom: 5px;"><a href="mailto:hello@toolshedtested.com" style="color: #2d5a27; font-weight: bold;">hello@toolshedtested.com</a></p>
<p style="color: #666; margin: 0; font-size: 0.9em;">We typically respond within 24-48 hours</p>
</div>

<h3>What Can We Help With?</h3>
<ul>
<li><strong>Tool Questions:</strong> Need advice on which tool to buy? Ask away!</li>
<li><strong>Review Requests:</strong> Have a tool you'd like us to test?</li>
<li><strong>Technical Issues:</strong> Problems with the website?</li>
<li><strong>Partnerships:</strong> Business and collaboration inquiries</li>
<li><strong>Feedback:</strong> Suggestions to improve our reviews</li>
</ul>

<div style="background: #e8f5e9; padding: 20px; border-radius: 8px; margin-top: 25px;">
<h4 style="margin-top: 0; color: #2d5a27;">For Manufacturers</h4>
<p style="margin-bottom: 0;">Want to submit a product for consideration? Email us with product details, availability, and retail pricing. Note: We maintain editorial independence by purchasing all tools ourselves.</p>
</div>
</div>

<div>
<h2 style="margin-top: 0;">Send Us a Message</h2>
<p>Drop us an email and we'll get back to you as soon as possible.</p>

<a href="mailto:hello@toolshedtested.com" style="display: block; background: linear-gradient(135deg, #ff9800, #f57c00); color: #1a1a1a; padding: 18px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 1.1rem; text-align: center; margin-top: 20px;">
Send Email →
</a>
</div>

</div>

<div style="background: #1a1a1a; color: white; padding: 30px; border-radius: 8px; text-align: center;">
<h3 style="color: white; margin-bottom: 10px;">Looking for Quick Answers?</h3>
<p style="margin-bottom: 20px; opacity: 0.9;">Check out our FAQ for common questions about tools and our review process.</p>
<a href="/faq/" style="background: white; color: #1a1a1a; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">View FAQ</a>
</div>
'''


def main():
    if not all([WP_USER, WP_APP_PASSWORD]):
        print("❌ Error: Missing WordPress credentials")
        print("Set environment variables: WP_USER, WP_APP_PASSWORD")
        print("")
        print("To create an Application Password:")
        print("1. Go to WordPress Admin > Users > Profile")
        print("2. Scroll to 'Application Passwords'")
        print("3. Enter a name (e.g., 'Publishing Script')")
        print("4. Click 'Add New Application Password'")
        print("5. Copy the password (you won't see it again)")
        print("")
        print("Then run:")
        print("  export WP_USER='your-username'")
        print("  export WP_APP_PASSWORD='xxxx xxxx xxxx xxxx xxxx xxxx'")
        print("  python publish_pages.py")
        sys.exit(1)

    print("=" * 50)
    print("Publishing Pages to ToolShed Tested")
    print("=" * 50)
    print("")

    # Publish Privacy Policy
    publish_page(
        title="Privacy Policy",
        slug="privacy-policy",
        content=PRIVACY_POLICY_CONTENT,
        meta_description="Privacy Policy for ToolShed Tested. Learn how we collect, use, and protect your information."
    )

    # Publish Affiliate Disclosure
    publish_page(
        title="Affiliate Disclosure",
        slug="affiliate-disclosure",
        content=AFFILIATE_DISCLOSURE_CONTENT,
        meta_description="ToolShed Tested affiliate disclosure. We earn from qualifying Amazon purchases. Learn how we stay independent."
    )

    # Publish FAQ
    publish_page(
        title="Frequently Asked Questions",
        slug="faq",
        content=FAQ_CONTENT,
        meta_description="Frequently asked questions about ToolShed Tested reviews, affiliate links, and tool recommendations."
    )

    # Publish Contact
    publish_page(
        title="Contact",
        slug="contact",
        content=CONTACT_CONTENT,
        meta_description="Contact ToolShed Tested. Questions about tools? Want to suggest a product for review? We'd love to hear from you."
    )

    print("")
    print("=" * 50)
    print("Done!")
    print("=" * 50)


if __name__ == "__main__":
    main()
