---
type: task
tags: [beeswax/task]
status: backlog
priority: low
project: "[[ui-and-interaction-review]]"
area: tooling
created: 2026-11-25
branch: feature/0021-agents-md-compliance-sweep
release:
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
- [ ] All includes isolate their context; outputs unchanged on the pages that use them
- [ ] `button.twig` has no dead branch and renders `type="button"` unless told otherwise
- [ ] A small script in `package.json` (and CI) greps for the two include patterns and fails on a match
- [ ] `npm run format` and `npm run build`; release note written

## Implementation notes
- Isolating a context can drop variables a template silently relied on (for example `navbar.twig` blocks); test each with its `component.yml` example and in Canvas.
- Key files: the templates listed above, `package.json`, `.github/workflows/ci.yml`.

## Related
- Project:: [[ui-and-interaction-review]]
