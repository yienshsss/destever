# Login Recovery Log

Date: 2026-04-28

## Status

- This login/register recovery work is **not approved for deployment yet**.
- Do not commit, push, or sync the current login recovery code as-is.
- Current local working changes remain only in the local workspace.

## What Happened

- The original custom login/register connection appeared to be broken.
- Ultimate Member form records still exist in the database:
  - `100665` `Default Registration`
  - `100666` `Default Login`
  - `100667` `Default Profile`
- However, Ultimate Member core page options were empty, so the site was falling back to:
  - `wp-login.php`
  - `wp-login.php?action=register`

## Mistake To Avoid

- A temporary recovery attempt recreated default UM core pages:
  - `100699` `login`
  - `100700` `register`
  - `100701` `account`
  - `100702` `password-reset`
  - `100703` `members`
  - `100704` `user`
  - `100705` `logout`
- This restored functionality, but it did **not** restore the previous custom design/settings.
- These temporary pages should be treated as a diagnostic recovery result, not the final fix.

## Current Findings

- The previously used custom-styled login/register pages were not immediately found as surviving page records in the local DB.
- That means the old experience was likely one of:
  - a deleted/replaced WordPress page setup
  - a theme/template-driven custom screen
  - a setup that depended on older UM core page bindings

## Files Currently Modified Locally

- `wp-content/themes/Avada-Child/functions.php`
- `tools/sync-theme-to-live.ps1`

## Next Recommended Step

- Investigate whether the old login/register UX came from:
  - custom theme templates/styles
  - deleted WordPress pages in backup/history
  - Ultimate Member page bindings that used different page IDs before
- Restore the original design path first.
- Only after that, reconnect login/register URLs to the restored pages.
