---
type: task
tags: [beeswax/task]
status: in-progress
priority: medium
project: "[[theme-health-review]]"
area: theme
created: 2026-11-13
branch: feature/0011-accessibility-pass-and-contrast-checks
release: 1.9.3
depends-on:
blocked-by:
---
# Task: Accessibility pass and contrast checks

## Context
Left open when [[0008-front-end-delivery-and-accessibility]] shipped in 1.9.2. That task fixed delivery, CSP and reduced motion but could not run an accessibility audit: it needs a real HiveLog site, which was not available in the session. Only `--bw-ok` has had its contrast computed (task 0001). Nothing has been checked in dark mode for the other tokens, the Tom Select controls or the vertical tabs.

## Acceptance criteria
- [x] axe (or Lighthouse) run on pages in light and dark: a HiveLog list page, a form, and the sign-in card (no vertical-tabs form on the demo)
- [x] Text contrast of at least 4.5:1 and non-text contrast (borders, focus rings, control outlines) of at least 3:1 for the `--bw-*` and `--hivelog-*` tokens in both modes, computed and recorded in `CUSTOMIZING.md` or the task
- [ ] Keyboard pass: focus visible and in a sensible order on the three pages, Tom Select open/select/close by keyboard, the message close button (focus now moves to the next message, from task 0004)
- [x] Failures fixed, or each ticketed as its own task with the page and rule
- [ ] Reduced motion checked on a real device or with the OS setting (group animations from task 0003, message dismissal from 0004, the blanket rule from 0008)
- [ ] Release note written

## Implementation notes
- Key files: `src/theme.css` (palette, `.dark` block), `src/hivelog.css`, `src/tom-select.css`, `lib/hivelog-select.js`, `lib/messages.js`.
- Needs a running HiveLog site. Record the version of HiveLog and the browser used.
- The honeycomb background sits behind content at 10% opacity; include it when checking contrast of text over it.
- Also confirm the 1.9.2 changes on that site: minified `build/hivelog.min.css` renders the same as before, and `lib/scheme-auto.js` still sets dark mode under *Match the device*.

## Progress (computed, no site yet)
Ratios for every `--bw-*` pair are in `CUSTOMIZING.md` ("Contrast"). Failures found and fixed:
- `--bw-ink-faint` used as text (stat-tile labels, table headers, vertical-tab summaries, upcoming "where", footer versions) was 2.96 to 3.14:1 in light and 4.04 to 4.43:1 in dark: those six uses now take `--bw-ink-muted`.
- `--bw-warning` on `--bw-warning-tint` (the attention chip) was 4.03:1 in light: `--bw-warning` is now `#965a08` (4.67:1).
- `--bw-critical` on `--bw-critical-tint` was 4.43:1 in dark: `--bw-critical` is now `#e8756c` (4.99:1).
- Control borders were about 1.3 to 1.6:1 (`--bw-field-border` `#cfc6b3` / `#4a4237`, and `--bw-hairline` on the filter selects and Tom Select controls): field border is now `#8f8575` / `#7d7360` (3.1:1 or more), and those two controls use it. **This makes input borders visibly darker.**
- The tokens `--bw-warning` and `--bw-critical` are mirrored in the Vinculum app (`Palette.swift`); it needs the same two values.
- Not checked: button borders (`--hivelog-btn-border`, hairline), focus rings, text over the honeycomb, and anything the HiveLog module draws with its own colours.

## Audit on a running site (2026-11-13)
Site: `hivelog/demo` (DDEV, Drupal 11.4.11, HiveLog 4.1.1, Beeswax copied from this branch), signed in as the admin. axe-core 4.10.2 (installed with npm, loaded from the site), rules wcag2a, wcag2aa, wcag21aa, wcag22aa and best-practice, light and dark, on the sign-in card, the dashboard, the apiary list, the apiary add form and the account edit page.
- **Fixed here:** the filter `<select>`s were unlabelled (`select-name`, critical): Tom Select moves the label to its own control, so `lib/hivelog-select.js` now sets `aria-hidden` on the original select. The branding block's logo `alt` repeated the site name beside it (`image-redundant-alt`): the alt is now empty when the name is shown. The messages template used `role="contentinfo"` (axe: a landmark nested in another); it is now `status`, or `alert` for errors.
- **Contrast:** no violations on any of the five pages in either mode after the earlier token fixes. axe cannot judge contrast under the honeycomb (it reports every text as "incomplete" because of the pseudo-element), so it was re-run with the honeycomb hidden; the worst case under a honeycomb line is `--bw-ink-muted` at 4.70:1 in light and 6.73:1 in dark (computed). A few items stay "incomplete" (the app-nav chips, two text areas, the error message title) and were not judged by eye.
- **Keyboard:** Tom Select opens with Enter, moves with the arrow keys, selects with Enter, and shows a visible pine focus border.
- **Not caused by the theme:** two `landmark-unique` and the duplicated page title and breadcrumb come from the demo site placing the breadcrumb and page-title blocks that the page template already renders; the Claro toolbar and admin pages are not Beeswax.
- **Not done:** a form with vertical tabs (none found on the demo), reduced motion (the pane cannot emulate it), the message close button's focus move (no message shown), and live-checking `lib/scheme-auto.js` under a strict CSP.

## Related
- Project:: [[theme-health-review]]
- Follows:: [[0008-front-end-delivery-and-accessibility]]
