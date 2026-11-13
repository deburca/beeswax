---
type: task
tags: [beeswax/task]
status: review
priority: low
project:
area: theme
created: 2026-11-13
branch: feature/0002-honeycomb-background
release: 1.9.0
depends-on:
blocked-by:
---
# Task: Honeycomb background, as in the Vinculum iOS app

## Context
The Vinculum iOS app (`../vinculum`) draws a faint honeycomb behind its welcome screens, empty states and the top of
most list and detail screens (`Sources/HiveLogUI/Design/HoneycombBackground.swift`, used from `Views.swift` and the
`Field/*Views.swift` files; the README lists it under "Hexagons"). The website has the hexagon as its mark and as the
header menu's cell shape, but no background pattern, so the app and the site feel less alike than they could. This task
re-implements the honeycomb for the theme. It is feasible in plain CSS, with no JavaScript and no new assets.

### What the app does (the reference)
- A grid of **pointy-top hexagon outlines** (a point at the top and bottom), drawn with a 1 pt stroke in the accent
  colour at **10% opacity**. No fill.
- Geometry, from a single `cell` value (default **38 pt**, the hexagon's circumradius): each hexagon is drawn at
  `0.96 * cell` so neighbouring outlines leave a hairline gap; columns are `cell * sqrt(3)` apart (about 65.8), rows are
  `cell * 1.5` apart (57), and every second row is shifted half a column.
- **Fades out downwards**: a vertical mask from fully visible at the top to transparent at the bottom of the frame
  (the frame is 600 pt on list and detail screens, 380 pt on the welcome screens). Anchored to the top of the screen,
  so it scrolls away with the content on a web page, which is the equivalent.
- Behind everything: it takes no taps and is hidden from VoiceOver.
- Vinculum task `0045-honeycomb-background-overlap-on-short-devices` records the lesson: the fade must finish before the
  first interactive content, or titles look crowded against the pattern.

## Proposed approach
- **Pure CSS pseudo-element.** A `::before` on the page container (or `body`) of the pages in scope: `position: absolute`,
  `inset: 0 0 auto 0`, `height: 600px` (shorter, about 380px, on the sign-in card pages), `pointer-events: none`, behind
  the content (`z-index` below the layout, with `isolation: isolate` on the wrapper so it cannot slip behind the page
  background).
- **Pattern as an SVG mask, colour from a token.** A data-URI SVG tile (two rows, about 65.8 x 114 px at `cell` 38, with
  the hexagon outlines drawn as strokes) used as `mask-image`, combined by `mask-composite: intersect` with a
  `linear-gradient(black, transparent)` for the fade; `background-color: var(--bw-accent)` and `opacity: 0.1` (or
  `color-mix`). Because the colour is a CSS variable and not baked into the SVG, light and dark follow the existing
  `--bw-accent` swap for free, and a site that copies `theme.css` to recolour the theme (see `CUSTOMIZING.md`) gets a
  matching honeycomb.
- **Tokens.** `--bw-honeycomb-cell` (38px) and `--bw-honeycomb-opacity` (0.1) in `src/theme.css`, so a site can resize
  or hide it (`--bw-honeycomb-opacity: 0`). The SVG tile's size cannot use a variable inside a data URI, so either keep
  the cell fixed and scale with `mask-size`, or generate the tile at the default and document that.
- **Where it goes.** `src/hivelog.css`, which already scopes by `body.hivelog-page`, `body.path-user` and
  `body:has(.beeswax-auth-form)`. Confirm that file is loaded on every page in scope (see `beeswax.libraries.yml`).
- **Print and forced colours.** Hide it under `@media print`; it is decorative, so no `forced-colors` handling is needed
  beyond not carrying meaning.

## Decisions (settled)
Scope agreed: HiveLog pages, account pages and sign-in cards only; the 380pt fade on sign-in cards; 10% opacity in both modes (checked by eye on the dark ground).

## Decisions as first raised
- **Scope.** Which pages get it: only the HiveLog pages, account pages and sign-in cards (matches the app and keeps
  Canvas/marketing pages and the front page's hero untouched), or every page? Recommended: the first.
- **Stronger on sign-in cards?** The app uses a shorter, 380 pt fade on its welcome screens; the sign-in card pages are
  the web equivalent.
- **Opacity on dark.** 10% accent on the dark ground may need checking by eye; the app uses the same value in both modes.

## Acceptance criteria
- [x] A honeycomb of pointy-top hexagon outlines appears behind the content of the in-scope pages, in the accent colour at about 10% opacity, fading to nothing before the first interactive content
- [x] The pattern's geometry matches the app's (column spacing `cell * sqrt(3)`, row spacing `cell * 1.5`, odd rows offset by half a column, outlines at 0.96 of the cell)
- [x] It takes no clicks or focus, adds no scrollbars or horizontal overflow, and is not announced by screen readers (pseudo-element, no content)
- [x] Light, dark and *Match the device* all look right; text contrast of headings and body text over the pattern is unchanged (the pattern is too faint to count, but check the area just under the header)
- [x] Phone, tablet and desktop widths: it covers the width, and the fade does not crowd the page title on a short screen (checked at 375px and 1024px)
- [x] Printing hides it
- [x] `CUSTOMIZING.md` documents the tokens and how to turn it off; the geometry note (cell, 0.96, 10%) is kept in sync with Vinculum's `HoneycombBackground.swift`, as the dark palette already is
- [x] Release note written for the version that ships this

## Implementation notes
- Key files: `src/hivelog.css` (the rule and its scoping), `src/theme.css` (tokens), `templates/layout/page.html.twig` (only if the page wrapper needs a class or `isolation`), `CUSTOMIZING.md`.
- Reference: `../vinculum/Sources/HiveLogUI/Design/HoneycombBackground.swift` and `Hexagon.swift` (`HexagonShape`, corners at angle `index * 60deg - 90deg`).
- Hexagon points for an SVG tile at cell `r` = 38 and drawn radius `0.96 r`: centre `(cx, cy)`, corners `(cx, cy - R)`, `(cx + R*sqrt(3)/2, cy - R/2)`, `(cx + R*sqrt(3)/2, cy + R/2)`, `(cx, cy + R)`, `(cx - R*sqrt(3)/2, cy + R/2)`, `(cx - R*sqrt(3)/2, cy - R/2)`.
- Run `npm run build` per this repo's conventions. Do not run `npm run format` over the whole repo: it reformats about 35 unrelated files; format only the files touched.
- Check the visual result in the browser pane in light and dark, at phone and desktop widths, on a HiveLog page and a sign-in page.

## Related
- Project::
- Decisions::
- Commits::
