# Destever Git management scope

## Git managed now

- `wp-content/themes/Avada-Child`
- `wp-content/mu-plugins/project-b-structure-sync.php`
- `tools/` validation and sync scripts
- `docs/` setup and workflow notes
- `docker/compose.example.yml`

## Keep out of Git

- Posts, pages, comments, and user-written content bodies
- `wp-content/uploads/`
- Cache, sessions, transient runtime files
- Database storage itself
- Secrets such as `.env` and `wp-config.php`
- WordPress core files

## Must be codeified next

- Critical navigation structure such as the `Blog` submenu
- Required category slugs and links used by theme logic
- Required page/template relationships such as `/oc-couples/`
- Avada or WordPress options that the site depends on structurally
- Deployment and sync steps

## Why the current issue happened

- The home query exclusion for `Done List` was a code change.
- The visible menu is at least partly driven by WordPress menu data in the database.
- File deployment alone could not fully repair a DB-backed menu structure.

## Practical rule

- If changing appearance or logic in PHP/CSS/JS, keep it in Git.
- If the site depends on a menu, page binding, or structural option to exist, add code that can recreate or repair it.
- Do not rely on one-off manual admin fixes for critical navigation or layout relationships.
