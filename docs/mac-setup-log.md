# Home Mac setup log

This file records the Mac-side setup so Codex sessions on both Windows and Mac can share the same operating context.

## 2026-04-28

### Goal

Move Mac development away from the CloudMounter/Synology WordPress mount and into a clean local Git clone, using GitHub as the coordination point between Windows and Mac.

### SSH and GitHub

- Created separate SSH identities on the Mac:
  - `~/.ssh/id_ed25519_work`
  - `~/.ssh/id_ed25519_personal`
- Configured SSH aliases:
  - `github-work`
  - `github-personal`
- Confirmed GitHub SSH authentication:
  - `ssh -T git@github-work`
  - `ssh -T git@github-personal`
- Registered Mac public keys in the matching GitHub accounts.

### Local clone

- Created the local working directory:
  `/Users/yien/Documents/work`
- Cloned the personal repository:
  `/Users/yien/Documents/work/destever-source`
- Confirmed the repository remote:
  `git@github-personal:yienshsss/destever.git`
- Confirmed the working tree was clean on `main`.

### Installed Mac tooling

Installed with Homebrew:

```bash
brew install php composer wp-cli
```

Verified versions:

```text
PHP 8.5.5
Composer 2.9.7
WP-CLI 2.12.0
```

Verified PHP extensions:

```text
curl
mbstring
mysqli
zip
```

WP-CLI note:

```text
WP-CLI runs successfully, but PHP 8.5.5 currently emits a deprecation warning from a bundled dependency.
```

### Operating rule

- Do all Mac edits in `/Users/yien/Documents/work/destever-source`.
- Do not use `/Users/yien/Library/CloudStorage/CloudMounter-NAS/docker/destever` as a Git working copy.
- Start each work session with `git pull`.
- Finish each work session with `git add -A`, `git commit`, and `git push`.

### Mac test runtime

- Added a Mac test runtime path:
  `/Volumes/KY 외장하드/SynologyDrive/04. 프로젝트/Codex/Destever test`
- Added Mac test compose file:
  `docker/compose.macos-test.yml`
- Added Mac test script:
  `tools/local-test-site-mac.sh`
- Intended local test URL:
  `http://localhost:8161`
- Docker Desktop for Mac is required before the test containers can be started.
