---
type: decision
tags: [beeswax/decision]
status: proposed
date: 2026-11-25
supersedes:
---
# ADR-0001: Token architecture and source of truth

## Status
proposed

## Context
Colour comes from three places that do not agree on names or location:
- shadcn-style tokens (`--background`, `--primary`, `--accent`, ...) in `src/theme.css`, mapped to Tailwind in `src/main.css`. Used by every component and by core form styling.
- `--bw-*` tokens in `src/theme.css`, used by the HiveLog skin. Their values duplicate the first set (`--bw-ground` equals `--background`, `--bw-accent` equals `--primary`).
- Tokens defined elsewhere: `--bw-field-border` lives in `src/hivelog.css`, not in `theme.css`, so a site that copies `theme.css` to the web root (the documented customisation route) does not carry it.

Names also collide in meaning. In the shadcn set `--accent` is the *pale tint* and `--primary` the solid pine; in the `--bw-*` set `--bw-accent` is the solid pine. Code that reads `accent` as "the brand colour" breaks (see [[0014-form-focus-and-error-states-use-pale-accent]]). Some components bypass tokens with literal Tailwind palette colours (`text-red-500`, `bg-green-100`, `text-white`, `bg-black/60`) and so ignore dark mode.

The `--bw-*` values are also mirrored in the Vinculum iOS app (`Palette.swift`), so renaming them has a cost outside this repo.

## Decision
Proposed: one source of truth per meaning.
1. `theme.css` holds every token, light and dark, including `--bw-field-border`. The HiveLog skin only consumes.
2. `--bw-*` stays as the semantic layer shared with the app (names are the contract with `Palette.swift`). The shadcn names become aliases of it (`--primary: var(--bw-accent)`), so each value is written once.
3. Add the semantic tokens Tailwind components need and lack: `--success`, `--warning`, `--info`, each with a `-foreground` and a tint, so messages, pager and form errors never use a literal palette colour.
4. A literal colour in a template or component is a review failure, except for overlays on photographs (`bg-black/*` in the hero and CTA) and the video control scrim.

## Consequences
- Positive: dark mode is correct by construction; a site can re-skin from one copied file; the pale-tint trap disappears if `--accent` is renamed (e.g. `--accent-tint`) in the same change.
- Negative / trade-offs: renaming `--accent` is a breaking change for any site that copied `theme.css`; needs a minor release note and a mirrored change in the app.
- Follow-up tasks: [[0022-token-consolidation]], [[0016-status-messages-bypass-tokens-and-dark-mode]], [[0015-pager-current-page-dark-mode-and-semantics]]
