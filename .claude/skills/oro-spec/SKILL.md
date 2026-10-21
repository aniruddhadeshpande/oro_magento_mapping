---
name: oro-spec
description: Create the learning spec for the next OroCommerce module (specs/NN-topic.md) from the template. Use when starting a new module or when the user runs /oro-spec.
---

# Create a module spec

1. Read `specs/_template.md` and list existing `specs/*.md` to find the next number.
2. If the user named a topic, use it; otherwise take the next module from the plan in CLAUDE.md.
3. Fill in:
   - **Magento equivalent**: the Magento 2 concepts, files and commands this replaces.
   - **Questions to answer**: 4–6 concrete questions a Magento developer would ask.
   - **Experiments**: 3–5 small hands-on tasks, each observable (a breakpoint, a command output, a page change).
   - **Acceptance criteria**: testable statements, always including one EE criterion.
4. Leave "Files and commands to explore" for plan mode.
5. Save as `specs/NN-topic.md` (e.g. `specs/01-bootstrapping.md`), set Status to "In progress", and show a short summary.
