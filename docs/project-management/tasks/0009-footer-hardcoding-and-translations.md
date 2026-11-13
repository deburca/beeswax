---
type: task
tags: [beeswax/task]
status: backlog
priority: low
project: "[[theme-health-review]]"
area: theme
created: 2026-11-13
branch: feature/0009-footer-hardcoding-and-translations
release:
depends-on:
blocked-by:
---
# Task: Footer hard-coded text and translations

## Context
The footer small print was added for the HiveLog sites (`footerMeta()` in `ThemeHooks.php`, markup in `templates/layout/page.html.twig`, settings in `config/`). Some of it is tied to one site or cannot be translated well.

## Problems found
- **"Get the Vinculum app" is hard-coded** in `footerMeta()` and in the `footer_app_url` setting description. A theme setting for a generic "app link" should have a configurable label (or the label should be a setting), so another site is not forced to say "Vinculum".
- **The credit line is assembled from fragments:** `{{ 'Made with'|t }} ... {{ 'by'|t }} ... {{ 'with a sprinkle of'|t }}`. Translators cannot reorder words across the heart and sparkle icons, which breaks the grammar in many languages. Use one translatable string with placeholders (`@name`, `@heart`, `@sparkle`).
- **Hidden text labels** for the icons are `love` and `AI`: the sparkle reads as "AI" to a screen reader, which may not be intended; make it a setting or a neutral label.
- The footer always renders (the versions string is never empty), so `{% if page.footer or footer_meta.versions %}` is always true; simplify, or let a site hide the versions line.
- `footer_copyright` uses `date('Y')` (see the cache note in task 0006).

## Acceptance criteria
- [ ] The app-link label is configurable (default neutral, e.g. "Get the app") and the Vinculum wording is only in a site's own settings
- [ ] The credit line is one translatable string with placeholders
- [ ] Screen-reader text for the icons reviewed
- [ ] `config/schema` and `CUSTOMIZING.md` updated for any new setting
- [ ] Release note written

## Implementation notes
- Key files: `src/Hook/ThemeHooks.php`, `templates/layout/page.html.twig`, `config/install/beeswax.settings.yml`, `config/schema/beeswax.schema.yml`, `CUSTOMIZING.md`, `src/hivelog.css` (`.beeswax-footer-meta`).
- Existing sites have the current settings stored; adding a setting needs a default that keeps their footer unchanged.

## Related
- Project:: [[theme-health-review]]
