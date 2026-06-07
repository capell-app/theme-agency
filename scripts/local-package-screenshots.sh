#!/usr/bin/env bash

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="${ROOT}/.env.deploy.local"
RUNNER_PATH="${CAPELL_SCREENSHOT_RUNNER_PATH:-/Users/ben/Sites/packages/capell/capell-screenshot-runner}"
CORE_REPO_PATH="${CAPELL_CORE_REPO_PATH:-/Users/ben/Sites/packages/capell/capell-4}"
DRY_RUN=false
SKIP_BUILD=false
SKIP_PREPARE=false
REUSE_APP=false
ONLY_ARGS=()

usage() {
  cat <<'USAGE'
Usage: scripts/local-package-screenshots.sh [options]

Options:
  --package <slug>       Capture or validate one package. Repeatable.
  --only-file <path>     File containing package slugs to capture.
  --runner-path <path>   capell-screenshot-runner checkout.
  --core-path <path>     capell-4 checkout.
  --env-file <path>      Env file. Defaults to .env.deploy.local.
  --dry-run              Validate manifests without browser capture.
  --skip-build           Pass --skip-build to the screenshot runner.
  --skip-prepare         Do not run the runner app prepare step.
  --reuse-app            Reuse a prepared app/database and skip setup/demo commands.
  -h, --help             Show this help.
USAGE
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --package|--only)
      ONLY_ARGS+=(--only "${2:-}")
      shift 2
      ;;
    --only-file)
      ONLY_ARGS+=(--only-file "${2:-}")
      shift 2
      ;;
    --runner-path)
      RUNNER_PATH="${2:-}"
      shift 2
      ;;
    --core-path)
      CORE_REPO_PATH="${2:-}"
      shift 2
      ;;
    --env-file)
      ENV_FILE="${2:-}"
      shift 2
      ;;
    --dry-run)
      DRY_RUN=true
      shift
      ;;
    --skip-build)
      SKIP_BUILD=true
      shift
      ;;
    --skip-prepare)
      SKIP_PREPARE=true
      shift
      ;;
    --reuse-app)
      REUSE_APP=true
      SKIP_PREPARE=true
      SKIP_BUILD=true
      shift
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "Unknown option: $1" >&2
      usage >&2
      exit 1
      ;;
  esac
done

if [[ -f "${ENV_FILE}" ]]; then
  set -a
  # shellcheck disable=SC1090
  source "${ENV_FILE}"
  set +a
fi

if [[ ! -f "${RUNNER_PATH}/src/cli.mjs" ]]; then
  echo "Screenshot runner was not found at ${RUNNER_PATH}." >&2
  exit 1
fi

export CAPELL_SCREENSHOT_RUNNER_PATH="${RUNNER_PATH}"
export CAPELL_PACKAGES_REPO_PATH="${ROOT}"
export CAPELL_PACKAGES_REPO="${ROOT}"
export CAPELL_REPO="${CORE_REPO_PATH}"
export CAPELL_SCREENSHOT_APP_PATH="${RUNNER_PATH}"
export CAPELL_ADMIN_URL="${CAPELL_ADMIN_URL:-http://127.0.0.1:8145}"
export CAPELL_FRONTEND_URL="${CAPELL_FRONTEND_URL:-http://127.0.0.1:8145}"
export CAPELL_SCREENSHOT_ADMIN_EMAIL="${CAPELL_SCREENSHOT_ADMIN_EMAIL:-test@example.com}"
export CAPELL_SCREENSHOT_ADMIN_PASSWORD="${CAPELL_SCREENSHOT_ADMIN_PASSWORD:-password}"
export DB_CONNECTION="${DB_CONNECTION:-sqlite}"
export DB_DATABASE="${DB_DATABASE:-${RUNNER_PATH}/database/database.sqlite}"
export CACHE_STORE="${CACHE_STORE:-array}"
export CAPELL_SCREENSHOT_SKIP_COMPOSER_UPDATE="${CAPELL_SCREENSHOT_SKIP_COMPOSER_UPDATE:-true}"

if [[ "${REUSE_APP}" != true || ! -d "${RUNNER_PATH}/node_modules/playwright" ]]; then
  npm ci --prefix "${RUNNER_PATH}"
fi

if [[ "${DRY_RUN}" == true ]]; then
  npm run screenshots:validate -- "${ONLY_ARGS[@]}"
  npm run screenshots:capture:check -- --runner "${RUNNER_PATH}" --repo "${ROOT}" --dry-run --skip-build "${ONLY_ARGS[@]}"
  exit $?
fi

npm run screenshots:manifest
npm run screenshots:validate -- "${ONLY_ARGS[@]}"

if [[ "${REUSE_APP}" != true ]]; then
  npm run install:browsers --prefix "${RUNNER_PATH}"
fi

if [[ "${SKIP_PREPARE}" != true ]]; then
  npm run prepare:app --prefix "${RUNNER_PATH}"
fi

if [[ "${REUSE_APP}" == true ]]; then
  find "${RUNNER_PATH}/storage/framework/cache/data" -type f ! -name '.gitignore' -delete
fi

SCREENSHOT_ARGS=(--runner "${RUNNER_PATH}" --repo "${ROOT}" "${ONLY_ARGS[@]}")

if [[ "${SKIP_BUILD}" == true ]]; then
  SCREENSHOT_ARGS+=(--skip-build)
fi

if [[ "${REUSE_APP}" == true ]]; then
  SCREENSHOT_ARGS+=(--reuse-app)
fi

npm run screenshots:capture -- "${SCREENSHOT_ARGS[@]}"
