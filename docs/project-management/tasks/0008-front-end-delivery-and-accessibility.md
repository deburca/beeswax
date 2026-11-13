---
type: task
tags: [beeswax/task]
status: backlog
priority: medium
project: "[[theme-health-review]]"
area: theme
created: 2026-11-13
branch: feature/0008-front-end-delivery-and-accessibility
release:
depends-on:
blocked-by:
---
# Task: Front-end delivery and accessibility

## Context
How the stylesheets and scripts reach the browser, and a few accessibility gaps, found during the review of 2026-11-13.

## Problems found
- **Unminified, unaggregated CSS on every page.** `src/hivelog.css` (46 KB, about 1,450 lines), `src/theme.css` and `src/fonts.css` are all `preprocess: false` and loaded site-wide (see the comment in `beeswax.libraries.yml`: `libraries-extend` does not reach the entity list pages, so it is global). They arrive as three extra requests, unminified, on pages that have no HiveLog markup (front page, Canvas pages). `preprocess: false` was chosen for hot-editing; only `theme.css` and `fonts.css` need that (they are meant to be copied). Option: build and minify `hivelog.css` into `build/`, or let Drupal aggregate it.
- **Inline script and CSP.** The *Match the device* script in `templates/layout/html.html.twig` is inline. A site with a Content-Security-Policy that forbids inline script will silently lose dark mode (the page stays light). Document it, or serve it as a tiny library file loaded early.
- **Reduced motion:** the honeycomb is static, but other transitions (message dismissal, group animations, hover transitions in `hivelog.css`) have no `prefers-reduced-motion` rule (see task 0003 for the group animations).
- **Focus and contrast spot checks not done:** `--bw-ok` was checked (task 0001); the other tokens, the Tom Select controls and the vertical tabs have not been checked for 4.5:1 text and 3:1 non-text contrast in dark mode. Run an automated pass (axe or Lighthouse) on a HiveLog list page, a form with vertical tabs, and the sign-in card, in light and dark.
- **Vendored Tom Select** (v2.4.3, 50 KB min + 7.6 KB CSS) loads only where a HiveLog filter or entity form renders: good. Record the upgrade procedure (where it came from, licence file) in `CUSTOMIZING.md` or a `lib/vendor/README`.
- **`!important` and specificity:** `hivelog.css` contains two `!important`s and several `body.hivelog-page` + `:is(...)` chains that exist to beat the module or Tailwind; list them with the reason so they can be retired as HiveLog changes.

## Acceptance criteria
- [ ] Decision recorded on how `hivelog.css` is delivered (minified build output, aggregation, or as is) and applied
- [ ] Dark-mode script works under a strict CSP, or the limitation is documented
- [ ] Axe/Lighthouse accessibility pass on three representative pages in light and dark, with failures fixed or ticketed
- [ ] `prefers-reduced-motion` honoured by every transition the theme adds
- [ ] Release note written

## Implementation notes
- Key files: `beeswax.libraries.yml`, `templates/layout/html.html.twig`, `src/hivelog.css`, `src/theme.css`, `lib/vendor/tom-select/`.
- Measure before and after (request count, transferred bytes) on a HiveLog page.

## Related
- Project:: [[theme-health-review]]
