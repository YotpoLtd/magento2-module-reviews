# Repository check instructions

## Environment

- **PHP 8.1 or newer.** Nothing in the repository pins a PHP version: `composer.json:10` allows
  `~5.6.0|^7.0|^8.0`, but `Block/WidgetsLocations.php:7` is a backed enum, so the code does not parse
  below PHP 8.1. Commit `a59dba9` fixed PHP 8.4 deprecations, so 8.4 is in use by merchants.
- **A Magento 2 installation with `yotpo/module-yotpo-core` at the version pinned in `composer.json:12`.**
  This is a module, not an application: it is installed into a Magento root with
  `composer require yotpo/module-yotpo-reviews` or under `app/code/Yotpo/` (`README.md:24-45`), and nothing
  in this repository compiles or runs on its own. There is no `composer.lock` and no `vendor/` here.
- TODO(ai-dlc): the agent image, and that it is not the runtime image. There is no agent image in this
  repository, and no runtime image either: the module runs inside the merchant's Magento.

## Always, on every changed file

**There is no formatter in this repository** -- no PHP-CS-Fixer, no EditorConfig, no `phpcs.xml`. Do not
add one, and do not reformat lines as a side effect of a fix.

TODO(ai-dlc): the linter, and whether a violation fails the build. There is no CI and no lint command in
the repository. `docs/Maintenance.md` names only the PhpStorm "Php Inspections (EA Extended)" plugin, an
IDE inspection with no command line. Adobe's Marketplace review runs phpcs with the Magento coding
standard and `--ignore-annotations` (commit `9730297`), but the exact command and standard version are not
written down in this repository.

```
TODO(ai-dlc): the command
```

TODO(ai-dlc): whether the linter can fix violations itself, and which repo-specific rules an agent is
most likely to trip. The two Marketplace rules this repository has already failed (commit `9730297`):

- `Magento2.Security.XssTemplate` -- every value printed in a `.phtml` goes through `$escaper->escapeHtmlAttr`,
  `escapeUrl` or `escapeHtml`. `phpcs:ignore` / `@codingStandardsIgnoreLine` do not count: Adobe runs with
  `--ignore-annotations`.
- `PSR1.Classes.ClassDeclaration.MultipleClasses` -- one class, interface or enum per file.

TODO(ai-dlc): where the linter config and its suppression list live. None is committed.

- **A violation your own edit introduced is part of the finding you are fixing** --
  iterate until it is clean. Never report a finding fixed while its checks are red, and
  never weaken or silence a check to get a commit through.
- **Pre-existing violations in code you did not touch are out of scope.** Leave them;
  fixing them widens the diff past the finding.
- **Reverting is the last resort, not the first move.** Only when the retry budget in
  the `code-review-fixer` skill is spent -- the rule is unclear, or satisfying it would
  change behaviour -- back that finding's edit out and report it unfixed, with the
  check's own message as the reason.

## By kind of change

Run the narrowest thing that covers what you touched.

| Change touches | Run |
|---|---|
| `Block/**`, `Controller/**`, `Helper/**`, `Model/**`, `Plugin/**`, `view/**`, `etc/**` | TODO(ai-dlc): no check is defined in CI, scripts or docs. The documented validation is installing the module into a Magento root and running `php bin/magento setup:upgrade` and `setup:di:compile` (`README.md:27-28`), which needs a Magento installation this environment does not have |
| `Test/Mftf/**` | nothing locally -- MFTF needs a Magento installation, a browser driver and, for `Test/Mftf/Test/RequiresYotpoAccount/**`, a live Yotpo account; see "Do not run" |
| `composer.json`, `registration.php`, `etc/module.xml` | TODO(ai-dlc): the full build -- a build-script change can break any area. None exists; these files are `human-required`, so a finding that needs them is out of scope |
| Markdown, `docs/**` only | nothing |

A change spanning several areas runs each area's row, not the full build.

## Full validation

```
bash .agents/adlc/repo-validation.sh
```

The canonical pre-merge run, and the single definition of "everything passed" -- see the
script's header for the exit-code contract. Not part of the fix loop: run it once at the
end of a pass.

## Do not run

- **Anything needing infrastructure this environment does not have** --
  integration or component suites that boot databases, brokers or other services. They
  belong to CI, not to a fix loop. Here: every `php bin/magento ...` command (`README.md:26-44`) and the
  MFTF suites (`yotpoDisableSuite` in `Test/Mftf/Suite/`, `yotpoSuite` from core), which need a Magento
  installation with a database, and a live Yotpo account for `Test/Mftf/Test/RequiresYotpoAccount/**`.
- **TODO(ai-dlc): dependency re-resolution** unless a dependency actually changed -- it
  re-fetches the whole graph over the network every time. No repo file names this repository's
  re-resolution command; `composer require yotpo/module-yotpo-reviews` (`README.md:25`) runs from a
  Magento root, not here.
- **Anything that writes to the default branch**, publishes an artifact, pushes an image,
  or triggers a deploy. Here: pushing a version tag or bumping `composer.json` `version` (Composer
  installs the published package from this public repository), and any Adobe Commerce Marketplace
  submission.
- **`git push --force`**, and any rebase to sync the branch.
