---
type: task
tags: [beeswax/task]
status: review
priority: medium
project: "[[ui-and-interaction-review]]"
area: theme
created: 2026-11-25
branch: feature/0020-landmarks-breadcrumb-and-page-structure
release:
depends-on: 
blocked-by:
---
# Task: Landmarks, breadcrumb and page structure

## Context
From reading `templates/layout/page.html.twig`, `templates/navigation/breadcrumb.html.twig` and `templates/misc/page-title.html.twig` for [[ui-and-interaction-review]]. Task [[0011-accessibility-pass-and-contrast-checks]] saw the duplicated breadcrumb and title and put them down to the demo site's block layout; the template is part of the cause.

## Problems found
- **Redundant ARIA roles:** `role="banner"`, `role="main"`, `role="complementary"` and `role="contentinfo"` on `<header>`, `<main>`, `<aside>` and `<footer>`, and `role="navigation"` on the pager `<nav>`. `<header>` inside `<main>` or sectioning would lose its banner role; it is better to drop the attributes.
- **The `<h1>` sits in the header**, outside `<main>`, together with the breadcrumb: the page title is not in the main landmark, and "Skip to main content" skips it.
- **Empty skip target:** `<a id="main-content" tabindex="-1"></a>` inside `<main>`; put the id and `tabindex="-1"` on `<main>` itself so focus lands on the landmark.
- **Breadcrumb is rendered twice** when a site also places the breadcrumb block in `page.breadcrumb`: the page template prints `{{ breadcrumb }}` in the header and `{{ page.breadcrumb }}` below it. Pick one, and document which.
- **Breadcrumb strings:** `<nav aria-label="breadcrumb">` and `<h2 class="sr-only">{{ 'Breadcrumb' }}</h2>` are not passed through `|t`, name the landmark twice and the `h2` pollutes the heading outline; keep one translatable `aria-label`.
- **Missing URL leaves a stray chevron:** a middle crumb with no URL is skipped, but its separator is still rendered (two chevrons in a row).
- **The ellipsis hides crumbs from everyone**; there is no way to reveal them (a `<details>` would do).
- **Footer small print:** the credit uses `|raw` on assembled markup (see [[0009-footer-hardcoding-and-translations]]); fine, but a test should pin it.

## Acceptance criteria
- [x] No redundant roles; one `<main>` containing the page title and `id="main-content"`
- [x] Skip link lands on `<main>` and the next Tab goes to its first control
- [x] One breadcrumb per page; one translatable name for the landmark
- [x] No double separators; hidden crumbs reachable, or a note on why not
- [x] `npm run format` and `npm run build`
- [ ] Rendered check on a front page, a Canvas page and a HiveLog list
- [ ] Release note written

## Implementation notes
- Key files: `templates/layout/page.html.twig`, `templates/navigation/breadcrumb.html.twig`, `templates/navigation/pager.html.twig`, `templates/misc/page-title.html.twig`.

## Related
- Project:: [[ui-and-interaction-review]]

## Outcome (2026-11-25)
- **`page.html.twig`:** `role` attributes are gone from `<header>`, `<aside>` and `<footer>` (`<main>` too); the empty `<a id="main-content">` is replaced by `id="main-content" tabindex="-1"` on `<main>`, which the skip link already targets; `main[tabindex="-1"]:focus` has no outline so focusing it does not ring the page.
- **The `<h1>` is inside `<main>`.** The breadcrumb and title moved from `<header>` into a `.beeswax-page-head` wrapper at the top of `<main>` (same `container` classes). **Visual order changes:** they now come after `page.primary_menu`, `secondary_menu`, `breadcrumb`, `highlighted` and `help`, where they used to sit directly under the navbar. Sites that fill those regions will see the title below them; Canvas pages (`rendered_by_canvas`) draw no head, as before.
- **One breadcrumb:** the page template prints the `breadcrumb` variable only when the `Breadcrumb` region is empty, so a site that places the block there gets exactly one.
- **`hivelog.css`:** four selectors that matched `header[role="banner"] > .container` and `footer[role="contentinfo"]` now match `.beeswax-page-head` and `footer`, so the narrowed sign-in and profile head and the empty footer row still apply. Any site CSS written against those role selectors needs the same change; name it in the release note.
- **Breadcrumb:** the `nav` label is `aria-label="{{ 'Breadcrumb'|t }}"` and the duplicate sr-only `<h2>` is removed; a crumb with no URL no longer leaves its separator (`is_drawn`). The pager's redundant `role="navigation"` went in [[0015-pager-current-page-dark-mode-and-semantics]].
- **Ellipsis: decision, left as is.** The crumbs a long trail folds away (more than four items) stay hidden. The first two and last two cover the way home, the section and the current page; a disclosure would need a different markup for the list and no site here has a trail that deep.
- Not done: the credit-line test (needs the kernel test harness from [[0013-php-quality-tests-and-ignored-templates]]); the Twig was not rendered on Drupal (Prettier parsed it), so the three-page render check and the skip-link check on a real page are open; release note not written. Stacked on the 0019 branch.
