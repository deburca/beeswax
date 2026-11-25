---
type: task
tags: [beeswax/task]
status: done
priority: high
project: "[[ui-and-interaction-review]]"
area: components
created: 2026-11-25
branch: feature/0017-navbar-mobile-menu-interaction
release: 1.9.5
depends-on:
blocked-by:
---
# Task: Navbar and mobile menu interaction

## Context
Reading `components/navbar/navbar.js`, `navbar.twig` and `templates/navigation/menu*.twig` for [[ui-and-interaction-review]]. The mobile menu is a full-screen overlay that locks page scroll, but it does not meet [[0003-interactive-component-accessibility-contract]].

## Problems found
- **Toggle state is not exposed:** the hamburger has no `aria-expanded` or `aria-controls`; the menu has no `id`.
- **No Escape to close**, no focus trap (Tab walks out of the overlay into the page behind it) and **focus is not returned** to the hamburger on close.
- **Page behind is not inert** while the overlay is open.
- **`aria-current="true"`** in `menu--main.html.twig` marks the whole active *trail* as current; use `page` for the current page only.
- **Placeholder content in the default blocks:** the `links` block renders two buttons labelled "Button" and "Another" pointing at `https://example.com` unless a site overrides the block. A real site that forgets to fill the slot ships dead example links.
- **Dead code:** `navbar.js` resets `.dropdown-menu__expand-button--has-been-opened`, which no template emits; `menu.html.twig` computes `aria_id` and never uses it; `menu_classes` is merged recursively and accumulates down the tree. The menu has no submenu disclosure at all, so any menu with children renders nested lists open and unstyled.
- **Scroll listener** (`--navbar-scroll-top`) is a non-passive handler registered and removed from three code paths (resize, media query, intersection observer); a single passive listener gated by the media query is simpler.
- **Three `include()` calls** in `navbar.twig` lack `with_context: false` (see [[0021-agents-md-compliance-sweep]]).

## Acceptance criteria
- [x] `aria-expanded`/`aria-controls` on the toggle, kept in sync
- [x] Escape closes; focus stays inside while open and returns to the toggle
- [x] Page behind the overlay is `inert` while open
- [x] `aria-current="page"` only on the current page link
- [x] Default `links` block is empty, or clearly placeholder-only in the Canvas editor
- [x] Dead selectors and unused variables removed; decision on submenus (disclosure button, or flat only) recorded
- [x] `npm run format` and `npm run build`
- [ ] Keyboard and screen-reader pass at mobile width on a real site; release note written

## Implementation notes
- Key files: `components/navbar/navbar.js`, `navbar.twig`, `navbar.tailwind.css`, `templates/navigation/menu.html.twig`, `menu--main.html.twig`.
- Canvas editor behaviour: keep the existing `currentlyInCanvasEditor()` guard.

## Related
- Project:: [[ui-and-interaction-review]]
- Decisions:: [[0003-interactive-component-accessibility-contract]]

## Outcome (2026-11-25)
- **Toggle:** the hamburger is `type="button"` with `aria-expanded` (kept in sync by `isOpen`) and `aria-controls` pointing at the menu's new unique id; the close button is `type="button"` too.
- **Overlay:** Escape closes it; while open every sibling along the path from the menu to `<body>` is `inert` (so Tab and screen readers stay in the overlay, which replaces a hand-written focus trap), restored on close; focus returns to the hamburger. If the window grows past the breakpoint while open, it closes. The setter ignores a repeat of the current state.
- **Verified** in a harness page (the real `navbar.js` with minimal markup, in a 375px iframe, in the browser pane): open sets `aria-expanded`, `inert` and the scroll lock and focuses the first control; Escape and the close button both close and focus returns to the hamburger. No Drupal site, so the real Twig, Canvas editor and a screen reader were not tried.
- **`aria-current`:** `menu--main.html.twig` now sets `aria-current="page"` when the link's URL equals `path('<current>')`; the active trail keeps its class only. Not run on Drupal: query strings and a menu cached across URLs of one route are the cases to try.
- **Placeholders:** the `links` block no longer renders two "Button"/"Another" links to `example.com`; it is empty. The Canvas slot is unchanged.
- **Dead code:** the `dropdown-menu__expand-button` reset in `navbar.js` and the unused `aria_id` in `menu.html.twig` are gone. The `menu_classes` accumulation in the task text was not real: a macro has its own scope, so it starts empty on each call; left as is.
- **Submenus: decision, flat navigation only.** No template or script exists for disclosure buttons and no site here uses nested items; a nested menu renders its child list open. Add a disclosure pattern ([[0003-interactive-component-accessibility-contract]]) when a site needs one.
- **Not changed:** the scroll listener (it works; simplifying it is optional), and the five `include()` calls without `with_context: false` (task [[0021-agents-md-compliance-sweep]]). Stacked on the 0016 branch.
