# Code Review Validation instructions

## Known false positives

Each of these looks like a finding but is deliberate or accepted in this repo:

- **"`phpcs:ignore` suppresses the warning."** It does not at Adobe's Marketplace review, which runs with
  `--ignore-annotations` (commit `9730297`). A finding that says an ignore comment makes unescaped output
  acceptable is wrong; the output must be escaped.
- **"MFTF tests hidden in a subfolder are skipped."** `Test/Mftf/Test/RequiresYotpoAccount/` is on purpose:
  MFTF still runs them in `yotpoSuite`; only Adobe's non-recursive listing skips them (ADR-0001).
- **"`yotpoSuite` / `EnableYotpoPlugin` / `DisableYotpoPlugin` not defined."** They come from
  `yotpo/module-yotpo-core`, not this repository.
- **"`RichSnippetFactory` / `CollectionFactory` class does not exist."** Magento generates factories at
  compile time.
- **"Template returns nothing when v3 is enabled."** Carousel, promoted products and reviews tab are
  v3-only; they render nothing without a synced instance id (`Block/Yotpo.php:503`).
- **"Magento review summary suppressed for the current product."** Intentional: the product page uses
  `bottomline.phtml` instead (`Plugin/Review/Block/Product/ReviewRenderer.php:38-47`).
- **"API errors are swallowed."** The storefront and admin must render without Yotpo; errors are logged
  and a default is returned. A finding stands only if an error can reach storefront rendering or is not
  logged at all.
- **"CSP is report-only."** `etc/config.xml:26-35` sets it deliberately; changing it is a human decision.
- **"`composer.json` pins core to one exact version."** Deliberate (ADR-0002).
- **"No unit tests."** The repository has none; that is the current state, not a regression in the PR.
- **The known issues in `docs/troubleshooting.md`** when the PR does not touch those lines.

## Always keep

Never filter these out, even if they look minor or cosmetic:

- a committed Yotpo app key, secret or token;
- unescaped output in a template or in plugin-built HTML;
- a `etc/db_schema.xml` change without its whitelist entry, or a dropped/renamed column;
- a new admin controller without `ADMIN_RESOURCE`;
- a CSP whitelist or CSP-mode change;
- a renamed config path, template, layout block or public helper method;
- version values that are not changed together (ADR-0002);
- an account-dependent MFTF test placed directly in `Test/Mftf/Test/`.

## Domain notes

- Nothing in this repository runs without a Magento installation; there is no CI. A verifier cannot run a
  check and must judge from the code.
- Classes are wired by XML: a class with no PHP importer can still be live through `etc/di.xml`, a layout
  XML file or `etc/adminhtml/system.xml`. Check those before calling code dead.
- `Model/Config.php` merges its arrays into core's (`Model/Config.php:96-97`); a config key or endpoint that
  is not in this file may come from core.
- `Block/Yotpo.php` backs every storefront template; a finding there has storefront-wide reach.
