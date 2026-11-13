---
type: task
tags: [beeswax/task]
status: review
priority: low
project:
area: theme
created: 2026-11-13
branch: feature/0001-hivelog-ok-tokens
release: 1.9.0
depends-on:
blocked-by:
---
# Task: Map the HiveLog `ok` tokens

## Context
HiveLog task 0256 (in the hivelog repository) added an `ok` sub-line variant to its stat tile, for positive states
(an inspection's "Clear" disease status, a done action, a positive net). It comes with two new tokens in HiveLog's
`css/hivelog.responsive.css`, `--hivelog-ok` and `--hivelog-ok-tint`, whose defaults are a neutral green
(`#1f6b3a` and `#e4f2e9`). Beeswax already redefines HiveLog's `--hivelog-*` surface and severity tokens from its own
`--bw-*` palette inside `body.hivelog-page` in `src/hivelog.css` (critical and warning today). Until it does the same
for `ok`, a Beeswax site shows HiveLog's default green, which does not match the theme and has no dark-mode value
(HiveLog has no dark mode; Beeswax sets one under `.dark`).

The first HiveLog page to emit the `ok` variant is the inspection page (hivelog task 0259, disease signs "None" shows
"Clear"), so this is visible as soon as that HiveLog release is installed. HiveLog tasks 0258, 0261 and 0262 will add
more. Needs the HiveLog release that contains task 0256.

## Acceptance criteria
- [x] `src/hivelog.css` maps `--hivelog-ok` and `--hivelog-ok-tint` in the `body.hivelog-page` block, beside critical and warning
- [x] `src/theme.css` defines `--bw-ok` and `--bw-ok-tint`, light and dark (`.dark`), rather than aliasing `--bw-accent`: "ok" is a status and must stay distinguishable from the brand colour on a tile that also has accent-coloured links
- [x] Text contrast of `--bw-ok` on `--bw-surface`, and of the sub-line on the tile, is at least 4.5:1 in both modes
- [ ] The `ok` sub-line renders on a stat tile in light and dark (an inspection with disease signs "None" on a HiveLog site shows one)
- [x] The minimum HiveLog version that introduced the tokens is stated where the theme documents its HiveLog expectations (`CUSTOMIZING.md`, and the release note)
- [x] Release note written for the version that ships this

## Implementation notes
- Key files: `src/theme.css` (palette: light block near `--bw-accent`, dark block under `.dark`), `src/hivelog.css` (token mapping, `body.hivelog-page` block), `CUSTOMIZING.md`.
- Existing palette for reference: accent `#2f6b4f` (dark `#63b389`), accent-tint `#e6efe8`, warning `#a4630a`, critical `#b3261e`. The accent is already a green, so choose an `ok` hue that reads apart from it (a yellower or clearer green) or justify reusing it.
- Run `npm run build` per this repo's conventions if the compiled stylesheet is committed.

## Related
- Project::
- Decisions::
- Commits::
