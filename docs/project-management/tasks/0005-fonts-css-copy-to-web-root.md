---
type: task
tags: [beeswax/task]
status: backlog
priority: medium
project: "[[theme-health-review]]"
area: theme
created: 2026-11-13
branch: feature/0005-fonts-css-copy-to-web-root
release:
depends-on:
blocked-by:
---
# Task: fonts.css breaks when copied to the web root

## Context
`CUSTOMIZING.md` tells a site to copy `src/theme.css` and `src/fonts.css` next to `index.php` to customise them, and `ThemeHooks::alterLibraryInfo()` then swaps the library entries for `/theme.css` and `/fonts.css` when those files exist. `src/fonts.css` points at its fonts with **relative URLs** (`url(../fonts/Fraunces/fraunces-latin.woff2)`, resolved from `src/`). Both entries are `preprocess: false`, so Drupal serves the file as is and does not rewrite relative URLs. From the web root `../fonts/...` no longer reaches the theme's `fonts/` directory, so the copied file would request fonts from the wrong place (or 404).

## Problems found
- Relative font URLs in the copied `fonts.css` (likely broken; confirm on a site that has copied it).
- `templates/includes/preload.twig` always preloads `fraunces-latin.woff2` and `ibm-plex-sans-latin.woff2` from the theme, even when the site's own `fonts.css` defines different fonts: a wasted request, and a preload that is not used (browser warning).
- The two preloaded files are 67 KB and 40 KB; the `latin-ext` files are never preloaded, which is right, but the Fraunces file loads on every page even where no serif is shown.
- `file_exists(self::$appRoot . '/theme.css')` runs on each `library_info_alter` for the beeswax extension; cheap, but the result is not cached with the library info beyond the normal library cache, so adding or removing the copy needs a cache clear (documented? say so).

## Acceptance criteria
- [ ] A copied `fonts.css` loads the theme's fonts correctly (use site-absolute URLs such as `/themes/contrib/beeswax/fonts/...` in the copy instructions, or have the hook rewrite the URLs, or ship the fonts path as a CSS variable)
- [ ] `CUSTOMIZING.md` explains the font paths for a copied file and the cache clear
- [ ] Preload follows the active font set (skip it, or make it a theme setting) so it never preloads files the page does not use
- [ ] Release note written

## Implementation notes
- Key files: `src/fonts.css`, `src/Hook/ThemeHooks.php` (`alterLibraryInfo`, `preprocessHtml` sets `beeswax_path`), `templates/includes/preload.twig`, `CUSTOMIZING.md`.
- Test on a real site: copy both files, clear caches, check the network panel for the `.woff2` requests.

## Related
- Project:: [[theme-health-review]]
