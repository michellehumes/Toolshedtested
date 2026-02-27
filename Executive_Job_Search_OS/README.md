# Executive Job Search OS
### Michelle Perkins · SVP-Level Healthcare Media Strategy

A fully automated, AI-powered job acquisition engine that operates daily like a retained executive search team working on your behalf.

---

## What It Does

Every weekday at 7 AM Eastern, this system:

1. **Sources** fresh SVP/VP/Director-level roles from LinkedIn, Indeed RSS, BuiltIn, and 25+ target company career pages
2. **Scores** each role 1–100 based on seniority, industry alignment, compensation, HCP/omnichannel fit, and oncology relevance
3. **Filters** to only roles scoring ≥ 75 (your exact target criteria)
4. **Generates** a tailored resume PDF and executive cover letter for each qualifying role using Claude AI
5. **Tracks** full lifecycle from Identified → Applied → Interview → Offer
6. **Follows up** on stale applications and generates interview prep briefs
7. **Updates** an executive HTML dashboard with live KPIs and pipeline view

---

## Quick Start

### 1. Run Setup (one-time)
```bash
cd Executive_Job_Search_OS
bash setup.sh
```

### 2. Add API Key
```bash
# Edit .env
ANTHROPIC_API_KEY=sk-ant-your-actual-key-here
```
Get your key at [console.anthropic.com](https://console.anthropic.com)

### 3. Personalize Your Profile
Edit `config.py` → `CANDIDATE` dict (email, phone, LinkedIn URL).

Edit `src/resume_engine.py` → `MICHELLE_PROFILE` to verify experience bullets match your actual background precisely.

### 4. Test Run (no AI, no PDFs)
```bash
source .venv/bin/activate
python main.py --dry-run
```

### 5. Full Run
```bash
python main.py
```

### 6. Open Dashboard
```bash
open dashboard/index.html
```

---

## Directory Structure

```
Executive_Job_Search_OS/
├── main.py                    # Daily orchestrator
├── scheduler.py               # APScheduler daemon
├── config.py                  # All configuration
├── requirements.txt
├── setup.sh
├── .env                       # API keys (never commit)
├── data/
│   └── jobs.db               # SQLite tracker database
├── resumes/                   # Generated PDFs: {Company}_{Title}_resume.pdf
├── cover_letters/             # Cover letter PDFs + email .txt files
├── job_descriptions/          # Raw JD text archive
├── logs/                      # Daily run logs and reports
├── exports/                   # Interview prep briefs
├── dashboard/
│   ├── index.html            # Executive HTML dashboard
│   └── data.json             # Live data (updated each run)
└── src/
    ├── database.py            # SQLite layer + full lifecycle tracking
    ├── sourcer.py             # LinkedIn · Indeed RSS · BuiltIn · Career pages
    ├── scorer.py              # 100-point scoring model
    ├── resume_engine.py       # AI tailored resume + PDF generator
    ├── cover_letter_engine.py # AI executive cover letter + recruiter email
    ├── follow_up_engine.py    # Stale app follow-ups + interview prep
    ├── dashboard_generator.py # Dashboard data.json builder
    └── utils.py               # Shared utilities
```

---

## Configuration Reference

### Target Role Filters (`config.py`)
| Setting | Default | Description |
|---|---|---|
| `SCORE_MINIMUM` | 75 | Minimum score to process a role |
| `SALARY_FLOOR` | $180K | Absolute salary minimum |
| `SALARY_TARGET` | $250K | Target compensation |
| `JOBS_PER_DAY_TARGET` | 3 | Max new roles processed per day |
| `EXCLUDED_COMPANIES` | Omnicom, IPG, CMI | Auto-excluded organizations |

### Scoring Weights
| Component | Max Points | What It Rewards |
|---|---|---|
| Title Seniority | 25 | SVP=24, VP=22, Dir=15 |
| Industry Alignment | 20 | Pharma/HCP/Healthtech keywords |
| Compensation | 20 | $250K+=20, $200K=14, below floor=0 |
| HCP/Omnichannel | 15 | NPI targeting, programmatic, endemic |
| Oncology | 10 | Oncology portfolio keywords |
| Leadership Scope | 5 | Budget, P&L, team, C-suite |
| Geography | 5 | NYC=5, Remote=4, Northeast=3 |

---

## Cron Setup (Recommended for Daily Automation)

Add this to your crontab (`crontab -e`):

```cron
# Executive Job Search OS — runs every weekday at 7:00 AM
0 7 * * 1-5 /path/to/Executive_Job_Search_OS/.venv/bin/python /path/to/Executive_Job_Search_OS/main.py >> /path/to/Executive_Job_Search_OS/logs/cron.log 2>&1
```

Replace `/path/to/Executive_Job_Search_OS/` with the actual absolute path.

To verify it's set: `crontab -l`

### Alternative: APScheduler Daemon
```bash
# Start in background
source .venv/bin/activate
nohup python scheduler.py > logs/scheduler.log 2>&1 &

# Check it's running
ps aux | grep scheduler.py
```

---

## Manual Commands

```bash
# Source and score only (no PDF generation)
python main.py --dry-run

# Full pipeline (source → score → insert → generate docs → dashboard)
python main.py

# Process up to 5 new jobs (override default of 3)
python main.py --max-jobs 5

# Rebuild dashboard data only (no sourcing)
python main.py --dashboard-only

# Initialize database only
python main.py --init-only
```

---

## Job Status Lifecycle

```
Identified → Resume Generated → Applied → Recruiter Screen
           → Interview Round 1 → Interview Round 2 → Final Round
           → Offer  OR  Rejected  OR  Closed
```

Update job status by clicking **Edit** in the dashboard.
To persist edits to the database, update via CLI or modify `src/database.py` → `update_job_status()`.

---

## What Gets Generated Per Role

**Resume:** `resumes/AstraZeneca_SVP_Media_Strategy_resume.pdf`
- AI-tailored headline and executive summary
- Top 6 bullets selected and rewritten to match this JD's language
- Core competencies curated for this specific role
- Professional two-color layout (navy + gold)

**Cover Letter:** `cover_letters/AstraZeneca_SVP_Media_Strategy_cover_letter.pdf`
- 4-paragraph executive letter tailored to company and role
- Strategic, confident tone — no desperation language

**Recruiter Email:** `cover_letters/AstraZeneca_SVP_Media_Strategy_email.txt`
- 4–6 sentence outreach email with subject line
- Peer-to-peer executive tone

**Interview Prep:** `exports/AstraZeneca_SVP_Media_Strategy_interview_prep_[date].md`
- Company snapshot, positioning, 5 likely questions + power answers
- 3 STAR-format achievement stories
- 5 sharp questions to ask
- Salary negotiation strategy

---

## Follow-Up Engine

Automatically triggered on each daily run:
- **Stale applications** (Applied > 7 days): generates a follow-up email draft
- **Active interviews**: regenerates interview prep brief before each round

Follow-up drafts are saved to the `follow_ups` table in `data/jobs.db`.

---

## Troubleshooting

| Issue | Fix |
|---|---|
| No jobs sourced | Verify network access. Run `--dry-run` and check logs/ |
| API error | Verify `ANTHROPIC_API_KEY` in .env |
| PDF generation fails | Run `pip install reportlab` and verify .env API key |
| LinkedIn returns no results | LinkedIn scraping can be rate-limited. Indeed RSS is the most reliable source. |
| Dashboard shows "No data" | Run `python main.py` first to populate data.json |

---

## Privacy

- All data stays local (SQLite + flat files on your machine)
- No data is sent to any third party except the Anthropic API (for resume/cover letter generation)
- The Anthropic API call includes only: the job description and Michelle's background description (no PII beyond what you configure)
- `.env` file with API key should never be committed to version control

---

*Built for Michelle Perkins · SVP-track Healthcare Media Executive · NYC*
