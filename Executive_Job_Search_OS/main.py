"""
Executive Job Search OS — Daily Orchestrator
Runs the full pipeline: source → score → insert → generate documents → follow-ups → dashboard.
"""

import argparse
import logging
import os
import sys
from datetime import date, datetime
from pathlib import Path
from typing import List, Dict

from dotenv import load_dotenv

# Load environment before importing anything that uses env vars
load_dotenv(Path(__file__).parent / ".env")

import config
from src import database as db
from src.utils import ensure_dirs, setup_logging, today_str
from src.sourcer import JobAggregator
from src.scorer import score_jobs
from src.resume_engine import ResumeEngine
from src.cover_letter_engine import CoverLetterEngine
from src.follow_up_engine import FollowUpEngine
from src.dashboard_generator import DashboardGenerator


class DailyRunner:
    """
    Executes the full daily job search pipeline autonomously.
    Safe to run multiple times — duplicate detection prevents re-processing.
    """

    def __init__(self, dry_run: bool = False, max_new_jobs: int = None):
        self.dry_run = dry_run
        self.max_new_jobs = max_new_jobs or config.JOBS_PER_DAY_TARGET
        self.log = logging.getLogger("job_os.runner")
        self.resume_engine = ResumeEngine()
        self.cover_letter_engine = CoverLetterEngine()
        self.follow_up_engine = FollowUpEngine()
        self.dashboard_gen = DashboardGenerator()

    def run(self):
        self.log.info("=" * 65)
        self.log.info("EXECUTIVE JOB SEARCH OS — Daily Run: %s", today_str())
        self.log.info("=" * 65)

        if self.dry_run:
            self.log.info("DRY RUN MODE — No PDFs will be generated")

        start_time = datetime.now()
        stats = {
            "raw_found": 0,
            "qualified": 0,
            "new_inserted": 0,
            "documents_generated": 0,
            "follow_ups_drafted": 0,
            "interview_preps": 0,
        }

        try:
            # ── Step 1: Source fresh job listings ─────────────────────────────
            self.log.info("\n▶ STEP 1: Sourcing job listings...")
            aggregator = JobAggregator(fetch_full_jds=not self.dry_run)
            raw_jobs = aggregator.run()
            stats["raw_found"] = len(raw_jobs)
            self.log.info("Raw listings sourced: %d", stats["raw_found"])

            # ── Step 2: Score and filter ───────────────────────────────────────
            self.log.info("\n▶ STEP 2: Scoring and filtering...")
            qualified_jobs = score_jobs(raw_jobs, use_ai_boost=not self.dry_run)
            stats["qualified"] = len(qualified_jobs)
            self.log.info("Qualified roles (score ≥ %d): %d", config.SCORE_MINIMUM, stats["qualified"])

            # Limit to target per-day count
            jobs_to_process = qualified_jobs[: self.max_new_jobs]
            self.log.info("Processing top %d roles today", len(jobs_to_process))

            # ── Step 3: Insert into database ──────────────────────────────────
            self.log.info("\n▶ STEP 3: Inserting into tracker database...")
            new_job_ids = []
            new_jobs_data = []
            for job in jobs_to_process:
                job_id = db.insert_job(job)
                if job_id:
                    new_job_ids.append(job_id)
                    new_jobs_data.append((job_id, job))
                    stats["new_inserted"] += 1

            self.log.info("New jobs inserted: %d", stats["new_inserted"])

            # ── Step 4: Generate resume + cover letter for each new job ────────
            if not self.dry_run and new_jobs_data:
                self.log.info("\n▶ STEP 4: Generating tailored documents...")
                for job_id, job in new_jobs_data:
                    self._generate_documents(job_id, job, stats)
            elif self.dry_run:
                self.log.info("\n▶ STEP 4: [DRY RUN] Skipping document generation")

            # ── Step 5: Follow-up engine ──────────────────────────────────────
            self.log.info("\n▶ STEP 5: Running follow-up engine...")
            if not self.dry_run:
                followup_results = self.follow_up_engine.run()
                stats["follow_ups_drafted"] = len(followup_results.get("follow_up_drafts", []))
                stats["interview_preps"] = len(followup_results.get("interview_preps", []))
                if stats["follow_ups_drafted"]:
                    self.log.info("Follow-up drafts generated: %d", stats["follow_ups_drafted"])
                    for item in followup_results["follow_up_drafts"]:
                        self.log.info(
                            "  → Follow-up for %s @ %s (applied %s)",
                            item["title"], item["company"],
                            db.get_job(item["job_id"]).get("date_applied", "unknown"),
                        )
                if stats["interview_preps"]:
                    self.log.info("Interview prep briefs generated: %d", stats["interview_preps"])

            # ── Step 6: Update dashboard ──────────────────────────────────────
            self.log.info("\n▶ STEP 6: Updating dashboard...")
            data_path = self.dashboard_gen.run()
            self.log.info("Dashboard data → %s", data_path)

            # ── Step 7: Write daily summary report ────────────────────────────
            elapsed = (datetime.now() - start_time).seconds
            summary = self._write_daily_report(stats, jobs_to_process, elapsed)
            db.log_daily_run(
                stats["raw_found"], stats["qualified"],
                stats["new_inserted"], summary,
            )

            self.log.info("\n%s", summary)
            self.log.info("=" * 65)
            self.log.info("Run complete in %ds", elapsed)

        except Exception as e:
            self.log.exception("Fatal error in daily run: %s", e)
            sys.exit(1)

    def _generate_documents(self, job_id: int, job: Dict, stats: Dict):
        company = job.get("company", "")
        title = job.get("title", "")
        self.log.info("  Generating docs for: %s @ %s (score: %d)", title, company, job.get("score", 0))

        try:
            resume_path = self.resume_engine.generate(job)
        except Exception as e:
            self.log.error("Resume generation failed for %s @ %s: %s", title, company, e)
            resume_path = None

        try:
            cover_letter_path, email_path = self.cover_letter_engine.generate(job)
        except Exception as e:
            self.log.error("Cover letter generation failed for %s @ %s: %s", title, company, e)
            cover_letter_path, email_path = None, None

        if any([resume_path, cover_letter_path, email_path]):
            db.update_job_assets(job_id, resume_path, cover_letter_path, email_path)
            db.update_job_status(job_id, db.STATUS_RESUME_GENERATED)
            stats["documents_generated"] += 1

            self.log.info("    ✓ Resume: %s", Path(resume_path).name if resume_path else "FAILED")
            self.log.info("    ✓ Cover letter: %s", Path(cover_letter_path).name if cover_letter_path else "FAILED")
            self.log.info("    ✓ Email draft: %s", Path(email_path).name if email_path else "FAILED")

    def _write_daily_report(self, stats: Dict, processed_jobs: List[Dict], elapsed_sec: int) -> str:
        """Write a human-readable daily summary report to /logs/."""
        lines = [
            f"EXECUTIVE JOB SEARCH OS — DAILY REPORT",
            f"Date: {today_str()}",
            f"Run duration: {elapsed_sec}s",
            "=" * 50,
            f"Raw listings sourced:       {stats['raw_found']:>4}",
            f"Qualified (score ≥ {config.SCORE_MINIMUM}):      {stats['qualified']:>4}",
            f"New jobs inserted:          {stats['new_inserted']:>4}",
            f"Documents generated:        {stats['documents_generated']:>4}",
            f"Follow-up drafts:           {stats['follow_ups_drafted']:>4}",
            f"Interview preps:            {stats['interview_preps']:>4}",
            "",
            "TODAY'S TOP OPPORTUNITIES:",
            "-" * 50,
        ]

        for job in processed_jobs:
            comp = job.get("compensation_estimate")
            comp_str = f"${comp:,}" if comp else "Not disclosed"
            lines.append(
                f"  [{job.get('score', 0):>3}/100] {job.get('company', '')} — "
                f"{job.get('title', '')} | {comp_str} | {job.get('location', '')}"
            )

        if not processed_jobs:
            lines.append("  No new qualifying roles found today.")

        kpis = db.get_dashboard_kpis()
        lines += [
            "",
            "TRACKER TOTALS:",
            "-" * 50,
            f"  Total identified:   {kpis['total_identified']}",
            f"  Applications sent:  {kpis['applications_sent']}",
            f"  Active interviews:  {kpis['interviews_active']}",
            f"  Offers:             {kpis['offers']}",
            f"  Conversion rate:    {kpis['conversion_rate']}%",
            f"  Avg score:          {kpis['avg_score']}",
            f"  Avg salary target:  ${kpis['avg_salary']:,}" if kpis['avg_salary'] else "  Avg salary target:  N/A",
        ]

        report = "\n".join(lines)

        # Save to /logs/
        config.LOGS_DIR.mkdir(parents=True, exist_ok=True)
        report_path = config.LOGS_DIR / f"report_{today_str()}.txt"
        report_path.write_text(report, encoding="utf-8")
        self.log.info("Daily report → %s", report_path)
        return report


def main():
    parser = argparse.ArgumentParser(description="Executive Job Search OS")
    parser.add_argument("--dry-run", action="store_true", help="Source and score only — skip PDF generation")
    parser.add_argument("--max-jobs", type=int, default=None, help="Override max new jobs per run")
    parser.add_argument("--init-only", action="store_true", help="Only initialize database and exit")
    parser.add_argument("--dashboard-only", action="store_true", help="Regenerate dashboard data only")
    args = parser.parse_args()

    ensure_dirs()
    setup_logging("daily_run")
    db.init_db()

    if args.init_only:
        print("Database initialized. Ready to run.")
        return

    if args.dashboard_only:
        DashboardGenerator().run()
        print(f"Dashboard updated: {config.DASHBOARD_DATA_PATH}")
        return

    runner = DailyRunner(
        dry_run=args.dry_run,
        max_new_jobs=args.max_jobs,
    )
    runner.run()


if __name__ == "__main__":
    main()
