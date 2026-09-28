---
type: Troubleshooting
title: magento2-module-reviews Troubleshooting
timestamp: 2026-09-28T07:07:02Z
---

# Troubleshooting

## Common Issues

| Symptom | Cause | Fix |
|---------|-------|-----|
| No Yotpo widget on any page | `yotpo_core/settings/active` is off, or the app key / secret is not set for that store view (`Block/Yotpo.php:61`) | Stores → Configuration → Yotpo, on the store view (not Default Config, `README.md:51`); enter the app key and secret |
| v3 widgets not shown, v2 markup instead (or carousel / promoted products / reviews tab missing) | `v3_enabled` is off, or the instance ids were never synced (`Block/Yotpo.php:503`) | Enable V3 Widgets, click "Sync widgets to v3", flush the cache |
| "You haven't customized your widgets yet" after the v3 sync | Yotpo returned no `widget_instances`; the stored ids were deleted (`Model/Sync/WidgetV3InstanceIds/Processor.php:113-124`) | Customize the widgets in Yotpo, then sync again |
| "Store not found at API" after the v3 sync | The API call for that store failed | Check the store's app key and secret; see the Yotpo log from core's general handler |
| Magento's own reviews still shown next to Yotpo's | "Hide Adobe Commerce Reviews" (`mdr_enabled`) is off | Turn it on; `Plugin/Catalog/Block/Product/View/Details.php` removes `reviews.tab` |
| Star ratings on category pages but none on the product page summary | By design: `Plugin/Review/Block/Product/ReviewRenderer.php:38-47` returns `''` for the current product; the PDP uses `bottomline.phtml` | Check "Show Star Rating on product pages" |
| Widgets empty, browser console shows CSP reports | A Yotpo host outside `*.yotpo.com` / Google Fonts, or a store that enforces CSP | Extend `etc/csp_whitelist.xml` (human-required) |
| Admin report shows `-` for every metric | The metrics API call failed or returned no data; the error is only logged (`Model/Sync/Reviews/Processor.php:90-92`) | Check credentials for the selected store |
| Rich-snippet values stay at 0 | The bottomline API call failed; the result is not cached (`Model/Sync/RichSnippets.php:135-137`) | Check credentials; the next page load retries |
| Module fails to load on PHP 7.x | `Block/WidgetsLocations.php` is a PHP 8.1 enum, although `composer.json:10` allows older PHP | Run PHP 8.1+ |

## CI Failures

This repository has no CI workflow. The external gates are Adobe's Marketplace review:

| Error Message | Meaning | Resolution |
|--------------|---------|-----------|
| `Magento2.Security.XssTemplate` | A template prints a value without an escaper | Wrap it in `escapeHtmlAttr` / `escapeUrl` / `escapeHtml`; `phpcs:ignore` does not help, Adobe runs with `--ignore-annotations` (commit `9730297`) |
| `PSR1.Classes.ClassDeclaration.MultipleClasses` | Two classes, enums or interfaces in one file | Move the second one to its own file (commit `9730297`) |
| MFTF suite before-block error "Please make sure the APP KEY and SECRET you've entered are correct" | A test that needs a Yotpo account is in `Test/Mftf/Test/*.xml` and ran in Adobe's environment, which has no Yotpo credentials | Move it to `Test/Mftf/Test/RequiresYotpoAccount/` (ADR-0001) |
| MFTF pulls every Magento test into `yotpoDisableSuite` | The suite's `YotpoDisable` group is empty in that install | Keep the three `YotpoDisable` tests in this module (commit `a7515e5`) |

## Known issues (found during harness onboarding, not changed)

- `Helper/Data.php` renders templates that do not exist: `showQNA` uses `qna.phtml`, the carousel and promoted-products helpers use `carousel_*_enabled.phtml` / `promoted_products_*_enabled.phtml` (`Helper/Data.php:73,86,99,112,125,138,151`). The shipped templates are `questions_and_answers.phtml`, `reviews_carousel.phtml` and `promoted_products.phtml`. `showWidget`, `showBottomline` and `showReviewsTab` match real templates.
- `Model/Sync/Reviews/Processor.php:89` logs "API Issue - Reason is 323232" after every metrics call, including successful ones.
- `Plugin/AbstractYotpoReviewsSummary.php:56-70` builds star-rating HTML by string concatenation without escaping, and calls `getProductName()`, `getProductImageUrl()` and `getProductDescription()` on a catalog `Product`, which has no such methods (they resolve to empty magic getters). Not verified at runtime.
- No admin controller defines `ADMIN_RESOURCE`, so the ACL resources in `etc/acl.xml` are not enforced on them; any logged-in admin can open them.
- `README.md:36` says to install into `app/code/Yotpo/Yotpo`; the module is `Yotpo_Reviews`, so the conventional path is `app/code/Yotpo/Reviews`.
- `README.md:19` lists Magento 2.4.8 as the newest version; commit `2382dd9` bumped core for Magento 2.4.9.
- `docs/Maintenance.md` describes only an IDE inspection, not a command.
