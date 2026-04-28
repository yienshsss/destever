# Destever workflow status

## Current source of truth

- Main working repository on this Windows machine:
  `C:\Users\nero_\OneDrive\Desktop\destever-source`
- Live WordPress mount currently used by Docker:
  `Z:\docker\destever`
- GitHub repository:
  `git@github-personal:yienshsss/destever.git`

## Current tracked project scope

- `wp-content/themes/Avada-Child`
- `tools/batch_style_check.py`
- `tools/batch_spelling_check.py`
- `tools/sync-theme-to-live.ps1`
- `tools/wp-docker.ps1`
- `docs/`
- `docker/compose.example.yml`

## Environment status on the Windows work machine

- Git installed
- Docker installed
- Node.js installed
- npm available
- Python 3.12 installed
- PHP 8.3 installed
- Composer installed
- WP-CLI installed
- PHP extensions enabled for local tooling:
  `mysqli`, `mbstring`, `curl`, `zip`

## Environment status on the home Mac

- Local source clone:
  `/Users/yien/Documents/work/destever-source`
- Previous CloudMounter/Synology WordPress mount:
  `/Users/yien/Library/CloudStorage/CloudMounter-NAS/docker/destever`
- Git installed:
  `git version 2.50.1 (Apple Git-155)`
- Node.js installed:
  `v24.11.1`
- npm available:
  `11.6.2`
- Python available:
  `Python 3.9.6`
- PHP installed with Homebrew:
  `PHP 8.5.5`
- Composer installed with Homebrew:
  `Composer 2.9.7`
- WP-CLI installed with Homebrew:
  `WP-CLI 2.12.0`
- PHP extensions enabled for local tooling:
  `mysqli`, `mbstring`, `curl`, `zip`
- Note: WP-CLI currently runs on PHP 8.5.5 but prints a PHP deprecation warning from one bundled dependency. The command still completes successfully.

## GitHub account split

- Work GitHub SSH alias: `github-work`
- Personal GitHub SSH alias: `github-personal`
- This repository must use the personal alias only.

## Non-negotiable rules

1. Do not work from `Z:\docker\destever-source` anymore.
2. Do not use the WebDAV drive as the Git working copy.
3. Do all editing from the local clone on Desktop.
4. Do not edit files directly on Synology except for emergency recovery.
5. Do not track WordPress core, database files, uploads, secrets, or cache in Git.
6. Before any edit session, run `git pull`.
7. After finishing work, run `git add -A`, `git commit`, and `git push`.
8. If the live site needs the latest theme locally, run the sync script after pulling or editing.

## Standard workflow

### Start work

```powershell
cd C:\Users\nero_\OneDrive\Desktop\destever-source
git pull
```

### Make changes

- Edit files directly in the local clone.
- Use Codex against the local clone, not the WebDAV folder.

### Save to GitHub

```powershell
git add -A
git commit -m "Describe the change"
git push
```

### Update the local live WordPress folder

```powershell
powershell -ExecutionPolicy Bypass -File .\tools\sync-theme-to-live.ps1
```

## Home Mac workflow

1. Work only from `/Users/yien/Documents/work/destever-source`.
2. Do not edit the CloudMounter/Synology mount directly except for emergency recovery.
3. Use the same rule: pull first, commit and push after changes.
4. The repository remote must stay on the personal alias:
   `git@github-personal:yienshsss/destever.git`

## Synology deployment rule

1. Pull the latest GitHub changes on Synology.
2. Sync only tracked theme and code assets into the live WordPress volume.
3. Do not overwrite uploads, database data, secrets, or cache.
