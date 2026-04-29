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

## Home Mac Command

The home Mac uses a separate test runtime folder on the external drive:

```text
/Volumes/KY 외장하드/SynologyDrive/04. 프로젝트/Codex/Destever test
```

Start the Mac test site from the Git clone:

```bash
cd /Users/yien/Documents/work/destever-source
./tools/local-test-site-mac.sh up
```

Other Mac commands:

```bash
./tools/local-test-site-mac.sh sync
./tools/local-test-site-mac.sh status
./tools/local-test-site-mac.sh logs
./tools/local-test-site-mac.sh down
./tools/local-test-site-mac.sh wp plugin list
```

The Mac test site uses:

```text
http://localhost:8161
```

Mac-specific notes:

- Docker Desktop for Mac must be installed and running before `up`, `status`, `logs`, `down`, or `wp`.
- The `sync` action overlays Git-managed files into the external-drive test runtime without Docker.
- Runtime test files stay on the external drive, not inside the Git clone.
- The Mac test runtime must already contain a full test snapshot under the external-drive folder:
  - `db/`
  - `wp-content/`
- At minimum, that `wp-content/` snapshot must include the Avada parent theme and active plugins such as Fusion Builder.
- The script does not read from the CloudMounter/NAS live mount.
- The script copies only Git-managed `wp-content` files from the clone onto the external-drive test runtime.
- The Mac test script removes the Redis `object-cache.php` drop-in from the test runtime so the local test site is not coupled to live Redis state.

## Mac Promotion Workflow

Use this order on the home Mac:

```bash
cd /Users/yien/Documents/work/destever-source
git pull --ff-only
./tools/local-test-site-mac.sh up
# verify http://localhost:8161
git add -A
git commit -m "Describe the change"
git push
```

Only after the test site passes should the change be deployed to the live Synology site. The live deployment step should sync only Git-managed theme and mu-plugin code, not uploads, database files, secrets, or cache.
