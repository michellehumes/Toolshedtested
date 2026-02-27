"""
Job scoring engine — rates each role 1–100 based on Michelle's target criteria.
Roles scoring < 75 are filtered out. Roles scoring >= 75 are queued for document generation.
"""

import logging
import re
from typing import Dict, Optional, Tuple

import anthropic

import config
from src.utils import (
    estimate_salary_from_title, extract_salary_from_text,
    normalize_title, is_excluded_company, is_excluded_title,
    format_currency,
)

log = logging.getLogger("job_os.scorer")


class JobScorer:
    """
    Scores a raw job dict on a 100-point scale.
    Component breakdown (per config.SCORING_WEIGHTS):
      • Title seniority      — 25 pts
      • Industry alignment   — 20 pts
      • Compensation         — 20 pts
      • HCP/Omnichannel      — 15 pts
      • Oncology             — 10 pts
      • Leadership scope     — 5 pts
      • Geography            — 5 pts
    """

    def __init__(self, use_ai_boost: bool = True):
        self.use_ai_boost = use_ai_boost
        self._ai_client: Optional[anthropic.Anthropic] = None

    @property
    def ai_client(self) -> anthropic.Anthropic:
        if self._ai_client is None:
            self._ai_client = anthropic.Anthropic()
        return self._ai_client

    # ── Main entry point ──────────────────────────────────────────────────────

    def score(self, job: Dict) -> Tuple[int, Dict]:
        """
        Score a job. Returns (final_score, score_breakdown_dict).
        """
        title = job.get("title", "")
        company = job.get("company", "")
        location = job.get("location", "")
        raw_jd = job.get("raw_description", "")
        jd_text = (title + " " + company + " " + raw_jd).lower()

        # Hard disqualifiers
        if is_excluded_company(company):
            return 0, {"reason": "excluded_company"}
        if is_excluded_title(title):
            return 0, {"reason": "excluded_title"}

        breakdown = {}

        # Component 1: Title Seniority (max 25)
        title_score, title_note = self._score_title(title)
        breakdown["title_seniority"] = {"score": title_score, "note": title_note}

        # Component 2: Industry Alignment (max 20)
        industry_score, industry_note = self._score_industry(jd_text)
        breakdown["industry_alignment"] = {"score": industry_score, "note": industry_note}

        # Component 3: Compensation (max 20)
        comp_estimate, comp_score, comp_note = self._score_compensation(title, company, raw_jd)
        breakdown["compensation"] = {"score": comp_score, "note": comp_note, "estimate": comp_estimate}

        # Component 4: HCP/Omnichannel Alignment (max 15)
        hcp_score, hcp_note = self._score_hcp_omnichannel(jd_text)
        breakdown["hcp_omnichannel"] = {"score": hcp_score, "note": hcp_note}

        # Component 5: Oncology Alignment (max 10)
        onco_score, onco_note = self._score_oncology(jd_text)
        breakdown["oncology"] = {"score": onco_score, "note": onco_note}

        # Component 6: Leadership Scope (max 5)
        lead_score, lead_note = self._score_leadership(jd_text)
        breakdown["leadership"] = {"score": lead_score, "note": lead_note}

        # Component 7: Geography (max 5)
        geo_score, geo_note = self._score_geography(location)
        breakdown["geography"] = {"score": geo_score, "note": geo_note}

        total = (
            title_score + industry_score + comp_score +
            hcp_score + onco_score + lead_score + geo_score
        )
        total = min(100, max(0, total))

        # AI boost: optional +0–5 for exceptional strategic fit
        if self.use_ai_boost and raw_jd and total >= 60:
            ai_boost = self._ai_score_boost(title, company, raw_jd)
            breakdown["ai_boost"] = ai_boost
            total = min(100, total + ai_boost)

        breakdown["total"] = total
        log.info(
            "Score for %s @ %s: %d/100 (title=%d ind=%d comp=%d hcp=%d onco=%d geo=%d)",
            title, company, total,
            title_score, industry_score, comp_score, hcp_score, onco_score, geo_score,
        )
        return total, breakdown

    # ── Scoring Components ────────────────────────────────────────────────────

    def _score_title(self, title: str) -> Tuple[int, str]:
        t = normalize_title(title)
        best_score = 0
        best_match = ""

        for keyword, pts in config.TITLE_SENIORITY_SCORES.items():
            if keyword in t:
                if pts > best_score:
                    best_score = pts
                    best_match = keyword

        # Director-level needs compensation to be high — handled in comp scoring
        # Deduct if title implies too junior
        if best_match == "director" and "executive director" not in t:
            best_score = 12  # Lower base — comp score will pull it up or down

        # Bonus: title explicitly matches target list
        for target in config.TARGET_TITLES:
            if normalize_title(target) in t or t in normalize_title(target):
                best_score = min(25, best_score + 3)
                break

        return min(25, best_score), f"Matched: '{best_match}'" if best_match else "No seniority keyword"

    def _score_industry(self, jd_text: str) -> Tuple[int, str]:
        hits = []
        for kw in config.TARGET_INDUSTRIES:
            if kw in jd_text:
                hits.append(kw)

        score = 0
        if hits:
            # Linear: 1 hit = 8 pts, 2 = 14, 3+ = 20
            score = min(20, 8 + (len(hits) - 1) * 4)

        return score, f"Industry hits: {hits[:4]}" if hits else "No industry match"

    def _score_compensation(self, title: str, company: str, raw_jd: str) -> Tuple[Optional[int], int, str]:
        # Try to extract explicit salary from JD
        explicit = extract_salary_from_text(raw_jd)

        # Determine company tier for estimate
        tier = self._company_tier(company)
        estimated = estimate_salary_from_title(title, tier)

        comp = explicit if explicit else estimated
        source_note = "explicit" if explicit else "estimated"

        if comp >= 250_000:
            return comp, 20, f"{format_currency(comp)} ({source_note}) — exceeds target"
        elif comp >= 220_000:
            return comp, 17, f"{format_currency(comp)} ({source_note}) — meets target"
        elif comp >= 200_000:
            return comp, 14, f"{format_currency(comp)} ({source_note}) — at floor"
        elif comp >= config.SALARY_FLOOR:
            return comp, 8, f"{format_currency(comp)} ({source_note}) — below target, above floor"
        else:
            return comp, 0, f"{format_currency(comp)} ({source_note}) — below salary floor"

    def _score_hcp_omnichannel(self, jd_text: str) -> Tuple[int, str]:
        hcp_hits = [kw for kw in config.HCP_KEYWORDS if kw in jd_text]
        omni_hits = [kw for kw in config.OMNICHANNEL_KEYWORDS if kw in jd_text]

        hcp_score = min(8, len(hcp_hits) * 2)
        omni_score = min(7, len(omni_hits) * 2)
        total = min(15, hcp_score + omni_score)

        return total, f"HCP: {hcp_hits[:3]}, Omnichannel: {omni_hits[:3]}"

    def _score_oncology(self, jd_text: str) -> Tuple[int, str]:
        hits = [kw for kw in config.ONCOLOGY_KEYWORDS if kw in jd_text]
        score = min(10, len(hits) * 3)
        return score, f"Oncology hits: {hits[:3]}" if hits else "No oncology keywords"

    def _score_leadership(self, jd_text: str) -> Tuple[int, str]:
        hits = [kw for kw in config.LEADERSHIP_KEYWORDS if kw in jd_text]
        score = min(5, len(hits) * 1)
        return score, f"Leadership: {hits[:3]}"

    def _score_geography(self, location: str) -> Tuple[int, str]:
        loc = location.lower()
        if any(t.lower() in loc for t in ["new york", "nyc", "manhattan", "brooklyn", "new jersey", "nj"]):
            return 5, "NYC metro preferred location"
        if any(t.lower() in loc for t in ["remote", "hybrid", "work from home", "wfh"]):
            return 4, "Remote/hybrid acceptable"
        if any(t.lower() in loc for t in ["connecticut", "ct", "boston", "philadelphia", "dc", "washington"]):
            return 3, "Northeast region"
        if location.lower() in ["", "see posting", "unknown"]:
            return 3, "Location not specified"
        return 2, f"Non-preferred: {location}"

    def _ai_score_boost(self, title: str, company: str, jd_text: str) -> int:
        """
        Use Claude to evaluate strategic fit and return a 0–5 bonus.
        Only called for roles already scoring >= 60 to conserve API calls.
        """
        prompt = f"""You are evaluating a job opportunity for Michelle Perkins, a 15+ year healthcare/pharma media executive.

Role: {title} at {company}
Job description excerpt: {jd_text[:1500]}

Michelle's background:
- SVP-track media strategist, healthcare/pharma focus
- Oncology and specialty pharma portfolio leadership
- Omnichannel HCP engagement (programmatic, endemic, EHR targeting, NPI targeting)
- $40M+ media investment oversight
- Automation frameworks for ad operations
- Cross-functional executive leadership

Rate the STRATEGIC FIT of this role for Michelle on a scale of 0–5:
0 = Poor fit despite passing filters
1–2 = Moderate fit, some gaps
3–4 = Strong fit, aligns with experience
5 = Exceptional fit, near-perfect match

Respond with ONLY a single integer (0, 1, 2, 3, 4, or 5). Nothing else."""

        try:
            response = self.ai_client.messages.create(
                model=config.AI_MODEL_FAST,
                max_tokens=5,
                messages=[{"role": "user", "content": prompt}],
            )
            score_text = response.content[0].text.strip()
            boost = int(re.search(r"\d", score_text).group())
            return min(5, max(0, boost))
        except Exception as e:
            log.debug("AI boost scoring failed: %s", e)
            return 0

    @staticmethod
    def _company_tier(company: str) -> str:
        """Classify company size tier for salary estimation."""
        large_pharma = [
            "pfizer", "merck", "johnson & johnson", "j&j", "abbvie", "bristol",
            "eli lilly", "astrazeneca", "novartis", "roche", "sanofi", "gsk",
            "glaxosmithkline", "amgen", "gilead", "biogen", "regeneron", "moderna",
            "bayer", "takeda", "boehringer",
        ]
        c = company.lower()
        if any(lp in c for lp in large_pharma):
            return "large"
        small_indicators = ["klick", "real chemistry", "prosciento", "doceree", "deepintent"]
        if any(s in c for s in small_indicators):
            return "small"
        return "mid"


def score_jobs(raw_jobs: list, use_ai_boost: bool = True) -> list:
    """
    Convenience function. Takes list of raw job dicts, returns scored + filtered list.
    Only jobs with score >= config.SCORE_MINIMUM are returned, sorted descending.
    """
    scorer = JobScorer(use_ai_boost=use_ai_boost)
    results = []

    for job in raw_jobs:
        try:
            score, breakdown = scorer.score(job)
            if score < config.SCORE_MINIMUM:
                log.debug(
                    "Filtered (score %d < %d): %s @ %s",
                    score, config.SCORE_MINIMUM, job.get("title"), job.get("company"),
                )
                continue
            job["score"] = score
            job["score_breakdown"] = breakdown
            # Extract compensation estimate from breakdown
            comp_data = breakdown.get("compensation", {})
            if "estimate" in comp_data and comp_data["estimate"]:
                job["compensation_estimate"] = comp_data["estimate"]
            results.append(job)
        except Exception as e:
            log.error("Scoring failed for %s @ %s: %s", job.get("title"), job.get("company"), e)

    results.sort(key=lambda j: j.get("score", 0), reverse=True)
    log.info("Scoring complete: %d/%d roles qualify (score >= %d)", len(results), len(raw_jobs), config.SCORE_MINIMUM)
    return results
