---
type: task
tags: [beeswax/task]
status: backlog
priority: high
project: "[[theme-health-review]]"
area: components
created: 2026-11-13
branch: feature/0003-group-animations-hide-content
release:
depends-on:
blocked-by:
---
# Task: Group animations can leave content invisible

## Context
The `group` component (`components/group/group.twig`) takes an `animation` prop. For every animation type its CVA adds `opacity-0`, and `src/motion.js` (built to `build/motion.js`, 64 KB, attached by the component via `attach_library('beeswax/motion')`) is what makes it visible again. The content is therefore hidden by CSS and only revealed by JavaScript.

## Problems found
- **No JavaScript, or a script failure** (blocked, slow, an error earlier on the page): every animated group stays at `opacity: 0` forever. Content is lost, not just unanimated.
- **`inView(..., { amount: 1 })`** only fires when the *whole* element is inside the viewport. A group taller than the viewport (a long section on a phone) can never satisfy that, so it never appears.
- **No `prefers-reduced-motion` handling** anywhere in `src/` or `components/`: a visitor who asked for less motion still gets a 100px slide.
- **Elements are collected once at load** (`querySelectorAll("[data-animation]")` at the top of `motion.js`), so groups added later (AJAX, Canvas live preview, BigPipe) are never animated and stay hidden. A Drupal behaviour with `once()` would cover them.
- `fade_*` transforms are fixed at 100px; the delay and duration data attributes are read without validation (fine, but an invalid value silently becomes the default).

## Acceptance criteria
- [ ] With JavaScript disabled, animated groups are fully visible (hide with a `js`-set class on `<html>`, or reveal with a `<noscript>`/CSS fallback, rather than `opacity-0` unconditionally)
- [ ] A group taller than the viewport still animates in (use a fractional `amount`, or a `margin`, and test on a 375px viewport)
- [ ] Under `prefers-reduced-motion: reduce` groups appear without movement (and ideally without the fade)
- [ ] Groups added after load animate too (Drupal behaviour + `once()`)
- [ ] `npm run build` output regenerated and committed; release note written

## Implementation notes
- Key files: `components/group/group.twig` (CVA `animation` variants), `src/motion.js`, `beeswax.libraries.yml` (`motion` library), `build/motion.js`.
- Check whether the HiveLog pages use `group` at all; if not, this only affects Canvas pages, but those are exactly the pages where a missing section is most visible.
- Compare: the `motion` package is the only runtime dependency (`package.json`); `animate` and `inView` are all that is used.

## Related
- Project:: [[theme-health-review]]
