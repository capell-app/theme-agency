#!/usr/bin/env bash

set -euo pipefail

MODE="committed"
RECTOR_ARGS=()

while [[ $# -gt 0 ]]; do
  case "$1" in
    --staged)
      MODE="staged"
      ;;
    *)
      RECTOR_ARGS+=("$1")
      ;;
  esac

  shift
done

CHANGED_FILES=()

if [[ "$MODE" == "staged" ]]; then
  while IFS= read -r file; do
    CHANGED_FILES+=("$file")
  done < <(git diff --cached --name-only --diff-filter=ACMRTUXB)
elif [[ -n "${GITHUB_BASE_REF:-}" ]]; then
  git fetch --no-tags --depth=1 origin "$GITHUB_BASE_REF"

  BASE_COMMIT="$(git merge-base "origin/$GITHUB_BASE_REF" HEAD)"

  while IFS= read -r file; do
    CHANGED_FILES+=("$file")
  done < <(git diff --name-only --diff-filter=ACMRTUXB "$BASE_COMMIT"...HEAD)
else
  while IFS= read -r file; do
    CHANGED_FILES+=("$file")
  done < <(git diff --name-only --diff-filter=ACMRTUXB HEAD)
fi

PHP_FILES=()

for file in "${CHANGED_FILES[@]}"; do
  if [[ -f $file && $file == *.php ]]; then
    PHP_FILES+=("$file")
  fi
done

if [[ ${#PHP_FILES[@]} -eq 0 ]]; then
  echo "No changed PHP files for Rector."
  exit 0
fi

echo "Running Rector on changed PHP files..."
XDEBUG_MODE=off "$PHP_BINARY" vendor/bin/rector --no-progress-bar "${RECTOR_ARGS[@]}" "${PHP_FILES[@]}"
