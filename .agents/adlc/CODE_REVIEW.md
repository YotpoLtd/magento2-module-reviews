# Code Review instructions

## Also read

- `ARCHITECTURE.md` -- module map, the storefront / admin / sync flows, the v2 vs v3 widget switch, the
  boundary with `Yotpo_Core`.
- `docs/conventions.md` -- patterns, Do's and Don'ts, and the Anti-Patterns table.
- `docs/troubleshooting.md` -- the Marketplace review failures and the known issues listed at the end.
- `docs/adr/` -- when the change touches MFTF tests or suites (0001) or versions (0002).
- `.claude/protected-files.json` -- the criticality tiers; `human-required` is authoritative.

## Risk areas

Where a mistake here is expensive, and why:

- **Public package, installed by merchants.** This repository is public and installed through Composer and
  the Adobe Commerce Marketplace into stores Yotpo does not operate. A bug ships to every merchant who
  upgrades; there is no rollback on Yotpo's side.
- **Every storefront page.** `view/frontend/layout/default.xml` adds the widget blocks everywhere, and the
  plugins run inside Magento's product list and product page rendering. An exception or a slow call there
  breaks or slows the merchant's store.
- **Marketplace review.** Adobe runs phpcs with `--ignore-annotations` and MFTF in an environment without
  Yotpo credentials. Unescaped template output, two classes in one file, or an account-dependent test at
  the top of `Test/Mftf/Test/` fails the submission (commits `9730297`, `a63f4c2`).
- **Merchant data and contracts.** `etc/db_schema.xml` runs on every merchant database. Config paths,
  template names, layout block names and the public `Helper/Data.php` methods are used by merchants'
  themes and stored config.
- **Security headers and admin access.** `etc/csp_whitelist.xml` and the CSP defaults in `etc/config.xml`
  apply to every store; `etc/acl.xml` defines who may open the Yotpo admin pages.
- **Versioning.** `composer.json` `version`, the exact `yotpo/module-yotpo-core` pin and `etc/module.xml`
  `setup_version` move together (ADR-0002).

## Always check

| Condition | Severity |
|---|---|
| A Yotpo app key, secret, `utoken` or other credential committed anywhere, including MFTF data. Report the file and line only; never quote the value | critical |
| A template or plugin that prints a value without `escapeHtmlAttr` / `escapeUrl` / `escapeHtml`, or relies on `phpcs:ignore` / `@codingStandardsIgnoreLine` to pass | critical |
| A change to `etc/db_schema.xml` without the matching `etc/db_schema_whitelist.json` change, or a dropped or renamed column | critical |
| A new admin controller without `ADMIN_RESOURCE` (or with a resource missing from `etc/acl.xml`) | critical |
| Loosening or tightening `etc/csp_whitelist.xml`, or changing the CSP `report_only` defaults in `etc/config.xml` | critical |
| A renamed or removed config path, template, layout block name, or public `Helper/Data.php` / `Block/Yotpo.php` method | major |
| `composer.json` version, the core pin and `etc/module.xml` `setup_version` not changed together | major |
| A widening of the `php` constraint, or new syntax the `composer.json` constraint does not allow | major |
| A Yotpo API call from a template or from a storefront plugin without a cache, or without the `try/catch` + log pattern | major |
| An exception that can escape into storefront rendering (plugins, `Block/Yotpo.php`, templates) | major |
| An MFTF test that saves Yotpo credentials placed directly in `Test/Mftf/Test/` instead of `RequiresYotpoAccount/` | major |
| A second class, interface or enum in one PHP file | major |
| A new direct `ObjectManager::getInstance()` call | minor |
| A config read that bypasses `Model/Config.php` | minor |
| A nullable parameter default without a nullable type (`int $x = null`) -- deprecated in PHP 8.4 (commit `a59dba9`) | minor |

## Do not report

- **The known issues listed in `docs/troubleshooting.md`** (the `Helper/Data.php` template names, the
  `323232` log line, the concatenated HTML in `Plugin/AbstractYotpoReviewsSummary.php`, the missing
  `ADMIN_RESOURCE` on existing controllers, the README drift), unless the PR touches those lines. Flag the
  same pattern when a PR adds a new instance of it.
- **The existing `ObjectManager::getInstance()` in `Block/Yotpo.php:529`**, unless the PR touches it.
- **Missing unit tests.** The repository has no unit-test setup; a missing MFTF test for storefront
  behaviour is worth a note, not a blocker.
- **Formatting and code style.** There is no formatter or committed linter config.
- **The `_`-prefixed properties in `Plugin/AbstractYotpoReviewsSummary.php`.** Legacy naming, used by its
  subclasses.
- **`AGENTS.md`**, unless a PR replaces the symlink to `CLAUDE.md` with a real file.
