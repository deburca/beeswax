---
type: task
tags: [beeswax/task]
status: backlog
priority: medium
project: "[[ui-and-interaction-review]]"
area: theme
created: 2026-11-25
branch: feature/0024-touch-targets-and-focus-audit-of-hivelog-skin
release:
depends-on: ["[[0014-form-focus-and-error-states-use-pale-accent]]"]
blocked-by:
---
# Task: Touch targets and focus audit of the HiveLog skin

## Context
`src/hivelog.css` (1,425 lines) was audited for contrast in [[0011-accessibility-pass-and-contrast-checks]] but not for focus indicators or target size. A search on 2026-11-25 for [[ui-and-interaction-review]] found 3 `:focus-visible` and 9 `:focus` rules, and no `44px` or `2.75rem` minimum size anywhere, while `navbar.twig` and `menu--main.html.twig` do use 44px. See [[0002-focus-and-interaction-state-pattern]].

## Open items
- List every interactive element the skin styles (filter selects, Tom Select, chips, tabs, list rows, buttons, the sign-in card) and record its focus indicator and its size.
- Replace per-component focus rules with the global pattern from [[0002-focus-and-interaction-state-pattern]] once accepted.
- Check targets are at least 24x24 CSS px (WCAG 2.5.8) and 44px for the primary touch controls on a phone-sized viewport (the app-nav chips, vertical-tab summaries, Tom Select clear and remove buttons).
- Replace hard-coded colours in the skin (`#ffffff`, the gold button `#d9a735`/`#e9b949`/`#241d15` near lines 760 to 790, `#12201a`) with tokens, or document them as the deliberate brand-gold exception; check the gold button's focus state and its contrast in dark mode.
- Hover-only information: check that nothing (tooltips, titles, reveal on hover) is unreachable by keyboard or touch.

## Acceptance criteria
- [ ] A table of interactive elements with focus indicator and target size, kept in the task or `CUSTOMIZING.md`
- [ ] Every element has a visible focus indicator at 3:1 or more in light and dark
- [ ] No interactive target under 24px; primary touch controls 44px
- [ ] Literal colours replaced or documented
- [ ] `npm run format` and `npm run build`; release note written

## Implementation notes
- Needs a running HiveLog site, as in [[0011-accessibility-pass-and-contrast-checks]]; record the HiveLog version and browser.
- Key files: `src/hivelog.css`, `src/tom-select.css`, `lib/hivelog-select.js`.

## Related
- Project:: [[ui-and-interaction-review]]
- Decisions:: [[0002-focus-and-interaction-state-pattern]]
