# Oro Claude Code kit

## Install
Copy everything in this folder into the root of your Oro repo (the `orocommerce-application` clone):

```bash
cp -r oro-claude-kit/. ~/oro/
chmod +x ~/oro/.claude/hooks/lint.sh
```

Requires `jq` (for the hook). Add `.claude/settings.local.json` to `.gitignore` if you make personal overrides.

## What's inside
| Path | Purpose |
| --- | --- |
| `CLAUDE.md` | Your background, target stack, teaching rules, plan |
| `specs/_template.md` | Template for each module spec |
| `notes/magento-to-oro.md` | Your growing comparison guide |
| `.claude/skills/oro-spec` | `/oro-spec` – create the next module spec |
| `.claude/skills/oro-trace` | `/oro-trace <flow>` – trace a flow through real code vs Magento |
| `.claude/skills/oro-quiz` | `/oro-quiz` – quiz against acceptance criteria |
| `.claude/skills/oro-notes` | `/oro-notes` – record what you learned |
| `.claude/agents/oro-explorer.md` | Read-only code search subagent |
| `.claude/agents/oro-docs-verifier.md` | Checks claims against doc.oroinc.com 7.0 |
| `.claude/agents/oro-reviewer.md` | Reviews your code in `src/` |
| `.claude/settings.json` | Safe permissions + lint hook |
| `.claude/hooks/lint.sh` | `php -l` / `lint:yaml` after every edit |

## Daily loop
1. `claude` in the repo root.
2. `/oro-spec bootstrapping` → creates `specs/01-bootstrapping.md`.
3. Shift+Tab to plan mode → "Plan how to learn spec 01".
4. `/oro-trace request from public/index.php to response`.
5. Run the experiments yourself.
6. `/oro-notes`, then `/oro-quiz`.

## Start Module 0
Before the repo exists, run Claude in an empty folder with only `CLAUDE.md`, and ask:
"Follow Module 0: clone orocommerce-application at the latest 7.0.x tag, start Docker services, install with sample data. Run one step at a time and explain each."
Then copy the rest of the kit in once the clone is done.
