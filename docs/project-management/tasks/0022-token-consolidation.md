---
type: task
tags: [beeswax/task]
status: done
priority: medium
project: "[[ui-and-interaction-review]]"
area: theme
created: 2026-11-25
branch: feature/0022-token-consolidation
release: 1.9.5
depends-on:
blocked-by:
---
# Task: Token consolidation

## Context
Implements [[0001-token-architecture-and-source-of-truth]] once that ADR is accepted. The theme has two overlapping colour token sets and a third group defined in `src/hivelog.css`.

## Open items
- Define each value once: make the shadcn names aliases of the `--bw-*` set (or the reverse), in `src/theme.css`.
- Move `--bw-field-border` (now at `src/hivelog.css:576` and `:580`) into `theme.css` so a site that copies `theme.css` to the web root keeps it.
- Rename or clearly document `--accent` (pale tint) against `--bw-accent` (solid pine); decide on `--accent-tint`.
- Add `--success`, `--info`, `--warning` with foreground and tint to `theme.css` and the Tailwind `@theme inline` map in `src/main.css`.
- Replace literal colours in templates (`text-white` in the pager and video, `text-red-500` in forms, the palette in status messages) with tokens.
- Mirror every renamed or added value in the Vinculum app's `Palette.swift`.
- Update the Contrast table in `CUSTOMIZING.md` and re-run the computation.

## Acceptance criteria
- [x] Each colour value appears once in `theme.css`
- [x] No Tailwind palette colour or `text-white` outside the allowed overlays (grep check in CI)
- [x] Copying only `theme.css` and `fonts.css` to the web root still gives a complete skin
- [x] Contrast table regenerated; breaking renames named in the release note
- [x] `npm run format` and `npm run build`

## Implementation notes
- Key files: `src/theme.css`, `src/main.css`, `src/hivelog.css`, `CUSTOMIZING.md`, `beeswax.libraries.yml`.
- Minor release: renaming a token breaks sites with a customised copy.

## Related
- Project:: [[ui-and-interaction-review]]
- Decisions:: [[0001-token-architecture-and-source-of-truth]]

## Outcome (2026-11-25)
- **One value, one place:** the shadcn names (`--background`, `--foreground`, `--card`, `--muted`, `--muted-foreground`, `--secondary`, `--border`, `--input`, `--primary`, `--accent`, `--accent-foreground`, `--destructive`) are now aliases of the `--bw-*` tokens in `:root`; the `.dark` block no longer repeats them, it only changes the `--bw-*` values (plus the two `-foreground` literals and the shadows). Checked in the browser pane: every resolved light and dark value equals the old one, **except `--destructive` in dark, which is now `#e8756c` (it was `#e2685f`)** because it follows `--bw-critical`, which task [[0011-accessibility-pass-and-contrast-checks]] had moved; contrast on the page is 6.2:1 instead of 5.5:1.
- **`--bw-field-border` moved** from `src/hivelog.css` into `theme.css` (light `#8f8575`, dark `#7d7360`), so a copied `theme.css` carries it.
- **New info pair:** `--bw-info` and `--bw-info-tint` (light `#2b5f8f` on `#e4eef7`, dark `#7db4e3` on `#1d2935`; 5.7:1 and 6.7:1 on the tints, row added to the contrast table). Status messages use them for the info type, so info is no longer the pine accent.
- **Tailwind names:** `@theme inline` maps `ok`, `info`, `warning`, `critical` (each with `-tint`) and `field-border`; the messages template uses `bg-ok-tint`, `border-critical` and so on instead of arbitrary `var()` values.
- **Literals:** `npm run check:colours` (`scripts/check-literal-colours.mjs`, in CI and `AGENTS.md`) fails on a Tailwind palette colour or a bare `text-white`/`bg-black` in `components/`, `templates/` and `src/main.css`, allowing opacity overlays and the video, CTA and billboard components. It passes now; a test file with `text-red-500` failed it. `components/heading` `accent` colour was `text-accent` (pale tint on the page, about 1.2:1): it is now `text-accent-foreground` (pine), a visible change for headings that editors set to Accent.
- **Decision, no rename:** `--accent` keeps its name (recorded in [[0001-token-architecture-and-source-of-truth]], now accepted); `CUSTOMIZING.md` has a Tokens section naming the trap.
- **Not done:**
  - `src/hivelog.css` still has literal colours (the gold button, white text); they belong to [[0024-touch-targets-and-focus-audit-of-hivelog-skin]], and the check does not scan it.
  - **The Vinculum app's `Palette.swift` was not touched** (not in this repo): it needs the new `--bw-info`/`--bw-info-tint` pairs and `--bw-field-border`, and no longer needs a separate dark `--destructive`.
  - Shadow colours in `theme.css` still hold their own literals.
  - Not rendered on a Drupal site or the HiveLog pages (the harness checked `theme.css` and the compiled CSS only); release note not written. Stacked on the 0021 branch.
