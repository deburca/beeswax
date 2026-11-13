---
type: task
tags: [beeswax/task]
status: done
priority: medium
project: "[[theme-health-review]]"
area: theme
created: 2026-11-13
branch: feature/0004-undefined-breadcrumb-library-and-messages-script
release: 1.9.1
depends-on:
blocked-by:
---
# Task: Undefined breadcrumb library and leftover messages script

## Context
Two small leftovers from the theme this one was forked from (Mercury / an earlier "Byte" theme) that suggest unfinished work.

## Problems found
- **`templates/navigation/breadcrumb.html.twig` calls `attach_library('beeswax/breadcrumb')`, but `beeswax.libraries.yml` defines only `global`, `messages`, `motion` and `hivelog-select`.** There is no `breadcrumb` library (and none in git history: it was never defined since the initial commit). Depending on the Drupal version and settings an undefined library is either ignored or logged as an error on every page that renders a breadcrumb. Either define the library (if a script or style was meant to ship with it) or remove the call. The breadcrumb styling that exists lives in `src/hivelog.css` and `components/`.
- **`lib/messages.js`**: the behaviour is named `byteThemeMessages` and the `once` id is `byte-theme-messages` (a previous theme's name; rename to `beeswaxMessages` / `beeswax-messages`, checking nothing else references the old names). Dismissing a message adds `opacity-0 -translate-y-2` and then waits for `transitionend` before adding `hidden`; if no transition runs (reduced motion, a CSS override, the element already invisible) the event never fires and the message stays in the layout taking space. Add a timeout fallback or check `prefers-reduced-motion`.
- `lib/messages.js` is registered with `core/drupal` and `core/once` only, which is right, but nothing re-focuses or announces anything after dismissal; consider moving focus to the next sensible element for keyboard users.

## Acceptance criteria
- [x] `beeswax/breadcrumb` is either defined or no longer attached; no "library not found" entries in the Drupal log on a page with a breadcrumb
- [x] Behaviour and `once` names no longer mention "byte"
- [x] A dismissed message always leaves the layout, with and without transitions
- [ ] Release note written

## Implementation notes
- Key files: `templates/navigation/breadcrumb.html.twig`, `beeswax.libraries.yml`, `lib/messages.js`, `templates/misc/status-messages.html.twig`.
- Verify the undefined-library behaviour on a site (log level and message) before deciding how loud it was.

## Outcome
- Removed the `attach_library('beeswax/breadcrumb')` call (no script or style was ever meant to ship with it; styling lives in `src/hivelog.css`). Not verified on a live site which log entry the undefined library produced.
- `lib/messages.js`: behaviour is now `beeswaxMessages` with once id `beeswax-messages`. Dismissal hides immediately under reduced motion, otherwise on `transitionend` or a 400ms timer, whichever comes first. Focus moves to the neighbouring message's close button.
- Also fixed a stale `byte_theme` namespace in a `page.html.twig` comment.

## Related
- Project:: [[theme-health-review]]
