# Split PR Forwarding

`capell-app/capell-packages` is the source of truth for first-party package code. Individual package repositories are generated splits that support Composer installs and focused collaborator PRs.

## Flow

1. A collaborator opens or updates a PR against a split repo such as `capell-app/address`.
2. The split repo workflow checks out the PR contents without installing dependencies or running contributor code.
3. The workflow checks out `capell-app/capell-packages:4.x`.
4. It replaces only `packages/<package-name>/` with the split PR contents.
5. It pushes `split-pr/<package-name>/<split-pr-number>` to `capell-packages`.
6. It opens or updates a monorepo PR titled `[<package-name>] <original split PR title>`.
7. Maintainers review and merge the monorepo PR. The split PR should not be merged directly.

When a split PR is closed, the workflow closes the matching forwarded monorepo PR when it is still open.

## Required Secret

Every package split repo needs access to either `SPLIT_PR_FORWARD_TOKEN` or `ACCESS_TOKEN` as an Actions secret. The token must be able to:

- read private split repositories,
- clone `capell-app/capell-packages`,
- push branches to `capell-app/capell-packages`,
- create and update PRs in `capell-app/capell-packages`,
- comment on the original split PR.

Prefer an org-level `SPLIT_PR_FORWARD_TOKEN` selected for all package split repositories.

## Package Split Coverage

The split workflow covers every directory currently present under `packages/`. The latest audit added these previously missing packages to split coverage:

- `agent-delivery`
- `comments`
- `filament-peek`
- `shopify-commerce`
- `theme-commerce`
- `theme-education`
- `theme-healthcare`
- `theme-knowledge`
- `theme-local-services`
- `theme-nonprofit`
- `theme-portfolio`

Run this check before adding a package:

```bash
comm -23 \
  <(find packages -mindepth 1 -maxdepth 1 -type d -exec basename {} \; | sort) \
  <(ruby -ryaml -e 'workflow = YAML.load_file(".github/workflows/split-monorepo.yml"); puts workflow.dig("jobs", "split-monorepo", "strategy", "matrix", "include").map { |row| row["repository_name"] }.sort')
```

The command should print nothing.
