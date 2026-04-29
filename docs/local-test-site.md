# Local Test Site

## Goal

Run a local WordPress test site from the Git working copy without editing the live Synology mount directly.

## What it does

- Copies live `wp-content` from `Z:\docker\destever\wp-content`
- Copies the live MariaDB data directory from `Z:\docker\destever\db`
- Skips runtime-heavy folders like uploads and cache
- Starts a separate Docker-based test site on `http://localhost:8160`

## Command

```powershell
cd C:\Users\nero_\OneDrive\Desktop\destever-source
powershell -ExecutionPolicy Bypass -File .\tools\local-test-site.ps1 up
```

## Other commands

```powershell
powershell -ExecutionPolicy Bypass -File .\tools\local-test-site.ps1 sync
powershell -ExecutionPolicy Bypass -File .\tools\local-test-site.ps1 status
powershell -ExecutionPolicy Bypass -File .\tools\local-test-site.ps1 logs
powershell -ExecutionPolicy Bypass -File .\tools\local-test-site.ps1 down
```

## Notes

- Local runtime data is stored under `.local/` and is ignored by Git.
- The local DB container runs with `--skip-grant-tables` so the copied Synology DB can open without needing the original container credentials.
- This is for testing only. Live deployment still uses the explicit sync script to `Z:\docker\destever`.
- Parent theme edits should still be avoided; use this environment to verify child-theme and mu-plugin behavior first.
