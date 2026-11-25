---
type: task
tags: [beeswax/task]
status: backlog
priority: medium
project: "[[ui-and-interaction-review]]"
area: theme
created: 2026-11-25
branch: feature/0016-status-messages-bypass-tokens-and-dark-mode
release:
depends-on: ["[[0022-token-consolidation]]"]
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
- [ ] Each type draws from semantic tokens (ok, info, warning, critical), correct in light and dark
- [ ] One CVA replaces the `if/elseif` chain
- [ ] Checked on a 360px viewport with a long form
- [ ] Decision on auto-dismiss for success messages recorded in the task
- [ ] `npm run format` and `npm run build`; release note written

## Implementation notes
- Key files: `templates/misc/status-messages.html.twig`, `lib/messages.js`, `src/theme.css`.
- Needs the `info` token from [[0001-token-architecture-and-source-of-truth]] (there is no `--bw-info` today).

## Related
- Project:: [[ui-and-interaction-review]]
- Decisions:: [[0001-token-architecture-and-source-of-truth]]
