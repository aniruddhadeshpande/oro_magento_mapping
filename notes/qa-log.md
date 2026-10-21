# Q&A log

Questions I asked while learning, with short answers. Newest at the bottom.

---

## Module 0 · Install

### What is this project about?
A Claude Code study kit for learning OroCommerce as a Magento 2 dev: `CLAUDE.md` (rules), `specs/`, `notes/`, and `.claude/` (skills `/oro-spec` `/oro-trace` `/oro-quiz` `/oro-notes`, plus subagents explorer, docs-verifier and reviewer). Modules 0–5: Install → Bootstrapping → DB → Routing → Events → Checkout.

### Which version is "stable"?
7.0.4 is the newest stable 7.0 tag. 6.1.8 is the previous LTS.

### Plan change: test an upgrade
Install **5.1.8**, build a feature, then upgrade **directly to 7.0.4** (via 6.1.8 only as a fallback).

| Tag | PHP | Symfony | Node | Postgres |
|---|---|---|---|---|
| 5.1.8 | 8.2 | 5.4 | 18 | 13.7 |
| 6.1.8 | 8.4 | 6.4 | 22 | 17.4 |
| 7.0.4 | 8.5 | 7.4 | 24 + pnpm | 18.3 |

### How do I use Claude Code on mobile?
`/remote-control`, then open the link in the Claude app. The session still runs on the laptop. `sudo` steps don't work remotely.

### Why did `! sudo ...` fail?
The `!` prefix has no terminal to read a password from. Run sudo commands in a separate terminal.

### Can PHP run in Docker instead of on the host?
Yes. Image `oro-study/php:8.2` (`.docker/php-8.2/Dockerfile`): PHP 8.2 + extensions, Composer, Symfony CLI, Node 18, uid 1001. Commands become `docker compose exec php symfony console ...`.

### Is master's `docker-compose.yml` useful?
On master it's identical to 7.0.4's. It only has services (pg18, redis 8, mailcatcher, gotenberg), with no PHP. For 5.1.8, use the tag's own file (pg 13.7). Master's version is useful as the Phase C upgrade target.

### Warning "the attribute `version` is obsolete"
Harmless: Compose v2 ignores it. Don't edit Oro's `docker-compose.yml` (keeps the upgrade merge clean). Customise via `docker-compose.override.yml`.

### Error "service php is not running"
`docker-compose.override.yml` didn't exist, so Compose had no `php` service. Create it in the project root.

### What goes in the env vars of the override?
Take each value from `.env-app` and replace `127.0.0.1` with the **service name**. Use the container port, not the random host port.
```yaml
ORO_DB_URL: "postgres://oro_db_user:oro_db_pass@pgsql:5432/oro_db?sslmode=disable&charset=utf8&serverVersion=13.7"
ORO_REDIS_URL: "redis://redis:6379"
ORO_MAILER_DSN: "smtp://mailcatcher:1025"
```
Magento analogy: `env.php` → `'host' => 'db'`. In Oro, real env vars override `.env-app`.

### `symfony check:requirements`: "No php.ini" and "short_open_tag should be disabled"
The official `php` image ships no php.ini, so PHP uses built-in defaults (`short_open_tag=On`). Fix in the Dockerfile: copy `php.ini-development` to `php.ini`, plus `short_open_tag=Off` in `conf.d/oro.ini`. "Vendor libraries must be installed" is expected until `composer install` runs. Side note: one rebuild failed because pecl.php.net returned 504. Retrying fixed it.

### Which Docker commands have we used so far?
Run from the project root. Compose merges `docker-compose.yml` (Oro's) + `docker-compose.override.yml` (ours).
```bash
# Build the PHP 8.2 image (first time, standalone)
docker build -t oro-study/php:8.2 .docker/php-8.2

# Check the image's toolchain
docker run --rm oro-study/php:8.2 sh -c 'php -v; php -m; composer --version; symfony version; node -v; npm -v; id'

# Start Oro's services (pgsql, redis, mailcatcher) + our php service
docker compose up -d
docker compose up -d php          # start/recreate just php (after editing the override or rebuilding)

# Inspect
docker compose config             # merged config; catches YAML errors
docker compose ps -a              # status, health, host→container ports
docker compose logs --tail 30 php

# Rebuild the php image after editing the Dockerfile
docker compose build php
docker compose build --progress=plain php   # full log, for debugging failed builds

# Run commands inside the php container
docker compose exec php symfony check:requirements
docker compose exec php php --ini           # which php.ini is loaded
docker compose exec php symfony composer install   # vendor/ + npm ci + assets
docker compose exec php symfony console oro:install --help
```

### What did `composer install` do (and are the warnings a problem)?
It installed 521 MB in `vendor/`. Then Composer `post-install-cmd` scripts ran, defined in `composer.json` → `vendor/oro/platform/src/Oro/Bundle/InstallerBundle/Composer/ScriptHandler.php`:
`setPermissions` (:175) → `installAssets` (:26, runs `npm ci` from `package-lock.json`, 321 MB `node_modules/`) → `setAssetsVersion` (:199) → `oro:assets:install`.
The "abandoned package" and `npm audit` warnings are normal for an old LTS (5.1). Ignore them locally, and don't run `npm audit fix`, because it would break the locked versions.
Magento analogy: like a Composer-triggered `setup:static-content:deploy`.

### Which message broker is it using? RabbitMQ?
No. It uses **DBAL, i.e. PostgreSQL itself**: `.env-app:51` `ORO_MQ_DSN=dbal:` → `config/config.yml:20` → transport `vendor/oro/platform/src/Oro/Component/MessageQueue/Transport/Dbal/` (messages are rows in the `oro_message_queue` table). CE ships only the DBAL transport. RabbitMQ (AMQP) is EE-only.
Check the queue: `docker compose exec -T pgsql psql -U oro_db_user -d oro_db -c "select count(*) from oro_message_queue;"`
Magento analogy: like Magento's MySQL queue (`queue_message` table) vs `amqp` in `env.php`. The difference is that Magento Open Source supports RabbitMQ, while Oro CE doesn't.

### "You are not allowed to access this file. Check index_dev.php" on http://oro.demo
`symfony serve` routed `/` to `public/index_dev.php`. Its guard (lines 13–19) allows only `127.0.0.1`/`::1`, but requests arrive from the Docker gateway (`172.25.0.1`), so it returns 403. Fix: `symfony serve ... --passthru=index.php` (prod front controller).
Oro 5.1 has one front controller per env (`index.php` = prod, `index_dev.php` = dev). Magento has a single `pub/index.php` + `deploy:mode:set`.
Side note: "CRITICAL: Orphans were found and redelivered" after a consumer restart is normal DBAL recovery (`RedeliverOrphanMessagesDbalExtension`).

### Still 403 after adding `--passthru=index.php`
Two causes. (1) The flag was put in the `x-php` anchor block, but `php:` has its own `command:`, and with `<<: *php` local keys override merged ones (shallow merge). (2) The container wasn't recreated: `docker compose up -d php` is needed after every override change, so check that `docker compose ps` shows "Up X seconds".

### Wrong service got `--passthru`
`--passthru` is a `symfony serve` (web server) option, so it goes on `php:` only. On `consumer:` (`oro:message-queue:consume`) it would fail with "option does not exist" and restart in a loop.

### Module 0 Phase A smoke test passed (2026-10-21)
`/`, `/admin/user/login` and `/product/` return 200. 64 sample products, MQ empty, consumer running. Mailcatcher UI: `docker compose port mailcatcher 1080`.

### Where is X in Oro (folders, DB creds, config)?
See **[oro-folder-structure.md](oro-folder-structure.md)**: top-level dirs, "where is X?" table, bundle anatomy, themes, where custom code goes.

### Push to my own remote fails: "did not receive expected object … index-pack failed"
The repo was a **shallow clone** (`git fetch --depth 1`). The missing object was the parent of the 5.1.8 commit, which the shallow clone cut off, and GitHub rejects a pushed history with missing parents. Fix: `git fetch --unshallow origin` (13 MB), then `git push -u local study`. Push only your branch and tag, not `--tags` (that would push all of Oro's release tags). Bonus: the `7.0.4` tag is now local, so `git diff 5.1.8 7.0.4` works.
