---
type: task
tags: [beeswax/task]
status: backlog
priority: high
project: "[[ui-and-interaction-review]]"
area: theme
created: 2026-11-25
branch: feature/0015-pager-current-page-dark-mode-and-semantics
release:
depends-on: 
blocked-by:
---
# Task: Pager current page, dark mode and semantics

## Context
Found by reading `templates/includes/pager.twig` and `templates/navigation/pager.html.twig` (project [[ui-and-interaction-review]]); not rendered yet.

## Problems found
- **Current page is invisible in dark mode.** The current page chip is `bg-foreground text-white`. In dark mode `--foreground` is `#efe9db`, so the label is white on near-white. Use `text-background`.
- **No `aria-current="page"`** on the current page link; screen-reader users cannot tell where they are. (The template already calculates `is_current_page`.)
- **Invalid markup:** `<div class="flex gap-1">` inside `<span class="pager__list-item">` in the first/previous/next/last links.
- **Redundant name:** the links carry both a `title` attribute and an `sr-only` span with the same wording; `<nav role="navigation">` repeats the element's own role.
- **Fixed 40px targets** (`h-10 w-10`) are fine for desktop but sit tight in the mobile row; check at 320px with seven items.
- **Heading level is a six-way `if` chain** repeated in the template; acceptable under `AGENTS.md`, but the sr-only heading plus `aria-labelledby` is also duplicated in `pager.html.twig` and `views-mini-pager.html.twig`: check they cannot share one include.

## Acceptance criteria
- [ ] Current page readable (4.5:1) in light and dark
- [ ] `aria-current="page"` on the current page link
- [ ] No block element inside inline; no redundant `role` or `title`
- [ ] Checked at 320px width and with keyboard
- [ ] `npm run format` and `npm run build`; release note written

## Implementation notes
- Key files: `templates/includes/pager.twig`, `templates/navigation/pager.html.twig`, `templates/views/views-mini-pager.html.twig`.
- A literal `text-white` is what [[0001-token-architecture-and-source-of-truth]] rules out.

## Related
- Project:: [[ui-and-interaction-review]]
- Decisions:: [[0001-token-architecture-and-source-of-truth]]
