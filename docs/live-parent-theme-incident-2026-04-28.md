# Live Parent Theme Incident - 2026-04-28

## Summary

- During emergency debugging, some checks and temporary edits were applied directly on the live Synology mount under `wp-content/themes/Avada`.
- Those parent-theme edits were not adopted as the long-term direction.
- The parent-theme loading changes that caused fatal-error risk were rolled back on live.

## Important rule confirmed

- Do not use `wp-content/themes/Avada` as the customization target.
- Going forward, all custom code must live in:
  - `wp-content/themes/Avada-Child`
  - `wp-content/mu-plugins`

## Why this matters

- The Git repository currently tracks child-theme and mu-plugin code, not the customized parent theme.
- Direct live edits in the parent theme create drift between:
  - live Synology files
  - local Git working copy
  - GitHub history

## Resulting action

- Sync the current `Avada-Child` and `mu-plugins` state from live into the local repository.
- Preserve a written incident log in Git.
- Rebuild any remaining functionality from the local repository only.
