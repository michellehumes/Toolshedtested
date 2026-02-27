"""
Daily scheduler — runs main.py every weekday at 7:00 AM Eastern.
Run this process in the background: `python scheduler.py &`
Or use cron (see README.md for crontab entry).
"""

import logging
import os
import signal
import subprocess
import sys
from pathlib import Path

from apscheduler.schedulers.blocking import BlockingScheduler
from apscheduler.triggers.cron import CronTrigger
from dotenv import load_dotenv

load_dotenv(Path(__file__).parent / ".env")

import config

log = logging.getLogger("job_os.scheduler")
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s  %(levelname)-8s  %(name)s — %(message)s",
    datefmt="%Y-%m-%d %H:%M:%S",
    handlers=[logging.StreamHandler(sys.stdout)],
)

PYTHON = sys.executable
MAIN_SCRIPT = str(Path(__file__).parent / "main.py")


def run_daily_job():
    """Subprocess call to main.py — keeps the scheduler process clean."""
    log.info("▶ Triggering daily job search run...")
    result = subprocess.run(
        [PYTHON, MAIN_SCRIPT],
        capture_output=False,
        text=True,
        cwd=str(Path(__file__).parent),
    )
    if result.returncode != 0:
        log.error("Daily run exited with code %d", result.returncode)
    else:
        log.info("Daily run completed successfully.")


def main():
    scheduler = BlockingScheduler(timezone=config.SCHEDULE_TIMEZONE)

    # Weekdays at 7:00 AM Eastern
    trigger = CronTrigger(
        day_of_week="mon-fri",
        hour=config.SCHEDULE_HOUR,
        minute=config.SCHEDULE_MINUTE,
        timezone=config.SCHEDULE_TIMEZONE,
    )
    scheduler.add_job(run_daily_job, trigger=trigger, id="daily_search", misfire_grace_time=3600)

    log.info(
        "Scheduler started. Next run: weekdays at %02d:%02d %s",
        config.SCHEDULE_HOUR, config.SCHEDULE_MINUTE, config.SCHEDULE_TIMEZONE,
    )
    log.info("Press Ctrl+C to stop.")

    def handle_signal(signum, frame):
        log.info("Shutdown signal received. Stopping scheduler.")
        scheduler.shutdown(wait=False)
        sys.exit(0)

    signal.signal(signal.SIGTERM, handle_signal)
    signal.signal(signal.SIGINT, handle_signal)

    try:
        scheduler.start()
    except (KeyboardInterrupt, SystemExit):
        log.info("Scheduler stopped.")


if __name__ == "__main__":
    main()
