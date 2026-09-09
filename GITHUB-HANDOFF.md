# GitHub handoff — 9 September 2026

## Canonical repository state

The project is consolidated onto `main` from the complete known-working local development line. The pre-consolidation source was `feature/achievements-followup` at `fbbe851f57f8e09585e199f3e918a85044bd9224`; the old `main` at `a7c792f2` was an ancestor 134 commits behind.

All reviewed development branch tips were ancestors of the source line. Old branches must not be merged back over `main` after consolidation.

## Preservation additions in this pass

- Added the seven locally served font files referenced by the committed redesign CSS to `assets/fonts/` and `game-full/assets/fonts/`.
- Synchronized repository-root `index.php` with the already committed and actively served simplified homepage in `game-full/index.php`.
- Updated `README.md`, `HANDOFF.md`, `GITHUB-HANDOFF.md` and the authoritative header of `ROADMAP.md` for the September 2026 state.

No gameplay feature implementation, database mutation or VPS deployment occurred.

## Source layout

- Root website tree: deploys directly to the Apache DocumentRoot.
- `game-full/`: recovered legacy game/PHP/CDN tree and matching endpoint sources.
- `server/`: Node/SmartFox server.
- `electron/`: local PepperFlash client.
- `migrations/`: additive migration records; review before applying.
- `docs/` and the historical roadmap: preserved evidence and chronology.

When root and `game-full/` contain the same logical served path, compare exact files. Never replace an entire tree blindly.

## Release caveat

A GitHub consolidation checkpoint is not a claim that every gameplay path is release-tested. Preserve manual-test gates and known open issues. No VPS deployment has been performed.
