# Login Recovery Log

Date: 2026-04-28

## Status

- The first login/register recovery attempt was not approved for deployment.
- A safer code-based recovery pass now exists in Git as:
  - `wp-content/mu-plugins/project-b-auth-pages.php`
- This pass recreates the public auth pages from code instead of relying on one-off manual DB fixes.

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

## Code-Based Recovery Added

The Mac recovery pass added a mu-plugin that ensures these pages exist:

- `/login/`
- `/register/`
- `/password-reset/`

It uses Avada/Fusion Builder auth shortcodes:

- `[fusion_login]`
- `[fusion_register]`
- `[fusion_lost_password]`

It also routes WordPress auth helpers to the custom pages:

- `wp_login_url()` -> `/login/`
- `wp_registration_url()` -> `/register/`
- `wp_lostpassword_url()` -> `/password-reset/`

Important limitation:

- This does not restore a deleted custom page design byte-for-byte.
- It recreates functional custom pages with Project B styling from code.
- If the exact previous design is found later in a DB backup or page history, replace the generated page contents or convert that design into this mu-plugin.
