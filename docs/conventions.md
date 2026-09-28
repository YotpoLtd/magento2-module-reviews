---
type: Conventions
title: magento2-module-reviews Conventions
timestamp: 2026-09-28T07:07:02Z
---

# Coding Conventions

## File Organization
Standard Magento 2 module layout; the directory is the namespace (`Yotpo\Reviews\<Dir>\...`, PSR-4 from the repo root, `composer.json:19-21`).
- Blocks in `Block/`, admin ones in `Block/Adminhtml/`; controllers only under `Controller/Adminhtml/<Group>/<Action>.php` (the module has no frontend routes)
- Plugins mirror the intercepted class: `Plugin/<MagentoModule>/Block/...` for `Magento\<MagentoModule>\Block\...`, registered in `etc/di.xml` (or `etc/adminhtml/di.xml` for admin-only)
- Templates in `view/<area>/templates/`, referenced as `Yotpo_Reviews::<path>.phtml`
- Canonical example: `Plugin/Catalog/Block/Product/ListProduct.php` + `etc/di.xml:3-5`

## Naming
| Thing | Convention | Example |
|-------|-----------|---------|
| Variables / properties | camelCase; legacy plugin properties keep a `_` prefix | `$yotpoConfig`, `$this->_yotpoConfig` (`Plugin/AbstractYotpoReviewsSummary.php:23`) |
| Functions | camelCase; boolean checks as `is*` / `isRender*` / `isV3*Widget` | `isRenderReviewsTab()` (`Block/Yotpo.php:333`) |
| Classes | PascalCase, one class per file (Marketplace phpcs rule, commit `9730297`) | `Block/WidgetsLocations.php` |
| Plugin methods | `before*` / `around*` + intercepted method | `aroundGetReviewsSummaryHtml` |
| Config keys | snake_case key in `$reviewsConfig`, path `yotpo/settings/<key>` or `yotpo/reviews/<key>` | `Model/Config.php:32-46` |
| DI plugin names | `yotpo_reviews_<module>_<class path>_plugin` | `etc/di.xml:4` |

## Error Handling
- Pattern: API calls are wrapped in `try/catch (\Exception $e)` and logged through `Model\Logger`; the caller gets an empty or default result, never an exception (`Model/Sync/RichSnippets.php:97-137`, `Model/Sync/Reviews/Processor.php:64-92`)
- Admin actions collect user-facing messages with `addMessage('success'|'error', ...)` and return them as JSON (`Model/Sync/WidgetV3InstanceIds/Processor.php:185`, `Controller/Adminhtml/SyncWidgetV3InstanceIds/Index.php:88-90`)
- Never: let an exception from a Yotpo call reach a storefront template; the page must render without Yotpo

## Logging
- What to log: API failures and sync outcomes, per store
- Levels: `info` is the only level used today
- Format: free text, translated with `__()` and `%1` placeholders
- Canonical example: `Model/Sync/WidgetV3InstanceIds/Processor.php:82-89`; the logger writes through core's general handler (`etc/di.xml:12-24`)

## Testing
- Unit tests: none; there is no `Test/Unit/` and no PHPUnit config
- Integration tests: none
- Functional tests: MFTF under `Test/Mftf/` (ActionGroup, Data, Section, Suite, Test). Tests in group `YotpoDisable` run in `yotpoDisableSuite` (this repo); group `Yotpo` runs in `yotpoSuite` (core), whose before-block enables Yotpo with real credentials
- Mocking strategy: none; MFTF drives a real Magento storefront and admin
- Fixture patterns: `createData` entities (`SimpleProduct`, `_defaultCategory`) in the test `before` block (`Test/Mftf/Test/DisableYotpoReviewsTest.xml:22-29`)
- Canonical example: `Test/Mftf/Test/DisableYotpoReviewsTest.xml`

## Do's and Don'ts
| Do (with file:line ref) | Don't | Why |
|--------------------------|-------|-----|
| Escape every template value: `escapeHtmlAttr` / `escapeUrl` / `escapeHtml` (`view/frontend/templates/widget_div.phtml:16-23`) | Print a block value raw and add `phpcs:ignore` | Adobe runs phpcs with `--ignore-annotations`, so the ignore does nothing and the Marketplace review fails (commit `9730297`) |
| One class, interface or enum per file (`Model/StarRatingSectionId.php`) | Add a second class to an existing file | `PSR1.Classes.ClassDeclaration.MultipleClasses` fails Marketplace review (commit `9730297`) |
| Read config through a `Model/Config.php` getter | Call `ScopeConfigInterface` directly in a block or template | Keeps paths and scope handling in one place (`Model/Config.php:29`) |
| Inject dependencies through the constructor (`Block/Conversion.php:42-53`) | Use `ObjectManager::getInstance()` | Bypasses DI; the one existing use (`Block/Yotpo.php:529`) is legacy, don't copy it |
| Return early from a template when the feature is off (`view/frontend/templates/reviews_tab.phtml:9-11`) | Render empty wrappers | Every widget template is on every page via `default.xml` |
| Put a test that needs a Yotpo account in `Test/Mftf/Test/RequiresYotpoAccount/` (ADR-0001) | Add it to `Test/Mftf/Test/*.xml` | Adobe's run lists only top-level files and has no Yotpo credentials |
| Bump `composer.json` version, the core pin and `etc/module.xml` together (ADR-0002) | Bump one of them | Merchants get a module that requires a core version it was not released with |
| Use nullable types for nullable defaults: `?int $scopeId = null` (`Model/Config.php:119`) | `int $scopeId = null` | Deprecated in PHP 8.4 (commit `a59dba9`) |

## Anti-Patterns

What agents should NEVER do in this repo:
| Anti-Pattern | Why It's Dangerous | See Also |
|--------------|-------------------|----------|
| Editing `etc/db_schema.xml` without the matching `etc/db_schema_whitelist.json` entry, or dropping/renaming a column | Declarative schema runs on every merchant database at `setup:upgrade`; a dropped column loses cached data and a missing whitelist entry breaks upgrades | `etc/db_schema.xml`, `etc/db_schema_whitelist.json` |
| Renaming a config path, template, public `Helper/Data.php` method or layout block name | Merchant themes and stored config reference them; the rename silently removes widgets | `Helper/Data.php`, `view/frontend/layout/default.xml` |
| Changing `etc/csp_whitelist.xml` or the CSP `report_only` defaults in `etc/config.xml` | Tightening breaks widgets on every store; loosening widens every merchant's CSP | `etc/csp_whitelist.xml`, `etc/config.xml:26-35` |
| Adding a new admin controller without `ADMIN_RESOURCE` | Any admin user can reach it; none of the existing controllers sets it, don't copy that | `etc/acl.xml`, `Controller/Adminhtml/**` |
| Building HTML by string concatenation with unescaped product data | XSS on the storefront; the existing case is `Plugin/AbstractYotpoReviewsSummary.php:56-70` | Do's and Don'ts above |
| Adding PHP 8.1+ syntax elsewhere while `composer.json` still claims PHP 5.6/7 | Hides the real requirement; decide the constraint first | `composer.json:10`, `Block/WidgetsLocations.php:7` |
| Committing a real Yotpo app key or secret to MFTF data | The repository is public | `Test/Mftf/Data/` |

## Patterns Library

### Config flag with admin field
- **When to use:** any new on/off setting.
- **Canonical implementation:** `reviews_tab_enabled` in `Model/Config.php:42,259`, `etc/config.xml:16`, `etc/adminhtml/system.xml:77-82`
- **How it works:** a key → path entry in `$reviewsConfig`, a typed getter, a default, and a Yes/No field with `config_path`.

### v3-aware widget template
- **When to use:** a new storefront widget.
- **Canonical implementation:** `view/frontend/templates/widget_div.phtml`
- **How it works:** return early unless enabled; render `yotpo-widget-instance` with the synced instance id when `isV3*Widget()`, otherwise the v2 `yotpo` markup.

### Around-plugin on a Magento review summary
- **When to use:** replacing Magento review output.
- **Canonical implementation:** `Plugin/Catalog/Block/Product/ListProduct.php:28-46`
- **How it works:** call `$proceed` when Yotpo is disabled; otherwise return Yotpo markup, or `''` when MDR hides Magento reviews.

### Store-scoped admin job
- **When to use:** an admin action that calls Yotpo per store.
- **Canonical implementation:** `Model/Sync/WidgetV3InstanceIds/Processor.php:61-94`
- **How it works:** extends core `AbstractJobs`; `emulateFrontendArea($storeId)`, call through `Model/Sync/Main`, `stopEnvironmentEmulation()`, collect messages.

# Citations

[1] Marketplace phpcs rules and `--ignore-annotations` (git commit `9730297`)
[2] MFTF split for Adobe's Marketplace run (git commit `a63f4c2`)
[3] Release bump pattern (git commit `0805255`)
[4] PHP 8.4 nullable-type fix (git commit `a59dba9`)
