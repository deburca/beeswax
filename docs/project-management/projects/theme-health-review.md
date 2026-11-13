---
type: project
tags: [beeswax/project]
status: planning
target:
created: 2026-11-13
---
# Project: Theme health review

## Goal
A read-through of the whole theme on 2026-11-13 (PHP hooks, templates, CSS, JS, libraries, assets, config, tooling) looking for bugs, dead code, gaps and cheap wins, turned into tasks so each can be done on its own. The aim is a theme that is more stable on a real site and cheaper to maintain; nothing here changes how it looks.

## Scope
- In scope: the findings listed in the tasks below.
- Out of scope: new features, visual redesign, the Canvas component library beyond removing what is demonstrably unused.

## What was checked, and what was found clean
- Every `.hivelog-*` class used in `src/hivelog.css` (102 classes) exists in the HiveLog module's templates, PHP or JS, and every `.beeswax-*` class in it is emitted by a template or `ThemeHooks.php`: no dead selectors found.
- Settings in `config/install`, `config/schema` and the theme-settings form agree with each other.
- All font files in `fonts/` are referenced by `src/fonts.css`.

## Tasks
```dataview
TABLE status, priority
FROM #beeswax/task
WHERE contains(string(project), this.file.name)
SORT priority asc, file.name asc
```

Suggested order: [[0003-group-animations-hide-content]], [[0004-undefined-breadcrumb-library-and-messages-script]], [[0005-fonts-css-copy-to-web-root]], [[0006-page-preprocess-cost-and-hook-cleanup]], [[0007-build-reproducibility-ci-and-checks]], [[0008-front-end-delivery-and-accessibility]], [[0009-footer-hardcoding-and-translations]], [[0010-dead-assets-and-stale-metadata]]. Follow-ups opened after 1.9.2: [[0011-accessibility-pass-and-contrast-checks]], [[0012-canvas-components-and-bundled-assets]].

## Open questions
- Is any of the Canvas component library (`components/`, 25 components) used by the HiveLog sites? Removing unused ones is only safe once that is known (task 0010).

## Related decisions
-
