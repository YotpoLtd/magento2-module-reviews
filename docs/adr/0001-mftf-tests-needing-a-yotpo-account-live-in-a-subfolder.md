---
type: ADR
title: ADR-0001 MFTF tests that need a Yotpo account live in a subfolder
timestamp: 2026-09-28T07:07:02Z
---

# ADR-0001: MFTF tests that need a Yotpo account live in a subfolder
- **Status:** Accepted
- **Date:** 2026-09-15 (recorded from commits `a7515e5` and `a63f4c2`, PR #110)

## Context
Adobe runs this module's MFTF tests as part of the Marketplace submission. Its environment has no Yotpo
credentials (its `.credentials` holds only the Magento admin password) and lists tests with a
non-recursive `ls Test/Mftf/Test/*.xml`. The 13 tests in group `Yotpo` run in core's `yotpoSuite`, whose
before-block enables Yotpo with real credentials; saving them makes core validate them against Yotpo's
API, so every one of those tests errors in Adobe's environment. `yotpoDisableSuite` used to live in core,
so a core install without this module left its include empty and MFTF pulled in every Magento test.

## Decision
- Tests that need a live Yotpo account live in `Test/Mftf/Test/RequiresYotpoAccount/`. MFTF still finds
  them (`yotpoSuite` keeps all of them); Adobe's listing does not.
- Only tests that run with Yotpo disabled (group `YotpoDisable`) sit directly in `Test/Mftf/Test/`.
- This module owns `Test/Mftf/Suite/yotpoDisableSuite.xml`, whose before-block only disables Yotpo.

## Alternatives Considered
- **Keep all tests at the top level** — rejected: every account-dependent test fails Adobe's run.
- **Keep `yotpoDisableSuite` in core** — rejected: an empty include pulls in every Magento test.

## Consequences
- Enables a Marketplace run with no Yotpo credentials.
- Constrains: a new test that saves Yotpo credentials must go in the subfolder; the folder name is load-bearing.
- Tradeoff: account-dependent behaviour is covered only where someone runs `yotpoSuite` with real credentials.
