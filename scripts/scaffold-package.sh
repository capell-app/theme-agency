#!/usr/bin/env bash

# Scaffold a new Capell add-on package.
#
# Creates the on-disk package skeleton under packages/<slug>/, registers the
# package in the monorepo split matrix, installs the per-package PR forwarding
# workflow, then (unless --local-only) creates the capell-app/<slug> GitHub
# repository and seeds its 4.x split branch.
#
# The script is idempotent: existing files are left untouched unless --force is
# passed, the split matrix is only appended to when the package is missing, and
# the GitHub repository / 4.x branch are created only when absent.

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SPLIT_WORKFLOW="${ROOT}/.github/workflows/split-monorepo.yml"
BRANCH="${CAPELL_SPLIT_BRANCH:-4.x}"
ORG="${CAPELL_SPLIT_ORG:-capell-app}"
COMMITTER_NAME="${CAPELL_SPLIT_COMMITTER_NAME:-capell-app}"
COMMITTER_EMAIL="${CAPELL_SPLIT_COMMITTER_EMAIL:-appcapello@gmail.com}"

SLUG=""
DISPLAY_NAME=""
NAMESPACE=""
KIND="feature"
DESCRIPTION=""
VISIBILITY="private"
DRY_RUN=false
LOCAL_ONLY=false
FORCE=false

usage() {
  cat <<'USAGE'
Usage: scripts/scaffold-package.sh <slug> [options]

Arguments:
  <slug>                    Package slug, e.g. theme-liquid-glass (kebab-case).

Options:
  --display-name <name>     Human display name. Defaults to a title-cased slug.
  --namespace <ns>          PHP namespace root. Defaults to Capell\<StudlySlug>.
  --kind <kind>             capell.json kind (feature, theme, integration, ...).
                            Defaults to feature.
  --description <text>      One-line package description.
  --public                  Create the GitHub repo as public. Defaults to private.
  --local-only              Scaffold files and register the split only. No GitHub.
  --force                   Overwrite existing scaffold files in packages/<slug>.
  --dry-run                 Print the planned actions without changing anything.
  -h, --help                Show this help.

Environment:
  CAPELL_SPLIT_ORG          GitHub org. Defaults to capell-app.
  CAPELL_SPLIT_BRANCH       Split branch. Defaults to 4.x.
USAGE
}

log() {
  printf '%s\n' "$*"
}

run() {
  if [[ "${DRY_RUN}" == true ]]; then
    printf '[dry-run]'
    printf ' %q' "$@"
    printf '\n'
    return 0
  fi

  "$@"
}

# Convert a kebab-case slug into StudlyCase, e.g. theme-liquid-glass -> ThemeLiquidGlass.
studly_case() {
  local input="$1"
  local result=""
  local segment

  IFS='-' read -ra segments <<<"${input}"
  for segment in "${segments[@]}"; do
    if [[ -n "${segment}" ]]; then
      result+="$(tr '[:lower:]' '[:upper:]' <<<"${segment:0:1}")${segment:1}"
    fi
  done

  printf '%s' "${result}"
}

# Convert a kebab-case slug into Title Case words, e.g. theme-liquid-glass -> Theme Liquid Glass.
title_case() {
  local input="$1"
  local result=""
  local segment

  IFS='-' read -ra segments <<<"${input}"
  for segment in "${segments[@]}"; do
    if [[ -n "${segment}" ]]; then
      if [[ -n "${result}" ]]; then
        result+=" "
      fi
      result+="$(tr '[:lower:]' '[:upper:]' <<<"${segment:0:1}")${segment:1}"
    fi
  done

  printf '%s' "${result}"
}

write_file() {
  local destination="$1"
  local contents="$2"

  if [[ -f "${destination}" && "${FORCE}" != true ]]; then
    log "  skip (exists): ${destination#"${ROOT}/"}"
    return 0
  fi

  if [[ "${DRY_RUN}" == true ]]; then
    log "  [dry-run] write ${destination#"${ROOT}/"}"
    return 0
  fi

  mkdir -p "$(dirname "${destination}")"
  printf '%s' "${contents}" >"${destination}"
  log "  wrote ${destination#"${ROOT}/"}"
}

if [[ $# -eq 0 ]]; then
  usage >&2
  exit 1
fi

SLUG="$1"
shift

if [[ "${SLUG}" == "-h" || "${SLUG}" == "--help" ]]; then
  usage
  exit 0
fi

while [[ $# -gt 0 ]]; do
  case "$1" in
    --display-name)
      DISPLAY_NAME="${2:-}"
      shift 2
      ;;
    --namespace)
      NAMESPACE="${2:-}"
      shift 2
      ;;
    --kind)
      KIND="${2:-}"
      shift 2
      ;;
    --description)
      DESCRIPTION="${2:-}"
      shift 2
      ;;
    --public)
      VISIBILITY="public"
      shift
      ;;
    --local-only)
      LOCAL_ONLY=true
      shift
      ;;
    --force)
      FORCE=true
      shift
      ;;
    --dry-run)
      DRY_RUN=true
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

if [[ ! "${SLUG}" =~ ^[a-z0-9]+(-[a-z0-9]+)*$ ]]; then
  echo "Invalid slug '${SLUG}'. Use lowercase kebab-case, e.g. theme-liquid-glass." >&2
  exit 1
fi

STUDLY="$(studly_case "${SLUG}")"
DISPLAY_NAME="${DISPLAY_NAME:-$(title_case "${SLUG}")}"
NAMESPACE="${NAMESPACE:-Capell\\${STUDLY}}"
DESCRIPTION="${DESCRIPTION:-The ${DISPLAY_NAME} package for Capell.}"
PROVIDER_CLASS="${STUDLY}ServiceProvider"
NAMESPACE_JSON="$(printf '%s' "${NAMESPACE}" | sed 's/\\/\\\\/g')"
PACKAGE_DIR="${ROOT}/packages/${SLUG}"
REPO="${ORG}/${SLUG}"

log "Scaffolding Capell package '${SLUG}'"
log "  display name : ${DISPLAY_NAME}"
log "  namespace    : ${NAMESPACE}"
log "  kind         : ${KIND}"
log "  github repo  : ${REPO} (${VISIBILITY})"
log "  split branch : ${BRANCH}"
if [[ "${DRY_RUN}" == true ]]; then
  log "  mode         : dry-run (no changes will be made)"
elif [[ "${LOCAL_ONLY}" == true ]]; then
  log "  mode         : local-only (no GitHub operations)"
fi
log ""

if [[ -d "${PACKAGE_DIR}" && "${FORCE}" != true ]]; then
  log "Package directory packages/${SLUG} already exists; only missing files will be added."
  log "Pass --force to overwrite existing files."
  log ""
fi

# ---------------------------------------------------------------------------
# 1. Package filesystem skeleton.
# ---------------------------------------------------------------------------
log "Writing package skeleton under packages/${SLUG}/"

write_file "${PACKAGE_DIR}/composer.json" "$(cat <<JSON
{
    "name": "${ORG}/${SLUG}",
    "description": "${DESCRIPTION}",
    "type": "library",
    "license": "MIT",
    "require": {
        "php": "^8.3",
        "capell-app/core": "^4.0 || 4.x-dev"
    },
    "require-dev": {
        "pestphp/pest": "^3.0|^4.0",
        "phpunit/phpunit": "^10.0"
    },
    "autoload": {
        "psr-4": {
            "${NAMESPACE_JSON}\\\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "${NAMESPACE_JSON}\\\\Tests\\\\": "tests/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "${NAMESPACE_JSON}\\\\${PROVIDER_CLASS}"
            ]
        },
        "branch-alias": {
            "dev-main": "4.x-dev"
        }
    },
    "homepage": "https://github.com/${ORG}/${SLUG}",
    "keywords": [
        "capell",
        "cms",
        "laravel"
    ],
    "authors": [
        {
            "name": "Capell Team",
            "email": "team@capell.app",
            "role": "Developer"
        }
    ],
    "support": {
        "source": "https://github.com/${ORG}/capell-packages",
        "docs": "https://docs.capell.app/packages/${SLUG}"
    }
}
JSON
)"

write_file "${PACKAGE_DIR}/capell.json" "$(cat <<JSON
{
    "manifest-version": 3,
    "name": "${ORG}/${SLUG}",
    "slug": "${SLUG}",
    "displayName": "${DISPLAY_NAME}",
    "kind": "${KIND}",
    "capellApiVersion": "^4.0",
    "version": "4.x-dev",
    "description": "${DESCRIPTION}",
    "product": {
        "group": "Capell Foundation",
        "tier": "free",
        "bundle": "foundation"
    },
    "namespace": "${NAMESPACE_JSON}",
    "surfaces": [],
    "dependencies": {
        "requires": ["capell-app/core"],
        "supports": [],
        "conflicts": []
    },
    "providers": {
        "metadata": [],
        "install": [],
        "runtime": [
            "${NAMESPACE_JSON}\\\\${PROVIDER_CLASS}"
        ],
        "admin": [],
        "frontend": []
    },
    "contributes": []
}
JSON
)"

write_file "${PACKAGE_DIR}/src/${PROVIDER_CLASS}.php" "$(cat <<PHP
<?php

declare(strict_types=1);

namespace ${NAMESPACE};

use Illuminate\Support\ServiceProvider;

final class ${PROVIDER_CLASS} extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
PHP
)"

write_file "${PACKAGE_DIR}/tests/Pest.php" "$(cat <<'PHP'
<?php

declare(strict_types=1);
PHP
)"

write_file "${PACKAGE_DIR}/tests/Unit/PackageSkeletonTest.php" "$(cat <<PHP
<?php

declare(strict_types=1);

it('exposes the package service provider', function (): void {
    expect(class_exists(\\${NAMESPACE}\\${PROVIDER_CLASS}::class))->toBeTrue();
});
PHP
)"

write_file "${PACKAGE_DIR}/README.md" "$(cat <<MARKDOWN
# ${DISPLAY_NAME}

${DESCRIPTION}

## Installation

\`\`\`bash
composer require ${ORG}/${SLUG}
\`\`\`

This package is developed in the [\`${ORG}/capell-packages\`](https://github.com/${ORG}/capell-packages)
monorepo. The [\`${ORG}/${SLUG}\`](https://github.com/${ORG}/${SLUG}) repository is a generated
split used for Composer installs and focused collaborator pull requests.
MARKDOWN
)"

write_file "${PACKAGE_DIR}/CHANGELOG.md" "$(cat <<MARKDOWN
# Changelog

## 4.x-dev

- Initial ${DISPLAY_NAME} package.
MARKDOWN
)"

write_file "${PACKAGE_DIR}/.gitattributes" "$(cat <<'GITATTRIBUTES'
/docs export-ignore
/tests export-ignore
/.github export-ignore
/.gitattributes export-ignore
/.gitignore export-ignore
GITATTRIBUTES
)"

write_file "${PACKAGE_DIR}/.gitignore" "$(cat <<'GITIGNORE'
/vendor
/node_modules
/.phpunit.cache
GITIGNORE
)"

write_file "${PACKAGE_DIR}/docs/.gitkeep" ""

# Per-package PR forwarding workflow. Generated by the dedicated installer so
# the YAML stays identical across every package.
log ""
log "Installing PR forwarding workflow"
if [[ "${DRY_RUN}" == true ]]; then
  log "  [dry-run] php scripts/install-split-pr-forwarding-workflows.php"
else
  run php "${ROOT}/scripts/install-split-pr-forwarding-workflows.php" >/dev/null
  log "  installed packages/${SLUG}/.github/workflows/forward-pr-to-monorepo.yml"
fi

# ---------------------------------------------------------------------------
# 2. Register the package in the monorepo split matrix.
# ---------------------------------------------------------------------------
log ""
log "Registering split matrix entry in .github/workflows/split-monorepo.yml"

if grep -q "package_directory: packages/${SLUG}$" "${SPLIT_WORKFLOW}"; then
  log "  already present; leaving split matrix unchanged"
elif [[ "${DRY_RUN}" == true ]]; then
  log "  [dry-run] insert matrix entry for packages/${SLUG} (repository_name: ${SLUG})"
else
  tmp_workflow="$(mktemp)"
  SLUG="${SLUG}" awk '
    BEGIN { inserted = 0; newslug = ENVIRON["SLUG"] }
    /^          - package_directory: packages\// {
      line = $0
      sub(/^.*packages\//, "", line)
      if (inserted == 0 && line > newslug) {
        printf "          - package_directory: packages/%s\n", newslug
        printf "            repository_name: %s\n", newslug
        inserted = 1
      }
    }
    /^    steps:/ && inserted == 0 {
      printf "          - package_directory: packages/%s\n", newslug
      printf "            repository_name: %s\n", newslug
      inserted = 1
    }
    { print }
  ' "${SPLIT_WORKFLOW}" >"${tmp_workflow}"
  mv "${tmp_workflow}" "${SPLIT_WORKFLOW}"
  log "  inserted matrix entry for packages/${SLUG}"
fi

# ---------------------------------------------------------------------------
# 3. GitHub repository + split branch.
# ---------------------------------------------------------------------------
if [[ "${LOCAL_ONLY}" == true ]]; then
  log ""
  log "Skipping GitHub operations (--local-only)."
else
  log ""
  log "Setting up GitHub repository ${REPO}"

  if ! command -v gh >/dev/null 2>&1; then
    echo "gh CLI is required for GitHub operations. Install gh or pass --local-only." >&2
    exit 1
  fi

  if gh repo view "${REPO}" >/dev/null 2>&1; then
    log "  repository ${REPO} already exists"
  else
    run gh repo create "${REPO}" \
      "--${VISIBILITY}" \
      --description "${DESCRIPTION}"
    log "  created repository ${REPO}"
  fi

  # Seed the split branch the same way the monorepo split workflow does: an
  # orphan branch with a single empty commit if it does not already exist.
  remote_url="https://github.com/${REPO}.git"
  if [[ "${DRY_RUN}" == true ]]; then
    log "  [dry-run] ensure ${BRANCH} branch exists on ${REPO} and set it as default"
  else
    token="$(gh auth token 2>/dev/null || true)"
    if [[ -n "${token}" ]]; then
      remote_url="https://x-access-token:${token}@github.com/${REPO}.git"
    fi

    if git ls-remote --exit-code --heads "${remote_url}" "${BRANCH}" >/dev/null 2>&1; then
      log "  branch ${BRANCH} already exists on ${REPO}"
    else
      worktree="$(mktemp -d)"
      trap 'rm -rf "${worktree}"' EXIT
      git init -q "${worktree}"
      git -C "${worktree}" remote add origin "${remote_url}"
      git -C "${worktree}" checkout -q --orphan "${BRANCH}"
      git -C "${worktree}" config user.name "${COMMITTER_NAME}"
      git -C "${worktree}" config user.email "${COMMITTER_EMAIL}"
      git -C "${worktree}" commit -q --allow-empty -m "Initialize ${BRANCH} branch"
      git -C "${worktree}" push -q origin "${BRANCH}"
      rm -rf "${worktree}"
      trap - EXIT
      log "  seeded ${BRANCH} branch on ${REPO}"
    fi

    gh repo edit "${REPO}" --default-branch "${BRANCH}" >/dev/null 2>&1 || true
    log "  default branch set to ${BRANCH}"
  fi
fi

log ""
log "Done."
log ""
log "Next steps:"
log "  1. Review packages/${SLUG}/ and flesh out src/, tests/, resources/."
log "  2. Confirm the split matrix entry in .github/workflows/split-monorepo.yml."
log "  3. Ensure ${ORG}/${SLUG} has the SPLIT_PR_FORWARD_TOKEN (or ACCESS_TOKEN) secret."
log "  4. Add matching composer.local.json autoload entries for local development."
