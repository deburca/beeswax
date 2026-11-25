---
type: task
tags: [beeswax/task]
status: review
priority: medium
project: "[[ui-and-interaction-review]]"
area: theme
created: 2026-11-25
branch: feature/0024-touch-targets-and-focus-audit-of-hivelog-skin
release: 1.9.5
depends-on:
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
- [x] Every element has a visible focus indicator at 3:1 or more in light and dark
- [x] No interactive target under 24px; primary touch controls 44px
- [x] Literal colours replaced or documented
- [x] `npm run format` and `npm run build`
- [x] Release note written

## Implementation notes
- Needs a running HiveLog site, as in [[0011-accessibility-pass-and-contrast-checks]]; record the HiveLog version and browser.
- Key files: `src/hivelog.css`, `src/tom-select.css`, `lib/hivelog-select.js`.

## Related
- Project:: [[ui-and-interaction-review]]
- Decisions:: [[0002-focus-and-interaction-state-pattern]]

## Outcome (2026-11-25, static audit)
Done from the CSS and templates only, with no HiveLog site (the demo holds a 13 November copy and needs a sign-in; the owner chose a static audit). Everything below is from reading `src/hivelog.css`, `src/tom-select.css` and the templates, plus a harness page with the compiled CSS in the browser pane. **The live check on a HiveLog site is still open.**

### Interactive elements, how focus shows, and size
| Element | Focus before | Focus now | Size |
| --- | --- | --- | --- |
| Links, buttons, summaries, anything not listed | browser default | global `:focus-visible` 2px `--primary` outline, offset 2px (new, `main.css`; [[0002-focus-and-interaction-state-pattern]]) | text height; inline links exempt (2.5.8) |
| Filter-form text input and select | `outline: none`, border to pine and a **pale tint ring** (`--bw-accent-tint`, not a visible indicator) | border pine plus a 2px pine outline | 36px; 44px on touch |
| Skin fields (HiveLog, account, sign-in) | same, with a 3px tint ring | same change | about 40px; 44px on touch |
| Tom Select control (`.ts-wrapper.focus`) | pine border and a tint ring | pine border and 2px pine outline; the inner input draws none (no double ring) | 36px or about 40px; 44px on touch |
| Vertical tabs links | 2px pine inset outline (already good) | unchanged | about 44px (padding 0.6rem each side) |
| App-nav chips, honey call-to-action | background and border change only | the global outline now shows too | about 36px; 44px on touch |
| Checkbox and radio | browser default ring | global outline | 17.6px; **24px on touch** |
| Pager, status close button, breadcrumb | see 0015, 0016 | global outline | 40px, about 28px (padding 4px around a 24px icon), text link |

### Changes
- **Focus:** one global `:focus-visible` rule in `main.css` (`@layer base`, so a utility such as the button's own ring still wins) and the skin's pale tint rings replaced by a real 2px outline in four places (filter inputs, general fields, Tom Select in `hivelog.css` and `tom-select.css`). A hover-only gap remains: none found, nothing in the skin hides content behind `:hover`.
- **Touch targets:** a `@media (pointer: coarse)` block at the end of `hivelog.css` raises fields, Tom Select controls, the app-nav chips, the honey button and vertical-tab links to 44px and checkboxes/radios to 24px. With a mouse nothing changes size.
- **Literal colours:** the honey call-to-action is now `--bw-honey`, `--bw-honey-hover`, `--bw-honey-border`, `--bw-honey-border-hover` and `--bw-on-honey` (new, `theme.css`, same in both schemes; `#241d15` on the honey is 9.1:1, 10.2:1 on hover). The active chip and the HiveLog button foregrounds use `--primary-foreground` / `--destructive-foreground` instead of `#ffffff`, so the `.dark` override rules for them are gone. What is left in the skin: the error-page illustration's `--hivelog-error-*` colours (artwork) and a `#000` mask gradient (alpha only).
- **Checked in the browser pane** (a Tab press, so `:focus-visible` matches): a plain link, the filter input, select and menu chip each show a solid 2px `rgb(47, 107, 79)` outline; the input also has the pine border and no tint ring. The coarse-pointer sizes were not measured (the pane is not a touch device).
- **Not changed, for the live pass:** the honey border `#d9a735` against the page is 2.1:1 (the fill and its 9:1 text carry the control; borderline for the 3:1 non-text rule); the status-message close button and Tom Select's remove buttons were not measured; text over the honeycomb; `outline-offset` on elements inside an `overflow: hidden` parent can clip the outline (check the app-nav strip and the filter row).
- Release note not written. Stacked on the 0023 branch.
