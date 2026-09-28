---
type: ADR
title: ADR-0002 Release in lockstep with yotpo/module-yotpo-core
timestamp: 2026-09-28T07:07:02Z
---

# ADR-0002: Release in lockstep with yotpo/module-yotpo-core
- **Status:** Accepted
- **Date:** 2026-09-28 (recorded from the release history, e.g. commits `0805255`, `e6042c0`; the external commit `212b735` bumped only `composer.json`)

## Context
This module extends core classes directly (`Model/Config.php` extends `Yotpo\Core\Model\Config`, the
processors extend `Yotpo\Core\Model\AbstractJobs`, the logger extends core's), and its MFTF tests use core's
suite and action groups. A core change can break this module without any change here.

## Decision
Every release sets the same version in three places in one commit: `composer.json` `version`, the
`yotpo/module-yotpo-core` requirement (an exact version, not a range) and `etc/module.xml`
`setup_version`. A core release is followed by a reviews release at the same number.

## Alternatives Considered
- **A version range on core** — not used in the history; it would let merchants combine versions that were never tested together.

## Consequences
- Enables: merchants always get a tested reviews/core pair.
- Constrains: a core-only fix still needs a reviews version bump; the three values must never diverge.
