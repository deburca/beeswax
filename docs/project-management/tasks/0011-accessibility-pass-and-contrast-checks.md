---
type: task
tags: [beeswax/task]
status: backlog
priority: medium
project: "[[theme-health-review]]"
area: theme
created: 2026-11-13
branch: feature/0011-accessibility-pass-and-contrast-checks
release:
depends-on:
blocked-by:
---
# Task: Accessibility pass and contrast checks

## Context
Left open when [[0008-front-end-delivery-and-accessibility]] shipped in 1.9.2. That task fixed delivery, CSP and reduced motion but could not run an accessibility audit: it needs a real HiveLog site, which was not available in the session. Only `--bw-ok` has had its contrast computed (task 0001). Nothing has been checked in dark mode for the other tokens, the Tom Select controls or the vertical tabs.

## Acceptance criteria
- [ ] axe (or Lighthouse) run on three pages, each in light and dark: a HiveLog list page, a form with vertical tabs, and the sign-in card
- [ ] Text contrast of at least 4.5:1 and non-text contrast (borders, focus rings, control outlines) of at least 3:1 for the `--bw-*` and `--hivelog-*` tokens in both modes, computed and recorded in `CUSTOMIZING.md` or the task
- [ ] Keyboard pass: focus visible and in a sensible order on the three pages, Tom Select open/select/close by keyboard, the message close button (focus now moves to the next message, from task 0004)
- [ ] Failures fixed, or each ticketed as its own task with the page and rule
- [ ] Reduced motion checked on a real device or with the OS setting (group animations from task 0003, message dismissal from 0004, the blanket rule from 0008)
- [ ] Release note written

## Implementation notes
- Key files: `src/theme.css` (palette, `.dark` block), `src/hivelog.css`, `src/tom-select.css`, `lib/hivelog-select.js`, `lib/messages.js`.
- Needs a running HiveLog site. Record the version of HiveLog and the browser used.
- The honeycomb background sits behind content at 10% opacity; include it when checking contrast of text over it.
- Also confirm the 1.9.2 changes on that site: minified `build/hivelog.min.css` renders the same as before, and `lib/scheme-auto.js` still sets dark mode under *Match the device*.

## Related
- Project:: [[theme-health-review]]
- Follows:: [[0008-front-end-delivery-and-accessibility]]
