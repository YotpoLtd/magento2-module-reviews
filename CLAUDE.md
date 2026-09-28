# magento2-module-reviews

## Purpose
The Yotpo Reviews extension for Magento 2 / Adobe Commerce, shipped as the Composer package `yotpo/module-yotpo-reviews` and registered as the Magento module `Yotpo_Reviews` (`composer.json:2`, `registration.php:4`). It renders the Yotpo reviews, star-rating, Q&A, carousel, promoted-products and reviews-tab widgets on the storefront, replaces Magento's own review summaries, sends order conversion data on the checkout success page, and adds Yotpo report and dashboard pages to the admin. It is a thin layer over `yotpo/module-yotpo-core` (`Yotpo_Core`), which owns credentials, the API client and the `yotpo_core` config section. The repository is public; merchants install it from Composer or the Adobe Commerce Marketplace. Owned by team Orbits (`CODEOWNERS`).

## Stack
- Language: PHP. `composer.json:10` allows `~5.6.0|^7.0|^8.0`, but `Block/WidgetsLocations.php:7` is a backed enum, so PHP 8.1+ is the real minimum
- Framework: Magento 2 (`magento/framework >=102.0.0`, `composer.json:11`); README lists Magento 2.4.8 for module 4.3.2+ (`README.md:19`)
- Runtime: inside a merchant's Magento installation; no service, no image
- Package manager: Composer (`composer.json`), PSR-4 `Yotpo\Reviews\` → repository root (`composer.json:19-21`)
- Key dependencies: `yotpo/module-yotpo-core`, pinned to the exact same version as this module (`composer.json:12`); Magento `Catalog`, `Review`, `Checkout`, `Sales`, `Backend`, `Csp`

## Directory Structure
Top-level only (run `tree -L 1` to verify if stale):
- `Block/`: storefront widget block (`Yotpo.php`), conversion block, admin report/dashboard/config-button blocks
- `Controller/Adminhtml/`: admin report page, dashboard tab, external redirects, widget v3 sync endpoint
- `Helper/`: public helpers merchants' themes call to render a widget anywhere (`Data.php`, `RichSnippets.php`)
- `Model/`: `Config.php` (all config reads), `Sync/` (Yotpo API calls through core), rich-snippet model and resource
- `Plugin/`: interceptors on Magento review/catalog/dashboard blocks
- `etc/`: module, DI, ACL, admin menu/routes/system config, CSP whitelist, declarative DB schema
- `view/`: layout XML, `.phtml` templates, LESS, images (`frontend/`, `adminhtml/`)
- `Test/Mftf/`: Magento Functional Testing Framework tests; `Test/RequiresYotpoAccount/` needs a live Yotpo account
- `docs/`: maintenance notes and harness docs

## Key Files
- `Model/Config.php:29`: every config path and API endpoint this module adds on top of `Yotpo\Core\Model\Config`
- `Block/Yotpo.php:16`: the block behind every storefront widget template; v2 vs v3 decision at `Block/Yotpo.php:503`
- `view/frontend/layout/default.xml`: places every storefront widget, on every page
- `view/frontend/templates/widget_script.phtml`: loads the Yotpo widget JavaScript
- `Plugin/AbstractYotpoReviewsSummary.php:52`: star-rating HTML for product lists and non-current products
- `Model/Sync/WidgetV3InstanceIds/Processor.php:61`: pulls v3 widget instance ids from Yotpo into config
- `Model/Sync/RichSnippets.php:88`: rich-snippet cache (`yotpo_rich_snippets`, 24 h TTL)
- `etc/di.xml`, `etc/adminhtml/di.xml`: which Magento blocks this module intercepts
- `etc/adminhtml/system.xml`: the Reviews Widgets admin settings (inside core's `yotpo_core` section)
- `composer.json`, `etc/module.xml`: the version, bumped together with the core pin

## Don't Touch
- `LICENSE.txt`, `LICENSE_AFL.txt`: the published licenses (OSL-3.0 / AFL-3.0)
- `etc/db_schema.xml`, `etc/db_schema_whitelist.json`: fragile. Declarative schema applied on every merchant database by `setup:upgrade`
- `Test/Mftf/Test/*.xml` vs `Test/Mftf/Test/RequiresYotpoAccount/`: fragile. Adobe's Marketplace run lists only the top-level files (commit `a63f4c2`); a test that needs Yotpo credentials must stay in the subfolder
- `composer.json` / `etc/module.xml` versions: fragile. Released only in lockstep with core (commit `0805255`)

## Quick Start
There is no build, test or lint command in this repository and no CI. The module is validated inside a Magento installation:

- **Install into Magento:** `composer require yotpo/module-yotpo-reviews`, then `php bin/magento setup:upgrade`, `setup:di:compile`, `setup:static-content:deploy`, `cache:flush` (`README.md:24-32`). Manual `app/code` install: `README.md:34-45`
- **Test:** MFTF tests in `Test/Mftf/` run from a Magento installation with MFTF; suite `yotpoDisableSuite` is in this repo, `yotpoSuite` and the Enable/Disable action groups come from core (commit `a7515e5`)
- **Lint:** only the PhpStorm "Php Inspections" plugin (`docs/Maintenance.md`). Adobe's Marketplace review runs phpcs with `--ignore-annotations` (commit `9730297`); the command is not in this repo
- **Checks per kind of change:** `.agents/adlc/REPO_CHECKS.md`

## Development Workflow (Spec-Driven)

> Requires: `superpowers` plugin. Install with `/plugin install superpowers@claude-plugins-official`

All feature work, bug fixes, and refactors MUST start with `/superpowers:brainstorming`.
Do NOT write code without first brainstorming and planning.
The Superpowers pipeline guides you through the rest (planning, execution, finishing).

Specs and plans MUST be committed to the feature branch and included in the PR.

## Common Patterns
- Config value: add the path to `$reviewsConfig` (`Model/Config.php:29`), a getter on `Model/Config.php`, a default in `etc/config.xml` and a field in `etc/adminhtml/system.xml`
- Storefront widget: a `Block\Yotpo` block in `view/frontend/layout/default.xml`, a template that returns early unless `isEnabled()` and its `isRender*()`, v3 markup when `isV3*Widget()` (`view/frontend/templates/reviews_tab.phtml`)
- Template output: always through `$escaper->escapeHtmlAttr/escapeUrl/escapeHtml`; `phpcs:ignore` does not count at Marketplace review (commit `9730297`)
- Yotpo API call: through `Model/Sync/Main.php:37` (core's `syncV1`), wrapped in `try/catch` that logs through `Model/Logger`
- Release: bump `composer.json` version, the `yotpo/module-yotpo-core` pin and `etc/module.xml` `setup_version` in one commit (commit `0805255`)
- Commits follow `type(scope):message` with no space after the colon (e.g. `feat(reviews):bump version`); PRs merge with merge commits

Full details: [docs/conventions.md](docs/conventions.md).

## Anti-Patterns
See [conventions.md](docs/conventions.md#anti-patterns) for the full list.

## Reference Docs
Read BEFORE starting work on the relevant area:
- [Architecture](ARCHITECTURE.md): system map, modules, boundaries, data flow, architectural decisions
- [Docs Index](docs/index.md): entry point for the docs/ folder (conventions, troubleshooting, and any other deep docs)
- Protected Files (`.claude/protected-files.json`): criticality tiers (human-required / review-required / auto-safe); CODEOWNERS mirrors the human-required tier and the agent files
- [Spec template](docs/specs/TEMPLATE.md): copy per feature. Specs are committed to the PR
