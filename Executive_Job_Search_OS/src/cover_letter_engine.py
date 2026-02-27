"""
AI-powered cover letter and recruiter email engine.
Generates:
  1. Executive cover letter (1-page PDF) — formal, strategic, confident
  2. Concise recruiter outreach email (.txt) — punchy, no fluff
"""

import logging
import re
from pathlib import Path
from typing import Dict, Optional, Tuple

import anthropic
from reportlab.lib import colors
from reportlab.lib.enums import TA_JUSTIFY, TA_LEFT, TA_RIGHT
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import ParagraphStyle
from reportlab.lib.units import inch
from reportlab.platypus import Paragraph, SimpleDocTemplate, Spacer, Table, TableStyle, HRFlowable

import config
from src.utils import slugify_filename, today_str
from src.resume_engine import NAVY, GOLD, DARK_GRAY, MID_GRAY, WHITE, MICHELLE_PROFILE

log = logging.getLogger("job_os.cover_letter")


class CoverLetterEngine:
    """Generates executive cover letters and recruiter emails for each qualified role."""

    def __init__(self):
        self._client: Optional[anthropic.Anthropic] = None

    @property
    def client(self) -> anthropic.Anthropic:
        if self._client is None:
            self._client = anthropic.Anthropic()
        return self._client

    # ── Public API ────────────────────────────────────────────────────────────

    def generate(self, job: Dict) -> Tuple[Optional[str], Optional[str]]:
        """
        Generate cover letter PDF and recruiter email .txt for a job.
        Returns (cover_letter_pdf_path, email_txt_path).
        """
        company = job.get("company", "Company")
        title = job.get("title", "Role")
        jd_text = job.get("raw_description", "")
        contact_name = job.get("contact_name", "Hiring Manager")

        log.info("Generating cover letter for: %s @ %s", title, company)

        letter_text, email_text = self._generate_content(title, company, jd_text, contact_name)

        stem = slugify_filename(company, title)
        config.COVER_LETTERS_DIR.mkdir(parents=True, exist_ok=True)

        # Save cover letter PDF
        pdf_path = config.COVER_LETTERS_DIR / f"{stem}_cover_letter.pdf"
        self._build_cover_letter_pdf(pdf_path, letter_text, job)

        # Save recruiter email .txt
        email_path = config.COVER_LETTERS_DIR / f"{stem}_email.txt"
        email_path.write_text(email_text, encoding="utf-8")

        log.info("Cover letter → %s", pdf_path)
        log.info("Recruiter email → %s", email_path)
        return str(pdf_path), str(email_path)

    # ── AI Generation ─────────────────────────────────────────────────────────

    def _generate_content(
        self, title: str, company: str, jd_text: str, contact_name: str
    ) -> Tuple[str, str]:
        """Call Claude to generate both the cover letter and email."""

        prompt = f"""You are an elite executive career coach writing on behalf of Michelle Perkins.

TARGET ROLE: {title} at {company}
HIRING CONTACT: {contact_name}

JOB DESCRIPTION:
{jd_text[:3500]}

MICHELLE'S PROFILE:
- 15+ years healthcare/pharma media strategy, SVP-track executive
- Oncology and specialty pharma portfolio leadership (DTC + HCP channels)
- Omnichannel orchestration: programmatic, endemic, EHR/NPI targeting, CTV, paid search
- $40M+ media investment oversight; managed $12M+ in annual vendor negotiations
- Implemented automation frameworks eliminating 500+ hours of annual manual ad ops
- Cross-functional executive leadership — teams of 12+; C-suite stakeholder engagement
- Deep regulatory/MLR compliance experience; speaker bureau and medical congress integration
- Location: New York, NY

GENERATE TWO OUTPUTS separated by the exact delimiter "===EMAIL_START===":

OUTPUT 1: Executive cover letter body (no letterhead, I'll add it separately)
- 3–4 tight paragraphs, maximum 1 page when typeset
- Paragraph 1: Hook — connect Michelle's specific expertise to this company's strategic need
- Paragraph 2: Proof — 2-3 specific achievements directly relevant to this JD
- Paragraph 3: Why this company specifically — show genuine knowledge/interest
- Paragraph 4: Close — confident, direct, no desperation language
- Tone: Strategic, executive, authoritative. Never "I am excited to apply." Never "I would be honored."
- Use first person but keep it achievement-forward, not humble

===EMAIL_START===

OUTPUT 2: Concise recruiter outreach email
- Subject line on first line as "Subject: [subject here]"
- 4–6 sentences maximum
- Lead with the most compelling differentiator for this specific role
- Include a specific question or call to action
- Tone: Direct, confident, peer-to-peer (SVP talking to a recruiter, not begging)
- No generic openers. No "I hope this email finds you well."

Generate now:"""

        try:
            response = self.client.messages.create(
                model=config.AI_MODEL,
                max_tokens=2000,
                messages=[{"role": "user", "content": prompt}],
            )
            full_text = response.content[0].text.strip()

            if "===EMAIL_START===" in full_text:
                parts = full_text.split("===EMAIL_START===", 1)
                letter_body = parts[0].strip()
                email_body = parts[1].strip()
            else:
                letter_body = full_text
                email_body = self._fallback_email(title, company, contact_name)

            return letter_body, email_body

        except Exception as e:
            log.error("Cover letter AI generation failed: %s", e)
            return self._fallback_letter(title, company), self._fallback_email(title, company, contact_name)

    def _fallback_letter(self, title: str, company: str) -> str:
        return f"""I am writing to express my strong interest in the {title} role at {company}.

With 15+ years of experience leading healthcare and pharmaceutical media strategy, I bring a track record of building omnichannel HCP engagement programs, managing $40M+ in media investment, and implementing automation frameworks that drive measurable efficiency and brand performance. My oncology portfolio leadership and deep expertise in programmatic, endemic, and NPI-targeted media position me to contribute immediately and strategically.

{company}'s commitment to innovation in healthcare aligns directly with my professional focus. I would welcome the opportunity to discuss how my background in omnichannel orchestration and executive media leadership can advance your strategic objectives.

I look forward to connecting.

Michelle Perkins"""

    def _fallback_email(self, title: str, company: str, contact: str) -> str:
        return f"""Subject: {title} — Michelle Perkins | 15 Years Healthcare Media Strategy

{contact},

I'm a healthcare media executive with 15+ years leading omnichannel strategy for pharma and oncology brands — currently targeting my next SVP-level opportunity. My background includes $40M+ in media investment oversight, HCP omnichannel orchestration, and automation frameworks that have significantly improved operational efficiency.

The {title} role at {company} aligns directly with my expertise. Would you have 20 minutes this week to connect?

Best,
Michelle Perkins
{config.CANDIDATE['email']} | {config.CANDIDATE['phone']}"""

    # ── PDF Construction ──────────────────────────────────────────────────────

    def _build_cover_letter_pdf(self, output_path: Path, letter_body: str, job: Dict):
        doc = SimpleDocTemplate(
            str(output_path),
            pagesize=letter,
            rightMargin=1.0 * inch,
            leftMargin=1.0 * inch,
            topMargin=0.75 * inch,
            bottomMargin=0.75 * inch,
        )
        story = []
        styles = self._build_styles()
        contact = MICHELLE_PROFILE["contact"]

        # ── Letterhead ────────────────────────────────────────────────────────
        name_para = Paragraph(MICHELLE_PROFILE["name"], styles["name"])
        contact_para = Paragraph(
            f"{contact['email']}  ·  {contact['phone']}  ·  {contact['location']}",
            styles["contact"]
        )
        letterhead = Table(
            [[name_para], [contact_para]],
            colWidths=[6.5 * inch],
        )
        letterhead.setStyle(TableStyle([
            ("BACKGROUND", (0, 0), (-1, -1), NAVY),
            ("TOPPADDING", (0, 0), (0, 0), 12),
            ("BOTTOMPADDING", (0, -1), (0, -1), 12),
            ("LEFTPADDING", (0, 0), (-1, -1), 16),
            ("RIGHTPADDING", (0, 0), (-1, -1), 16),
            ("ALIGN", (0, 0), (-1, -1), "CENTER"),
        ]))
        story.append(letterhead)
        story.append(HRFlowable(width="100%", thickness=2, color=GOLD, spaceBefore=0, spaceAfter=14))

        # ── Date and Addressee ────────────────────────────────────────────────
        from datetime import date as dt
        story.append(Paragraph(dt.today().strftime("%B %d, %Y"), styles["date"]))
        story.append(Spacer(1, 8))

        contact_name = job.get("contact_name", "Hiring Manager")
        company = job.get("company", "")
        title = job.get("title", "")

        story.append(Paragraph(contact_name, styles["addressee"]))
        story.append(Paragraph(company, styles["addressee"]))
        story.append(Spacer(1, 8))

        story.append(Paragraph(f"Re: {title}", styles["re_line"]))
        story.append(Spacer(1, 12))

        # ── Letter Body ───────────────────────────────────────────────────────
        # Split on double newlines to get paragraphs
        paragraphs = [p.strip() for p in letter_body.split("\n\n") if p.strip()]
        for i, para_text in enumerate(paragraphs):
            # Handle single-line breaks within paragraphs
            para_text = para_text.replace("\n", " ")
            story.append(Paragraph(para_text, styles["body"]))
            if i < len(paragraphs) - 1:
                story.append(Spacer(1, 10))

        # ── Signature ─────────────────────────────────────────────────────────
        story.append(Spacer(1, 20))
        story.append(Paragraph("Sincerely,", styles["body"]))
        story.append(Spacer(1, 24))
        story.append(Paragraph("<b>Michelle Perkins</b>", styles["body"]))
        story.append(Paragraph(contact["email"], styles["contact_footer"]))
        story.append(Paragraph(contact["phone"], styles["contact_footer"]))

        doc.build(story)

    def _build_styles(self) -> Dict:
        styles = {}
        styles["name"] = ParagraphStyle(
            "cl_name",
            fontName="Helvetica-Bold",
            fontSize=18,
            textColor=WHITE,
            alignment=1,  # CENTER
            leading=22,
        )
        styles["contact"] = ParagraphStyle(
            "cl_contact",
            fontName="Helvetica",
            fontSize=8.5,
            textColor=colors.HexColor("#cccccc"),
            alignment=1,
        )
        styles["date"] = ParagraphStyle(
            "cl_date",
            fontName="Helvetica",
            fontSize=9.5,
            textColor=MID_GRAY,
            alignment=TA_RIGHT,
        )
        styles["addressee"] = ParagraphStyle(
            "cl_addressee",
            fontName="Helvetica",
            fontSize=10,
            textColor=DARK_GRAY,
            leading=14,
        )
        styles["re_line"] = ParagraphStyle(
            "cl_re",
            fontName="Helvetica-Bold",
            fontSize=10,
            textColor=NAVY,
        )
        styles["body"] = ParagraphStyle(
            "cl_body",
            fontName="Helvetica",
            fontSize=10.5,
            textColor=DARK_GRAY,
            alignment=TA_JUSTIFY,
            leading=16,
        )
        styles["contact_footer"] = ParagraphStyle(
            "cl_footer",
            fontName="Helvetica",
            fontSize=9,
            textColor=MID_GRAY,
            leading=13,
        )
        return styles
