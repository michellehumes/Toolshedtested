"""
SQLite database layer — jobs tracker, deduplication, status management.
"""

import json
import logging
import sqlite3
from contextlib import contextmanager
from datetime import date, datetime, timedelta
from typing import Dict, List, Optional

import config
from src.utils import compute_hash

log = logging.getLogger("job_os.db")

# ── Status constants ──────────────────────────────────────────────────────────
STATUS_IDENTIFIED = "Identified"
STATUS_RESUME_GENERATED = "Resume Generated"
STATUS_APPLIED = "Applied"
STATUS_RECRUITER_SCREEN = "Recruiter Screen"
STATUS_INTERVIEW_R1 = "Interview Round 1"
STATUS_INTERVIEW_R2 = "Interview Round 2"
STATUS_FINAL_ROUND = "Final Round"
STATUS_OFFER = "Offer"
STATUS_REJECTED = "Rejected"
STATUS_CLOSED = "Closed"

ALL_STATUSES = [
    STATUS_IDENTIFIED, STATUS_RESUME_GENERATED, STATUS_APPLIED,
    STATUS_RECRUITER_SCREEN, STATUS_INTERVIEW_R1, STATUS_INTERVIEW_R2,
    STATUS_FINAL_ROUND, STATUS_OFFER, STATUS_REJECTED, STATUS_CLOSED,
]

SCHEMA_SQL = """
CREATE TABLE IF NOT EXISTS jobs (
    id                   INTEGER PRIMARY KEY AUTOINCREMENT,
    company              TEXT    NOT NULL,
    title                TEXT    NOT NULL,
    source               TEXT,
    location             TEXT,
    compensation_estimate INTEGER,
    link                 TEXT    UNIQUE,
    link_hash            TEXT    UNIQUE,
    description_hash     TEXT,
    date_found           TEXT    NOT NULL,
    score                INTEGER DEFAULT 0,
    status               TEXT    DEFAULT 'Identified',
    date_applied         TEXT,
    contact_name         TEXT,
    contact_email        TEXT,
    last_touchpoint      TEXT,
    next_step            TEXT,
    notes                TEXT,
    resume_path          TEXT,
    cover_letter_path    TEXT,
    email_path           TEXT,
    raw_description      TEXT,
    created_at           TEXT    NOT NULL,
    updated_at           TEXT    NOT NULL
);

CREATE TABLE IF NOT EXISTS follow_ups (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    job_id      INTEGER NOT NULL REFERENCES jobs(id),
    type        TEXT,       -- 'follow_up_email' | 'interview_prep'
    content     TEXT,
    created_at  TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS daily_logs (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    run_date    TEXT NOT NULL,
    jobs_found  INTEGER DEFAULT 0,
    jobs_scored INTEGER DEFAULT 0,
    jobs_queued INTEGER DEFAULT 0,
    summary     TEXT,
    created_at  TEXT NOT NULL
);
"""


@contextmanager
def get_conn():
    """Context manager yielding a sqlite3 connection with row_factory."""
    config.DATA_DIR.mkdir(parents=True, exist_ok=True)
    conn = sqlite3.connect(str(config.DB_PATH))
    conn.row_factory = sqlite3.Row
    conn.execute("PRAGMA journal_mode=WAL;")
    conn.execute("PRAGMA foreign_keys=ON;")
    try:
        yield conn
        conn.commit()
    except Exception:
        conn.rollback()
        raise
    finally:
        conn.close()


def init_db():
    """Create tables if they don't exist."""
    with get_conn() as conn:
        conn.executescript(SCHEMA_SQL)
    log.info("Database initialised at %s", config.DB_PATH)


# ── Deduplication ─────────────────────────────────────────────────────────────

def job_exists(link: str, description_text: str = "") -> bool:
    """Return True if this job is already in the database."""
    link_hash = compute_hash(link)
    desc_hash = compute_hash(description_text) if description_text else None

    with get_conn() as conn:
        row = conn.execute(
            "SELECT id FROM jobs WHERE link_hash = ? OR link = ?",
            (link_hash, link)
        ).fetchone()
        if row:
            return True
        if desc_hash:
            row = conn.execute(
                "SELECT id FROM jobs WHERE description_hash = ?",
                (desc_hash,)
            ).fetchone()
            if row:
                return True
    return False


def similar_job_exists(company: str, title: str) -> bool:
    """Fuzzy check — same company + very similar title already tracked."""
    c = company.lower().strip()
    t_words = set(title.lower().split())

    with get_conn() as conn:
        rows = conn.execute(
            "SELECT title FROM jobs WHERE LOWER(company) = ?", (c,)
        ).fetchall()
        for row in rows:
            existing_words = set(row["title"].lower().split())
            overlap = len(t_words & existing_words) / max(len(t_words), 1)
            if overlap >= 0.7:
                return True
    return False


# ── CRUD ──────────────────────────────────────────────────────────────────────

def insert_job(job: Dict) -> Optional[int]:
    """
    Insert a new job record. Returns the new row id, or None if duplicate.
    job dict keys: company, title, source, location, compensation_estimate,
                   link, score, raw_description, [optional fields]
    """
    link = job.get("link", "")
    raw = job.get("raw_description", "")

    if job_exists(link, raw):
        log.debug("Duplicate skipped: %s — %s", job.get("company"), job.get("title"))
        return None

    if similar_job_exists(job.get("company", ""), job.get("title", "")):
        log.debug("Similar job exists, skipping: %s — %s", job.get("company"), job.get("title"))
        return None

    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    link_hash = compute_hash(link)
    desc_hash = compute_hash(raw) if raw else ""

    with get_conn() as conn:
        try:
            cursor = conn.execute(
                """INSERT INTO jobs
                   (company, title, source, location, compensation_estimate,
                    link, link_hash, description_hash, date_found, score, status,
                    raw_description, created_at, updated_at)
                   VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)""",
                (
                    job.get("company", ""),
                    job.get("title", ""),
                    job.get("source", ""),
                    job.get("location", ""),
                    job.get("compensation_estimate"),
                    link,
                    link_hash,
                    desc_hash,
                    job.get("date_found", date.today().isoformat()),
                    job.get("score", 0),
                    STATUS_IDENTIFIED,
                    raw[:50000] if raw else "",
                    now,
                    now,
                ),
            )
            log.info("Inserted job #%d: %s — %s", cursor.lastrowid, job.get("company"), job.get("title"))
            return cursor.lastrowid
        except sqlite3.IntegrityError:
            log.debug("IntegrityError — duplicate link for: %s", link)
            return None


def update_job_status(job_id: int, status: str, notes: str = None):
    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    with get_conn() as conn:
        if notes:
            conn.execute(
                "UPDATE jobs SET status=?, notes=?, updated_at=? WHERE id=?",
                (status, notes, now, job_id)
            )
        else:
            conn.execute(
                "UPDATE jobs SET status=?, updated_at=? WHERE id=?",
                (status, now, job_id)
            )
    log.info("Job #%d status → %s", job_id, status)


def update_job_assets(job_id: int, resume_path: str = None,
                      cover_letter_path: str = None, email_path: str = None):
    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    updates, params = [], []
    if resume_path:
        updates.append("resume_path=?")
        params.append(resume_path)
    if cover_letter_path:
        updates.append("cover_letter_path=?")
        params.append(cover_letter_path)
    if email_path:
        updates.append("email_path=?")
        params.append(email_path)
    if not updates:
        return
    updates.append("updated_at=?")
    params.append(now)
    params.append(job_id)
    with get_conn() as conn:
        conn.execute(f"UPDATE jobs SET {', '.join(updates)} WHERE id=?", params)


def set_applied(job_id: int):
    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    with get_conn() as conn:
        conn.execute(
            "UPDATE jobs SET status=?, date_applied=?, updated_at=? WHERE id=?",
            (STATUS_APPLIED, date.today().isoformat(), now, job_id)
        )


def get_job(job_id: int) -> Optional[Dict]:
    with get_conn() as conn:
        row = conn.execute("SELECT * FROM jobs WHERE id=?", (job_id,)).fetchone()
        return dict(row) if row else None


def get_all_jobs(status_filter: str = None) -> List[Dict]:
    with get_conn() as conn:
        if status_filter:
            rows = conn.execute(
                "SELECT * FROM jobs WHERE status=? ORDER BY score DESC, date_found DESC",
                (status_filter,)
            ).fetchall()
        else:
            rows = conn.execute(
                "SELECT * FROM jobs ORDER BY score DESC, date_found DESC"
            ).fetchall()
        return [dict(r) for r in rows]


def get_stale_applied_jobs(days: int = 7) -> List[Dict]:
    """Return Applied jobs with no touchpoint in `days` days."""
    cutoff = (datetime.now() - timedelta(days=days)).strftime("%Y-%m-%d")
    with get_conn() as conn:
        rows = conn.execute(
            """SELECT * FROM jobs
               WHERE status = ? AND (date_applied <= ? OR date_applied IS NULL)
               ORDER BY date_applied ASC""",
            (STATUS_APPLIED, cutoff)
        ).fetchall()
        return [dict(r) for r in rows]


def get_active_interviews() -> List[Dict]:
    statuses = (STATUS_RECRUITER_SCREEN, STATUS_INTERVIEW_R1, STATUS_INTERVIEW_R2, STATUS_FINAL_ROUND)
    placeholders = ",".join("?" * len(statuses))
    with get_conn() as conn:
        rows = conn.execute(
            f"SELECT * FROM jobs WHERE status IN ({placeholders}) ORDER BY updated_at DESC",
            statuses
        ).fetchall()
        return [dict(r) for r in rows]


def get_dashboard_kpis() -> Dict:
    with get_conn() as conn:
        total = conn.execute("SELECT COUNT(*) FROM jobs").fetchone()[0]
        applied = conn.execute(
            "SELECT COUNT(*) FROM jobs WHERE status NOT IN (?,?)",
            (STATUS_IDENTIFIED, STATUS_RESUME_GENERATED)
        ).fetchone()[0]
        interviews = conn.execute(
            """SELECT COUNT(*) FROM jobs WHERE status IN (?,?,?,?)""",
            (STATUS_RECRUITER_SCREEN, STATUS_INTERVIEW_R1, STATUS_INTERVIEW_R2, STATUS_FINAL_ROUND)
        ).fetchone()[0]
        offers = conn.execute(
            "SELECT COUNT(*) FROM jobs WHERE status=?", (STATUS_OFFER,)
        ).fetchone()[0]
        avg_score = conn.execute(
            "SELECT AVG(score) FROM jobs WHERE score > 0"
        ).fetchone()[0] or 0
        avg_salary = conn.execute(
            "SELECT AVG(compensation_estimate) FROM jobs WHERE compensation_estimate IS NOT NULL"
        ).fetchone()[0] or 0
        conv_rate = round((interviews / applied * 100) if applied else 0, 1)

    return {
        "total_identified": total,
        "applications_sent": applied,
        "interviews_active": interviews,
        "offers": offers,
        "conversion_rate": conv_rate,
        "avg_score": round(avg_score, 1),
        "avg_salary": int(avg_salary),
        "as_of": datetime.now().strftime("%B %d, %Y at %I:%M %p"),
    }


# ── Follow-ups ────────────────────────────────────────────────────────────────

def save_follow_up(job_id: int, follow_up_type: str, content: str):
    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    with get_conn() as conn:
        conn.execute(
            "INSERT INTO follow_ups (job_id, type, content, created_at) VALUES (?,?,?,?)",
            (job_id, follow_up_type, content, now)
        )


# ── Daily log ─────────────────────────────────────────────────────────────────

def log_daily_run(jobs_found: int, jobs_scored: int, jobs_queued: int, summary: str):
    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    with get_conn() as conn:
        conn.execute(
            """INSERT INTO daily_logs (run_date, jobs_found, jobs_scored, jobs_queued, summary, created_at)
               VALUES (?,?,?,?,?,?)""",
            (date.today().isoformat(), jobs_found, jobs_scored, jobs_queued, summary, now)
        )
