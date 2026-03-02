"""
Central configuration for the Executive Job Search OS.
All filters, weights, target criteria, and company lists live here.
"""

import os
from pathlib import Path

# ── Paths ─────────────────────────────────────────────────────────────────────
BASE_DIR = Path(__file__).parent
DATA_DIR = BASE_DIR / "data"
RESUMES_DIR = BASE_DIR / "resumes"
COVER_LETTERS_DIR = BASE_DIR / "cover_letters"
JD_DIR = BASE_DIR / "job_descriptions"
LOGS_DIR = BASE_DIR / "logs"
EXPORTS_DIR = BASE_DIR / "exports"
DASHBOARD_DIR = BASE_DIR / "dashboard"
DB_PATH = DATA_DIR / "jobs.db"
DASHBOARD_DATA_PATH = DASHBOARD_DIR / "data.json"

# ── Candidate Profile ─────────────────────────────────────────────────────────
CANDIDATE = {
    "name": "Michelle Perkins",
    "email": "michelle.perkins@email.com",        # Update in .env or here
    "phone": "(555) 000-0000",                    # Update in .env or here
    "location": "New York, NY",
    "linkedin": "linkedin.com/in/michelleperkins",
    "title_positioning": "SVP, Media Strategy | Healthcare & Pharma | Omnichannel | Oncology",
    "years_experience": 15,
}

# ── Target Role Criteria ──────────────────────────────────────────────────────
TARGET_TITLES = [
    "SVP, Strategy",
    "SVP Strategy",
    "SVP, Media",
    "SVP Media",
    "VP, Media Strategy",
    "VP Media Strategy",
    "VP, Omnichannel",
    "VP Omnichannel",
    "Executive Director, Media",
    "Executive Director Media",
    "Head of Media",
    "Head of Omnichannel",
    "Vice President Media",
    "Vice President Strategy",
    "Senior Vice President Media",
    "Senior Vice President Strategy",
    "Chief Media Officer",
    "Global Head of Media",
    "Director, Media Strategy",      # Director only if comp >= $180K
    "Director Media Strategy",
    "Director, Omnichannel",
]

TARGET_TITLE_KEYWORDS = [
    "svp", "senior vice president", "vice president", "vp",
    "executive director", "head of", "chief media",
    "global head", "director",
]

TARGET_INDUSTRIES = [
    "pharmaceutical", "pharma", "healthcare", "health care",
    "hcp", "hcp marketing", "physician marketing",
    "oncology", "rare disease", "specialty pharma",
    "healthtech", "health tech", "health technology",
    "biotech", "biotechnology", "life sciences",
    "medical", "clinical", "health system",
    "managed care", "payer", "benefit manager",
    "healthcare agency", "health agency", "pharma agency",
    "ai health", "ai healthcare", "digital health",
]

SALARY_TARGET = 250000
SALARY_FLOOR = 180000
SALARY_FLOOR_DIRECTOR = 180000  # Directors require at least this

TARGET_LOCATIONS = ["New York", "NYC", "Remote", "Hybrid", "New Jersey", "NJ", "Connecticut", "CT"]

# ── Exclusion Lists ───────────────────────────────────────────────────────────
EXCLUDED_COMPANIES = [
    # Omnicom agencies
    "omnicom", "omnicom health group", "cdm ny", "cdm princeton",
    "palio", "concord health", "proed communications",
    "tbwa worldhealth", "ddb health", "bbdo health",
    "abelson-taylor", "abelson taylor",
    "OMC", "DAS Health",
    # IPG agencies
    "ipg", "interpublic", "mccann health", "mccann worldgroup",
    "fcb health", "foote cone belding",
    "initiative health", "um healthcare", "universal mccann",
    "ipg mediabrands", "momentum healthcare",
    "jack morton", "initiative",
    # Explicitly excluded
    "cmi media group", "cmi",
]

EXCLUDED_KEYWORDS_IN_TITLE = [
    "coordinator", "specialist", "associate director", "manager",
    "analyst", "intern", "freelance", "contract",
]

# ── Search Queries ────────────────────────────────────────────────────────────
LINKEDIN_SEARCH_QUERIES = [
    "SVP Media Strategy healthcare pharma",
    "SVP Strategy pharmaceutical oncology",
    "VP Media Strategy healthcare omnichannel",
    "VP Omnichannel pharmaceutical HCP",
    "Executive Director Media pharma healthcare",
    "Head of Media pharmaceutical",
    "Head of Omnichannel healthcare",
    "Senior Vice President Media pharma",
    "SVP Media healthtech biotech",
    "VP Media Strategy HCP marketing",
]

INDEED_SEARCH_QUERIES = [
    '"SVP" OR "Senior Vice President" media strategy healthcare',
    '"VP" OR "Vice President" omnichannel pharmaceutical',
    '"Executive Director" media pharma oncology',
    '"Head of Media" healthcare',
    '"Head of Omnichannel" pharmaceutical',
    '"SVP" media strategy healthtech',
]

BUILTIN_SEARCH_QUERIES = [
    "SVP media healthcare",
    "VP omnichannel pharma",
    "head of media health",
    "senior vice president strategy healthcare",
]

# ── Target Company Career Pages ───────────────────────────────────────────────
# Format: {company_name: (career_page_url, search_pattern)}
TARGET_COMPANY_CAREER_PAGES = {
    "AstraZeneca": "https://careers.astrazeneca.com/search-jobs?Keywords=media+strategy",
    "Pfizer": "https://www.pfizercareers.com/search-jobs?Keywords=media+strategy+vice+president",
    "Bristol Myers Squibb": "https://careers.bms.com/search-jobs?Keywords=media+strategy+svp",
    "Merck": "https://jobs.merck.com/us/en/search-results?keywords=media+strategy+vp",
    "Eli Lilly": "https://careers.lilly.com/us/en/search-results?keywords=media+omnichannel",
    "Johnson & Johnson": "https://jobs.jnj.com/jobs?keywords=svp+media+strategy",
    "Novartis": "https://www.novartis.com/careers/career-search?search_api_fulltext=media+strategy+vp",
    "Roche": "https://www.roche.com/careers/jobs.htm?keywords=media+strategy+director",
    "Amgen": "https://careers.amgen.com/en/search-jobs?keywords=media+strategy+vp",
    "Biogen": "https://www.biogen.com/careers.html",
    "Regeneron": "https://careers.regeneron.com/us/en/search-results?keywords=media+strategy",
    "Gilead Sciences": "https://jobs.gilead.com/search/?q=media+strategy",
    "Sanofi": "https://www.sanofi.com/en/our-commitments/sanofi-careers",
    "Moderna": "https://www.modernatx.com/careers",
    "Takeda": "https://careers.takeda.com/search-jobs?Keywords=media+strategy",
    # Healthtech
    "Veeva Systems": "https://careers.veeva.com/jobs?search=media+strategy",
    "Inovalon": "https://www.inovalon.com/careers",
    "DeepIntent": "https://deepintent.com/about/careers/",
    "Doceree": "https://doceree.com/careers/",
    "PulsePoint": "https://pulsepoint.com/company/careers/",
    "MMIT": "https://www.mmitnetwork.com/careers/",
    # Independent Healthcare Agencies
    "Real Chemistry": "https://www.realchemistry.com/careers",
    "Klick Health": "https://www.klick.com/careers/",
    "Intouch Group": "https://www.intouchgroup.com/about/careers",
    "Publicis Health": "https://publicishealth.com/about/careers",
    "Havas Health": "https://havasgrouphealth.com/careers",
    "Ogilvy Health": "https://www.ogilvyhealth.com/careers",
    "ProSciento": "https://www.prosciento.com/careers",
    "Palio+McCann Health": None,  # Excluded — McCann is IPG, Palio is Omnicom
}

# Remove None entries (excluded companies that snuck into both lists)
TARGET_COMPANY_CAREER_PAGES = {
    k: v for k, v in TARGET_COMPANY_CAREER_PAGES.items() if v is not None
}

# ── Scoring Weights ───────────────────────────────────────────────────────────
SCORING_WEIGHTS = {
    "title_seniority": 25,          # Max 25 pts
    "industry_alignment": 20,       # Max 20 pts
    "compensation_estimate": 20,    # Max 20 pts
    "hcp_omnichannel_alignment": 15, # Max 15 pts
    "oncology_alignment": 10,       # Max 10 pts
    "leadership_scope": 5,          # Max 5 pts
    "geography": 5,                 # Max 5 pts
}
SCORE_MINIMUM = 75

# ── Title Seniority Mapping ───────────────────────────────────────────────────
TITLE_SENIORITY_SCORES = {
    "cmo": 25, "chief": 25,
    "svp": 24, "senior vice president": 24,
    "evp": 25, "executive vice president": 25,
    "vp": 22, "vice president": 22,
    "head of": 21,
    "executive director": 20,
    "global head": 22,
    "director": 15,              # Base — compensated if salary qualifies
}

# ── HCP / Omnichannel / Oncology Keywords (for scoring) ───────────────────────
HCP_KEYWORDS = [
    "hcp", "physician", "prescriber", "medical professional",
    "healthcare professional", "specialist", "oncologist",
    "npi", "ehr", "electronic health record", "provider",
    "speaker bureau", "medical affairs", "key opinion leader", "kol",
    "patient journey", "rx", "brand", "clinical",
]

OMNICHANNEL_KEYWORDS = [
    "omnichannel", "omni-channel", "integrated media",
    "programmatic", "paid media", "digital media",
    "media strategy", "media planning", "media buying",
    "investment strategy", "media mix", "attribution",
    "connected tv", "ctv", "streaming", "social media",
    "endemic", "non-endemic", "search", "sem", "display",
    "automation", "trafficking", "ad operations", "adops",
]

ONCOLOGY_KEYWORDS = [
    "oncology", "cancer", "tumor", "immuno-oncology", "io",
    "rare disease", "specialty pharma", "biologics",
    "targeted therapy", "checkpoint inhibitor", "immunotherapy",
    "hematology", "solid tumor", "lung cancer", "breast cancer",
]

LEADERSHIP_KEYWORDS = [
    "manage team", "lead team", "direct reports", "p&l",
    "budget", "budget oversight", "investment",
    "cross-functional", "stakeholder", "executive",
    "c-suite", "board", "strategy", "vision",
]

# ── AI Model Config ───────────────────────────────────────────────────────────
AI_MODEL = "claude-opus-4-6"          # Best model for executive-quality output
AI_MODEL_FAST = "claude-haiku-4-5-20251001"   # Fast model for scoring/extraction

# ── Scheduler ─────────────────────────────────────────────────────────────────
SCHEDULE_HOUR = 7          # 7 AM
SCHEDULE_MINUTE = 0
SCHEDULE_TIMEZONE = "America/New_York"
JOBS_PER_DAY_TARGET = 3   # Target 2–3 new qualified jobs per run

# ── Networking ────────────────────────────────────────────────────────────────
REQUEST_DELAY_SECONDS = 2.5   # Polite delay between requests
MAX_RETRIES = 3
REQUEST_TIMEOUT = 30

USER_AGENTS = [
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15",
]
