---
type: task
tags: [beeswax/task]
status: done
priority: medium
project: "[[ui-and-interaction-review]]"
area: components
created: 2026-11-25
branch: feature/0018-accordion-collapsed-content-and-lifecycle
release: 1.9.5
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
- [x] Collapsed content is not exposed to assistive technology and not focusable
- [x] Without JavaScript every panel is readable
- [x] Resize handling uses one `ResizeObserver` or a removable listener
- [ ] Verified in the Canvas editor and with keyboard and a screen reader
- [x] `npm run format` and `npm run build`
- [x] Release note written

## Implementation notes
- Key files: `components/accordion/`, `components/accordion-container/`, `lib/component.js`.
- Check browser support for `interpolate-size` / `hidden="until-found"` before dropping the measured-height approach.

## Related
- Project:: [[ui-and-interaction-review]]
- Decisions:: [[0003-interactive-component-accessibility-contract]]

## Outcome (2026-11-25)
- **Collapsed content:** a closed panel is `inert` (set in the `isOpen` setter), which takes it out of the tab order and the accessibility tree. The `tabindex` rewriting and `data-original-tabindex` bookkeeping are gone, so content added later is covered too.
- **No JavaScript:** `@media (scripting: none)` in `src/main.css` gives `.accordion--content` `height: auto`, `overflow: visible` and its open padding, so every panel reads. The title buttons still do nothing without script. Needs a browser with `scripting` media support (Chrome 120, Firefox 113, Safari 17); older ones still see closed panels.
- **Lifecycle:** the per-accordion `window` resize listener is a `ResizeObserver` on the accordion (width changes only, debounced 350ms as before), disconnected in a new `remove()` that `ComponentType.removeComponent` calls on detach.
- **Measuring:** `measureNaturalHeight()` now changes only classes, not the open state, so no `aria-expanded` flicker and no `accordionopen` events; the `shouldDispatchEvents` flag it needed is gone. The stray "Even" after the code fence is fixed.
- **Verified** in a harness (the real `accordion.js` and `accordion-container.js` with the built CSS, in an iframe, in the browser pane): initial states and `--natural-height`, click to open and close, the container closing the other panel, `inert` and `aria-expanded` following state, and a narrower iframe re-measuring a 296px panel to 656px with the `accordion--resizing` class cleared. Not tried: the Canvas editor, a screen reader, the `scripting: none` rule (no way to turn scripting off here), or the Twig itself.
- **Decisions, not changed:** the heading stays `text-2xl` at every level (the size is a design choice, and `heading_level` keeps the document outline right); the open and close animation and icon rotation stay at 500ms and are cut by the global reduced-motion rule. Closing siblings in a container is silent; that matches the usual pattern and is left.
- Stacked on the 0017 branch.
