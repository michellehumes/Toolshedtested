"""
Dashboard data generator.
Queries the SQLite database, computes KPIs, and writes dashboard/data.json
so the HTML dashboard can read live data without a server.
"""

import json
import logging
from datetime import datetime
from pathlib import Path
from typing import Dict, List

import config
from src import database as db
from src.utils import format_currency

log = logging.getLogger("job_os.dashboard")

# Pipeline order for display
PIPELINE_ORDER = [
    db.STATUS_IDENTIFIED,
    db.STATUS_RESUME_GENERATED,
    db.STATUS_APPLIED,
    db.STATUS_RECRUITER_SCREEN,
    db.STATUS_INTERVIEW_R1,
    db.STATUS_INTERVIEW_R2,
    db.STATUS_FINAL_ROUND,
    db.STATUS_OFFER,
    db.STATUS_REJECTED,
    db.STATUS_CLOSED,
]


class DashboardGenerator:
    """Generates the dashboard data.json payload from the SQLite database."""

    def run(self) -> Path:
        """Build data.json and return its path."""
        log.info("Generating dashboard data...")

        kpis = db.get_dashboard_kpis()
        all_jobs = db.get_all_jobs()
        pipeline_counts = self._pipeline_counts(all_jobs)
        recent_activity = self._recent_activity(all_jobs)
        score_distribution = self._score_distribution(all_jobs)
        source_breakdown = self._source_breakdown(all_jobs)

        payload = {
            "kpis": kpis,
            "pipeline": pipeline_counts,
            "jobs": self._format_jobs_for_dashboard(all_jobs),
            "recent_activity": recent_activity,
            "score_distribution": score_distribution,
            "source_breakdown": source_breakdown,
            "all_statuses": db.ALL_STATUSES,
            "generated_at": datetime.now().isoformat(),
        }

        config.DASHBOARD_DIR.mkdir(parents=True, exist_ok=True)
        out = config.DASHBOARD_DATA_PATH
        out.write_text(json.dumps(payload, indent=2, default=str), encoding="utf-8")
        log.info("Dashboard data written to %s (%d jobs)", out, len(all_jobs))
        return out

    def _pipeline_counts(self, jobs: List[Dict]) -> List[Dict]:
        counts = {s: 0 for s in PIPELINE_ORDER}
        for job in jobs:
            status = job.get("status", db.STATUS_IDENTIFIED)
            if status in counts:
                counts[status] += 1
        return [{"status": s, "count": counts[s]} for s in PIPELINE_ORDER]

    def _recent_activity(self, jobs: List[Dict], limit: int = 8) -> List[Dict]:
        """Most recently updated jobs."""
        sorted_jobs = sorted(
            jobs,
            key=lambda j: j.get("updated_at", ""),
            reverse=True,
        )
        out = []
        for job in sorted_jobs[:limit]:
            out.append({
                "company": job.get("company"),
                "title": job.get("title"),
                "status": job.get("status"),
                "updated": job.get("updated_at", "")[:10],
                "score": job.get("score", 0),
            })
        return out

    def _score_distribution(self, jobs: List[Dict]) -> Dict:
        """Bucket scores into ranges."""
        buckets = {"75-79": 0, "80-84": 0, "85-89": 0, "90-94": 0, "95-100": 0}
        for job in jobs:
            score = job.get("score", 0)
            if score >= 95:
                buckets["95-100"] += 1
            elif score >= 90:
                buckets["90-94"] += 1
            elif score >= 85:
                buckets["85-89"] += 1
            elif score >= 80:
                buckets["80-84"] += 1
            elif score >= 75:
                buckets["75-79"] += 1
        return buckets

    def _source_breakdown(self, jobs: List[Dict]) -> List[Dict]:
        counts: Dict[str, int] = {}
        for job in jobs:
            source = job.get("source", "Unknown")
            # Normalize company career pages
            if source.startswith("CareerPage:"):
                source = "Company Career Page"
            counts[source] = counts.get(source, 0) + 1
        return [{"source": k, "count": v} for k, v in sorted(counts.items(), key=lambda x: -x[1])]

    def _format_jobs_for_dashboard(self, jobs: List[Dict]) -> List[Dict]:
        """Clean and format job records for JSON serialization."""
        out = []
        for job in jobs:
            out.append({
                "id": job.get("id"),
                "company": job.get("company", ""),
                "title": job.get("title", ""),
                "source": job.get("source", ""),
                "location": job.get("location", ""),
                "compensation": format_currency(job.get("compensation_estimate")),
                "compensation_raw": job.get("compensation_estimate"),
                "link": job.get("link", ""),
                "date_found": job.get("date_found", ""),
                "score": job.get("score", 0),
                "status": job.get("status", db.STATUS_IDENTIFIED),
                "date_applied": job.get("date_applied", ""),
                "contact_name": job.get("contact_name", ""),
                "contact_email": job.get("contact_email", ""),
                "last_touchpoint": job.get("last_touchpoint", ""),
                "next_step": job.get("next_step", ""),
                "notes": job.get("notes", ""),
                "resume_path": job.get("resume_path", ""),
                "cover_letter_path": job.get("cover_letter_path", ""),
                "email_path": job.get("email_path", ""),
                "updated_at": job.get("updated_at", "")[:10],
            })
        return out
