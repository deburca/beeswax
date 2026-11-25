---
type: task
tags: [beeswax/task]
status: backlog
priority: medium
project: "[[ui-and-interaction-review]]"
area: components
created: 2026-11-25
branch: feature/0019-video-controls-accessibility-and-translation
release:
depends-on: 
blocked-by:
---
# Task: Video controls accessibility and translation

## Context
From reading `components/video/video.twig` and `video.js` for [[ui-and-interaction-review]]; see [[0003-interactive-component-accessibility-contract]] and [[0002-focus-and-interaction-state-pattern]].

## Problems found
- **No focus style** on the play/pause button (`text-white cursor-pointer` only) and no backing scrim: a white icon over arbitrary footage can have no contrast at all.
- **Hard-coded English:** "Pause video", "Play video" and the fallback "Your browser does not support the video tag." are neither `|t` in Twig nor `Drupal.t()` in `video.js`, so the label can switch language mid-session.
- **Autoplay with no control:** `controls` is optional, so an `autoplay` video can play and loop with nothing to pause it (WCAG 2.2.2). Autoplay should also not start under `prefers-reduced-motion`.
- **Native controls are never offered:** the component builds its own single button; there is no seek, volume or captions, and no `<track>` slot, so captions cannot be supplied.
- **Missing attributes:** no `playsinline` (iOS plays fullscreen), no `preload`.
- **`aria-label` on `<video>`** is the caption or `false`; with no caption the element has no name.
- **Two spans toggle icons by `hidden`** and the state is held in JS and in the label; a single button with `aria-pressed` would be simpler.
- Twig uses `{% include ... only %}` correctly but with a trailing comma in the hash (`icon_size: "large",`), which Prettier tolerates; confirm Twig 3 does too.

## Acceptance criteria
- [ ] Visible focus indicator and a 3:1 icon against a scrim
- [ ] All labels translatable (Twig and JS)
- [ ] Autoplay videos always have a pause control, and do not autoplay under reduced motion
- [ ] A way to supply captions (`<track>` slot or prop) documented
- [ ] `playsinline` added; `npm run format` and `npm run build`; release note written

## Implementation notes
- Key files: `components/video/video.twig`, `video.js`, `video.component.yml`.

## Related
- Project:: [[ui-and-interaction-review]]
- Decisions:: [[0002-focus-and-interaction-state-pattern]], [[0003-interactive-component-accessibility-contract]]
