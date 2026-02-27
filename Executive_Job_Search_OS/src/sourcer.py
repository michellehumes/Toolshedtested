"""
Multi-source job scraping engine.
Sources: LinkedIn · Indeed RSS · BuiltIn · Curated company career pages.
All sources return a uniform list of raw job dicts.
"""

import json
import logging
import re
import time
import urllib.parse
from dataclasses import dataclass, field, asdict
from datetime import date
from pathlib import Path
from typing import List, Optional
from xml.etree import ElementTree

import feedparser
import requests
from bs4 import BeautifulSoup
from tenacity import retry, stop_after_attempt, wait_exponential, retry_if_exception_type

import config
from src.utils import (
    get_random_user_agent, polite_delay, is_excluded_company,
    is_excluded_title, today_str, truncate,
)

log = logging.getLogger("job_os.sourcer")


@dataclass
class RawJob:
    title: str
    company: str
    location: str
    link: str
    source: str
    raw_description: str = ""
    date_found: str = field(default_factory=today_str)
    compensation_estimate: Optional[int] = None

    def to_dict(self) -> dict:
        return asdict(self)

    def is_valid(self) -> bool:
        return bool(self.title and self.company and self.link)


# ── Base class ────────────────────────────────────────────────────────────────

class BaseSourcer:
    def __init__(self):
        self.session = requests.Session()
        self.session.headers.update({
            "User-Agent": get_random_user_agent(),
            "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8",
            "Accept-Language": "en-US,en;q=0.9",
            "Accept-Encoding": "gzip, deflate, br",
            "DNT": "1",
            "Connection": "keep-alive",
            "Upgrade-Insecure-Requests": "1",
        })

    @retry(
        stop=stop_after_attempt(config.MAX_RETRIES),
        wait=wait_exponential(multiplier=2, min=3, max=20),
        retry=retry_if_exception_type((requests.Timeout, requests.ConnectionError)),
        reraise=True,
    )
    def _get(self, url: str, params: dict = None, timeout: int = None) -> Optional[requests.Response]:
        try:
            resp = self.session.get(
                url, params=params,
                timeout=timeout or config.REQUEST_TIMEOUT,
                allow_redirects=True,
            )
            resp.raise_for_status()
            return resp
        except requests.HTTPError as e:
            log.warning("HTTP %s for %s", e.response.status_code if e.response else "?", url)
            return None

    def fetch_job_description(self, url: str) -> str:
        """Fetch full job description text from a posting page."""
        polite_delay()
        try:
            resp = self._get(url)
            if not resp:
                return ""
            soup = BeautifulSoup(resp.text, "lxml")
            # Remove nav, header, footer, scripts, styles
            for tag in soup(["nav", "header", "footer", "script", "style", "aside"]):
                tag.decompose()
            # Try common JD containers
            for selector in [
                ".job-description", "#job-description", ".description__text",
                ".jobDescriptionContent", ".job-details", "[data-testid='jobdescription']",
                ".jobs-description", "#jobDescriptionText", ".jobsearch-JobComponent-description",
                "article", "main",
            ]:
                el = soup.select_one(selector)
                if el:
                    return truncate(el.get_text(separator="\n").strip(), 8000)
            return truncate(soup.get_text(separator="\n").strip(), 8000)
        except Exception as e:
            log.debug("Could not fetch JD from %s: %s", url, e)
            return ""

    def save_raw_jd(self, job: RawJob):
        """Persist raw JD text to /job_descriptions/ folder."""
        if not job.raw_description:
            return
        config.JD_DIR.mkdir(parents=True, exist_ok=True)
        safe_name = re.sub(r"[^\w\-]", "_", f"{job.company}_{job.title}")[:80]
        path = config.JD_DIR / f"{safe_name}_{today_str()}.txt"
        path.write_text(job.raw_description, encoding="utf-8")

    def fetch(self) -> List[RawJob]:
        raise NotImplementedError


# ── LinkedIn Sourcer ──────────────────────────────────────────────────────────

class LinkedInSourcer(BaseSourcer):
    """
    Scrapes LinkedIn's public (no-login) job search pages.
    URL: https://www.linkedin.com/jobs/search/?keywords=...&location=...
    """
    BASE_URL = "https://www.linkedin.com/jobs/search/"

    def fetch(self) -> List[RawJob]:
        jobs = []
        for query in config.LINKEDIN_SEARCH_QUERIES[:5]:  # Limit to avoid rate limiting
            log.info("LinkedIn search: %s", query)
            params = {
                "keywords": query,
                "location": "United States",
                "f_TPR": "r86400",    # Past 24 hours
                "f_E": "4,5",         # Senior / Director level
                "sortBy": "DD",
                "start": 0,
            }
            polite_delay()
            resp = self._get(self.BASE_URL, params=params)
            if not resp:
                continue
            batch = self._parse_linkedin_page(resp.text, query)
            jobs.extend(batch)
            log.info("LinkedIn '%s' → %d listings", query, len(batch))
            polite_delay(4)  # Longer delay for LinkedIn

        return self._dedupe(jobs)

    def _parse_linkedin_page(self, html: str, query: str) -> List[RawJob]:
        soup = BeautifulSoup(html, "lxml")
        results = []

        # LinkedIn public search job cards
        cards = soup.select("div.base-card, li.jobs-search__results-list > div")
        if not cards:
            # Fallback: look for job-search-card
            cards = soup.select(".job-search-card")

        for card in cards:
            try:
                title_el = card.select_one(
                    "h3.base-search-card__title, h3.job-search-card__title, "
                    ".base-search-card__title, [class*='title']"
                )
                company_el = card.select_one(
                    "h4.base-search-card__subtitle, .base-search-card__subtitle, "
                    ".job-search-card__company-name, [class*='company']"
                )
                location_el = card.select_one(
                    ".job-search-card__location, .base-search-card__metadata, "
                    "[class*='location']"
                )
                link_el = card.select_one("a[href*='/jobs/view/'], a.base-card__full-link")

                if not (title_el and company_el and link_el):
                    continue

                title = title_el.get_text(strip=True)
                company = company_el.get_text(strip=True)
                location = location_el.get_text(strip=True) if location_el else "Unknown"
                link = link_el.get("href", "").split("?")[0]

                if not link.startswith("http"):
                    link = "https://www.linkedin.com" + link

                job = RawJob(
                    title=title, company=company,
                    location=location, link=link,
                    source="LinkedIn",
                )
                if job.is_valid():
                    results.append(job)
            except Exception as e:
                log.debug("LinkedIn card parse error: %s", e)
                continue

        return results

    @staticmethod
    def _dedupe(jobs: List[RawJob]) -> List[RawJob]:
        seen = set()
        out = []
        for j in jobs:
            key = j.link
            if key not in seen:
                seen.add(key)
                out.append(j)
        return out


# ── Indeed RSS Sourcer ────────────────────────────────────────────────────────

class IndeedSourcer(BaseSourcer):
    """
    Uses Indeed's public RSS feed — the most reliable, stable endpoint.
    RSS URL: https://www.indeed.com/rss?q=...&l=...&sort=date&fromage=1
    """
    RSS_BASE = "https://www.indeed.com/rss"

    def fetch(self) -> List[RawJob]:
        jobs = []
        location_encoded = urllib.parse.quote("United States")

        for query in config.INDEED_SEARCH_QUERIES[:6]:
            log.info("Indeed RSS: %s", query)
            params = {
                "q": query,
                "l": "United States",
                "sort": "date",
                "fromage": "1",
                "radius": "50",
                "limit": "25",
            }
            url = self.RSS_BASE + "?" + urllib.parse.urlencode(params)
            polite_delay()

            try:
                feed = feedparser.parse(url)
                batch = self._parse_indeed_feed(feed)
                jobs.extend(batch)
                log.info("Indeed '%s' → %d listings", query, len(batch))
            except Exception as e:
                log.warning("Indeed RSS error for '%s': %s", query, e)

            polite_delay(3)

        return self._dedupe(jobs)

    def _parse_indeed_feed(self, feed) -> List[RawJob]:
        results = []
        for entry in feed.entries:
            try:
                title = entry.get("title", "").strip()
                company = self._extract_company_from_entry(entry)
                location = entry.get("indeed_city", "") or entry.get("location", "")
                link = entry.get("link", "").split("?")[0]
                summary = BeautifulSoup(
                    entry.get("summary", ""), "html.parser"
                ).get_text(separator="\n").strip()

                job = RawJob(
                    title=title, company=company,
                    location=location, link=link,
                    source="Indeed", raw_description=truncate(summary, 3000),
                )
                if job.is_valid():
                    results.append(job)
            except Exception as e:
                log.debug("Indeed entry parse error: %s", e)
                continue
        return results

    @staticmethod
    def _extract_company_from_entry(entry) -> str:
        # Indeed often puts company in 'author' or inside title as "Title - Company"
        company = entry.get("author", "").strip()
        if not company:
            title_raw = entry.get("title", "")
            if " - " in title_raw:
                parts = title_raw.rsplit(" - ", 1)
                company = parts[-1].strip()
        return company or "Unknown"

    @staticmethod
    def _dedupe(jobs: List[RawJob]) -> List[RawJob]:
        seen = set()
        out = []
        for j in jobs:
            key = j.link
            if key not in seen:
                seen.add(key)
                out.append(j)
        return out


# ── BuiltIn Sourcer ───────────────────────────────────────────────────────────

class BuiltInSourcer(BaseSourcer):
    """Scrapes BuiltIn.com's job search for NYC healthcare/pharma/tech roles."""
    BASE_URL = "https://builtin.com/jobs"

    def fetch(self) -> List[RawJob]:
        jobs = []
        for query in config.BUILTIN_SEARCH_QUERIES[:4]:
            log.info("BuiltIn search: %s", query)
            params = {
                "search": query,
                "level[]": "Senior,Director,VP,CXO",
                "remote": "true",
            }
            polite_delay()
            resp = self._get(self.BASE_URL, params=params)
            if not resp:
                continue
            batch = self._parse_builtin_page(resp.text)
            jobs.extend(batch)
            log.info("BuiltIn '%s' → %d listings", query, len(batch))
            polite_delay(3)

        return self._dedupe(jobs)

    def _parse_builtin_page(self, html: str) -> List[RawJob]:
        soup = BeautifulSoup(html, "lxml")
        results = []
        # BuiltIn job cards
        cards = soup.select("li[data-id], div.job-boundingbox, article.job-card, .jobs-list__item")

        for card in cards:
            try:
                title_el = card.select_one("h2, h3, [data-testid='job-title'], .job-title, a[href*='/jobs/']")
                company_el = card.select_one(".company-name, [data-testid='company-name'], .employer-name")
                location_el = card.select_one(".job-info, .location, [data-testid='job-location']")
                link_el = card.select_one("a[href*='/jobs/']")

                if not title_el or not link_el:
                    continue

                title = title_el.get_text(strip=True)
                company = company_el.get_text(strip=True) if company_el else "Unknown"
                location = location_el.get_text(strip=True) if location_el else "Remote/Unknown"
                link = link_el.get("href", "")
                if not link.startswith("http"):
                    link = "https://builtin.com" + link

                job = RawJob(
                    title=title, company=company,
                    location=location, link=link, source="BuiltIn",
                )
                if job.is_valid():
                    results.append(job)
            except Exception as e:
                log.debug("BuiltIn card parse error: %s", e)
                continue

        return results

    @staticmethod
    def _dedupe(jobs: List[RawJob]) -> List[RawJob]:
        seen = set()
        out = []
        for j in jobs:
            if j.link not in seen:
                seen.add(j.link)
                out.append(j)
        return out


# ── Company Career Page Sourcer ───────────────────────────────────────────────

class CompanyCareerSourcer(BaseSourcer):
    """
    Directly monitors curated pharma/healthtech company career pages.
    Looks for any senior-level media/strategy roles posted recently.
    """
    TARGET_KEYWORDS_IN_TITLE = [
        "media", "omnichannel", "strategy", "svp", "vp", "vice president",
        "head of", "executive director", "director",
    ]

    def fetch(self) -> List[RawJob]:
        jobs = []
        for company, url in config.TARGET_COMPANY_CAREER_PAGES.items():
            if not url:
                continue
            log.info("Checking career page: %s", company)
            polite_delay()
            resp = self._get(url)
            if not resp:
                log.debug("No response from %s career page", company)
                continue
            batch = self._extract_jobs_from_page(resp.text, company, url)
            if batch:
                log.info("Found %d potential roles at %s", len(batch), company)
                jobs.extend(batch)
            polite_delay(3)

        return jobs

    def _extract_jobs_from_page(self, html: str, company: str, base_url: str) -> List[RawJob]:
        soup = BeautifulSoup(html, "lxml")
        results = []

        # Generic approach: find all links containing job-related path words
        all_links = soup.find_all("a", href=True)
        for a in all_links:
            href = a.get("href", "")
            text = a.get_text(strip=True)

            if not text or len(text) < 5 or len(text) > 120:
                continue

            # Check if link text looks like a job title
            text_lower = text.lower()
            if not any(kw in text_lower for kw in self.TARGET_KEYWORDS_IN_TITLE):
                continue

            # Resolve URL
            if href.startswith("http"):
                full_url = href
            elif href.startswith("/"):
                from urllib.parse import urlparse
                parsed = urlparse(base_url)
                full_url = f"{parsed.scheme}://{parsed.netloc}{href}"
            else:
                continue

            # Skip if clearly not a job posting
            if any(skip in href.lower() for skip in ["#", "mailto:", "javascript:", "linkedin", "twitter"]):
                continue

            job = RawJob(
                title=text,
                company=company,
                location="See posting",
                link=full_url,
                source=f"CareerPage:{company}",
            )
            if job.is_valid():
                results.append(job)

        # Dedupe within company
        seen = set()
        unique = []
        for j in results:
            if j.link not in seen:
                seen.add(j.link)
                unique.append(j)
        return unique[:10]  # Cap per company


# ── Main Aggregator ───────────────────────────────────────────────────────────

class JobAggregator:
    """
    Orchestrates all sourcers. Filters exclusions, enriches with full JDs,
    and returns deduplicated list of raw job dicts ready for scoring.
    """

    def __init__(self, fetch_full_jds: bool = True):
        self.sourcers = [
            IndeedSourcer(),    # Most reliable (RSS)
            LinkedInSourcer(),  # High quality
            BuiltInSourcer(),   # Good for healthtech
            CompanyCareerSourcer(),  # Direct company pages
        ]
        self.fetch_full_jds = fetch_full_jds
        self._base_sourcer = BaseSourcer()

    def run(self) -> List[dict]:
        all_jobs: List[RawJob] = []

        for sourcer in self.sourcers:
            try:
                batch = sourcer.fetch()
                log.info("%s returned %d raw listings", sourcer.__class__.__name__, len(batch))
                all_jobs.extend(batch)
            except Exception as e:
                log.error("Sourcer %s failed: %s", sourcer.__class__.__name__, e)

        log.info("Total raw listings before filtering: %d", len(all_jobs))

        # Pre-filter: exclusions, title quality
        filtered = self._pre_filter(all_jobs)
        log.info("After pre-filter: %d listings", len(filtered))

        # Deduplicate across all sources
        deduped = self._global_dedupe(filtered)
        log.info("After dedup: %d listings", len(deduped))

        # Enrich with full job descriptions
        if self.fetch_full_jds:
            for job in deduped:
                if not job.raw_description:
                    log.debug("Fetching full JD for: %s @ %s", job.title, job.company)
                    job.raw_description = self._base_sourcer.fetch_job_description(job.link)
                self._base_sourcer.save_raw_jd(job)

        return [j.to_dict() for j in deduped]

    def _pre_filter(self, jobs: List[RawJob]) -> List[RawJob]:
        """Remove excluded companies, junior titles, obvious mismatches."""
        out = []
        for job in jobs:
            if is_excluded_company(job.company):
                log.debug("Excluded company: %s", job.company)
                continue
            if is_excluded_title(job.title):
                log.debug("Excluded title: %s", job.title)
                continue
            out.append(job)
        return out

    @staticmethod
    def _global_dedupe(jobs: List[RawJob]) -> List[RawJob]:
        """Dedupe across all sources by link URL."""
        seen_links = set()
        out = []
        for job in jobs:
            if job.link not in seen_links:
                seen_links.add(job.link)
                out.append(job)
        return out
