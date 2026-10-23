# Handoff: Oro study project

_Last updated 2026-10-23. Branch `study`; `local/study` (GitHub `aniruddhadeshpande/oro_magento_mapping`) is at `6ed0842`. Newer local commits are **not pushed**._

## Goal
Learn OroCommerce as a Magento 2 developer, by doing a **real upgrade**: install Oro **5.1.8** CE → build a custom feature → upgrade **directly to 7.0.4** (6.1.8 only as a fallback), and record what breaks. Teaching rules are in `CLAUDE.md`.

## What we did
| Date | Step |
|---|---|
| 10-17 | Reviewed the study kit (`CLAUDE.md`, skills, agents). Checked the machine: Docker OK, no host PHP. |
| 10-19 | Plan changed from a 7.0-only install to **5.1.8 → feature → direct 7.0.4 upgrade**. Built PHP image `oro-study/php:8.2`. |
| 10-19 | Renamed the kit README to `README-kit.md`. `git init` + fetched tag `5.1.8` into this folder as branch `study`. |
| 10-21 | Wrote `docker-compose.override.yml` (`php` + `consumer`), then `composer install` and `oro:install` with sample data. Site up at **http://oro.demo** (admin `/admin`, `admin` / `admin123`). Smoke test passed: 64 products, queue empty. |
| 10-21 | Fixes along the way: no php.ini in the image, `index_dev.php` 403 (→ `--passthru=index.php`), a YAML anchor override mix-up. |
| 10-21 | Commit + tag `baseline-5.1.8`. Unshallowed the repo (needed to push), pushed to the `local` remote. |
| 10-21 | Docs: `notes/oro-folder-structure.md`, `notes/qa-log.md`. Updated `CLAUDE.md` for the new stack. |
| 10-21 | Module 0B spec (`specs/0B-store-locator.md`) + request-flow diagrams (`notes/trace-request-flow.md`). |
| 10-23 | Restarted the stack (the consumer needed a manual `docker compose restart consumer` to escape Docker's restart backoff). Added `restart: unless-stopped` to all 5 services via the override. Site 200, consumer idle, queue empty. |

## Decisions
- **Version path:** 5.1.8 → 7.0.4 directly, even though Oro may officially expect going through 6.x. Failures are the learning output.
- **Everything in Docker, no host PHP.** Oro's `docker-compose.yml` stays untouched. Our additions go in `docker-compose.override.yml`, which is force-added to git because Oro's `.gitignore` lists it.
- **Commands:** `docker compose exec php symfony console …` / `… symfony composer …`. Env vars (`ORO_DB_URL` etc.) are set in the override, so Symfony CLI label injection isn't used.
- **Web server:** `symfony serve --no-tls --allow-all-ip --port=8000 --passthru=index.php`, host port 80 → `http://oro.demo` (already in `/etc/hosts`). Runs in **prod** mode.
- **MQ:** CE DBAL transport (the `oro_message_queue` table). The consumer runs as its own service with memory/time limits + `restart: unless-stopped`.
- **Feature for 0B:** Store Locator (`Store` entity, admin CRUD + grid, storefront `/stores`) + extend field `Product.pickupAvailable`. Bundle `src/Acme/Bundle/StoreLocatorBundle`.
- **Way of working:** the user writes the feature code. Claude gives hints, reviews with `oro-reviewer`, logs Q&A in `notes/qa-log.md`, commits only when asked, and **never pushes without asking**.

## Files changed (vs Oro 5.1.8)
| File | Purpose |
|---|---|
| `.docker/php-8.2/Dockerfile` | PHP 8.2 + extensions, Composer 2, Symfony CLI, Node 18, php.ini, uid 1001 |
| `docker-compose.override.yml` | `x-php` anchor (incl. `restart: unless-stopped`); `php` (web, `80:8000`) and `consumer` services; restart policy for Oro's `pgsql`/`redis`/`mailcatcher` |
| `.gitignore` | + `/.claude/settings.local.json` |
| `CLAUDE.md` | Stack, commands, modules, rules for the upgrade path |
| `README-kit.md` | The kit's README (renamed so it doesn't clash with Oro's `README.md`) |
| `.claude/` | Skills, agents, lint hook, settings (from the kit) |
| `notes/qa-log.md` | Q&A history (every question asked + answer) |
| `notes/oro-folder-structure.md` | Oro folders ↔ Magento folders, "where is X?" table |
| `notes/trace-request-flow.md` | Mermaid diagrams D1 storefront render, D2 admin datagrid, D3 extend field |
| `notes/oro-request-flow.html` | Interactive step-through of D1–D3 (also published as a private claude.ai artifact) |
| `notes/magento-to-oro.md` | Comparison guide (still empty, filled by `/oro-notes`) |
| `specs/0B-store-locator.md` | Module 0B spec, milestones M1–M6 |
| `HANDOFF.md` | This file |

Git: tags `5.1.8` (Oro), `baseline-5.1.8` (ours). Oro tags up to `7.0.4` are available locally (from the unshallow).

## Open issues
1. ~~**The stack wasn't running after a machine restart.**~~ **Fixed 10-23:** all services now have `restart: unless-stopped` (Oro's `pgsql` had `on-failure`, which ignores clean shutdowns). If the consumer is ever stuck in "Restarting" after the DB comes back, run `docker compose restart consumer`.
2. **Mermaid diagrams aren't confirmed to render.** Check `notes/trace-request-flow.md` in GitHub's preview.
3. **Dev mode is untested.** Setting `ORO_ENV=dev` + `ORO_DEBUG=1` on `php` should enable the profiler without `index_dev.php`.
4. **Unverified items for the upgrade:** whether Oro supports a direct 5.1 → 7.0 jump, the exact `oro:platform:update` flags, the migration-status command, and whether `oro:entity-extend:update` is needed after an extend migration.
5. **Known noise:** the "version is obsolete" Compose warning (Oro's file, leave it), the npm audit/abandoned-package warnings (expected on 5.1, don't run `npm audit fix`), and the Mailcatcher host port changes on restart (`docker compose port mailcatcher 1080`).

## Next steps
1. ~~Bring the stack up~~ (done 10-23).
2. **M1 (in progress, the user is writing it):** `src/Acme/Bundle/StoreLocatorBundle/AcmeStoreLocatorBundle.php` + `Resources/config/oro/bundles.yml` (priority ~255), then `cache:clear` and `grep Acme var/cache/prod/bundles.php`. Review with `oro-reviewer`.
3. M2 entity + installer migration → M3 admin CRUD + grid → M4 storefront `/stores` → M5 `pickupAvailable` extend field → M6 test data, tag `feature-5.1.8`, list expected breakages.
4. **Phase 0C:** `pg_dump` + tag → read the 6.0/6.1/7.0 `UPGRADE.md` → PHP 8.5 / Node 24 / pnpm image → merge the 7.0.4 skeleton (`git diff 5.1.8 7.0.4`) → Postgres 13 → 18 dump/restore → `oro:platform:update` → log what breaks.
5. Then Modules 1–5 (Bootstrapping, Database, Routing, Events, Checkout).

## Quick reference
```bash
docker compose up -d                       # start everything
docker compose ps                          # status
docker compose logs -f consumer            # watch MQ
docker compose exec php symfony console cache:clear
docker compose exec -T pgsql psql -U oro_db_user -d oro_db
git push local study                       # only when you decide to push
```
