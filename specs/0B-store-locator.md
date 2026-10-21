# Module 0B: Store Locator + extended Product field (on Oro 5.1.8)

Build a small but representative feature on 5.1.8, so that the Phase 0C upgrade to 7.0.4 has real custom code to break. Each milestone is linked to a request-flow diagram in [`notes/trace-request-flow.md`](../notes/trace-request-flow.md).

Bundle: `src/Acme/Bundle/StoreLocatorBundle/` (namespace `Acme\Bundle\StoreLocatorBundle`).

## Magento equivalent
- A custom module `app/code/Acme/StoreLocator` with `registration.php` + `etc/module.xml`.
- `db_schema.xml` for a `acme_store` table, plus a `Model` / `ResourceModel` / `Collection`.
- Admin: `routes.xml` (adminhtml), controllers, `menu.xml`, `acl.xml`, a UI component listing + form.
- Frontend: `routes.xml` (frontend), a controller returning a result page, `layout/acme_store_index_index.xml`, a block + `.phtml`.
- Product attribute `pickup_available` via a Data Patch (`EavSetup::addAttribute`), shown with `catalog_product_view.xml`.

## Questions to answer
1. How does Oro discover and register my bundle without editing `AppKernel.php`? (≈ `registration.php` + `setup:upgrade`)
2. How does a Doctrine entity + migration replace `db_schema.xml` + Model/ResourceModel/Collection, and how does Oro track which migrations ran?
3. How does one route become `/admin/...` and another `/stores`? What is `options: frontend: true`?
4. How does a storefront controller returning an **array** end up as HTML? (`@Layout` → `LayoutListener` → layout YAML by route name → block types → Twig)
5. How do I add a field to a **core entity** (`Product`) without touching `vendor/`, and why does it then appear in admin forms and grids automatically? (≈ EAV attribute)
6. Which parts of this code are most likely to break in 7.0 (annotations → attributes, Sensio `@Template`, extend entities, datagrid/layout YAML)?

## Files and commands to explore
- References to copy patterns from (read-only):
  - Admin CRUD: `vendor/oro/commerce/src/Oro/Bundle/ProductBundle/Controller/BrandController.php`
  - Storefront page: `vendor/oro/commerce/src/Oro/Bundle/CMSBundle/Controller/Frontend/PageController.php` + `Resources/views/layouts/default/oro_cms_frontend_page_view/`
  - Extend field on another bundle's entity: `vendor/oro/commerce/src/Oro/Bundle/PromotionBundle/Migrations/Schema/v1_5/AddDisablePromotionsField.php`
- Commands (all via `docker compose exec php symfony console …`):
  - `cache:clear`, `debug:router | grep acme`, `debug:event-dispatcher kernel.view`
  - `oro:migration:load --force` (add `--dry-run` first)
  - `oro:entity-extend:update` *(to verify whether it's needed after the extend migration)*

## Milestones and experiments
- [ ] **M1 Bundle skeleton**: bundle class + `Resources/config/oro/bundles.yml`, then cache clear. *Observe:* the bundle appears in `var/cache/prod/bundles.php`. → Diagram D1, step "boot"
- [ ] **M2 Entity + migration**: `Store` (id, name, street, city, postcode, phone, openingHours, active, createdAt, updatedAt), `Migrations/Schema/AcmeStoreLocatorBundleInstaller.php`. *Observe:* `--dry-run` SQL, then the `acme_store` table in psql, then the row in `oro_migrations`.
- [ ] **M3 Admin CRUD**: controller (`@Route`, `@Template`, `@Acl`), form type, `datagrids.yml` (`acme-store-grid`), `navigation.yml` menu item, translations. *Observe:* `/admin/acme/store` (path to be decided) lists, filters and sorts. In DevTools, the first page's data is embedded in the HTML, and sorting fires an XHR to `/admin/datagrid/acme-store-grid`. → Diagram D2
- [ ] **M4 Storefront page**: `/stores` (list of active stores) + `/stores/{id}`. A frontend controller with `@Layout`, routing `options: frontend: true`, `layouts/default/<route_name>/layout.yml` + a block-theme Twig file. *Observe:* the route has no `/admin` prefix. Rename the layout folder and watch the page lose your blocks. → Diagram D1
- [ ] **M5 Extended field**: `Product.pickupAvailable` (boolean) via a migration with `oro_options.extend`. Show "Pickup available at store" on the product page via `layouts/default/oro_product_frontend_product_view/*.yml`. *Observe:* the field appears in the admin product edit form with no form code. `$product->getPickupAvailable()` works through `ExtendEntityTrait::__call`. → Diagram D3
- [ ] **M6 Freeze for the upgrade**: 3 stores, 5 products flagged. Commit + tag `feature-5.1.8`. List the expected breakages in `notes/qa-log.md`.

## Acceptance criteria
- [ ] I can draw the storefront request flow (browser → `index.php` → kernel → router → theme → controller → `LayoutListener` → layout updates → Twig) from memory, and name the Magento step for each.
- [ ] I can explain why `/stores` has no `/admin` prefix and `/admin/acme/store` does (`RouteCollectionListener`).
- [ ] I can explain how a datagrid gets its first page vs later pages.
- [ ] I can add a custom field to a core entity without editing `vendor/`, and explain where it's stored (DB column + `oro_entity_config`) and how the getter works.
- [ ] `oro-reviewer` finds no Magento-style anti-patterns in `src/Acme`.
- [ ] I can find the controller, layout YAML and grid config for any storefront or admin page in under 5 minutes.
- [ ] **EE:** I can explain what would change in EE: store ownership/organization scoping, per-website visibility, store ↔ warehouse inventory, and Elasticsearch indexing of stores if they're searchable.

## Status
In progress (started 2026-10-21)
