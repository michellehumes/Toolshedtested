"""
Smart follow-up and interview prep engine.
  - Detects applications stale > 7 days → generates follow-up email draft
  - Detects active interviews → generates tailored prep brief
"""

import logging
from datetime import date
from pathlib import Path
from typing import Dict, List, Optional

import anthropic

import config
from src import database as db
from src.utils import today_str, slugify_filename

log = logging.getLogger("job_os.followup")


class FollowUpEngine:

    def __init__(self):
        self._client: Optional[anthropic.Anthropic] = None

    @property
    def client(self) -> anthropic.Anthropic:
        if self._client is None:
            self._client = anthropic.Anthropic()
        return self._client

    # ── Public API ────────────────────────────────────────────────────────────

    def run(self) -> Dict:
        """
        Run the full follow-up cycle. Returns a summary dict.
        """
        results = {
            "follow_up_drafts": [],
            "interview_preps": [],
        }

        # Check stale applied roles (> 7 days, no response)
        stale = db.get_stale_applied_jobs(days=7)
        log.info("Stale applied jobs (>7 days): %d", len(stale))
        for job in stale:
            draft = self._generate_follow_up_email(job)
            if draft:
                db.save_follow_up(job["id"], "follow_up_email", draft)
                results["follow_up_drafts"].append({
                    "job_id": job["id"],
                    "company": job["company"],
                    "title": job["title"],
                    "draft": draft,
                })

        # Generate prep briefs for active interviews
        interviews = db.get_active_interviews()
        log.info("Active interview roles: %d", len(interviews))
        for job in interviews:
            prep = self._generate_interview_prep(job)
            if prep:
                db.save_follow_up(job["id"], "interview_prep", prep)
                # Also save to file
                self._save_prep_brief(job, prep)
                results["interview_preps"].append({
                    "job_id": job["id"],
                    "company": job["company"],
                    "title": job["title"],
                    "status": job["status"],
                })

        return results

    # ── Follow-up Email ───────────────────────────────────────────────────────

    def _generate_follow_up_email(self, job: Dict) -> str:
        company = job.get("company", "")
        title = job.get("title", "")
        contact = job.get("contact_name", "Hiring Team")
        applied_date = job.get("date_applied", "recently")

        prompt = f"""Write a concise, professional follow-up email for Michelle Perkins.

She applied for: {title} at {company}
Applied on: {applied_date}
Contact: {contact}

Requirements:
- 3–4 sentences maximum
- Subject line on first line: "Subject: [subject]"
- Tone: Confident and professional, not desperate or apologetic
- Reference the specific role
- Reiterate 1 compelling differentiator (15 years pharma media, oncology expertise, or automation track record)
- Clear call to action: request a brief call or status update
- No clichés. No "I hope you are doing well."
- Sign off as Michelle Perkins with email {config.CANDIDATE['email']}

Write the email now:"""

        try:
            response = self.client.messages.create(
                model=config.AI_MODEL_FAST,
                max_tokens=400,
                messages=[{"role": "user", "content": prompt}],
            )
            return response.content[0].text.strip()
        except Exception as e:
            log.error("Follow-up email generation failed for %s: %s", company, e)
            return self._fallback_follow_up(title, company, contact, applied_date)

    def _fallback_follow_up(self, title: str, company: str, contact: str, applied_date: str) -> str:
        return f"""Subject: Following Up — {title} Application | Michelle Perkins

{contact},

I wanted to follow up on my application for the {title} role at {company}, submitted on {applied_date}. Given my 15+ years leading pharmaceutical media strategy with a specific focus on oncology and HCP omnichannel programs, I remain very interested in this opportunity.

Would you be available for a 20-minute call this week to discuss fit? I'm happy to work around your schedule.

Michelle Perkins
{config.CANDIDATE['email']} | {config.CANDIDATE['phone']}"""

    # ── Interview Prep Brief ──────────────────────────────────────────────────

    def _generate_interview_prep(self, job: Dict) -> str:
        company = job.get("company", "")
        title = job.get("title", "")
        status = job.get("status", "")
        jd_text = job.get("raw_description", "")

        interview_round = {
            db.STATUS_RECRUITER_SCREEN: "initial recruiter screen",
            db.STATUS_INTERVIEW_R1: "first round interview with hiring manager",
            db.STATUS_INTERVIEW_R2: "second round panel interview",
            db.STATUS_FINAL_ROUND: "final round executive interview",
        }.get(status, "interview")

        prompt = f"""Create a focused interview prep brief for Michelle Perkins.

INTERVIEW: {interview_round.upper()} for {title} at {company}

JOB DESCRIPTION:
{jd_text[:2500] if jd_text else 'Not available'}

MICHELLE'S BACKGROUND:
- 15+ years pharmaceutical/healthcare media strategy
- Oncology and specialty pharma portfolio (DTC + HCP)
- Omnichannel orchestration, $40M+ investment oversight
- Automation frameworks, ad ops efficiency
- Team leadership (12+ direct/indirect), C-suite engagement

Generate a structured prep brief with these exact sections:

## COMPANY SNAPSHOT
3–4 bullet points on {company}: industry position, recent news, pipeline/products, strategic priorities.

## ROLE POSITIONING
How to position Michelle's background for THIS specific role. What to lead with.

## LIKELY QUESTIONS + POWER ANSWERS
5 questions they will likely ask for a {interview_round}, with Michelle's ideal answers (150–200 words each, specific to her background).

## MICHELLE'S POWER STORIES (STAR Format)
3 achievement stories from Michelle's background most relevant to this role:
- Situation/Task → Action → Result (quantified)

## QUESTIONS MICHELLE SHOULD ASK
5 sharp, strategic questions that demonstrate executive thinking.

## SALARY NEGOTIATION NOTES
Michelle's target: $200K–$280K+ depending on scope. Anchor strategy and walk-away point.

Be specific, practical, and executive-level throughout."""

        try:
            response = self.client.messages.create(
                model=config.AI_MODEL,
                max_tokens=3000,
                messages=[{"role": "user", "content": prompt}],
            )
            return response.content[0].text.strip()
        except Exception as e:
            log.error("Interview prep generation failed for %s: %s", company, e)
            return self._fallback_prep(title, company, interview_round)

    def _fallback_prep(self, title: str, company: str, interview_round: str) -> str:
        return f"""## INTERVIEW PREP: {title} at {company}
## {interview_round.upper()}

## POSITIONING
Lead with: 15 years healthcare media strategy → oncology focus → omnichannel execution → automation impact.

## KEY MESSAGES
1. Strategic breadth: DTC + HCP omnichannel across the full funnel
2. Investment leadership: $40M+ media oversight, disciplined optimization
3. Automation: built frameworks that eliminated 500+ hours of manual work
4. Oncology depth: specialist targeting, NPI segmentation, EHR integration
5. Executive presence: C-suite briefings, cross-functional leadership

## SALARY TARGET
Base: $200K minimum | Target: $240K–$260K | Exceptional scope: $280K+
Always negotiate total comp (base + bonus + equity where applicable).

## QUESTIONS TO ASK
1. What does success look like in this role at 90 days and 12 months?
2. What are the current gaps in the media strategy you're looking to fill?
3. How does media strategy connect to the broader brand/commercial leadership team?
4. What technology and data infrastructure is currently in place?
5. What is the biggest strategic challenge facing this team right now?"""

    def _save_prep_brief(self, job: Dict, prep_content: str):
        """Save interview prep brief to /exports/ folder."""
        config.EXPORTS_DIR.mkdir(parents=True, exist_ok=True)
        stem = slugify_filename(job.get("company", ""), job.get("title", ""))
        path = config.EXPORTS_DIR / f"{stem}_interview_prep_{today_str()}.md"
        path.write_text(prep_content, encoding="utf-8")
        log.info("Interview prep saved: %s", path)
