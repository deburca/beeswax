---
type: task
tags: [beeswax/task]
status: backlog
priority: low
project: "[[theme-health-review]]"
area: tests
created: 2026-11-13
branch: feature/0013-php-quality-tests-and-ignored-templates
release:
depends-on:
blocked-by:
---
# Task: PHP quality checks, tests and ignored templates

## Context
Left open by [[0006-page-preprocess-cost-and-hook-cleanup]] and [[0007-build-reproducibility-ci-and-checks]], both shipped in 1.9.1. CI now runs the format check, the build check and `php -l` on `src/**/*.php`, but there is still no static analysis, coding-standard check or PHP test, and two templates sit in `.prettierignore` because they break the rules in `AGENTS.md`.

## Open items
- **Coding standard and static analysis:** add phpcs (Drupal standard) and, if it fits a theme with one class, phpstan to CI. `src/Hook/ThemeHooks.php` is the only PHP, plus `src/RenderCallbacks.php`.
- **A kernel test** for `footerMeta()` and `preprocessPage()` (`rendered_by_canvas`, the cache tags and the New Year max-age added in 0006, the app label and versions switch from 0009). A theme has no test namespace by default; decide whether the tests live in the theme or in a test module, and how CI gets a Drupal to run them against.
- **Constructor weight:** `ThemeHooks` injects 11 services, several used by one method (`TitleResolverInterface`, the breadcrumb builder and the route match, which Drupal's block system could supply). Trim, or split footer code into its own class.
- **Two templates ignored by Prettier:** `templates/field/field--node--field-content.html.twig` and `templates/navigation/menu--main.html.twig` fail to parse because Twig tags sit inside HTML tags, which `AGENTS.md` forbids. Rewrite them to follow the rules (no conditionals inside tags, no split tags), remove them from `.prettierignore`, and check the rendered output is unchanged on a page that uses each.
- **`templates/layout/page.html.twig`** is also ignored (Prettier would reflow its inline icon spans). Revisit when the footer markup next changes; the credit line there already uses `|replace` and `|raw` (0009), so its output deserves a test.
- **`CUSTOMIZING.md`/`AGENTS.md`:** say how to run whatever checks are added.

## Acceptance criteria
- [ ] phpcs (and phpstan if adopted) pass locally and run in `.github/workflows/ci.yml`
- [ ] At least one test covers `footerMeta()` and the `preprocessPage()` cache metadata, and runs in CI or has documented steps to run
- [ ] The two ignored templates follow the `AGENTS.md` rules and are no longer in `.prettierignore`, with their rendered output checked
- [ ] Constructor dependencies reduced, or a note says why they stay
- [ ] Release note written

## Implementation notes
- Key files: `src/Hook/ThemeHooks.php`, `src/RenderCallbacks.php`, `.github/workflows/ci.yml`, `.prettierignore`, `AGENTS.md`, the two templates above.
- Keep CI fast: it is about 15 seconds now. A Drupal-based test job is much heavier; consider running it only on PHP changes.
- Needs a Drupal site or a composer-built test harness; neither exists in the repo.

## Related
- Project:: [[theme-health-review]]
- Follows:: [[0006-page-preprocess-cost-and-hook-cleanup]], [[0007-build-reproducibility-ci-and-checks]]
