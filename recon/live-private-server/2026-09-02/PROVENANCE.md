# Provenance — live private-server recon 2026-09-02

## CAPTURE ORIGIN

These files were recovered from network behaviour of another live Bin Weevils private server on 2 September 2026.

The capture includes:
- A HAR file exported from a browser gameplay session
- Two WeevilTools SmartFox/SFS JSONL captures from the same session

## WHAT IS PRESERVED HERE

- Sanitized metadata: timestamps, endpoint paths, request parameters (excluding live credentials), response bodies that were embedded in the capture, packet counts, command inventories
- Asset URL inventories: SWF/image/XML references observed in traffic
- Request contracts: exact parameters sent to each endpoint
- Response fragments: only response bodies actually present in the capture

## WHAT IS NOT PRESERVED / IS REDACTED

- Raw cookies
- Session identifiers
- Authentication tokens
- Login password hashes / credentials
- Any other sensitive headers

The raw HAR and JSONL files remain LOCAL and UNTRACKED in this repository.

## DISTINCTION OF SOURCES

### OUR ORIGINAL RECOVERED ASSETS
These are files recovered from the Bin Weevils Flash client via JPEXS decompilation and SWF corpus scanning, performed on our own recovered binaries. They live under `game-full/`, `docs/CORE-ENDPOINT-AUDIT-2026-09-02.md`, and related paths on the `feature/core-endpoint-recovery` branch.

### LIVE PRIVATE-SERVER OBSERVATIONS
Everything under `recon/live-private-server/2026-09-02/` is observed behaviour from a third-party live server. It may reflect their own modifications, fixes, or additions. It is NOT canonical Bin Weevils data. Treat it as behavioural evidence only.

### NEWLY DOWNLOADED LIVE-SERVER ASSETS
No binaries were downloaded in this pass. All SWF references are URLs observed in the HAR. Download is a follow-up task.

## CAVEATS

- Response bodies are missing for most endpoints (849 of 911 HAR entries have no embedded body).
- The observed server may implement behaviour that differs from original Bin Weevils or from our recovered client contracts.
- No gameplay formulas, reward amounts, cooldowns, or schema were inferred from this capture.
- This recon is a starting point for further evidence gathering, not a complete contract specification.
