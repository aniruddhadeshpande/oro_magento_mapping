# OroCommerce learning project

## Who I am
- Experienced Magento 2 / Adobe Commerce developer, limited Symfony experience.
- Learning OroCommerce from scratch. Goal: understand Community Edition hands-on AND Enterprise Edition conceptually.

## Target stack
- OroCommerce 7.0 LTS Community Edition (latest 7.0.x patch), Symfony 7.4, PHP 8.5, PostgreSQL.
- Local setup: services in Docker (`docker compose`), PHP via Symfony CLI.
- ALWAYS run console commands as `symfony console ...` and composer as `symfony composer ...` (never `php bin/console`), so Docker service env vars are injected.

## How to teach me
- Explain every Oro concept with the closest Magento 2 equivalent, then the key difference.
- Ground answers in real code: cite file paths (and line numbers) in `vendor/oro/...` or this repo.
- Your Oro knowledge may be outdated (older versions used Symfony 4-6, RequireJS, YAML-only checkout). Verify non-trivial claims against https://doc.oroinc.com (7.0) or the source in `vendor/`. Say clearly when something is unverified.
- End every explanation with a short **EE note**: how this differs in Enterprise Edition (multi-org, multi-website, Elasticsearch, RabbitMQ, warehouses), or "same in EE".
- Guide, don't solve: give hints and let me write code unless I ask for the solution. Review what I write.
- Be concise.

## Learning method (per module)
1. Spec in `specs/NN-topic.md` (use `/oro-spec`).
2. Plan mode: propose what to read and run before doing it.
3. Explore with the `oro-explorer` subagent; verify with `oro-docs-verifier`.
4. I run a small experiment.
5. Record in `notes/magento-to-oro.md` (use `/oro-notes`).
6. Quiz me against the spec's acceptance criteria (use `/oro-quiz`).

## Modules (4-week plan)
0 Install (Docker, sample data) · 1 Bootstrapping · 2 Database · 3 Routing · 4 Events/observers/plugins · 5 Checkout
Phase 2 later: entities & admin, storefront, message queue & cron, API, testing, capstone.

## Rules
- Never edit files under `vendor/`. Custom code goes in `src/`.
- Don't run destructive commands (drop database, reinstall, `rm -rf var/`) without asking.
- Keep the message consumer in mind: if prices/search/emails look stale, check `oro:message-queue:consume` is running.
