---
type: task
tags: [beeswax/task]
status: review
priority: medium
project: "[[ui-and-interaction-review]]"
area: theme
created: 2026-11-25
branch: feature/0016-status-messages-bypass-tokens-and-dark-mode
release: 1.9.5
depends-on:
blocked-by:
---
# Task: Status messages bypass tokens and dark mode

## Context
`templates/misc/status-messages.html.twig` colours each message type with a literal Tailwind palette (`bg-green-100 text-green-900`, `blue`, `yellow`, `red`), found while reading the templates for [[ui-and-interaction-review]]. The rest of the theme uses tokens, and [[0011-accessibility-pass-and-contrast-checks]] fixed `--bw-warning` and `--bw-critical` for contrast, but the messages never use them.

## Problems found
- The four palettes do not follow `--bw-ok`, `--bw-warning`, `--bw-critical` or the dark palette: in dark mode a bright pale box sits on a dark page.
- The set is a different green, yellow and red from the chips and status colours the HiveLog skin shows, so the same state is drawn two ways.
- The `if/elseif` chain sets three class variables per type; a CVA with a `type` variant is what `AGENTS.md` asks for.
- Messages are `fixed bottom-4` with no `left` at small widths and no offset for the Gin toolbar or the mobile browser bar; check they do not cover the footer or a form's submit button on a phone.
- Messages never time out (right for errors, probably not for success); decide and document.
- `aria-label` on a `role="status"` div duplicates the visually hidden heading.

## Acceptance criteria
- [x] Each type draws from semantic tokens (ok, info, warning, critical), correct in light and dark
- [x] One CVA replaces the `if/elseif` chain
- [ ] Checked on a 360px viewport with a long form
- [x] Decision on auto-dismiss for success messages recorded in the task
- [x] `npm run format` and `npm run build`
- [x] Release note written

## Implementation notes
- Key files: `templates/misc/status-messages.html.twig`, `lib/messages.js`, `src/theme.css`.
- Needs the `info` token from [[0001-token-architecture-and-source-of-truth]] (there is no `--bw-info` today).

## Related
- Project:: [[ui-and-interaction-review]]
- Decisions:: [[0001-token-architecture-and-source-of-truth]]

## Outcome (2026-11-25)
- Each type has a tinted background (`--bw-ok-tint`, `--bw-accent-tint`, `--bw-warning-tint`, `--bw-critical-tint`), a 4px left border and an icon in the matching `--bw-*` colour; the text is `--foreground`. Computed ratios of each colour on its tint are in `CUSTOMIZING.md` (4.67:1 to 7.50:1), and the body text is the ink colour, so it follows dark mode.
- Two CVAs (message, icon colour) replace the `if/elseif` chain; the icon names are a lookup map. The close button is one class set (`hover:bg-foreground/10`, a 2px focus outline per [[0002-focus-and-interaction-state-pattern]]).
- **Info uses the accent** (pine) because there is no `--bw-info` token yet; it is the same colour as a success border's neighbour but a different tint. Revisit when [[0022-token-consolidation]] adds the semantic tokens; this task no longer depends on it.
- Layout: the container now spans the viewport width on phones (`left-0 right-0`) instead of shrinking to its content, and is `pointer-events-none` with the messages `pointer-events-auto`, so the empty gutter does not block the page below.
- The `aria-label` on the `role` element is removed; the hidden heading names the group.
- **Auto-dismiss: decided not to.** Messages stay until dismissed (WCAG 2.2.1 timing, and a vanishing success message is easy to miss); `messages.js` is unchanged.
- **Not done:** not rendered (the Twig was not compiled and the icons were not checked to follow `currentColor`); the 360px check with a long form is open; release note not written. Stacked on the 0015 branch.
