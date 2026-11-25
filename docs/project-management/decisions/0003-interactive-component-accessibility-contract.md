---
type: decision
tags: [beeswax/decision]
status: proposed
date: 2026-11-25
supersedes:
---
# ADR-0003: Interactive component accessibility contract

## Status
proposed

## Context
The four scripted components (`accordion`, `accordion-container`, `navbar`, `video`) each wire their own behaviour on a shared `ComponentInstance` base (`lib/component.js`) and each meets a different part of the expected keyboard and screen-reader contract:
- Accordion: collapsed panels are hidden visually (`h-0 overflow-hidden`) and made unfocusable by rewriting `tabindex`, but remain in the accessibility tree; resize listeners are never removed.
- Navbar: the mobile menu opens as a full-screen overlay, but the toggle has no `aria-expanded` or `aria-controls`, there is no Escape handler, focus is not trapped or returned, and `aria-current="true"` is used where `page` is meant.
- Video: labels are English strings in both Twig and JS (not translatable), and an autoplaying video can have no pause control.
- Messages (`lib/messages.js`), by contrast, already handles focus after dismissal and reduced motion.

Drupal core's `Drupal.t()` and `once()` are available, and the platform now covers part of this natively (`inert`, `hidden="until-found"`, `<details name>`).

## Decision
Proposed: every interactive component meets a short written contract, documented in `CUSTOMIZING.md`, and new components are reviewed against it.
1. A control that shows or hides content carries `aria-expanded` and `aria-controls`, and the hidden content is removed from the accessibility tree (`hidden`, `inert` or `display: none`), not just clipped.
2. An overlay (mobile menu) closes on Escape, keeps focus inside while open, and returns focus to the control that opened it.
3. Any string produced by script goes through `Drupal.t()`; any string in Twig goes through `|t`.
4. Anything that plays automatically has a visible pause control and does not start when the visitor prefers reduced motion.
5. Listeners added in `init()` are removable, so a Canvas re-render does not leak them.

## Consequences
- Positive: a checklist to review against, fewer bespoke fixes, and behaviour that holds up when Canvas re-renders components.
- Negative / trade-offs: `inert` and `hidden` change how the accordion animates (height transitions on `hidden` content need `content-visibility` or `interpolate-size`); browser support has to be confirmed first.
- Follow-up tasks: [[0017-navbar-mobile-menu-interaction]], [[0018-accordion-collapsed-content-and-lifecycle]], [[0019-video-controls-accessibility-and-translation]]
