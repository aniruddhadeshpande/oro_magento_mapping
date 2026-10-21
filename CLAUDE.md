# OroCommerce learning project

## Who I am
- Experienced Magento 2 / Adobe Commerce developer, limited Symfony experience.
- Learning OroCommerce from scratch. Goal: understand Community Edition hands-on AND Enterprise Edition conceptually.

## Target stack (upgrade path)
- **Now:** OroCommerce **5.1.8** CE (Symfony 5.4, PHP 8.2, Node 18/npm, PostgreSQL 13.7). Git branch `study`, tag `baseline-5.1.8`.
- **Goal:** build a feature on 5.1.8, then upgrade **directly to 7.0.4** (Symfony 7.4, PHP 8.5, Node 24/pnpm, PostgreSQL 18). Fallback via 6.1.8 only if the direct jump can't be fixed. Record what breaks.
- Everything runs in Docker: Oro's `docker-compose.yml` (pgsql, redis, mailcatcher) + our `docker-compose.override.yml` (`php` = `symfony serve`, `consumer` = MQ consumer), image `oro-study/php:8.2` from `.docker/php-8.2/`. No PHP on the host.
- ALWAYS run console/composer inside the php container: `docker compose exec php symfony console ...` and `docker compose exec php symfony composer ...` (never `php bin/console`). DB/Redis/mail env vars (`ORO_DB_URL` etc.) are set in the override, so don't rely on Symfony CLI Docker label injection.
- Site: http://oro.demo (admin at `/admin`). After changing the override, run `docker compose up -d`.

## How to teach me
- Explain every Oro concept with the closest Magento 2 equivalent, then the key difference.
- Ground answers in real code: cite file paths (and line numbers) in `vendor/oro/...` or this repo.
- Your Oro knowledge may be outdated or version-mixed. Verify non-trivial claims against the source in `vendor/` (currently 5.1.8) or https://doc.oroinc.com for the matching version (5.1 now, 7.0 for the upgrade). Say clearly when something is unverified, and flag when behaviour differs between 5.1 and 7.0.
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
- Log each question I ask (with a short answer) in `notes/qa-log.md`, except when I say not to. The folder map is in `notes/oro-folder-structure.md`.

## Modules (4-week plan)
0 Install ✅ (5.1.8, Docker, sample data) → 0B Build a feature on 5.1.8 → 0C Upgrade 5.1.8 → 7.0.4
1 Bootstrapping · 2 Database · 3 Routing · 4 Events/observers/plugins · 5 Checkout
Phase 2 later: entities & admin, storefront, message queue & cron, API, testing, capstone.

## Rules
- Never edit files under `vendor/`. Custom code goes in `src/`.
- Don't run destructive commands (drop database, reinstall, `rm -rf var/`) without asking.
- Keep the message consumer in mind: if prices/search/emails look stale, check the `consumer` service is up (`docker compose ps`). CE uses the DBAL queue (`oro_message_queue` table).
- Don't edit Oro's own `docker-compose.yml`; customise via `docker-compose.override.yml`.
- Before any upgrade step: `pg_dump` backup + git tag. Never push without asking.
