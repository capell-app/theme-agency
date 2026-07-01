#!/usr/bin/env bash

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="${ROOT}/.env.deploy.local"
RUNNER_PATH="${CAPELL_SCREENSHOT_RUNNER_PATH:-/Users/ben/Sites/packages/capell/capell-screenshot-runner}"
CORE_REPO_PATH="${CAPELL_CORE_REPO_PATH:-/Users/ben/Sites/packages/capell/capell-4}"
TEMP_APP_CREATED=false
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

REQUESTED_APP_PATH="${CAPELL_SCREENSHOT_APP_PATH:-}"

if [[ ! -f "${RUNNER_PATH}/src/cli.mjs" ]]; then
  echo "Screenshot runner was not found at ${RUNNER_PATH}." >&2
  exit 1
fi

APP_PATH="${REQUESTED_APP_PATH}"
if [[ -z "${APP_PATH}" ]]; then
  if [[ "${REUSE_APP}" == true || "${DRY_RUN}" == true ]]; then
    APP_PATH="${RUNNER_PATH}"
  else
    APP_PATH="$(mktemp -d "${TMPDIR:-/tmp}/capell-screenshot-runner.XXXXXX")"
    TEMP_APP_CREATED=true
    rsync -a --delete \
      --exclude '.git/' \
      --exclude 'database/database.sqlite' \
      --exclude 'database/.screenshot-state.sqlite' \
      --exclude 'storage/framework/cache/data/*' \
      --exclude 'storage/framework/sessions/*' \
      --exclude 'storage/framework/views/*' \
      --exclude 'storage/logs/*' \
      --exclude 'public/page-cache/' \
      "${RUNNER_PATH}/" "${APP_PATH}/"

    APP_PARENT_PATH="$(dirname "${APP_PATH}")"
    ln -sfn "${ROOT}" "${APP_PARENT_PATH}/capell-packages-4"
    ln -sfn "${CORE_REPO_PATH}" "${APP_PARENT_PATH}/capell-4"

    SEEDER_PATH="${APP_PATH}/database/seeders/DatabaseSeeder.php"
    if [[ -f "${SEEDER_PATH}" ]] && ! grep -q -- "--skip-permission-sync" "${SEEDER_PATH}"; then
      perl -0pi -e "s/(\\s+'--skip-panel-integration' => true,\\n)/\\1            '--skip-permission-sync' => true,\\n/" "${SEEDER_PATH}"
    fi
    if [[ -f "${SEEDER_PATH}" ]] && ! grep -q -- "Role::findOrCreate('super_admin'" "${SEEDER_PATH}"; then
      perl -0pi -e "s/(\\s+)\\\$user->assignRole\\('super_admin'\\);/\\1\\\\Spatie\\\\Permission\\\\Models\\\\Role::findOrCreate('super_admin', (string) config('auth.defaults.guard', 'web'));\\n\\1\\\$user->assignRole('super_admin');/" "${SEEDER_PATH}"
    fi
  fi
fi

export CAPELL_SCREENSHOT_RUNNER_PATH="${RUNNER_PATH}"
export CAPELL_PACKAGES_REPO_PATH="${ROOT}"
export CAPELL_PACKAGES_REPO="${ROOT}"
export CAPELL_REPO="${CORE_REPO_PATH}"
export CAPELL_SCREENSHOT_APP_PATH="${APP_PATH}"
if [[ "${APP_PATH}" != "${RUNNER_PATH}" && -z "${CAPELL_FRONTEND_URL:-}" && -z "${CAPELL_ADMIN_URL:-}" ]]; then
  PORT="$(python3 - <<'PY'
import socket

with socket.socket() as sock:
    sock.bind(("127.0.0.1", 0))
    print(sock.getsockname()[1])
PY
)"
  export CAPELL_FRONTEND_URL="http://127.0.0.1:${PORT}"
  export CAPELL_ADMIN_URL="http://127.0.0.1:${PORT}/admin"
else
  export CAPELL_ADMIN_URL="${CAPELL_ADMIN_URL:-http://127.0.0.1:8145/admin}"
  export CAPELL_FRONTEND_URL="${CAPELL_FRONTEND_URL:-http://127.0.0.1:8145}"
fi
export CAPELL_SCREENSHOT_ADMIN_EMAIL="${CAPELL_SCREENSHOT_ADMIN_EMAIL:-test@example.com}"
export CAPELL_SCREENSHOT_ADMIN_PASSWORD="${CAPELL_SCREENSHOT_ADMIN_PASSWORD:-password}"
export DB_CONNECTION="${DB_CONNECTION:-sqlite}"
export DB_DATABASE="${DB_DATABASE:-${APP_PATH}/database/database.sqlite}"
export CACHE_STORE="${CACHE_STORE:-array}"
export CAPELL_SCREENSHOT_SKIP_COMPOSER_UPDATE="${CAPELL_SCREENSHOT_SKIP_COMPOSER_UPDATE:-true}"
export XDEBUG_MODE="${XDEBUG_MODE:-off}"

if [[ "${TEMP_APP_CREATED}" == true ]]; then
  PHP_WRAPPER_DIR="${APP_PATH}/.capell-screenshot-bin"
  PHP_BINARY_PATH="$(command -v php)"
  mkdir -p "${PHP_WRAPPER_DIR}"
  cat > "${PHP_WRAPPER_DIR}/php" <<SH
#!/usr/bin/env bash
exec "${PHP_BINARY_PATH}" -d pcov.enabled=0 "\$@"
SH
  chmod +x "${PHP_WRAPPER_DIR}/php"
  export PATH="${PHP_WRAPPER_DIR}:${PATH}"
fi

if [[ "${REUSE_APP}" != true || ! -d "${RUNNER_PATH}/node_modules/playwright" ]]; then
  npm ci --prefix "${RUNNER_PATH}"
fi

if [[ "${DRY_RUN}" == true ]]; then
  npm run screenshots:validate -- ${ONLY_ARGS[@]+"${ONLY_ARGS[@]}"}
  npm run screenshots:capture:check -- --runner "${RUNNER_PATH}" --repo "${ROOT}" --dry-run --skip-build ${ONLY_ARGS[@]+"${ONLY_ARGS[@]}"}
  exit $?
fi

npm run screenshots:manifest
npm run screenshots:validate -- ${ONLY_ARGS[@]+"${ONLY_ARGS[@]}"}

if [[ "${REUSE_APP}" != true ]]; then
  npm run install:browsers --prefix "${RUNNER_PATH}"
fi

if [[ "${SKIP_PREPARE}" != true ]]; then
  npm run prepare:app --prefix "${RUNNER_PATH}"
fi

if [[ "${REUSE_APP}" == true ]]; then
  find "${RUNNER_PATH}/storage/framework/cache/data" -type f ! -name '.gitignore' -delete
fi

SCREENSHOT_ARGS=(--runner "${RUNNER_PATH}" --repo "${ROOT}" ${ONLY_ARGS[@]+"${ONLY_ARGS[@]}"})

if [[ "${SKIP_BUILD}" == true ]]; then
  SCREENSHOT_ARGS+=(--skip-build)
fi

if [[ "${REUSE_APP}" == true ]]; then
  SCREENSHOT_ARGS+=(--reuse-app)
fi

npm run screenshots:capture -- "${SCREENSHOT_ARGS[@]}"
