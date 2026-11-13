---
type: task
tags: [beeswax/task]
status: backlog
priority: low
project: "[[theme-health-review]]"
area: housekeeping
created: 2026-11-13
branch: feature/0010-dead-assets-and-stale-metadata
release:
depends-on:
blocked-by:
---
# Task: Dead assets and stale metadata

## Context
Files and metadata that nothing uses, or that still describe the theme this was forked from. Found by checking every asset name against the rest of the repo on 2026-11-13.

## Findings
- **Unreferenced files:** `components/footer/assets/starshot_ds.svg` (12 KB, no references anywhere) and `components/navbar/assets/logo-light.svg` (no references).
- **Duplicates:** `logo-light.svg` and `logo-dark.svg` in the theme root are byte-identical to the copies in `components/navbar/assets/` (two 6.8 KB files, so the logo is stored four times; only `logo-dark.svg` in the root is referenced, by `beeswax.info.yml`, and the navbar's `logo-dark.svg` by its component). The three `luke-chesser-...jpg` hero images (85 KB each) in `hero-billboard`, `hero-blog` and `hero-side-by-side` are identical. Keep one copy and reference it.
- **Demo images** shipped with the theme: `pexels-felix-mittermeier-1209650.jpg` (247 KB), `jj-ying-...jpg` (241 KB), `portrait.png` (25 KB), `card-logo/assets/dark.png`. They are example content for Canvas components; if they are only used as `component.yml` examples they could be smaller or fetched.
- **`screenshot.png` is 400 KB** (the theme's screenshot for the Appearance page); it can be optimised to a fraction of that.
- **`icons/phosphor/` is 5.9 MB, 1,514 SVGs**, used through `beeswax.icons.yml` (Drupal icon API) and the footer (`heart-fill`, `sparkle-fill`) plus whatever Canvas components reference. It is the largest part of the repo; keep it if editors pick icons from the full set, but note it.
- **The 25 Canvas components** (`components/`, about 1 MB with assets) come from Mercury. Find out whether any site uses them (see the project's open question). Unused ones are maintenance weight (each has a `.component.yml`, Twig and sometimes JS); `RenderCallbacks.php` exists only for `hero-blog`.
- **Stale metadata:** `generator: mercury:1.0.5` in `beeswax.info.yml`; `composer.json` description ("A scalable, modern Drupal CMS prototype theme...") and `README.md` (talks about Canvas and Mercury workarounds, says nothing about HiveLog or the account pages); `package.json` version `1.0.3` (see task 0007).
- **`conflict: drupal/experience_builder` and `require-dev: drupal/canvas`** in `composer.json` carry the Canvas assumption; if Canvas is optional for HiveLog sites, say so in the README.

## Acceptance criteria
- [ ] Unreferenced files removed; duplicate images collapsed to one copy each
- [ ] `screenshot.png` optimised
- [ ] Decision recorded on the Canvas components (keep, trim to the used ones, or move to a separate branch) and applied
- [ ] README and `composer.json` describe the theme as it is today (HiveLog skin, account pages, footer, honeycomb), `generator` removed or corrected
- [ ] Release note written

## Implementation notes
- To find what a site uses, search its content templates and Canvas pages for `beeswax:<component>` rather than the repo.
- Do not delete `icons/phosphor/` files without checking `beeswax.icons.yml` consumers; removing icons breaks any saved content that references them.
- Removing a component is a breaking change for a site that has content using it: do it in a minor release with a note.

## Related
- Project:: [[theme-health-review]]
