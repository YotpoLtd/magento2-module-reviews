# Blast Radius profile

## entryPoints

Magento wires everything through XML manifests; the PHP classes are reached from them, not from a router:

- DI plugins (framework manifest): `etc/di.xml` (storefront and admin) and `etc/adminhtml/di.xml` (admin only) -- `Plugin/Catalog/Block/Product/ListProduct.php`, `Plugin/Review/Block/Product/ReviewRenderer.php`, `Plugin/Catalog/Block/Product/View/Details.php`, `Plugin/Backend/Block/Dashboard/Grids.php`
- Storefront layout (framework manifest): `view/frontend/layout/default.xml` (every storefront page: `Block/Yotpo.php` with the widget templates) and `view/frontend/layout/checkout_onepage_success.xml` (`Block/Conversion.php`)
- Admin routes: `etc/adminhtml/routes.xml` (frontName `yotpo_reviews`) → `Controller/Adminhtml/**/*.php`; menu entries in `etc/adminhtml/menu.xml`; layout `view/adminhtml/layout/yotpo_reviews_report_reviews.xml`
- Admin configuration: `etc/adminhtml/system.xml` (fields and the `frontend_model` `Block/Adminhtml/System/Config/SyncWidgetV3InstanceIdsButton.php`), defaults in `etc/config.xml`
- Public helpers called from merchants' own theme templates: `Helper/Data.php`, `Helper/RichSnippets.php`
- Install-time: `registration.php`, `etc/module.xml`, `etc/db_schema.xml` (run by `setup:upgrade` on every merchant database)
- MFTF: `Test/Mftf/Suite/yotpoDisableSuite.xml` and `Test/Mftf/Test/**/*.xml`

## areas

- widgets: `Block/Yotpo.php`, `Block/WidgetsLocations.php`, `view/frontend/` -- storefront widget blocks, layouts, templates, LESS
- review-summary: `Plugin/` -- interceptors on Magento review and catalog blocks
- conversion: `Block/Conversion.php`, `view/frontend/templates/conversion.phtml`
- admin: `Block/Adminhtml/`, `Controller/Adminhtml/`, `view/adminhtml/`, `Model/Sync/Reviews/`, `Model/Sync/WidgetV3InstanceIds/`
- rich-snippets: `Model/Sync/RichSnippets.php`, `Model/RichSnippet.php`, `Model/ResourceModel/`, `Helper/RichSnippets.php`
- helpers: `Helper/Data.php`
- mftf: `Test/Mftf/`
- docs: `docs/`, `*.md`
- core: `Model/Config.php`, `Model/Sync/Main.php`, `Model/Logger.php`, `Model/Logger/`, `Model/StarRatingSectionId.php`, `etc/`, `composer.json`, `registration.php` -- every area reads config through `Model/Config.php` and calls Yotpo through `Model/Sync/Main.php`, so a change here is cross-cutting

## imports

No aliases. PSR-4 maps `Yotpo\Reviews\` to the repository root (`composer.json:19-21`), so `Yotpo\Reviews\Model\Sync\Main` is `Model/Sync/Main.php`. PHP files reference each other by fully-qualified name in `use` statements, often with an alias (`use Yotpo\Reviews\Model\Config as YotpoConfig;`), so search for the class name, not the alias. No barrel files.

Three things a text search for `use` misses:
- XML references: `etc/di.xml`, the layout XML (`class="Yotpo\Reviews\Block\Yotpo"`) and `etc/adminhtml/system.xml` (`frontend_model`) name classes by FQCN.
- Templates are referenced by module path: `Yotpo_Reviews::widget_div.phtml` → `view/<area>/templates/widget_div.phtml` (layout XML, `Helper/Data.php:31`, `$_template` properties). Admin URLs name controllers by route: `yotpo_reviews/syncwidgetv3instanceids/index` → `Controller/Adminhtml/SyncWidgetV3InstanceIds/Index.php`.
- Magento generates `*Factory` classes (`RichSnippetFactory`, `ResourceModel\RichSnippet\CollectionFactory`) at compile time; they are not in the tree. Base classes `Yotpo\Core\*` come from the `yotpo/module-yotpo-core` package, not from this repository.

## hotspots

- `Model/Config.php` -- every block, plugin, controller and processor reads config and endpoints through it
- `Block/Yotpo.php` -- backs every storefront widget template
- `view/frontend/layout/default.xml` -- adds the widget blocks to every storefront page
- `view/frontend/templates/widget_script.phtml` -- loads the Yotpo JavaScript on every storefront page
- `Plugin/AbstractYotpoReviewsSummary.php` -- the star-rating HTML for every product list and non-current product
- `Model/Sync/Main.php` -- every Yotpo API call from this module
- `etc/di.xml` -- every plugin and the logger wiring
- `etc/config.xml`, `etc/csp_whitelist.xml` -- defaults and CSP for every store
- `composer.json`, `etc/module.xml`, `registration.php` -- the package, its version and the core pin

## generatedFiles

None tracked. Magento writes interceptors and factories to the merchant's `generated/` directory at `setup:di:compile`; they are never in this repository.

## tables

### criticality

- 1.0: every storefront page or checkout -- `view/frontend/layout/default.xml`, `widget_script.phtml`, `Block/Yotpo.php`, `Model/Config.php`, `Plugin/**` (they run inside Magento's product list and PDP rendering; an exception breaks the page), `Block/Conversion.php` and `conversion.phtml` (order success page), `etc/csp_whitelist.xml`, `etc/config.xml`, `etc/di.xml`
- 0.7: admin pages and settings a merchant uses to configure Yotpo -- `Controller/Adminhtml/**`, `Block/Adminhtml/**`, `view/adminhtml/**`, `etc/adminhtml/**`, `etc/acl.xml`, `Model/Sync/WidgetV3InstanceIds/**`
- 0.4: cached or degraded-on-failure reads -- `Model/Sync/RichSnippets.php`, `Model/RichSnippet.php`, `Model/ResourceModel/**`, `Model/Sync/Reviews/Processor.php` (errors are logged, values fall back)
- 0.2: theme helpers used only where a merchant calls them -- `Helper/Data.php`, `Helper/RichSnippets.php`
- 0.1: never on a merchant's live path -- `Test/Mftf/**`, `docs/**`, `*.md`, `.gitignore`

### change-type

- 0.95: irreversible or trust-breaking -- `etc/db_schema.xml` / `etc/db_schema_whitelist.json` (applied to every merchant database), `composer.json` (published package, `version`, the exact core pin, the PHP constraint), `etc/module.xml` `setup_version` or `sequence`, `registration.php`, `etc/acl.xml`, `etc/csp_whitelist.xml` and the CSP defaults in `etc/config.xml`, a renamed config path / template / layout block / public `Helper/Data.php` method (merchant themes and stored config use them), unescaped output in a template, a committed Yotpo app key or secret, `.github/workflows/**`, `.agents/adlc/**`, `.claude/**`, `CODEOWNERS`
- 0.75: shared/core infrastructure -- the hotspots above, `etc/di.xml` / `etc/adminhtml/di.xml` plugin wiring, a new Magento module dependency, moving an MFTF test into or out of `Test/Mftf/Test/RequiresYotpoAccount/` (ADR-0001), `Test/Mftf/Suite/**`
- 0.50: non-trivial logic on a storefront path without test coverage -- a changed `isRender*` / `isV3*Widget` condition, plugin branching, the v3 instance-id parsing in `Model/Config.php:386`
- 0.30: contained feature work -- a new config flag, widget or admin action following the extension points in `ARCHITECTURE.md`
- 0.15: copy/translations, config defaults, log messages -- labels in `etc/adminhtml/system.xml`, `__()` strings, `.less` styling
- 0.05: comments, docs, formatting, dead-code removal -- `docs/**`, `*.md`, docblocks
