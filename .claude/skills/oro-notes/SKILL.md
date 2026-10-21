---
name: oro-notes
description: Append what was learned in the current module to notes/magento-to-oro.md. Use at the end of a module or when the user runs /oro-notes.
---

# Record learning notes

1. Read `notes/magento-to-oro.md` (create it with a `# Magento 2 → OroCommerce` title if missing) and the current spec.
2. Add or update one section for the module:

```
## Module NN: <Topic>
| Concern | Magento 2 | Oro 7.0 | Where to look |
| --- | --- | --- | --- |

**Key insight:** one or two sentences in the user's words where possible.
**Commands:** the useful commands discovered.
**Gotchas:** surprises from experiments.
**EE note:** ...
```

3. Only record things verified in code or docs during this session. Mark anything else "unverified".
4. Show the user the added section briefly.
