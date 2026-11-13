---
type: notes
tags: [beeswax/notes]
---
# Beeswax — Dashboard

## Active tasks
```dataview
TABLE status, priority, project
FROM #beeswax/task
WHERE status != "done" AND status != "dropped"
SORT priority asc, status asc
```

## Active projects
```dataview
TABLE status, target
FROM #beeswax/project
WHERE status = "active" OR status = "planning"
```

## Open decisions
```dataview
TABLE status, date
FROM #beeswax/decision
WHERE status = "proposed"
SORT date desc
```

## Upcoming release
```dataview
TABLE version, status, date
FROM #beeswax/release
WHERE status != "released"
SORT date asc
```
