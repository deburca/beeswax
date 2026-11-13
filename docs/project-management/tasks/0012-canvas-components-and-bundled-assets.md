---
type: task
tags: [beeswax/task]
status: backlog
priority: low
project: "[[theme-health-review]]"
area: housekeeping
created: 2026-11-13
branch: feature/0012-canvas-components-and-bundled-assets
release:
depends-on:
blocked-by:
---
# Task: Canvas components and bundled assets

## Context
Left open when [[0010-dead-assets-and-stale-metadata]] shipped in 1.9.2. That task removed the files nothing referenced and refreshed the metadata, but the rest needs a decision about what sites actually use.

## Open items
- **The 25 Canvas components** in `components/` come from Mercury, about 1 MB with assets. Decide per component: keep, trim to the ones in use, or move to a separate branch. Find usage by searching each site's content templates and Canvas pages for `beeswax:<component>`, not the repo. Removing a component breaks saved content that uses it, so do it in a minor release with a note.
- **`RenderCallbacks.php` and the `element_info_alter` hook** exist only to turn a timestamp into a date string for `hero-blog`; they go if that component goes.
- **Duplicate hero images:** the three identical `luke-chesser-...jpg` files (85 KB each) in `hero-billboard`, `hero-blog` and `hero-side-by-side` are scoped to their components, so they cannot share one copy while they stay there. If the components stay, consider one shared image outside them, referenced by path.
- **Demo images:** `pexels-felix-mittermeier-1209650.jpg` (247 KB), `jj-ying-...jpg` (241 KB), `portrait.png` (25 KB) and `card-logo/assets/dark.png`. If they are only `component.yml` examples they can be made smaller.
- **`icons/phosphor/`:** 5.9 MB, 1,514 SVGs, used through `beeswax.icons.yml`. Keep if editors choose from the full set; otherwise trim after checking every consumer (removing an icon breaks content that references it).
- **`screenshot.png`** (96 KB now) still shows the Mercury logo and demo content. Retake it from a running Beeswax site (800x620 is the current size).
- **`conflict: drupal/experience_builder` and `require-dev: drupal/canvas`** in `composer.json` assume Canvas; the README now says Canvas is optional, so check these still fit.

## Acceptance criteria
- [ ] Decision recorded on the Canvas components (keep, trim or move) and applied, with the usage evidence
- [ ] Anything removed is named in the release note as a breaking change for content that used it
- [ ] Demo images and hero duplicates handled, or left with a reason
- [ ] `screenshot.png` shows the current theme
- [ ] Release note written

## Implementation notes
- Key files: `components/`, `src/RenderCallbacks.php`, `src/Hook/ThemeHooks.php` (`alterElementInfo`), `icons/phosphor/`, `beeswax.icons.yml`, `composer.json`, `screenshot.png`.
- Do not delete icons without checking `beeswax.icons.yml` consumers.

## Related
- Project:: [[theme-health-review]]
- Follows:: [[0010-dead-assets-and-stale-metadata]]
