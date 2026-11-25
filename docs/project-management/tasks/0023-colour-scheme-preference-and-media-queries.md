---
type: task
tags: [beeswax/task]
status: backlog
priority: low
project: "[[ui-and-interaction-review]]"
area: theme
created: 2026-11-25
branch: feature/0023-colour-scheme-preference-and-media-queries
release:
depends-on: 
blocked-by:
---
# Task: Colour scheme preference and media queries

## Context
Gaps in how the theme responds to the visitor's settings, found for [[ui-and-interaction-review]] while reading `templates/layout/html.html.twig`, `lib/scheme-auto.js` and the CSS.

## Problems found
- **Match the device is script-only:** with JavaScript off, or before the script runs under a strict CSP that blocks the library, the page is light even for a dark-mode device (documented in `CUSTOMIZING.md`). A `prefers-color-scheme` media query for the tokens, with the class as an override, would not need a script for the first paint.
- **No visitor toggle:** a visitor cannot choose light or dark independently of the device; only the site setting does. Decide whether one is wanted.
- **`<meta name="color-scheme">` and `theme-color`** are emitted only for `auto`; a site fixed to *Dark* gets a light browser chrome, light scrollbars before CSS loads and a white flash.
- **No `forced-colors` or `prefers-contrast` rules:** `outline-none`, background-only states and the 10%-opacity honeycomb are lost or ignored in Windows High Contrast mode. Print styles exist only for the HiveLog skin (`src/hivelog.css`), so a Canvas page prints with its navbar and animations.
- **Scroll reveal** relies on CSS hiding elements until the script runs; the reduced-motion and no-JS fallbacks already exist (`src/motion.js`), but check them with the *Dark* scheme.

## Acceptance criteria
- [ ] First paint matches the device's scheme without JavaScript, or the limitation is stated in the setting's description
- [ ] Decision on a visitor toggle recorded (build it, or note why not)
- [ ] `color-scheme` meta and `theme-color` follow the chosen scheme
- [ ] A forced-colors check on the navbar, buttons, form fields and pager, with fixes
- [ ] A minimal print stylesheet for non-HiveLog pages, or a decision not to
- [ ] `npm run format` and `npm run build`; release note written

## Implementation notes
- Key files: `templates/layout/html.html.twig`, `lib/scheme-auto.js`, `src/theme.css`, `src/main.css`, `CUSTOMIZING.md`.

## Related
- Project:: [[ui-and-interaction-review]]
