---
type: task
tags: [beeswax/task]
status: backlog
priority: medium
project: "[[theme-health-review]]"
area: tooling
created: 2026-11-13
branch: feature/0007-build-reproducibility-ci-and-checks
release:
depends-on:
blocked-by:
---
# Task: Reproducible build, CI and automated checks

## Context
The theme commits its compiled output (`build/main.min.css`, `build/motion.js`) and has no automation: no CI workflow, no linting, no tests. Found during the review of 2026-11-13.

## Problems found
- **The committed build is not reproducible.** Running `npm run build` on an unchanged tree changes both `build/main.min.css` (68,492 to 68,519 bytes) and `build/motion.js`. The installed Tailwind is 4.3.3 against `^4.1.18` in `package.json`, and esbuild/motion are similarly floating. Every contributor who builds produces a noisy diff, and it is impossible to tell a real change from a toolchain bump.
- **`npm run format` is destructive.** It runs `prettier --write .`, which currently rewrites about 35 files that are not part of any change (vendored Tom Select, `lib/hivelog-select.js`, `config/`, a Twig template, `CUSTOMIZING.md`, docs), so following `AGENTS.md` ("run format and build after changes") produces a huge unrelated diff. Either add a `.prettierignore` (vendor, build, docs) and format the tree once in its own commit, or change `format` to touch only changed files.
- **No CI.** Nothing runs on a pull request: not `format:check`, not the build, not a check that `build/` is up to date, not PHP syntax or coding standards.
- **No PHP checks** (phpcs with the Drupal standard, phpstan) and **no tests** for the theme hooks.
- **Versions out of step:** `package.json` says `version 1.0.3` while the theme is 1.9.0 (the tags and `beeswax.info.yml` agree with each other). `generator: mercury:1.0.5` in `beeswax.info.yml` is stale.

## Acceptance criteria
- [ ] `npm ci && npm run build` is byte-for-byte reproducible and matches the committed `build/` (pin exact versions or rely on the lockfile with `npm ci`, and document the Node version in `.nvmrc`/`engines`)
- [ ] `format` no longer touches unrelated files; `AGENTS.md` updated to match
- [ ] A GitHub Actions workflow runs `format:check`, the build and a `git diff --exit-code build/`, plus `php -l` (and phpcs if adopted) on pull requests
- [ ] `package.json` version is either kept in sync with releases or removed
- [ ] Release note written

## Implementation notes
- Key files: `package.json`, `package-lock.json`, `.prettierrc`, `.github/` (new), `AGENTS.md` (workflow section), `build/`.
- The repo has no GitHub Actions today (`gh pr` shows no checks), so every PR so far has been merged unchecked.
- Do the one-off prettier reformat as a separate PR before anything else so later diffs stay small.

## Related
- Project:: [[theme-health-review]]
