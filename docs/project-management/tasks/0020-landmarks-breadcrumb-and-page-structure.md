---
type: task
tags: [beeswax/task]
status: backlog
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
- [ ] No redundant roles; one `<main>` containing the page title and `id="main-content"`
- [ ] Skip link lands on `<main>` and the next Tab goes to its first control
- [ ] One breadcrumb per page; one translatable name for the landmark
- [ ] No double separators; hidden crumbs reachable, or a note on why not
- [ ] Rendered check on a front page, a Canvas page and a HiveLog list; `npm run format` and `npm run build`; release note written

## Implementation notes
- Key files: `templates/layout/page.html.twig`, `templates/navigation/breadcrumb.html.twig`, `templates/navigation/pager.html.twig`, `templates/misc/page-title.html.twig`.

## Related
- Project:: [[ui-and-interaction-review]]
