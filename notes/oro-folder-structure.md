# OroCommerce folder structure (for a Magento 2 dev)

Based on the **installed Oro 5.1.8 CE** in this repo, with every path checked with `ls`/`grep`. Notes on 7.0 appear where they're already known. Anything not verified in code is marked *(unverified)*.

---

## 1. Top level

```
.
├── bin/console              CLI entry point
├── config/                  app-level config (YAML)
├── public/                  web root
│   ├── index.php            prod front controller
│   ├── index_dev.php        dev front controller (localhost only)
│   ├── build/<theme>/       compiled JS/CSS (webpack)
│   ├── bundles/             copied bundle assets
│   └── media/               resized images, cache
├── src/                     YOUR code
│   └── AppKernel.php        the kernel
├── templates/               app-level Twig overrides
├── translations/            app-level translation overrides
├── var/                     cache, logs, sessions, private files
├── vendor/oro/*             Oro core packages (never edit)
├── node_modules/            JS deps (npm in 5.1)
├── composer.json            PHP deps
├── package.json             JS deps + engines
├── webpack.config.js        asset build
├── .env-app                 default env vars (DB, MQ, search, …)
├── .php-version / .nvmrc    PHP 8.2 / Node 18
└── docker-compose.yml       services (pgsql, redis, mailcatcher); our docker-compose.override.yml adds php + consumer
```

| Path | What it is | Magento 2 equivalent | Notes |
|---|---|---|---|
| `bin/console` | Symfony console. Run as `symfony console …` | `bin/magento` | All `oro:*` commands live here |
| `config/` | App-level YAML config | `app/etc/` + global `di.xml` | Details in §2 |
| `public/` | Web root (the only dir the web server exposes) | `pub/` | |
| `public/index.php` | Prod front controller | `pub/index.php` | One file **per env** (`index_dev.php` = dev). Magento has a single file + `deploy:mode:set` |
| `public/build/` | Webpack output, per theme (`admin`, `default`, `custom`, `_static`) | `pub/static/` | Built by `oro:assets:build` |
| `public/bundles/` | Each bundle's `Resources/public/` copied here | `pub/static/<area>/<theme>/<Module>` | Done by `oro:assets:install` (runs after `composer install`) |
| `public/media/` | Image cache (`media/cache/product_large/…`), generated JS | `pub/media/catalog/product/cache` | |
| `src/` | Your bundles + `AppKernel.php` | `app/code/` | Custom code only goes here |
| `src/AppKernel.php` | Extends `OroKernel`. Adds dev/test-only bundles | `app/etc/config.php` + bootstrap | Normal bundles are **not** listed here (see `bundles.yml`, §3) |
| `templates/` | App-level Twig overrides, e.g. `templates/bundles/TwigBundle/Exception/error.html.twig` | `app/design/<area>/Vendor/theme/Module_Name/templates` | Symfony override convention: `templates/bundles/<BundleName>/…` |
| `translations/` | App-level translation overrides (empty now) | `app/i18n/` | |
| `var/cache/<env>/` | Compiled container, routes, Twig, Doctrine metadata | `generated/` + `var/cache/` | `AppKernel.php:54`. Clear with `oro:cache:clear` or `cache:clear` |
| `var/logs/` | `prod.log`, `dev.log` | `var/log/` | `AppKernel.php:62` |
| `var/sessions/` | PHP sessions (when `ORO_SESSION_DSN=native:`) | `var/session/` | |
| `var/data/` | **Private** files: `attachments/`, `protected_mediacache/` | `var/import`, protected media | Served through controllers with ACL, never directly |
| `vendor/oro/` | `platform`, `commerce`, `crm`, `customer-portal`, `commerce-crm`, … | `vendor/magento/module-*` | One package contains many bundles (e.g. `commerce` = 38 bundles) |
| `node_modules/`, `package.json`, `package-lock.json` | JS toolchain | `node_modules` from Grunt/`lib/web` | Installed by Composer's post-install script (`npm ci`) |
| `.env-app` | Default values for `ORO_*` env vars | `app/etc/env.php` (defaults) | Override with real env vars or `.env-app.local` |
| `composer.json` | Uses the `packagist.oroinc.com` repo | `composer.json` + `repo.magento.com` | |

---

## 2. "Where is X?" quick answers

| Question | Oro answer | Magento 2 equivalent |
|---|---|---|
| **DB credentials?** | Env var `ORO_DB_URL` → `ORO_DB_DSN` → `config/config.yml` `database_dsn: '%env(ORO_DB_DSN)%'`. The default is in `.env-app`. **Ours** comes from `docker-compose.override.yml` (host `pgsql`) | `app/etc/env.php` → `db` |
| **MQ / search / mail / Redis / session?** | Same pattern: `ORO_MQ_DSN`, `ORO_SEARCH_URL`, `ORO_MAILER_DSN`, `ORO_REDIS_URL`, `ORO_SESSION_DSN` (`.env-app` → `config/config.yml` parameters) | `env.php` → `queue`, `cache`, `session` |
| **Framework / app config?** | `config/config.yml` (imports `security.yml`, `doctrine.yml`), `config_{dev,prod,test}.yml`, `routing.yml`. Compiled into `var/cache/<env>`, so **clear the cache after changes** | `config.xml` / `di.xml` + `setup:di:compile` |
| **Admin "System → Configuration" values?** | DB tables `oro_config` (scope: `entity`, `record_id`) + `oro_config_value` (`section`, `name`, value). Global scope = `entity='app', record_id=0` | `core_config_data` (`scope`, `scope_id`, `path`) |
| **Which config scopes exist?** | CE: **global** and **user** (`oro_config.scope.global`, `oro_config.scope.user`). Organization and website scopes are EE | default → website → store view |
| **Where are config fields declared?** | Each bundle's `Resources/config/oro/system_configuration.yml`, e.g. `ProductBundle/…/system_configuration.yml:104` `fields:` → `oro_product.related_products_enabled` | `etc/adminhtml/system.xml` + `config.xml` defaults |
| **How do I read config in code?** | Inject `oro_config.manager` (`Oro\Bundle\ConfigBundle\Config\ConfigManager`), then `->get('oro_product.related_products_enabled')`. Also `set()`, then `flush()` (`ConfigManager.php:114, 197, 234`) | `ScopeConfigInterface::getValue('section/group/field')` |
| **Admin URL?** | `/admin`: parameter `web_backend_prefix`, default set in `PlatformBundle/DependencyInjection/OroPlatformExtension.php:25` | `app/etc/env.php` → `backend.frontName` |
| **Application (base) URL?** | System config `oro_ui.application_url`, declared in `UIBundle/Resources/config/oro/system_configuration.yml:18`, set by `oro:install --application-url` | `web/unsecure/base_url` |
| **Logs?** | `var/logs/prod.log` | `var/log/system.log`, `exception.log` |
| **Message queue storage (CE)?** | Table `oro_message_queue` (DBAL transport) | `queue_message` (MySQL queue) |

---

## 3. Inside a bundle (≈ a Magento module)

Example: `vendor/oro/commerce/src/Oro/Bundle/ProductBundle/`

### PHP directories
| Dir | Purpose | Magento 2 equivalent |
|---|---|---|
| `OroProductBundle.php` | Bundle class | `registration.php` |
| `Controller/` | Admin + storefront controllers (`Controller/Frontend/`) | `Controller/Adminhtml/`, `Controller/` |
| `Entity/` | Doctrine entities (the model **and** schema mapping) | `Model/` + `Model/ResourceModel/` |
| `Migrations/Schema/v1_0 … v1_x/` + `OroProductBundleInstaller.php` | Versioned schema migrations. The installer is a fast path for fresh installs | `db_schema.xml` / `Setup/Patch/Schema` |
| `Migrations/Data/` | Data fixtures | `Setup/Patch/Data` |
| `Form/` | Symfony form types | UI component forms |
| `EventListener/` | Doctrine and kernel event listeners | `Observer/` + `events.xml` |
| `Async/` | Message-queue processors | queue consumers (`queue_consumer.xml`) |
| `Api/` | REST/JSON:API processors | `Api/` + `webapi.xml` |
| `DependencyInjection/` | Bundle extension + config tree | `etc/di.xml` loading |
| `Layout/` | Storefront layout data providers / block types | `Block/`, `ViewModel/` |
| `Twig/` | Twig extensions (functions/filters) | template helpers / ViewModels |
| `Tests/` | Unit, Functional, Behat | `Test/` |

### `Resources/config/*.yml` (standard Symfony)
| File | Purpose | Magento 2 equivalent |
|---|---|---|
| `services.yml` | DI service definitions | `etc/di.xml` |
| `controllers.yml` | Controllers registered as services | none (automatic) |
| `form_types.yml` | Form type services | |
| `mq_topics.yml` | MQ topic definitions | `communication.xml`, `queue_topology.xml` |
| `validation.yml` | Entity validation constraints | validation in `Model` / UI components |
| `block_types.yml` | Storefront layout block types | `Block` classes |

### `Resources/config/oro/*.yml` (Oro's auto-discovered files)
Oro scans every bundle for these. You register nothing by hand.

| File | Purpose | Magento 2 equivalent |
|---|---|---|
| `bundles.yml` | Registers the bundle + priority. Read by `OroKernel.php:123` | `etc/module.xml` + `app/etc/config.php` |
| `routing.yml` | Bundle routes, loaded by `oro_auto` (`OroAutoLoader.php:18`) | `etc/{adminhtml,frontend}/routes.xml` |
| `datagrids.yml` | Admin (and storefront) grids. Read by `RawConfigurationProvider.php:23` | `ui_component/*_listing.xml` |
| `acls.yml` / `acl_categories.yml` | Permissions | `etc/acl.xml` |
| `navigation.yml` | Admin menu, titles | `etc/adminhtml/menu.xml` |
| `system_configuration.yml` | System config fields + tree | `etc/adminhtml/system.xml` |
| `api.yml` / `api_frontend.yml` | Back-office / storefront API exposure | `etc/webapi.xml` |
| `search.yml` / `website_search.yml` | Back-office / storefront search index mapping | `etc/indexer.xml` / `mview.xml` |
| `entity_config.yml` | Entity config attributes (extend, UI, audit) | none (Oro-specific) |
| `actions.yml` | Operations/buttons (Action framework) | UI component buttons |
| `features.yml` | Feature toggles | none |
| `jsmodules.yml` | JS module registration (webpack) | `requirejs-config.js` |
| `assets.yml` | SCSS entry points per theme | `web/css/source/_module.less` |
| `placeholders.yml` | Admin template insertion points | layout containers |
| `app.yml` | Bundle's contribution to app config | `config.xml` |

### `Resources/` (non-config)
| Dir | Purpose | Magento 2 equivalent |
|---|---|---|
| `views/<Entity>/*.html.twig` | **Admin** Twig templates | `view/adminhtml/templates` |
| `views/layouts/<theme>/<route_name>/*.yml` | **Storefront** layout updates per route, e.g. `layouts/default/oro_product_frontend_product_view/` | `view/frontend/layout/<handle>.xml` |
| `public/` | JS/SCSS/images, copied to `public/bundles/<bundle>/` | `view/<area>/web/` |
| `translations/` | `messages.en.yml`, `jsmessages.en.yml`, … | `i18n/en_US.csv` |
| `doc/` | Bundle docs | |

---

## 4. Themes

- **Where:** storefront themes are bundle resources: `Resources/views/layouts/<theme>/theme.yml`. Real (non-test) themes in 5.1.8:
  - `default`: `customer-portal/…/FrontendBundle/Resources/views/layouts/default/theme.yml` (group `commerce`)
  - `custom`: `commerce/…/CustomThemeBundle/…/layouts/custom/theme.yml` (`parent: default`)
  - `optimized`: `commerce/…/CMSBundle/…/layouts/optimized/theme.yml` (`parent: default`)
  - `embedded_default`: for embedded forms
  - Several bundles add files to the **same** theme (e.g. `CommerceMenuBundle/…/layouts/default/`), and they're merged.
- **Magento equivalent:** `app/design/frontend/<Vendor>/<theme>/theme.xml`.
- **Key difference:** there's no separate `app/design` tree. A custom theme is a **bundle** in `src/` with a `theme.yml` declaring `parent: default`. Layout updates are **YAML keyed by route name**, not XML handles.

---

## 5. Where your code goes

```
src/Acme/Bundle/StoreBundle/
├── AcmeStoreBundle.php
├── Controller/  Entity/  Migrations/Schema/v1_0/
└── Resources/config/
    ├── services.yml
    └── oro/bundles.yml        ← auto-registered, no AppKernel edit
```
Magento analogy: `app/code/Acme/Store/` + `registration.php` + `module.xml`. Never edit `vendor/`. Override with DI decoration, events, layout YAML, or Twig overrides in `templates/bundles/`.

---

## 6. Known changes in 7.0 (from the 7.0.4 tree)
- npm → **pnpm** (`pnpm-lock.yaml`, `pnpm-workspace.yaml`), Node 24.
- `Vagrantfile` removed. `docker-compose.yml` uses postgres 18, redis 8, and a new **gotenberg** service (PDF).
- PHP 8.5, Symfony 7.4. The front-controller layout, `AppKernel`, and annotation → attribute changes are **to verify during the upgrade**.

---

**EE note:** the folder layout is the same in EE. EE adds packages (`vendor/oro/commerce-enterprise`, `platform-enterprise`, …) with more bundles, such as multi-warehouse inventory and the website/organization config scopes. It also adds env vars for Elasticsearch (`ORO_SEARCH_URL=elastic-search://…`) and RabbitMQ (`ORO_MQ_DSN=amqp://…`).
