---
name: oro-quiz
description: Quiz the user on the current OroCommerce module against its spec's acceptance criteria. Use when the user runs /oro-quiz or wants to check understanding before moving on.
---

# Quiz on a module

1. Find the current module: the spec with Status "In progress" in `specs/`, or the one the user names.
2. Write 5 questions from its acceptance criteria and questions:
   - 2 conceptual ("why does Oro…")
   - 2 practical ("which file/command would you use to…")
   - 1 Enterprise Edition question
3. Ask ONE question at a time. Wait for the answer. Give brief feedback with a file path or doc reference, then move on.
4. At the end, give a score and list weak areas.
5. If the score is 4/5 or better, offer to set the spec Status to "Done" and run `/oro-notes`. Otherwise suggest what to revisit.
