---
type: task
tags: [beeswax/task]
status: backlog
priority: medium
project: "[[ui-and-interaction-review]]"
area: theme
created: 2026-11-25
branch: feature/0022-token-consolidation
release:
depends-on: ["[[0014-form-focus-and-error-states-use-pale-accent]]"]
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
- [ ] Each colour value appears once in `theme.css`
- [ ] No Tailwind palette colour or `text-white` outside the allowed overlays (grep check in CI)
- [ ] Copying only `theme.css` and `fonts.css` to the web root still gives a complete skin
- [ ] Contrast table regenerated; breaking renames named in the release note
- [ ] `npm run format` and `npm run build`

## Implementation notes
- Key files: `src/theme.css`, `src/main.css`, `src/hivelog.css`, `CUSTOMIZING.md`, `beeswax.libraries.yml`.
- Minor release: renaming a token breaks sites with a customised copy.

## Related
- Project:: [[ui-and-interaction-review]]
- Decisions:: [[0001-token-architecture-and-source-of-truth]]
