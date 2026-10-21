# Request flow traces (Oro 5.1.8)

How a URL becomes HTML in Oro, traced through the real code in `vendor/` (5.1.8). Every file:line below was checked with `grep -n`. Paths are shortened: `platform/` = `vendor/oro/platform/src/Oro/`, `commerce/` = `vendor/oro/commerce/src/Oro/Bundle/`, `portal/` = `vendor/oro/customer-portal/src/Oro/Bundle/`.

Used by spec [`0B-store-locator.md`](../specs/0B-store-locator.md).

---

## D1. Storefront page (e.g. `/cms/page/view/1`, later your `/stores`)

```mermaid
sequenceDiagram
    autonumber
    participant B as Browser
    participant S as symfony serve (php-cgi)
    participant FC as public/index.php
    participant K as AppKernel / OroKernel
    participant HK as HttpKernel + EventDispatcher
    participant C as Frontend controller
    participant LL as LayoutListener
    participant LM as LayoutManager / LayoutBuilder
    participant T as Twig layout renderer

    B->>S: GET http://oro.demo/cms/page/view/1
    S->>FC: --passthru=index.php
    FC->>K: new AppKernel(ORO_ENV, ORO_DEBUG) [index.php:5-7]
    K->>K: registerBundles() reads every Resources/config/oro/bundles.yml [OroKernel.php:123]
    K->>HK: handle(Request)
    Note over HK: kernel.request
    HK->>HK: RouterListener matches route oro_cms_frontend_page_view
    Note right of HK: Routes come from each bundle's Resources/config/oro/routing.yml via oro_auto [OroAutoLoader.php:18]. Non-frontend routes got /admin prepended at build time [RouteCollectionListener.php:37]
    HK->>HK: ThemeListener sets _theme = default [ThemeListener.php:35-45]
    Note over HK: kernel.controller
    HK->>HK: @Layout annotation stored as request attribute _layout [Annotation/Layout.php:160-162]
    HK->>C: viewAction(Page $page) (ParamConverter loads the entity)
    C-->>HK: return ['data' => ['page' => $page]] [PageController.php:18,33]
    Note over HK: kernel.view (controller did not return a Response)
    HK->>LL: onKernelView() [LayoutListener.php:48] (priority 10, view_annotations.yml:8)
    LL->>LL: new LayoutContext(data, vars). Configurators add theme, route_name, action [RouteContextConfigurator.php:29]
    LL->>LM: getLayout(context) [LayoutManager.php:15, LayoutBuilder.php:204]
    LM->>LM: Collect layout updates from theme dirs default/, default/{action}/, default/{route_name}/ across the theme hierarchy [ThemePathProvider.php:38-75]
    LM->>LM: Apply YAML actions (@add, @setOption, @move, @remove) to build the block tree, then block types build BlockViews
    LM->>T: Layout::render() [Layout.php:49-61]
    T->>T: renderBlock(root) using block themes (*.html.twig with {% block xxx_widget %})
    T-->>LL: HTML string
    LL-->>HK: new Response(html) [LayoutListener.php:139]
    HK-->>B: 200 text/html
```

**Key idea:** the storefront controller doesn't render anything. It returns **data**. The *page* is assembled from layout-update YAML files, chosen by **theme + route name**, that any bundle can contribute.

### Magento side by side
| Step | Magento 2 | Oro 5.1 |
|---|---|---|
| Entry | `pub/index.php` → `Bootstrap::run` | `public/index.php` → Symfony Runtime → `AppKernel` |
| Module registration | `registration.php` + `app/etc/config.php` | `Resources/config/oro/bundles.yml` → `OroKernel::registerBundles` |
| Routing | `FrontController` loops routers, `routes.xml` frontName | Symfony `RouterListener`, routes from `routing.yml` + controller annotations. `/admin` prefix added by `RouteCollectionListener` |
| Area / design | area code (`frontend`/`adminhtml`) + design theme | `frontend: true` route option + `_theme` request attribute |
| Controller | `execute()` returns a `ResultPage` | action returns an **array** (with `@Layout`) |
| Layout selection | handles: `default`, `catalog_product_view`, … | dirs: `layouts/default/`, `layouts/default/{route_name}/` |
| Layout language | XML (`<referenceContainer>`, `<block>`) | YAML actions (`@add`, `@setBlockTheme`, `@move`) |
| Rendering | Block classes + `.phtml` | Block types (PHP) + Twig block themes |
| Output | `ResultPage::renderResult` | `Layout::render()` → `Response` |

### How to see it yourself
```bash
docker compose exec php symfony console debug:router oro_cms_frontend_page_view
docker compose exec php symfony console debug:event-dispatcher kernel.view
docker compose exec php symfony console oro:debug:storefront-routes | head          # only frontend: true routes
docker compose exec php symfony console oro:debug:layout:block-types                 # block types usable in layout YAML
docker compose exec php symfony console oro:debug:layout --type=container          # options of one block type
```
**Optional dev mode, with profiler and layout tree.** `public/index.php` reads `ORO_ENV`/`ORO_DEBUG` from env (`index.php:5-6`), so instead of opening up `index_dev.php`, add `ORO_ENV: dev` + `ORO_DEBUG: "1"` to the `php` service env, then `docker compose up -d php` and `cache:clear --env=dev`. *(Not tried yet: the profiler toolbar should then show a "Layout" panel.)*

---

## D2. Admin page + datagrid (e.g. `/admin/brand/`, later your store grid)

```mermaid
sequenceDiagram
    autonumber
    participant B as Browser
    participant HK as HttpKernel
    participant C as Admin controller
    participant TW as Twig (admin templates)
    participant DGM as Datagrid Manager
    participant DS as OrmDatasource
    participant GC as GridController

    B->>HK: GET /admin/brand/
    HK->>C: BrandController::indexAction [BrandController.php:25-35, @Route + @Template]
    C-->>HK: return [] (or ['entity_class' => ...])
    HK->>TW: @Template renders @OroProduct/Brand/index.html.twig
    TW->>TW: dataGrid.renderGrid('brand-grid') [DataGridBundle macros.html.twig:9]
    TW->>DGM: oro_datagrid_data(datagrid) [macros.html.twig:43, DataGridExtension.php:106,169]
    DGM->>DGM: getDatagrid(name) [Manager.php:53] reads datagrids.yml [RawConfigurationProvider.php:23]
    DGM->>DS: build query from source.query (DQL parts) + filters/sorters
    DS-->>DGM: getResults() [OrmDatasource.php:87]
    DGM-->>TW: first page embedded in data-page-component-options JSON [macros.html.twig:63-64,81]
    TW-->>B: HTML page incl. grid metadata + first page
    Note over B: user sorts or filters
    B->>GC: XHR GET /admin/datagrid/brand-grid?brand-grid[_sort_by]... [route oro_datagrid_index]
    GC->>DGM: getAction(gridName) [GridController.php:65]
    DGM->>DS: same pipeline, new parameters
    DGM-->>B: JSON (data + options), JS grid re-renders
```

**Key idea:** the admin side is "classic Symfony" (controller → Twig). The grid is **configured in YAML, not code**. The first page is server-rendered into the HTML, and every later sort or filter is an XHR to the generic `oro_datagrid_index` route. Magento analogy: a UI-component listing (`*_listing.xml` + `mui/index/render`). The difference is that Oro embeds the first page in the HTML, and there's no data-provider PHP class unless you need one.

### How to see it yourself
- DevTools → Network → sort a column → `datagrid/brand-grid?...` returns JSON.
- View source on `/admin/brand/` and search for `data-page-component-options`: it holds the grid `metadata` + first-page `data` JSON. (5.1.8 has no CLI command that dumps a grid's merged config.)

---

## D3. Extended field lifecycle (your `Product.pickupAvailable`)

```mermaid
flowchart TD
    A["Your migration in src/Acme/.../Migrations/Schema/v1_x<br/>$schema->getTable('oro_product')->addColumn('pickupAvailable', 'boolean',<br/>['oro_options' => ['extend' => [...]]])<br/>(pattern: PromotionBundle/.../v1_5/AddDisablePromotionsField.php:17-27)"]
    B["oro:migration:load<br/>• ALTER TABLE oro_product ADD pickupavailable<br/>• row added to oro_migrations"]
    C["Entity config updated<br/>oro_entity_config_field: extend.owner = Custom/System, is_extend = true"]
    D["Extend cache rebuilt<br/>var/cache/prod/oro_entities/Extend/Entity/{classes,aliases}.php<br/>+ Doctrine metadata gets the extra field"]
    E["Product (vendor, untouched)<br/>implements ExtendEntityInterface, uses ExtendEntityTrait<br/>[Product.php:173-182]"]
    F["$product->getPickupAvailable()<br/>handled by ExtendEntityTrait::__call [ExtendEntityTrait.php:139]<br/>(__get :84, __set :101)"]
    G["Shows up automatically:<br/>admin product form, view page, grids (if configured),<br/>import/export, API (if enabled)"]
    H["Storefront: you add a layout update in<br/>layouts/default/oro_product_frontend_product_view/*.yml<br/>(merged with ProductBundle's own updates, as in D1)"]
    A --> B --> C --> D
    E --> F
    D --> F --> G
    F --> H
```

**Key idea:** core entities are open for extension through **migrations + entity config**, not by subclassing or editing them. In 5.1 there are **no generated proxy classes for extend fields**. `ExtendEntityTrait` resolves `get*`/`set*` magically against metadata, and only a small class/alias map is cached.
Magento analogy: an EAV attribute added with a Data Patch. The difference is that Oro adds a **real column** to `oro_product` (no EAV value tables), and the field config (`extend`, `form`, `view`, `datagrid`, `dataaudit` scopes) lives in `oro_entity_config_field`.

⚠️ **Upgrade watch:** extend-entity internals are a likely place for 5.1 → 7.0 changes. Check `UPGRADE.md` for `ExtendEntityTrait`/extend changes in Phase 0C.

---

**EE note:** the request flow is the same in EE. Differences appear *inside* the steps: website resolution picks the theme and config per website (multi-website), ACL checks include organization ownership, the datagrid applies org/website scoping, and storefront product data usually comes from **Elasticsearch** (website search) rather than the ORM.
