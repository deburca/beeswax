---
type: task
tags: [beeswax/task]
status: review
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
- [x] First paint matches the device's scheme without JavaScript, or the limitation is stated in the setting's description
- [x] Decision on a visitor toggle recorded (build it, or note why not)
- [x] `color-scheme` meta and `theme-color` follow the chosen scheme
- [x] A forced-colors check on the navbar, buttons, form fields and pager, with fixes
- [x] A minimal print stylesheet for non-HiveLog pages, or a decision not to
- [x] `npm run format` and `npm run build`
- [ ] Release note written

## Implementation notes
- Key files: `templates/layout/html.html.twig`, `lib/scheme-auto.js`, `src/theme.css`, `src/main.css`, `CUSTOMIZING.md`.

## Related
- Project:: [[ui-and-interaction-review]]

## Outcome (2026-11-25)
- **First paint without JavaScript: stated, not solved.** Fixing it needs the dark values twice (a `prefers-color-scheme` block next to `.dark`, in `theme.css` and in the two `.dark` blocks of `hivelog.css`), or rewriting every token with `light-dark()`; both undo the single source of [[0001-token-architecture-and-source-of-truth]]. The setting's description now says that with JavaScript off the page stays light, and `CUSTOMIZING.md` repeats it. The script is still a file loaded in the head, so with JavaScript there is no flash.
- **Visitor toggle: decided not to build.** The scheme is a site setting; a per-visitor choice needs a cookie (or storage) and varies every cached page by it, and *Match the device* already follows the visitor's own setting.
- **`color-scheme` and `theme-color`:** `html.html.twig` always sends `<meta name="color-scheme">` (`light`, `dark` or `light dark`; an unknown setting reads as light) and a `theme-color` (two, with `prefers-color-scheme` media, for *Match the device*). The hex values are `--bw-ground` written in the template; noted in `CUSTOMIZING.md`.
- **Forced colours:** the button component (`forced-colors:border forced-colors:border-[ButtonText]`), `.form-submit` / `input.button` and the pager's current page (`border-[Highlight]`) get a border where the background used to be the only edge. Focus rings are real outlines and are kept by the browser. **Not tried on Windows**: only the compiled CSS and class names were checked.
- **Print:** `main.css` hides the skip link, navbar menu and hamburger, messages and video controls, and shows `[data-animation]` elements that were waiting for a scroll. `theme.css` has a print block that puts the light values back on a `.dark` page (checked in the browser pane by applying the block's rule outside print: the aliased `--background`, `--foreground`, `--primary` and the others all follow). This block repeats ten light values by design; its comment names them.
- **Scroll reveal with the *Dark* scheme:** unchanged and not re-checked; the reveal is scheme-independent CSS.
- Not run: phpcs (not installed here; the new string avoids the backslash escape 0013 flagged), a Drupal page, a real print preview or print-to-PDF. Release note not written. Stacked on the 0022 branch.
