---
type: task
tags: [beeswax/task]
status: review
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
- [x] Visible focus indicator and a 3:1 icon against a scrim
- [x] All labels translatable (Twig and JS)
- [x] Autoplay videos always have a pause control, and do not autoplay under reduced motion
- [x] A way to supply captions (`<track>` slot or prop) documented
- [x] `playsinline` added; `npm run format` and `npm run build`
- [ ] Release note written

## Implementation notes
- Key files: `components/video/video.twig`, `video.js`, `video.component.yml`.

## Related
- Project:: [[ui-and-interaction-review]]
- Decisions:: [[0002-focus-and-interaction-state-pattern]], [[0003-interactive-component-accessibility-contract]]

## Outcome (2026-11-25)
- **Focus and contrast:** the button is a dark disc (`bg-black/60`, `hover:bg-black/80`) with the white icon, and draws a 2px white outline offset 2px on `:focus-visible` (ADR-0002); the disc is what gives the icon contrast over any footage. Checked with a real Tab press in a harness: `:focus-visible` matches and the outline is solid 2px white.
- **Translation:** "Play video", "Pause video", the fallback text and the default `aria-label` ("Video", used when there is no caption) go through `|t`. The script no longer holds any English: the translated labels travel in `data-label-play` and `data-label-pause`, and `updateIcon()` reads them (verified with French labels).
- **Autoplay:** the pause control now renders whenever `autoplay` is on, even with "controls" off. Under `prefers-reduced-motion` the script pauses an autoplaying video and shows the play state. Without JavaScript an autoplaying video still autoplays; `muted` is the visitor's only brake there.
- **Captions:** new props `captions_src` (WebVTT), `captions_lang` (default `en`) and `captions_label` render a `<track kind="captions">`. Canvas editors enter a file URL; there is no slot for several tracks.
- **`playsinline`** added. The icon spans' `hidden` toggles are now CVAs (no inline `{% if %}`), and the initial state comes from the video (`autoplay || !paused`) instead of only the attribute.
- Not changed: a single `aria-pressed` button (the label swap tests the same and reads well), `preload`, and the hash's trailing comma (Prettier keeps it; it is valid in Twig 3).
- **Not done:** the real Twig was not rendered on Drupal or in Canvas; the `captions_*` props were not tried in the Canvas props form; a play/pause click on real footage and the reduced-motion pause were not exercised (no media file, no way to set the OS preference here). Release note not written. Stacked on the 0018 branch.
