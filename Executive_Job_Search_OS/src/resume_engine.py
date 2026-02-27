"""
AI-powered resume tailoring engine.
Analyzes each job description, extracts required competencies, and generates
a tailored executive resume PDF using Michelle's background.
"""

import logging
import re
from datetime import date
from pathlib import Path
from typing import Dict, List, Optional

import anthropic
from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_JUSTIFY
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import inch
from reportlab.platypus import (
    HRFlowable, PageBreak, Paragraph, SimpleDocTemplate, Spacer, Table, TableStyle,
)
from reportlab.platypus.flowables import KeepTogether

import config
from src.utils import slugify_filename, today_str

log = logging.getLogger("job_os.resume")

# ── Design constants ──────────────────────────────────────────────────────────
NAVY = colors.HexColor("#0a1628")
GOLD = colors.HexColor("#c9a84c")
DARK_GRAY = colors.HexColor("#2c2c2c")
MID_GRAY = colors.HexColor("#555555")
LIGHT_GRAY = colors.HexColor("#f5f5f5")
WHITE = colors.white

# ── Michelle's Base Profile ───────────────────────────────────────────────────
MICHELLE_PROFILE = {
    "name": "MICHELLE PERKINS",
    "contact": {
        "email": config.CANDIDATE["email"],
        "phone": config.CANDIDATE["phone"],
        "location": config.CANDIDATE["location"],
        "linkedin": config.CANDIDATE["linkedin"],
    },
    "default_title": "SVP, Media Strategy | Healthcare & Pharma | Omnichannel | Oncology",
    "executive_summary_base": (
        "Senior healthcare media executive with 15+ years driving omnichannel strategy, "
        "HCP engagement, and investment optimization for oncology and specialty pharma portfolios. "
        "Proven track record building automation frameworks, managing $40M+ media investments, "
        "and leading cross-functional teams to deliver measurable brand outcomes."
    ),
    "core_competencies": [
        "Omnichannel Media Strategy",
        "HCP & DTC Engagement",
        "Oncology Portfolio Leadership",
        "Programmatic & Endemic Media",
        "Investment Strategy & Optimization",
        "Automation Framework Design",
        "Ad Operations & Trafficking",
        "Cross-Functional Executive Leadership",
        "Vendor & Partner Management",
        "Data-Driven Attribution",
        "Budget Oversight ($40M+)",
        "Regulatory-Compliant Communications",
    ],
    "experience": [
        {
            "title": "Director, Media Strategy",
            "company": "Leading Healthcare Media Agency",
            "dates": "2018 – Present",
            "bullets": [
                "Architected end-to-end omnichannel media strategy for $40M+ oncology portfolio spanning DTC and HCP channels across programmatic, paid search, social, endemic publishers, and CTV.",
                "Led HCP segmentation and targeting strategy using NPI targeting, EHR data integration, and specialty physician audience models across 6 oncology and rare disease brands.",
                "Implemented banner trafficking automation framework reducing production cycle time by 60% and eliminating 500+ hours of manual ad operations annually.",
                "Managed strategic relationships with 20+ media partners, DSPs, and data vendors; negotiated annual deals totaling $12M+ in value.",
                "Directed cross-functional teams of 12+ including media planners, analysts, digital strategists, and creative production partners.",
                "Developed competitive intelligence and market analysis frameworks informing annual brand positioning and investment prioritization for SVP and C-suite stakeholders.",
                "Designed automated reporting infrastructure integrating platform data from 12+ channels, reducing reporting cycle from 5 days to same-day.",
                "Led omnichannel HCP engagement strategy integrating speaker bureau, EHR-point-of-care, NPI-targeted digital, and medical congress activations.",
            ],
        },
        {
            "title": "Senior Media Strategist",
            "company": "Pharma-Focused Media Consultancy",
            "dates": "2014 – 2018",
            "bullets": [
                "Developed integrated media plans for 10+ pharmaceutical brands including oncology, immunology, and rare disease portfolios with budgets of $5M–$25M.",
                "Spearheaded programmatic media buying practice for HCP audiences; established relationships with DeepIntent, PulsePoint, and endemic publisher networks.",
                "Led quarterly business reviews with VP-level brand stakeholders; translated media performance data into strategic recommendations and budget reallocation.",
                "Built HCP audience segmentation model using NPI, specialty, prescribing behavior, and call plan alignment to optimize media delivery against high-value physician segments.",
                "Partnered with medical affairs, regulatory, and legal teams to develop compliant HCP communication frameworks and review processes.",
            ],
        },
        {
            "title": "Media Planner / Account Manager",
            "company": "Healthcare Advertising Agency",
            "dates": "2009 – 2014",
            "bullets": [
                "Managed media planning and buying across DTC and HCP channels for 6 pharma brand accounts totaling $15M in annual media spend.",
                "Executed banner trafficking, tag management, and ad operations across DSP and publisher-direct placements.",
                "Introduced process improvements to media billing and reconciliation workflows, reducing discrepancy rate by 40%.",
            ],
        },
    ],
    "education": [
        {
            "degree": "Bachelor of Science, Communications & Marketing",
            "school": "University (Confidential)",
            "year": "2009",
        }
    ],
}


class ResumeEngine:
    """Generates tailored executive resumes for each qualified job opportunity."""

    def __init__(self):
        self._client: Optional[anthropic.Anthropic] = None

    @property
    def client(self) -> anthropic.Anthropic:
        if self._client is None:
            self._client = anthropic.Anthropic()
        return self._client

    # ── Public API ────────────────────────────────────────────────────────────

    def generate(self, job: Dict) -> Optional[str]:
        """
        Generate a tailored resume PDF for the given job.
        Returns the absolute path string to the saved PDF, or None on failure.
        """
        company = job.get("company", "Company")
        title = job.get("title", "Role")
        jd_text = job.get("raw_description", "")

        log.info("Generating resume for: %s @ %s", title, company)

        # Step 1: AI analysis of JD
        tailored = self._tailor_content(title, company, jd_text)

        # Step 2: Build PDF
        filename = slugify_filename(company, title) + "_resume.pdf"
        output_path = config.RESUMES_DIR / filename
        config.RESUMES_DIR.mkdir(parents=True, exist_ok=True)

        self._build_pdf(output_path, tailored, job)
        log.info("Resume saved: %s", output_path)
        return str(output_path)

    # ── AI Tailoring ──────────────────────────────────────────────────────────

    def _tailor_content(self, title: str, company: str, jd_text: str) -> Dict:
        """
        Use Claude to:
        1. Extract key requirements from JD
        2. Tailor headline and summary
        3. Select + rewrite top bullet points
        """
        if not jd_text:
            return self._default_content(title, company)

        prompt = f"""You are an elite executive resume writer tailoring a resume for Michelle Perkins.

TARGET ROLE: {title} at {company}

JOB DESCRIPTION:
{jd_text[:4000]}

MICHELLE'S BACKGROUND:
- 15+ years healthcare/pharma media strategy
- Oncology and specialty pharma portfolio leadership (DTC + HCP)
- Omnichannel orchestration: programmatic, endemic, EHR/NPI targeting, CTV, paid search
- $40M+ media investment oversight and optimization
- Built automation frameworks saving 500+ hours/year in ad operations
- Led cross-functional teams of 12+ across planning, analytics, creative
- Managed 20+ vendor/partner relationships; $12M+ negotiated value
- Executive stakeholder engagement (SVP/C-suite briefings)
- Deep regulatory/compliance experience (MLR, OPDP)

TASK: Return a JSON object with these exact keys:
{{
  "headline": "Tailored 1-line positioning statement for this specific role (max 12 words)",
  "summary": "3-sentence executive summary tailored to this JD — strategic, confident, no fluff",
  "top_bullets": ["List of 6 best bullet points from Michelle's experience, rewritten to match this JD's language and priorities. Each bullet starts with a strong action verb, includes a measurable result where possible."],
  "key_competencies": ["List of 10 skills/competencies that best match this JD, drawn from Michelle's actual background"]
}}

Rules:
- Every statement must be truthful to Michelle's actual background
- Use language and keywords from the JD
- Emphasize what this specific employer cares about most
- Bullets must be achievement-oriented and quantified where possible
- No fluff, no clichés, no filler language

Return ONLY valid JSON. No preamble, no explanation."""

        try:
            response = self.client.messages.create(
                model=config.AI_MODEL,
                max_tokens=1800,
                messages=[{"role": "user", "content": prompt}],
            )
            raw = response.content[0].text.strip()
            # Extract JSON from response
            json_match = re.search(r"\{[\s\S]*\}", raw)
            if json_match:
                import json
                return json.loads(json_match.group())
        except Exception as e:
            log.error("AI tailoring failed: %s", e)

        return self._default_content(title, company)

    def _default_content(self, title: str, company: str) -> Dict:
        """Fallback if AI call fails — use base profile."""
        return {
            "headline": config.CANDIDATE["title_positioning"],
            "summary": MICHELLE_PROFILE["executive_summary_base"],
            "top_bullets": MICHELLE_PROFILE["experience"][0]["bullets"][:6],
            "key_competencies": MICHELLE_PROFILE["core_competencies"][:10],
        }

    # ── PDF Construction ──────────────────────────────────────────────────────

    def _build_pdf(self, output_path: Path, tailored: Dict, job: Dict):
        doc = SimpleDocTemplate(
            str(output_path),
            pagesize=letter,
            rightMargin=0.6 * inch,
            leftMargin=0.6 * inch,
            topMargin=0.5 * inch,
            bottomMargin=0.5 * inch,
        )
        story = []
        styles = self._build_styles()

        # ── Header block ──────────────────────────────────────────────────────
        story.append(self._build_header(tailored, styles))
        story.append(Spacer(1, 10))

        # ── Executive Summary ─────────────────────────────────────────────────
        story.append(self._section_title("EXECUTIVE SUMMARY", styles))
        story.append(Paragraph(tailored.get("summary", MICHELLE_PROFILE["executive_summary_base"]), styles["body"]))
        story.append(Spacer(1, 8))

        # ── Core Competencies ─────────────────────────────────────────────────
        story.append(self._section_title("CORE COMPETENCIES", styles))
        comps = tailored.get("key_competencies", MICHELLE_PROFILE["core_competencies"])
        story.append(self._build_competency_grid(comps, styles))
        story.append(Spacer(1, 8))

        # ── Professional Experience ───────────────────────────────────────────
        story.append(self._section_title("PROFESSIONAL EXPERIENCE", styles))
        story.append(self._build_experience(tailored, styles))
        story.append(Spacer(1, 6))

        # ── Education ─────────────────────────────────────────────────────────
        story.append(self._section_title("EDUCATION", styles))
        for edu in MICHELLE_PROFILE["education"]:
            story.append(Paragraph(
                f"<b>{edu['degree']}</b> | {edu['school']} | {edu['year']}",
                styles["body"]
            ))

        doc.build(story)

    def _build_header(self, tailored: Dict, styles: Dict) -> Table:
        contact = MICHELLE_PROFILE["contact"]
        headline = tailored.get("headline", MICHELLE_PROFILE["default_title"])

        name_para = Paragraph(MICHELLE_PROFILE["name"], styles["name"])
        headline_para = Paragraph(headline, styles["headline"])
        contact_line = (
            f"{contact['email']}  ·  {contact['phone']}  ·  "
            f"{contact['location']}  ·  {contact['linkedin']}"
        )
        contact_para = Paragraph(contact_line, styles["contact"])

        header_table = Table(
            [[name_para], [headline_para], [contact_para]],
            colWidths=[7.3 * inch],
        )
        header_table.setStyle(TableStyle([
            ("BACKGROUND", (0, 0), (-1, -1), NAVY),
            ("TOPPADDING", (0, 0), (0, 0), 14),
            ("BOTTOMPADDING", (0, -1), (0, -1), 14),
            ("LEFTPADDING", (0, 0), (-1, -1), 18),
            ("RIGHTPADDING", (0, 0), (-1, -1), 18),
            ("ALIGN", (0, 0), (-1, -1), "CENTER"),
        ]))
        return header_table

    def _section_title(self, text: str, styles: Dict):
        return KeepTogether([
            Paragraph(text, styles["section_header"]),
            HRFlowable(width="100%", thickness=1.5, color=GOLD, spaceAfter=4),
        ])

    def _build_competency_grid(self, competencies: List[str], styles: Dict) -> Table:
        """Render competencies as a 3-column grid."""
        cols = 3
        rows = []
        for i in range(0, len(competencies), cols):
            row = []
            for j in range(cols):
                idx = i + j
                if idx < len(competencies):
                    row.append(Paragraph(f"▸  {competencies[idx]}", styles["competency"]))
                else:
                    row.append(Paragraph("", styles["competency"]))
            rows.append(row)

        col_width = 7.3 * inch / cols
        t = Table(rows, colWidths=[col_width] * cols)
        t.setStyle(TableStyle([
            ("BACKGROUND", (0, 0), (-1, -1), LIGHT_GRAY),
            ("PADDING", (0, 0), (-1, -1), 5),
            ("ROWBACKGROUNDS", (0, 0), (-1, -1), [LIGHT_GRAY, WHITE]),
        ]))
        return t

    def _build_experience(self, tailored: Dict, styles: Dict):
        """Build the experience section, blending tailored bullets with full history."""
        from reportlab.platypus import ListFlowable, ListItem
        story = []
        exp = MICHELLE_PROFILE["experience"]
        tailored_bullets = tailored.get("top_bullets", [])

        for idx, role in enumerate(exp):
            # Header line
            header = f"<b>{role['title']}</b>   |   {role['company']}"
            story.append(Paragraph(header, styles["job_title"]))
            story.append(Paragraph(role["dates"], styles["dates"]))

            # Use tailored bullets for first/current role
            bullets = tailored_bullets if (idx == 0 and tailored_bullets) else role["bullets"]

            for bullet in bullets[:7]:  # Max 7 bullets per role
                story.append(Paragraph(f"• {bullet}", styles["bullet"]))
            story.append(Spacer(1, 6))

        from reportlab.platypus.flowables import KeepTogether as KT
        return KeepTogether(story)

    def _build_styles(self) -> Dict:
        styles = {}

        styles["name"] = ParagraphStyle(
            "name",
            fontName="Helvetica-Bold",
            fontSize=22,
            textColor=WHITE,
            alignment=TA_CENTER,
            spaceAfter=2,
            leading=26,
        )
        styles["headline"] = ParagraphStyle(
            "headline",
            fontName="Helvetica",
            fontSize=10,
            textColor=GOLD,
            alignment=TA_CENTER,
            spaceAfter=2,
            leading=14,
        )
        styles["contact"] = ParagraphStyle(
            "contact",
            fontName="Helvetica",
            fontSize=8.5,
            textColor=colors.HexColor("#cccccc"),
            alignment=TA_CENTER,
            spaceAfter=0,
            leading=12,
        )
        styles["section_header"] = ParagraphStyle(
            "section_header",
            fontName="Helvetica-Bold",
            fontSize=9.5,
            textColor=NAVY,
            spaceBefore=8,
            spaceAfter=2,
            letterSpacing=1.5,
        )
        styles["body"] = ParagraphStyle(
            "body",
            fontName="Helvetica",
            fontSize=9.5,
            textColor=DARK_GRAY,
            alignment=TA_JUSTIFY,
            leading=14,
            spaceAfter=4,
        )
        styles["competency"] = ParagraphStyle(
            "competency",
            fontName="Helvetica",
            fontSize=8.5,
            textColor=DARK_GRAY,
            leading=13,
        )
        styles["job_title"] = ParagraphStyle(
            "job_title",
            fontName="Helvetica-Bold",
            fontSize=10,
            textColor=NAVY,
            spaceBefore=4,
            spaceAfter=1,
        )
        styles["dates"] = ParagraphStyle(
            "dates",
            fontName="Helvetica-Oblique",
            fontSize=8.5,
            textColor=MID_GRAY,
            spaceAfter=4,
        )
        styles["bullet"] = ParagraphStyle(
            "bullet",
            fontName="Helvetica",
            fontSize=9,
            textColor=DARK_GRAY,
            leftIndent=10,
            leading=13.5,
            spaceAfter=2,
        )
        return styles
