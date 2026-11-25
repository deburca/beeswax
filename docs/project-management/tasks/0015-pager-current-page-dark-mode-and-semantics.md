---
type: task
tags: [beeswax/task]
status: review
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
- [x] Current page readable (4.5:1) in light and dark
- [x] `aria-current="page"` on the current page link
- [x] No block element inside inline; no redundant `role` or `title`
- [ ] Checked at 320px width and with keyboard
- [x] `npm run format` and `npm run build`
- [ ] Release note written

## Implementation notes
- Key files: `templates/includes/pager.twig`, `templates/navigation/pager.html.twig`, `templates/views/views-mini-pager.html.twig`.
- A literal `text-white` is what [[0001-token-architecture-and-source-of-truth]] rules out.

## Related
- Project:: [[ui-and-interaction-review]]
- Decisions:: [[0001-token-architecture-and-source-of-truth]]

## Outcome (2026-11-25)
- Current page chip is `text-background` on `bg-foreground`: 15.7:1 light, 15.0:1 dark (it was 1.2:1 in dark). The build is unchanged because `.text-background` was already compiled.
- The current page link now carries `aria-current="page"`.
- The two `<div class="flex gap-1">` inside `<span>` (first and last links) are spans; `role="navigation"` is gone from the `<nav>`; page-number links no longer carry a `title` that repeated the hidden "Page N" text. The icon-only first/previous/next/last links keep their `title`, which gives sighted mouse users a tooltip.
- Not changed: the heading-level chain, and the two pager templates already share one include (`templates/includes/pager.twig`), so there is nothing to merge. The shared include relies on the parent context, so [[0021-agents-md-compliance-sweep]] must pass its variables explicitly when it isolates it.
- **Not done:** not rendered; the 320px check and the keyboard pass are open, and the release note is not written. This branch is stacked on the 0014 branch because the task note lives there.
