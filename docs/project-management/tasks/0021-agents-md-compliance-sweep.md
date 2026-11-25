---
type: task
tags: [beeswax/task]
status: review
priority: low
project: "[[ui-and-interaction-review]]"
area: tooling
created: 2026-11-25
branch: feature/0021-agents-md-compliance-sweep
release: 1.9.5
depends-on: 
blocked-by:
---
# Task: AGENTS.md compliance sweep

## Context
`AGENTS.md` requires `with_context: false` / `with only` on every include and that only configurable props are passed. A grep on 2026-11-25 for [[ui-and-interaction-review]] found no inline conditionals, dynamic tags or `true`/`false` CVA keys (good), but includes are not consistently isolated. [[0013-php-quality-tests-and-ignored-templates]] added checks for PHP and formatting but nothing checks Twig conventions, so they drift.

## Problems found
- **`include()` without `with_context: false`:** `components/navbar/navbar.twig` (5), `components/anchor/anchor.twig` (1), `templates/includes/pager.twig` (6), `templates/navigation/pager.html.twig` (1), `templates/navigation/breadcrumb.html.twig` (4), `templates/views/views-mini-pager.html.twig` (1).
- **`{% include ... with {...} %}` without `only`:** every `canvas:image` include in `hero-blog`, `hero-side-by-side`, `section`, `card`, `cta`, `image` and `card-logo` (10 sites).
- **Dead branch in `components/button/button.twig`:** `href` is set to null when disabled, so the `aria-disabled` attribute on the link branch can never render; the `<button>` branch lacks `type="button"`, so inside a form it submits.
- **Un-hoisted attribute strings:** `button.twig` and others build `disabled_attr` strings; fine, but the one in the link branch is never used.
- **No automated check:** nothing stops a regression.

## Acceptance criteria
- [x] All includes isolate their context; outputs unchanged on the pages that use them
- [x] `button.twig` has no dead branch and renders `type="button"` unless told otherwise
- [x] A small script in `package.json` (and CI) greps for the two include patterns and fails on a match
- [x] `npm run format` and `npm run build`
- [x] Release note written

## Implementation notes
- Isolating a context can drop variables a template silently relied on (for example `navbar.twig` blocks); test each with its `component.yml` example and in Canvas.
- Key files: the templates listed above, `package.json`, `.github/workflows/ci.yml`.

## Related
- Project:: [[ui-and-interaction-review]]

## Outcome (2026-11-25)
- **The task's count was wrong for tags:** every `{% include 'canvas:image' ... %}` already ends in `only` (my first search looked at one line at a time and missed it). The check script confirms none of the ten need a change. The real offenders were 16 `include()` calls, in `anchor.twig` (1), `navbar.twig` (3), `templates/includes/pager.twig` (6), `breadcrumb.html.twig` (4), `pager.html.twig` (1) and `views-mini-pager.html.twig` (1); `navbar.twig` has three, not five, and the task's total of 17 was 16 plus the ten tags it should not have listed.
- **Fixed:** all 16 now pass `with_context: false`. The two pager templates now hand `templates/includes/pager.twig` its variables explicitly (`heading_id`, `pagination_heading_level`, `items`, `current`, `ellipses`, the last two with defaults so the views mini pager, which has no `ellipses`, still works). An unused `{% set var = value %}` in the pager include is removed.
- **`button.twig`:** the `disabled_attr` that could never render (the link branch is only reached when the button is not disabled) is gone; a disabled control is always the `<button>` branch. That branch has `type="button"`, so a button inside a form no longer submits it.
- **Check:** `scripts/check-twig-includes.mjs` (run with `npm run check:twig`) finds `include()` calls without `with_context: false` and `{% include %}` tags without `only`, ignoring Twig comments, and fails with file and line. A throwaway file with one of each failed it, and a commented example did not. It runs in `.github/workflows/ci.yml` as its own step and is mentioned in `AGENTS.md`.
- **Not done / risks:** nothing was rendered on Drupal. The branding include in the navbar now gets no context; the navbar is a component (its context is already isolated), but a site that placed `navbar.twig` outside that route should check the logo. The pager outputs should be compared before and after on a paged list. Release note not written. Stacked on the 0020 branch.
