---
type: task
tags: [beeswax/task]
status: backlog
priority: medium
project: "[[ui-and-interaction-review]]"
area: components
created: 2026-11-25
branch: feature/0018-accordion-collapsed-content-and-lifecycle
release:
depends-on: 
blocked-by:
---
# Task: Accordion collapsed content and lifecycle

## Context
From reading `components/accordion/accordion.js`, `accordion.twig` and `accordion-container.js` for [[ui-and-interaction-review]]; see [[0003-interactive-component-accessibility-contract]].

## Problems found
- **Collapsed panels stay in the accessibility tree.** They are `h-0 overflow-hidden` and their focusable children get `tabindex=-1`, but a screen reader can still read the text. Use `hidden`/`inert` (or `hidden="until-found"` so find-in-page opens it).
- **The `tabindex` rewrite is fragile:** it sets `tabIndex = el.tabIndex || 0` on every descendant at `init()`, so content added later (a Canvas edit, an embedded widget) is never managed, and elements that were naturally focusable get an explicit `tabindex="0"`.
- **`aria-expanded` is set only by JavaScript** and the open state without JS relies on `in-[.accordion--open:not(.accordion--js)]`; check the no-JS render shows every panel.
- **`window` resize listener is never removed**, one per accordion; re-rendered components in the Canvas editor leak them. The 350ms debounce also leaves the panel `height:auto` while resizing, which makes the open panel jump.
- **Opening re-measures by toggling state** (`measureNaturalHeight()` opens, reads and restores) which fires `aria-expanded` changes that screen readers can announce.
- **Heading and button:** the heading is `text-2xl` on every level, so the visual size never follows `heading_level`; the title button text is `text-left` with a 500ms icon rotation that ignores `prefers-reduced-motion` only because the global rule happens to override it.
- **Container closes siblings with no announcement**; acceptable, but record it.
- A comment in `set isOpen()` ends with a stray "Even" after a closing code fence.

## Acceptance criteria
- [ ] Collapsed content is not exposed to assistive technology and not focusable
- [ ] Without JavaScript every panel is readable
- [ ] Resize handling uses one `ResizeObserver` or a removable listener
- [ ] Verified in the Canvas editor and with keyboard and a screen reader
- [ ] `npm run format` and `npm run build`; release note written

## Implementation notes
- Key files: `components/accordion/`, `components/accordion-container/`, `lib/component.js`.
- Check browser support for `interpolate-size` / `hidden="until-found"` before dropping the measured-height approach.

## Related
- Project:: [[ui-and-interaction-review]]
- Decisions:: [[0003-interactive-component-accessibility-contract]]
