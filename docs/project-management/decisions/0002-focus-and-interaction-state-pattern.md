---
type: decision
tags: [beeswax/decision]
status: proposed
date: 2026-11-25
supersedes:
---
# ADR-0002: Focus and interaction state pattern

## Status
proposed

## Context
Each control family signals focus differently:
- Buttons (`components/button`): `outline-none` plus a 3px ring at 50% opacity on `:focus-visible`. A 50% pine ring on a pine button is well under 3:1 against the button.
- Core form fields (`src/main.css`): `outline-none`, and on `:focus` only the bottom border and background change, to the pale `--accent` tint.
- Menu links: background swap on `focus:md:` only, nothing below `md`.
- Video play/pause (`components/video`): no focus style at all, white icon on arbitrary footage.
- HiveLog skin: 3 `:focus-visible` rules and 9 `:focus` rules across 1,425 lines, and no 44px minimum target rule.
- Hover-only affordances exist on cards (`hover:scale-103`) with no keyboard equivalent.

The 2026-11-13 contrast pass (see [[0011-accessibility-pass-and-contrast-checks]]) computed text and border ratios but not focus indicators, which it listed as not checked.

## Decision
Proposed: a single focus indicator, defined once and applied to every interactive element.
1. A global `:focus-visible` rule in `@layer base`: `outline: 2px solid var(--ring); outline-offset: 2px`, with `--ring` chosen to reach 3:1 against both `--background` and `--primary` in light and dark (WCAG 1.4.11, 2.4.13).
2. Components may add to it but never remove it: `outline-none` is allowed only where an equal or stronger indicator replaces it.
3. Form fields keep their underline style at rest and gain the global outline on focus; the border colour change is decoration, not the indicator.
4. Anything that reacts to hover also reacts to `:focus-visible`; anything that scales or moves on hover respects `prefers-reduced-motion` (already global).
5. Interactive targets are at least 24px (WCAG 2.5.8) and 44px on touch-primary controls (menu, close, play).

## Consequences
- Positive: one place to restyle focus, one thing to test, and a rule reviewers can check.
- Negative / trade-offs: a visible outline changes the look of buttons and fields slightly; sites that customised focus styles will see a double indicator until they drop theirs.
- Follow-up tasks: [[0014-form-focus-and-error-states-use-pale-accent]], [[0019-video-controls-accessibility-and-translation]], [[0024-touch-targets-and-focus-audit-of-hivelog-skin]]
