---
type: project
tags: [beeswax/project]
status: planning
target:
created: 2026-11-25
---
# Project: UI and interaction review

## Goal
A read-through of the theme's templates, components, scripts and CSS on 2026-11-25, looking at user interface design, interaction behaviour and design patterns (rather than the code-health themes of [[theme-health-review]]). It turns what it found into ADRs for the patterns that need a decision and tasks for the fixes. Nothing was rendered in a browser: every finding below comes from reading the source, so each task starts by reproducing the problem.

## Scope
- In scope: focus and form states, dark-mode correctness, the interactive components (navbar, accordion, video, messages, pager, breadcrumb), landmarks and translations, token architecture, and consistency with `AGENTS.md`.
- Out of scope: new components, visual redesign, the HiveLog skin's own layout.

## What was checked, and what was found clean
- No `{% if %}` inside a `class` attribute, no dynamic heading tags and no `true`/`false` CVA keys anywhere in `components/` or `templates/`.
- Message close, reduced-motion handling, the scheme script and the scroll reveal (`src/motion.js`) are careful and need no change.
- The breadcrumb truncation, the card stretched-link pattern and the button CVA are sound apart from the items listed in the tasks.

## Decisions
- [[0001-token-architecture-and-source-of-truth]]
- [[0002-focus-and-interaction-state-pattern]]
- [[0003-interactive-component-accessibility-contract]]

## Tasks
```dataview
TABLE status, priority
FROM #beeswax/task
WHERE contains(string(project), this.file.name)
SORT priority asc, file.name asc
```

Suggested order: [[0014-form-focus-and-error-states-use-pale-accent]], [[0015-pager-current-page-dark-mode-and-semantics]], [[0016-status-messages-bypass-tokens-and-dark-mode]], [[0017-navbar-mobile-menu-interaction]], [[0018-accordion-collapsed-content-and-lifecycle]], [[0019-video-controls-accessibility-and-translation]], [[0020-landmarks-breadcrumb-and-page-structure]], [[0021-agents-md-compliance-sweep]], [[0022-token-consolidation]], [[0023-colour-scheme-preference-and-media-queries]], [[0024-touch-targets-and-focus-audit-of-hivelog-skin]].

## Open questions
- Do the findings in tasks 0014 to 0016 reproduce on a page outside `body.hivelog-page` (a Canvas page, a user form)? The HiveLog skin overrides several of them.
- Is a visitor-facing light/dark toggle wanted, or is *Match the device* enough (task 0023)?

## Related decisions
- [[0001-token-architecture-and-source-of-truth]], [[0002-focus-and-interaction-state-pattern]], [[0003-interactive-component-accessibility-contract]]
