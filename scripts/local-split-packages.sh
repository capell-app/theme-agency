#!/usr/bin/env bash

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="${ROOT}/.env.deploy.local"
DRY_RUN=false
REF=""
TAG=""
BRANCH="${CAPELL_SPLIT_BRANCH:-4.x}"
ORG="${CAPELL_SPLIT_ORG:-capell-app}"
REMOTE_TEMPLATE="${CAPELL_SPLIT_REMOTE_TEMPLATE:-}"
VISIBILITY="${CAPELL_SPLIT_REPO_VISIBILITY:-private}"
SELECTED_PACKAGES=()

usage() {
  cat <<'USAGE'
Usage: scripts/local-split-packages.sh --tag <tag> [options]

Options:
  --tag <tag>               Release tag to split and push.
  --ref <branch|tag|sha>    Source ref to split. Defaults to --tag.
  --package <slug>          Package slug to split. Repeatable. Defaults to workflow matrix.
  --branch <branch>         Destination branch. Defaults to 4.x.
  --org <org>               GitHub org. Defaults to capell-app.
  --public                  Create missing GitHub repositories as public. Defaults to private.
  --remote-template <fmt>   printf template for repo URL, e.g. file:///tmp/%s.git.
  --env-file <path>         Env file. Defaults to .env.deploy.local.
  --dry-run                 Print commands without pushing.
  -h, --help                Show this help.
USAGE
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --tag)
      TAG="${2:-}"
      shift 2
      ;;
    --ref)
      REF="${2:-}"
      shift 2
      ;;
    --package)
      SELECTED_PACKAGES+=("${2:-}")
      shift 2
      ;;
    --branch)
      BRANCH="${2:-}"
      shift 2
      ;;
    --org)
      ORG="${2:-}"
      shift 2
      ;;
    --public)
      VISIBILITY="public"
      shift
      ;;
    --remote-template)
      REMOTE_TEMPLATE="${2:-}"
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

TAG="${TAG:-}"
REF="${REF:-${TAG}}"

if [[ -z "${TAG}" ]]; then
  echo "Missing required --tag value." >&2
  exit 1
fi

if [[ -z "${REF}" ]]; then
  echo "Missing source ref. Pass --ref or --tag." >&2
  exit 1
fi

MATRIX_ROWS=()
while IFS= read -r row; do
  MATRIX_ROWS+=("${row}")
done < <(
  awk '
    /package_directory: packages\// {
      directory = $NF
    }
    /repository_name:/ {
      repository = $2
      if (directory != "" && repository != "") {
        print directory "|" repository
        directory = ""
        repository = ""
      }
    }
  ' "${ROOT}/.github/workflows/split-monorepo.yml"
)

if [[ ${#MATRIX_ROWS[@]} -eq 0 ]]; then
  echo "Could not read package matrix from .github/workflows/split-monorepo.yml." >&2
  exit 1
fi

MATRIX_PACKAGES=()
MATRIX_DIRECTORIES=()
MATRIX_REPOSITORIES=()

for row in "${MATRIX_ROWS[@]}"; do
  directory="${row%%|*}"
  repository="${row##*|}"
  package="${directory#packages/}"
  MATRIX_PACKAGES+=("${package}")
  MATRIX_DIRECTORIES+=("${directory}")
  MATRIX_REPOSITORIES+=("${repository}")
done

if [[ ${#SELECTED_PACKAGES[@]} -gt 0 ]]; then
  PACKAGES=("${SELECTED_PACKAGES[@]}")
else
  PACKAGES=("${MATRIX_PACKAGES[@]}")
fi

PACKAGES=($(printf '%s\n' "${PACKAGES[@]}" | sort))

matrix_lookup() {
  local package="$1"
  local index

  for index in "${!MATRIX_PACKAGES[@]}"; do
    if [[ "${MATRIX_PACKAGES[$index]}" == "${package}" ]]; then
      printf '%s|%s\n' "${MATRIX_DIRECTORIES[$index]}" "${MATRIX_REPOSITORIES[$index]}"
      return 0
    fi
  done

  return 1
}

github_token() {
  if [[ -n "${CAPELL_GITHUB_TOKEN:-}" ]]; then
    printf '%s' "${CAPELL_GITHUB_TOKEN}"
    return
  fi

  if command -v gh >/dev/null 2>&1; then
    gh auth token 2>/dev/null
    return
  fi

  return 1
}

gh_with_token() {
  if [[ -n "${CAPELL_GITHUB_TOKEN:-}" ]]; then
    GH_TOKEN="${CAPELL_GITHUB_TOKEN}" gh "$@"
    return
  fi

  gh "$@"
}

ensure_github_repository() {
  local repository="$1"
  local full_repository="${ORG}/${repository}"

  if [[ -n "${REMOTE_TEMPLATE}" ]]; then
    return
  fi

  if ! command -v gh >/dev/null 2>&1; then
    echo "gh CLI is required to create missing GitHub repositories." >&2
    exit 1
  fi

  if [[ "${DRY_RUN}" == true ]]; then
    echo "[dry-run] gh repo view ${full_repository} || gh repo create ${full_repository} --${VISIBILITY}"
    return
  fi

  if gh_with_token repo view "${full_repository}" >/dev/null 2>&1; then
    return
  fi

  echo "Creating GitHub repository ${full_repository}."
  gh_with_token repo create "${full_repository}" "--${VISIBILITY}" --description "Capell ${repository} package"
}

remote_url_for() {
  local repository="$1"
  local token

  if [[ -n "${REMOTE_TEMPLATE}" ]]; then
    printf "${REMOTE_TEMPLATE}" "${repository}"
    return
  fi

  token="$(github_token || true)"

  if [[ -z "${token}" ]]; then
    echo "CAPELL_GITHUB_TOKEN is required, or install/authenticate gh." >&2
    exit 1
  fi

  printf 'https://x-access-token:%s@github.com/%s/%s.git' "${token}" "${ORG}" "${repository}"
}

run() {
  if [[ "${DRY_RUN}" == true ]]; then
    printf '[dry-run] %q' "$1"
    shift
    printf ' %q' "$@"
    printf '\n'
    return
  fi

  "$@"
}

cd "${ROOT}"

for package in "${PACKAGES[@]}"; do
  lookup="$(matrix_lookup "${package}" || true)"
  directory="${lookup%%|*}"
  repository="${lookup##*|}"

  if [[ -z "${lookup}" || -z "${directory}" || -z "${repository}" ]]; then
    echo "Package '${package}' is not in the workflow split matrix." >&2
    exit 1
  fi

  if [[ ! -d "${directory}" ]]; then
    echo "Missing package directory: ${directory}" >&2
    exit 1
  fi

  echo "Splitting ${directory} from ${REF} for ${ORG}/${repository}:${BRANCH} (${TAG})."

  if [[ "${DRY_RUN}" == true ]]; then
    ensure_github_repository "${repository}"
    echo "[dry-run] git subtree split --prefix ${directory} ${REF}"
    echo "[dry-run] git push <${ORG}/${repository}> <split-sha>:refs/heads/${BRANCH}"
    echo "[dry-run] git push <${ORG}/${repository}> <split-sha>:refs/tags/${TAG}"
    continue
  fi

  split_sha="$(git subtree split --prefix "${directory}" "${REF}")"
  ensure_github_repository "${repository}"
  remote_url="$(remote_url_for "${repository}")"

  git push "${remote_url}" "${split_sha}:refs/heads/${BRANCH}"
  git push "${remote_url}" "${split_sha}:refs/tags/${TAG}"
done
