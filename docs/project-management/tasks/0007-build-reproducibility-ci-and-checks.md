---
type: task
tags: [beeswax/task]
status: review
priority: medium
project: "[[theme-health-review]]"
area: tooling
created: 2026-11-13
branch: feature/0007-build-reproducibility-ci-and-checks
release: 1.9.1
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
- [x] `npm ci && npm run build` is byte-for-byte reproducible and matches the committed `build/` (pin exact versions or rely on the lockfile with `npm ci`, and document the Node version in `.nvmrc`/`engines`)
- [x] `format` no longer touches unrelated files; `AGENTS.md` updated to match
- [x] A GitHub Actions workflow runs `format:check`, the build and a `git diff --exit-code build/`, plus `php -l` (and phpcs if adopted) on pull requests
- [x] `package.json` version is either kept in sync with releases or removed
- [ ] Release note written

## Implementation notes
- Key files: `package.json`, `package-lock.json`, `.prettierrc`, `.github/` (new), `AGENTS.md` (workflow section), `build/`.
- The repo has no GitHub Actions today (`gh pr` shows no checks), so every PR so far has been merged unchecked.
- Do the one-off prettier reformat as a separate PR before anything else so later diffs stay small.

## Related
- Project:: [[theme-health-review]]

## Done
- **Root cause of the unstable build was not the toolchain** (installed Tailwind 4.3.3, esbuild 0.28.2 and motion match the lockfile, and two builds in a row are byte-identical). Tailwind v4 scans every non-ignored file for class names, so any note that mentioned `isolate`, `table`, `drop-shadow`, `ease-in` or an example like AGENTS.md's `bg-[var(--hgc-btn-bg)]` made it into `build/main.min.css`; writing `bg-red-500` in a docs file added that rule. `src/main.css` now uses `@import "tailwindcss" source(none)` plus `@source` for `templates`, `components`, `src` and `lib`. This drops about 1.7 KB of unused rules (none from the theme's own markup: every dropped rule came from AGENTS.md or prose in docs).
- `.prettierignore` now also excludes `docs/`, `lib/vendor/`, the Drupal info and config YAML, `templates/layout/page.html.twig` (Prettier adds whitespace inside its inline icon spans) and two templates the Twig plugin cannot parse (`field--node--field-content`, `menu--main`; both also break the AGENTS.md rules on tags inside HTML tags, so they want fixing, then un-ignoring). `prettier --check .` passes on a clean tree; `lib/hivelog-select.js` and `CUSTOMIZING.md` were formatted (quotes, emphasis marks only).
- `package.json`: stale `version` removed (the theme version lives in `beeswax.info.yml`), `engines.node >=24` added (`.nvmrc` is 24.12.0), `check:build` script added. `package-lock.json` regenerated: it also gains npm's bundled optional wasm entries for Tailwind's oxide, which `npm ci` on Linux needs.
- `.github/workflows/ci.yml`: format check, `check:build`, `php -l`.
- Not done: phpcs/phpstan and tests (still none); `generator: mercury:1.0.5` is in task 0010.
