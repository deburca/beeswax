---
type: task
tags: [beeswax/task]
status: backlog
priority: medium
project: "[[theme-health-review]]"
area: php
created: 2026-11-13
branch: feature/0006-page-preprocess-cost-and-hook-cleanup
release:
depends-on:
blocked-by:
---
# Task: preprocess_page cost and hook cleanup

## Context
`src/Hook/ThemeHooks.php` is the only PHP in the theme. A few things in it cost time on every request or are loose.

## Problems found
- **An entity query on every node page.** For `entity.node.canonical` with the Canvas module installed, `preprocessPage()` runs a `content_template` count query (`accessCheck(FALSE)`) on every view to set `rendered_by_canvas`. Memoise it per bundle for the request and/or cache it with the right cache tag so it is not a database query on each uncached render; check the render array carries cache metadata for it.
- **`assert($node instanceof NodeInterface)`** is compiled out on production (assertions off), so a missing or non-node parameter would reach `$node->getType()` and fatal. Use a real guard.
- **Cache metadata for the footer.** `footerMeta()` reads theme settings, the installed HiveLog version, the site name and the current year (`date('Y')`) into `$variables['footer_meta']` with no cache metadata of its own (the menu render array carries its own). Theme-setting saves flush the render cache, so this mostly works, but the year can be stale on a cached page across 1 January, and the site-name config is not a declared dependency. Add the config cache tags.
- **Mixed translation helpers:** `themeSettingsFormAlter()` uses the global `t()` while the class uses `StringTranslationTrait`; use `$this->t()` throughout.
- **Settings form shows an info message each time it is built** (`messenger()->addMessage(...)` in `form_system_theme_settings_alter`), including when the form is rebuilt after validation: it can pile up. Prefer a form description.
- **Cosmetic:** two stray blank lines before the closing brace; the constructor injects 11 services, several used by one method only (`TitleResolver`, `Breadcrumb`, `RouteMatch` for the title/breadcrumb that Drupal's own block system could supply).
- `RenderCallbacks::preRenderComponent()` exists only to turn a UNIX timestamp into a date string for `hero-blog`; if that component is removed (task 0010) this class and the `element_info_alter` hook go with it.

## Acceptance criteria
- [ ] No uncached entity query per node view (verify with the database log or Devel query count on a node page)
- [ ] No `assert()` guarding a dereference
- [ ] `footer_meta` and `rendered_by_canvas` carry correct cache metadata
- [ ] One translation style; no accumulated info messages on the settings form
- [ ] Release note written

## Implementation notes
- Key file: `src/Hook/ThemeHooks.php` (`preprocessPage`, `footerMeta`, `themeSettingsFormAlter`), `src/RenderCallbacks.php`.
- There are no PHP tests in the repo (see task 0007); a kernel test for `footerMeta()` and `preprocessPage()` would be the natural first one.

## Related
- Project:: [[theme-health-review]]
