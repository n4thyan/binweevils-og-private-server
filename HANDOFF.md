# HANDOFF — 9 September 2026 main consolidation

## Read this first

Repository:

`C:\repos\binweevils-og-private-server`

The complete current development line through the full-RGB Weevil editor checkpoint was reviewed for consolidation onto `main`. Before consolidation, the clean source branch was:

- Branch: `feature/achievements-followup`
- SHA: `fbbe851f57f8e09585e199f3e918a85044bd9224`
- Previous `origin/main`: `a7c792f2970c9a6937ff22a8c270c90d4444e24c`
- Divergence: current branch was 134 commits ahead and 0 behind

Every reviewed local and remote development branch tip was already reachable from that current branch. No old branch needed to be merged over it.

After this consolidation, begin work from freshly fetched `origin/main`. Historical branch names in dated documents are not the current source of truth.

## Local/XAMPP preservation

The active Apache DocumentRoot is `C:\xampp\htdocs`.

A focused comparison of the 580 paths changed after the old `origin/main` found:

- 466 intended deployed counterparts byte-identical
- Seven live font dependencies referenced by `assets/css/site-redesign.css` were absent from Git and have now been preserved under both `assets/fonts/` and `game-full/assets/fonts/`
- The served simplified homepage matched `game-full/index.php`; repository-root `index.php` was stale and has been synchronized to that known-working version
- Five other served PHP copies were older than their repository counterparts and were not copied back because that would reintroduce superseded behavior
- `site/runtime-status.json` remains generated runtime state and is not source material
- Test-only files absent from the DocumentRoot are not deployment omissions

Do not copy the whole XAMPP tree into Git. Continue comparing exact logical counterparts.

## What the current line preserves

The consolidated history includes the accumulated work previously split across website, endpoint recovery, recon, quest, Bin Pets, loyalty, achievements, map/teleporter and follow-up branches. Important recent checkpoints include:

- Bin Pets and nest-inventory integration corrections
- XP award reconciliation and Prestige progression corrections
- Rewarded account activation
- Logged-in homepage simplification
- Current map and teleporter implementation/follow-up
- Achievement implementation line
- Advanced arbitrary-RGB Weevil appearance and Dosh's Palace randomisation

This consolidation session did not implement or alter gameplay behavior. It only preserved missing live dependencies, synchronized duplicate source, corrected status documentation, verified the tree and promoted the existing line.

## Verification boundary

Run and record focused existing checks during consolidation. Passing them means the promotion did not lose files or introduce syntax errors; it does not replace real Electron/PepperFlash testing.

Retain these explicit boundaries:

- Do not fabricate missing shop stock, rewards, IDs or room behavior.
- Keep lifetime XP (`users.xp`) separate from banked/spendable XP (`users.xp1`).
- Do not silently weaken request validation to satisfy a synthetic probe.
- Do not declare Bin Pets, achievements, the map, teleporter or appearance flows universally complete without the relevant manual client evidence.
- Known unrelated test failures must stay reported rather than being fixed during a consolidation pass.

## Deployment status

No VPS deployment was performed.

`main` is the canonical repository checkpoint after this pass, but public deployment still requires:

1. Fresh schema/migration review and backups.
2. Deliberate security review, including login/session-key replay protections.
3. Focused manual Electron/PepperFlash regression of release-critical paths.
4. Served-artifact verification in the target environment.

## Historical documentation

Dated files under `docs/` and the historical portion of `ROADMAP.md` intentionally retain earlier branch names, partial states, investigations and open questions. Treat them as chronology/evidence. The authoritative status at the top of `ROADMAP.md`, this handoff and `README.md` supersede stale operational instructions without erasing the history.
