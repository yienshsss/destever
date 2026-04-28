# Destever source repo

This repository is the source of truth for the Destever site's custom code and deployment notes.

## Track in Git

- `wp-content/themes/Avada-Child`
- `tools/` validation and sync scripts
- `docs/` workflow and setup notes
- `docker/` example deployment config

## Keep out of Git

- WordPress core such as `wp-admin/` and `wp-includes/`
- Runtime data such as `db/`, Redis dumps, cache folders, and uploads
- Secrets such as `.env` and `wp-config.php`
- Large import and backup artifacts such as SQL dumps and XML exports

## Working rule

Always work from the local clone, pull before editing, and push after committing.
