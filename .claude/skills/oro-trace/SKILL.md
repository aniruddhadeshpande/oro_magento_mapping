---
name: oro-trace
description: Trace an OroCommerce flow (request, event, route, checkout step, query) through real code and compare it with Magento 2. Use when the user asks how something works in Oro or runs /oro-trace <flow>.
---

# Trace a flow

1. Restate the flow to trace in one line.
2. Delegate the code search to the `oro-explorer` subagent. Ask for the entry point and each hop with file path and line number.
3. If any claim depends on version-specific behavior, ask `oro-docs-verifier` to confirm it against doc.oroinc.com 7.0.
4. Where useful, suggest a command that proves a step live (`symfony console debug:router`, `debug:event-dispatcher`, `debug:container`, `router:match`, Xdebug breakpoint location).
5. Output:
   - A numbered list of hops (`file:line` and one line each).
   - A table: Step | Magento 2 | Oro | Key difference.
   - **EE note**: what changes in Enterprise Edition, or "same in EE".
   - One follow-up experiment for the user to run.

Keep it concise. Mark anything unverified.
