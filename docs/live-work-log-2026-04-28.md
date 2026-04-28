# Work Log - 2026-04-28

- Started structured recovery/debug pass for menu/theme issue.
- Goal: verify active theme/render path first, then apply only minimal safe changes.
- Attempted direct DB read of `template` / `stylesheet` / `current_theme`; no usable output in current local environment.
- Falling back to render-path confirmation via theme file inspection and current page behavior.
- Confirmed current visible overlay menu is rendered from parent theme [wp-content/themes/Avada/header.php].
- Found hardcoded `BLOG` submenu array there missing `일상` and `Done List`.
- Updated parent theme `BLOG` submenu list to: `전체 / 잡상노트 / 일상 / 캐나다 워홀 / Done List`.
- Confirmed active home template [wp-content/themes/Avada/front-page.php] already excludes `blog-done-list` posts from the main thumbnail stream.
- Added minimal parent-theme helper file [wp-content/themes/Avada/project-b-access.php] for role-based `Done List` access checks.
- Connected parent theme [wp-content/themes/Avada/functions.php] to load the new access helper only.
- Updated parent theme [wp-content/themes/Avada/header.php] so `Done List` menu appears only for `지인 / 앤오 / Admin`.
- Updated parent theme [wp-content/themes/Avada/category.php] so `blog-done-list` redirects guests to login and returns 404 for logged-in users without permission.
