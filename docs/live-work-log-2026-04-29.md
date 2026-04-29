## 2026-04-29

### Local Test Workflow

- Reworked `tools/local-test-site.ps1` so `refresh-db` now streams a Synology `mariadb-dump` over SSH into the local MariaDB container instead of copying raw DB files.
- Updated `tools/local-test-site.ps1` so `up` reuses the current local DB snapshot, seeds from Synology only when empty, and runs serialized-safe localhost URL replacement after startup.
- Added Synology SSH and DB settings support in `.local/.env` handling for the Windows local test workflow.
- Updated `tools/local-test-site-mac.sh` to match the localhost URL repair flow for the Mac runtime.
- Updated `docs/local-test-site.md` to document the new refresh/import workflow and required Synology environment variables.

### Avada Drift Cleanup

- Copied the live parent theme `Avada/header.php` into `wp-content/themes/Avada-Child/header.php` without modification so the child theme now owns the active custom header.
- Expanded `tools/sync-theme-to-live.ps1` so live deployment mirrors both `Avada-Child` and `mu-plugins`.
- Removed the unused live parent theme file `Avada/project-b-custom.php`.
- Confirmed `wp-content/mu-plugins/project-b-auth-pages.php` is absent on the live site and kept its deletion in Git so the tracked state matches production.
