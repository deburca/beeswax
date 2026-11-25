---
type: task
tags: [beeswax/task]
status: backlog
priority: high
project: "[[ui-and-interaction-review]]"
area: components
created: 2026-11-25
branch: feature/0017-navbar-mobile-menu-interaction
release:
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
- [ ] `aria-expanded`/`aria-controls` on the toggle, kept in sync
- [ ] Escape closes; focus stays inside while open and returns to the toggle
- [ ] Page behind the overlay is `inert` while open
- [ ] `aria-current="page"` only on the current page link
- [ ] Default `links` block is empty, or clearly placeholder-only in the Canvas editor
- [ ] Dead selectors and unused variables removed; decision on submenus (disclosure button, or flat only) recorded
- [ ] Keyboard and screen-reader pass at mobile width; `npm run format` and `npm run build`; release note written

## Implementation notes
- Key files: `components/navbar/navbar.js`, `navbar.twig`, `navbar.tailwind.css`, `templates/navigation/menu.html.twig`, `menu--main.html.twig`.
- Canvas editor behaviour: keep the existing `currentlyInCanvasEditor()` guard.

## Related
- Project:: [[ui-and-interaction-review]]
- Decisions:: [[0003-interactive-component-accessibility-contract]]
