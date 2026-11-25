---
type: task
tags: [beeswax/task]
status: review
priority: high
project: "[[ui-and-interaction-review]]"
area: theme
created: 2026-11-25
branch: feature/0014-form-focus-and-error-states-use-pale-accent
release:
depends-on: 
blocked-by:
---
# Task: Form focus and error states use the pale accent

## Context
Found by reading `src/main.css` (project [[ui-and-interaction-review]]); not yet rendered. In `src/theme.css` `--accent` is the *pale tint* (`#e6efe8` light, `#22302a` dark), not the brand colour (see [[0001-token-architecture-and-source-of-truth]]). The form base styles use it as if it were the brand colour, and they apply on every page the HiveLog skin does not cover (Canvas pages, user and admin-adjacent forms).

## Problems found
- **Focus is nearly invisible.** `input:focus`, `textarea:focus` and `select:focus` set `border-accent` and `bg-muted/60`; the border goes from `--border` to a tint that is *lighter* than the page. The field has `outline-none`, so there is no other indicator (WCAG 2.4.7, 2.4.13).
- **Label turns invisible on focus.** `.form-item:focus-within > label` is `text-accent`: pale green text on the page background (about 1.2:1).
- **Focused select text** is `text-accent` too: pale text on `bg-muted/60`.
- **Errors use `red-500`.** `.form-required::after`, `.form-item--error-message` and the error borders use Tailwind `red-500` (`#ef4444`, about 3.8:1 on white): small error text fails 4.5:1, and it ignores the `--destructive` token and dark mode.
- **Submit buttons** (`.form-submit`, `input.button`) have no `:focus-visible` style, unlike the `button` component.
- **Placeholder and `select` share a rule** (`input::placeholder, textarea::placeholder, select { text-muted-foreground }`): a selected value reads as a placeholder.

## Acceptance criteria
- [ ] Reproduced on a page outside `body.hivelog-page`, light and dark (screenshot in the task)
- [x] A visible focus indicator on every text field, select, checkbox, radio and submit button that meets 3:1 against its surroundings (per [[0002-focus-and-interaction-state-pattern]])
- [x] Label and select text stay at 4.5:1 or more in focus-within, light and dark
- [x] Errors use `--destructive` (and a text-safe variant) and read 4.5:1 in both modes
- [x] A selected `<select>` value no longer looks like placeholder text
- [x] `npm run format` and `npm run build`
- [ ] Release note written

## Implementation notes
- Key files: `src/main.css` (`@layer base` form block), `src/theme.css`.
- The HiveLog skin may already override these; check with and without `body.hivelog-page`.
- Decide with [[0002-focus-and-interaction-state-pattern]] whether the indicator is the global outline or a field-specific style.

## Related
- Project:: [[ui-and-interaction-review]]
- Decisions:: [[0001-token-architecture-and-source-of-truth]], [[0002-focus-and-interaction-state-pattern]]

## Outcome (2026-11-25)
- `src/main.css`: field focus now draws a 2px `--primary` outline (offset 2px) on `:focus-visible` for text fields, textarea, select, `.form-submit` and `input.button`, plus a `--primary` border. The outline is plain CSS because Tailwind's `outline-none` sets `--tw-outline-style: none`, which the `outline-*` utilities inherit (the first attempt compiled to no outline).
- Label on `:focus-within` and the focused select text: `--accent` replaced by `--primary` (select text now keeps the normal ink colour). Computed: `--primary` on the page background is 5.93:1 light, 7.23:1 dark.
- Errors, the required asterisk and error borders use `--destructive` (6.16:1 light, 5.53:1 dark on the page background) instead of `red-500`.
- `select` no longer shares the placeholder rule, so a chosen value is not muted.
- **Not done:** not rendered, so the task's reproduction criterion stays open (no site, and no browser check of the compiled CSS). The resting border (`--border`, about 1.3:1) is unchanged; the outline carries focus, but a 3:1 resting border (as the HiveLog skin's `--bw-field-border`) belongs with [[0022-token-consolidation]]. Checkboxes and radios still use browser defaults for focus. Release note not written.
