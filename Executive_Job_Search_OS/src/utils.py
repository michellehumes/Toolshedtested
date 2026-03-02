"""
Shared utility functions for the Executive Job Search OS.
"""

import hashlib
import logging
import os
import re
import sys
import time
import random
from datetime import datetime, date
from pathlib import Path
from typing import Optional

import config


def setup_logging(log_name: str = "daily_run") -> logging.Logger:
    """Configure file + console logging. Returns the root logger."""
    config.LOGS_DIR.mkdir(parents=True, exist_ok=True)
    log_file = config.LOGS_DIR / f"{log_name}_{date.today().isoformat()}.log"

    fmt = "%(asctime)s  %(levelname)-8s  %(name)s — %(message)s"
    datefmt = "%Y-%m-%d %H:%M:%S"

    logging.basicConfig(
        level=logging.DEBUG if os.getenv("VERBOSE", "false").lower() == "true" else logging.INFO,
        format=fmt,
        datefmt=datefmt,
        handlers=[
            logging.FileHandler(log_file, encoding="utf-8"),
            logging.StreamHandler(sys.stdout),
        ],
    )
    return logging.getLogger("job_os")


def compute_hash(text: str) -> str:
    """SHA-256 hash of arbitrary text — used for deduplication."""
    return hashlib.sha256(text.strip().encode("utf-8")).hexdigest()


def slugify_filename(company: str, title: str) -> str:
    """Return a safe filename stem: 'AstraZeneca_SVP_Media_Strategy'."""
    def clean(s: str) -> str:
        s = s.strip().replace(" ", "_")
        s = re.sub(r"[^\w\-]", "", s)
        return s[:40]

    return f"{clean(company)}_{clean(title)}"


def sanitize_company_name(name: str) -> str:
    return re.sub(r"\s+", " ", name.strip())


def estimate_salary_from_title(title: str, company_tier: str = "mid") -> int:
    """
    Rough salary estimate based on seniority keyword and company tier.
    company_tier: 'large' (Fortune 500 pharma), 'mid' (agency/healthtech), 'small'
    """
    title_lower = title.lower()
    multipliers = {"large": 1.15, "mid": 1.0, "small": 0.85}
    mult = multipliers.get(company_tier, 1.0)

    if any(k in title_lower for k in ["chief", "cmo", "evp", "executive vice"]):
        base = 350_000
    elif any(k in title_lower for k in ["svp", "senior vice president"]):
        base = 280_000
    elif any(k in title_lower for k in ["vp", "vice president", "head of", "global head"]):
        base = 230_000
    elif "executive director" in title_lower:
        base = 210_000
    elif "director" in title_lower:
        base = 185_000
    else:
        base = 160_000

    return int(base * mult)


def extract_salary_from_text(text: str) -> Optional[int]:
    """
    Parse a salary range from raw job description text.
    Returns the mid-point if a range is found, or the single value.
    """
    # Patterns: $200,000 – $250,000 / $200K - $250K / 200000-250000
    patterns = [
        r"\$\s*([\d,]+)[Kk]?\s*[-–—to]+\s*\$?\s*([\d,]+)[Kk]?",
        r"([\d,]+)[Kk]\s*[-–—to]+\s*([\d,]+)[Kk]",
        r"\$\s*([\d]{3,}[,\d]*)\s*(?:per year|annually|/yr|/year)",
    ]
    for pat in patterns:
        m = re.search(pat, text, re.IGNORECASE)
        if m:
            groups = m.groups()
            vals = []
            for g in groups:
                if g:
                    g = g.replace(",", "")
                    v = int(g)
                    if v < 1000:
                        v *= 1000    # e.g., "250K" parsed as 250
                    vals.append(v)
            if len(vals) == 2:
                return int((vals[0] + vals[1]) / 2)
            elif vals:
                return vals[0]
    return None


def polite_delay(base: float = None):
    """Sleep with slight jitter to be a polite scraper."""
    base = base or config.REQUEST_DELAY_SECONDS
    time.sleep(base + random.uniform(0.5, 1.5))


def get_random_user_agent() -> str:
    return random.choice(config.USER_AGENTS)


def today_str() -> str:
    return date.today().isoformat()


def now_str() -> str:
    return datetime.now().strftime("%Y-%m-%d %H:%M:%S")


def normalize_title(title: str) -> str:
    """Lower-case, collapse whitespace, strip punctuation for comparison."""
    t = title.lower()
    t = re.sub(r"[,/|]", " ", t)
    t = re.sub(r"\s+", " ", t).strip()
    return t


def is_excluded_company(company: str) -> bool:
    """Return True if this company is on the exclusion list."""
    c = company.lower().strip()
    for excl in config.EXCLUDED_COMPANIES:
        if excl in c or c in excl:
            return True
    return False


def is_excluded_title(title: str) -> bool:
    """Return True if title contains a junior/excluded keyword."""
    t = normalize_title(title)
    for kw in config.EXCLUDED_KEYWORDS_IN_TITLE:
        if kw in t:
            return True
    return False


def format_currency(amount: Optional[int]) -> str:
    if not amount:
        return "Not disclosed"
    return f"${amount:,.0f}"


def truncate(text: str, max_len: int = 2000) -> str:
    """Safely truncate text for DB storage or API calls."""
    if not text:
        return ""
    return text[:max_len] if len(text) > max_len else text


def ensure_dirs():
    """Create all required output directories if they don't exist."""
    for d in [
        config.DATA_DIR,
        config.RESUMES_DIR,
        config.COVER_LETTERS_DIR,
        config.JD_DIR,
        config.LOGS_DIR,
        config.EXPORTS_DIR,
        config.DASHBOARD_DIR,
    ]:
        d.mkdir(parents=True, exist_ok=True)
