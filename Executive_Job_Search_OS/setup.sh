#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# Executive Job Search OS — One-Command Setup
# Usage: bash setup.sh
# ─────────────────────────────────────────────────────────────────────────────

set -euo pipefail

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$DIR"

echo ""
echo "══════════════════════════════════════════════════════════════"
echo "  EXECUTIVE JOB SEARCH OS — Setup"
echo "══════════════════════════════════════════════════════════════"
echo ""

# ── 1. Python version check ───────────────────────────────────────────────────
if command -v python3 &>/dev/null; then
  PY=python3
elif command -v python &>/dev/null; then
  PY=python
else
  echo "ERROR: Python 3.9+ is required. Install from https://python.org"
  exit 1
fi

PY_VERSION=$($PY -c "import sys; print(f'{sys.version_info.major}.{sys.version_info.minor}')")
PY_MAJOR=$($PY -c "import sys; print(sys.version_info.major)")
PY_MINOR=$($PY -c "import sys; print(sys.version_info.minor)")

if [[ "$PY_MAJOR" -lt 3 ]] || [[ "$PY_MAJOR" -eq 3 && "$PY_MINOR" -lt 9 ]]; then
  echo "ERROR: Python 3.9+ required. Found Python $PY_VERSION"
  exit 1
fi
echo "✓ Python $PY_VERSION found"

# ── 2. Virtual environment ────────────────────────────────────────────────────
if [[ ! -d ".venv" ]]; then
  echo "→ Creating virtual environment..."
  $PY -m venv .venv
fi
echo "✓ Virtual environment ready"

# Activate
source .venv/bin/activate

# ── 3. Install dependencies ───────────────────────────────────────────────────
echo "→ Installing Python dependencies..."
pip install --quiet --upgrade pip
pip install --quiet -r requirements.txt
echo "✓ Dependencies installed"

# ── 4. Playwright browser ─────────────────────────────────────────────────────
echo "→ Installing Playwright Chromium (for JavaScript-heavy career pages)..."
playwright install chromium --with-deps 2>/dev/null || {
  echo "  ⚠  Playwright install skipped (optional — requests-based scraping still works)"
}

# ── 5. .env file ──────────────────────────────────────────────────────────────
if [[ ! -f ".env" ]]; then
  cp .env.example .env
  echo ""
  echo "════════════════════════════════════════════════════════════"
  echo "  ACTION REQUIRED: Edit .env and add your Anthropic API key"
  echo "  File location: $DIR/.env"
  echo "════════════════════════════════════════════════════════════"
  echo ""
else
  echo "✓ .env file exists"
fi

# Check for API key
if grep -q "sk-ant-your-key-here" .env 2>/dev/null || ! grep -q "ANTHROPIC_API_KEY=sk-ant-" .env 2>/dev/null; then
  echo ""
  echo "  ⚠  ANTHROPIC_API_KEY not set in .env"
  echo "  Get your key at: https://console.anthropic.com"
  echo "  Then edit .env: ANTHROPIC_API_KEY=sk-ant-..."
  echo ""
fi

# ── 6. Ensure output directories exist ───────────────────────────────────────
echo "→ Creating output directories..."
mkdir -p data resumes cover_letters job_descriptions logs exports dashboard
echo "✓ Directories ready"

# ── 7. Initialize database ────────────────────────────────────────────────────
echo "→ Initializing SQLite database..."
$PY main.py --init-only
echo "✓ Database initialized at data/jobs.db"

# ── 8. Candidate profile reminder ────────────────────────────────────────────
echo ""
echo "════════════════════════════════════════════════════════════"
echo "  PERSONALIZE YOUR PROFILE"
echo "════════════════════════════════════════════════════════════"
echo "  1. Edit config.py → CANDIDATE dict:"
echo "       email, phone, linkedin URL"
echo ""
echo "  2. Edit src/resume_engine.py → MICHELLE_PROFILE:"
echo "       Verify all experience dates, titles, companies"
echo "       Update education details"
echo ""
echo "  3. (Optional) Add your actual resume bullets in:"
echo "       src/resume_engine.py → MICHELLE_PROFILE['experience']"
echo "════════════════════════════════════════════════════════════"
echo ""

# ── Done ──────────────────────────────────────────────────────────────────────
echo "══════════════════════════════════════════════════════════════"
echo "  ✓ Setup complete!"
echo ""
echo "  NEXT STEPS:"
echo ""
echo "  1. Activate venv:    source .venv/bin/activate"
echo "  2. Set API key:      edit .env"
echo "  3. Test run:         python main.py --dry-run"
echo "  4. Full run:         python main.py"
echo "  5. View dashboard:   open dashboard/index.html"
echo "  6. Start scheduler:  python scheduler.py &"
echo "  7. Add cron job:     see README.md for crontab entry"
echo "══════════════════════════════════════════════════════════════"
echo ""
